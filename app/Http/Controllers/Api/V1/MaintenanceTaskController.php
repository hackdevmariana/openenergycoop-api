<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MaintenanceTask\StoreMaintenanceTaskRequest;
use App\Http\Requests\Api\V1\MaintenanceTask\UpdateMaintenanceTaskRequest;
use App\Http\Resources\Api\V1\MaintenanceTask\MaintenanceTaskResource;
use App\Http\Resources\Api\V1\MaintenanceTask\MaintenanceTaskCollection;
use App\Models\MaintenanceTask;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MaintenanceTaskController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of maintenance tasks.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        try {
            $query = MaintenanceTask::query()
                ->with(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization'])
                ->when($request->filled('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%")
                          ->orWhere('task_type', 'like', "%{$search}%");
                    });
                })
                ->when($request->filled('task_type'), function ($query, $taskType) {
                    $query->where('task_type', $taskType);
                })
                ->when($request->filled('status'), function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($request->filled('priority'), function ($query, $priority) {
                    $query->where('priority', $priority);
                })
                ->when($request->filled('assigned_to'), function ($query, $assignedTo) {
                    $query->where('assigned_to', $assignedTo);
                })
                ->when($request->filled('equipment_id'), function ($query, $equipmentId) {
                    $query->where('equipment_id', $equipmentId);
                })
                ->when($request->filled('location_id'), function ($query, $locationId) {
                    $query->where('location_id', $locationId);
                })
                ->when($request->filled('is_overdue'), function ($query, $isOverdue) {
                    if (filter_var($isOverdue, FILTER_VALIDATE_BOOLEAN)) {
                        $query->overdue();
                    }
                })
                ->when($request->filled('due_date_from'), function ($query, $date) {
                    $query->where('due_date', '>=', $date);
                })
                ->when($request->filled('due_date_to'), function ($query, $date) {
                    $query->where('due_date', '<=', $date);
                })
                ->when($request->filled('estimated_hours_min'), function ($query, $hours) {
                    $query->where('estimated_hours', '>=', $hours);
                })
                ->when($request->filled('estimated_hours_max'), function ($query, $hours) {
                    $query->where('estimated_hours', '<=', $hours);
                })
                ->when($request->filled('estimated_cost_min'), function ($query, $cost) {
                    $query->where('estimated_cost', '>=', $cost);
                })
                ->when($request->filled('estimated_cost_max'), function ($query, $cost) {
                    $query->where('estimated_cost', '<=', $cost);
                })
                ->when($request->filled('sort_by'), function ($query, $sortBy) {
                    $direction = $request->get('sort_direction', 'asc');
                    $query->orderBy($sortBy, $direction);
                }, function ($query) {
                    $query->orderBy('due_date', 'asc');
                });

            $perPage = $request->get('per_page', 15);
            $tasks = $query->paginate($perPage);

            return MaintenanceTaskCollection::make($tasks);
        } catch (\Exception $e) {
            Log::error('Error fetching maintenance tasks: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a newly created maintenance task.
     */
    public function store(StoreMaintenanceTaskRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $task = MaintenanceTask::create($request->validated());
            
            // Set assigned_by to current user if not specified
            if (!$task->assigned_by) {
                $task->assigned_by = auth()->id();
                $task->save();
            }

            // Load relationships
            $task->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task created successfully', ['task_id' => $task->id]);

            return response()->json([
                'message' => 'Maintenance task created successfully',
                'data' => MaintenanceTaskResource::make($task)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display the specified maintenance task.
     */
    public function show(MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            return response()->json([
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update the specified maintenance task.
     */
    public function update(UpdateMaintenanceTaskRequest $request, MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            DB::beginTransaction();

            $maintenanceTask->update($request->validated());
            
            // Load relationships
            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task updated successfully', ['task_id' => $maintenanceTask->id]);

            return response()->json([
                'message' => 'Maintenance task updated successfully',
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove the specified maintenance task.
     */
    public function destroy(MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            DB::beginTransaction();

            $taskId = $maintenanceTask->id;
            $maintenanceTask->delete();

            DB::commit();

            Log::info('Maintenance task deleted successfully', ['task_id' => $taskId]);

            return response()->json([
                'message' => 'Maintenance task deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get overdue maintenance tasks.
     */
    public function overdueTasks(): JsonResponse
    {
        try {
            $tasks = MaintenanceTask::query()
                ->overdue()
                ->with(['assignedTo', 'equipment', 'location'])
                ->orderBy('due_date', 'asc')
                ->get();

            return response()->json([
                'data' => MaintenanceTaskResource::collection($tasks)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching overdue maintenance tasks: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get today's maintenance tasks.
     */
    public function todayTasks(): JsonResponse
    {
        try {
            $tasks = MaintenanceTask::query()
                ->whereDate('due_date', today())
                ->with(['assignedTo', 'equipment', 'location'])
                ->orderBy('priority', 'desc')
                ->orderBy('due_date', 'asc')
                ->get();

            return response()->json([
                'data' => MaintenanceTaskResource::collection($tasks)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching today\'s maintenance tasks: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get this week's maintenance tasks.
     */
    public function weekTasks(): JsonResponse
    {
        try {
            $tasks = MaintenanceTask::query()
                ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->with(['assignedTo', 'equipment', 'location'])
                ->orderBy('due_date', 'asc')
                ->orderBy('priority', 'desc')
                ->get();

            return response()->json([
                'data' => MaintenanceTaskResource::collection($tasks)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching week\'s maintenance tasks: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Start a maintenance task.
     */
    public function startTask(MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            if ($maintenanceTask->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending tasks can be started.'
                ]);
            }

            DB::beginTransaction();

            $maintenanceTask->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'actual_start_time' => now(),
            ]);

            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task started successfully', ['task_id' => $maintenanceTask->id]);

            return response()->json([
                'message' => 'Maintenance task started successfully',
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Complete a maintenance task.
     */
    public function completeTask(Request $request, MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            $request->validate([
                'completion_notes' => 'nullable|string|max:1000',
                'actual_hours' => 'nullable|numeric|min:0',
                'actual_cost' => 'nullable|numeric|min:0',
                'quality_score' => 'nullable|numeric|min:0|max:100',
            ]);

            if ($maintenanceTask->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'status' => 'Only in-progress tasks can be completed.'
                ]);
            }

            DB::beginTransaction();

            $maintenanceTask->update([
                'status' => 'completed',
                'completed_at' => now(),
                'actual_end_time' => now(),
                'completion_notes' => $request->completion_notes,
                'actual_hours' => $request->actual_hours,
                'actual_cost' => $request->actual_cost,
                'quality_score' => $request->quality_score,
            ]);

            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task completed successfully', ['task_id' => $maintenanceTask->id]);

            return response()->json([
                'message' => 'Maintenance task completed successfully',
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Pause a maintenance task.
     */
    public function pauseTask(Request $request, MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            $request->validate([
                'pause_reason' => 'required|string|max:500',
            ]);

            if ($maintenanceTask->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'status' => 'Only in-progress tasks can be paused.'
                ]);
            }

            DB::beginTransaction();

            $maintenanceTask->update([
                'status' => 'paused',
                'paused_at' => now(),
                'pause_reason' => $request->pause_reason,
            ]);

            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task paused successfully', ['task_id' => $maintenanceTask->id]);

            return response()->json([
                'message' => 'Maintenance task paused successfully',
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pausing maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Resume a paused maintenance task.
     */
    public function resumeTask(MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            if ($maintenanceTask->status !== 'paused') {
                throw ValidationException::withMessages([
                    'status' => 'Only paused tasks can be resumed.'
                ]);
            }

            DB::beginTransaction();

            $maintenanceTask->update([
                'status' => 'in_progress',
                'resumed_at' => now(),
                'pause_reason' => null,
            ]);

            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task resumed successfully', ['task_id' => $maintenanceTask->id]);

            return response()->json([
                'message' => 'Maintenance task resumed successfully',
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resuming maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel a maintenance task.
     */
    public function cancelTask(Request $request, MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            $request->validate([
                'cancellation_reason' => 'required|string|max:500',
            ]);

            if (!in_array($maintenanceTask->status, ['pending', 'in_progress', 'paused'])) {
                throw ValidationException::withMessages([
                    'status' => 'Only pending, in-progress, or paused tasks can be cancelled.'
                ]);
            }

            DB::beginTransaction();

            $maintenanceTask->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task cancelled successfully', ['task_id' => $maintenanceTask->id]);

            return response()->json([
                'message' => 'Maintenance task cancelled successfully',
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reassign a maintenance task.
     */
    public function reassignTask(Request $request, MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'reassignment_reason' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            $maintenanceTask->update([
                'assigned_to' => $request->assigned_to,
                'reassigned_at' => now(),
                'reassignment_reason' => $request->reassignment_reason,
            ]);

            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task reassigned successfully', ['task_id' => $maintenanceTask->id]);

            return response()->json([
                'message' => 'Maintenance task reassigned successfully',
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reassigning maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update task progress.
     */
    public function updateProgress(Request $request, MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            $request->validate([
                'progress_percentage' => 'required|numeric|min:0|max:100',
                'progress_notes' => 'nullable|string|max:1000',
                'estimated_completion_time' => 'nullable|date|after:now',
            ]);

            if ($maintenanceTask->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'status' => 'Only in-progress tasks can have progress updated.'
                ]);
            }

            DB::beginTransaction();

            $maintenanceTask->update([
                'progress_percentage' => $request->progress_percentage,
                'progress_notes' => $request->progress_notes,
                'estimated_completion_time' => $request->estimated_completion_time,
                'progress_updated_at' => now(),
            ]);

            $maintenanceTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task progress updated successfully', ['task_id' => $maintenanceTask->id]);

            return response()->json([
                'message' => 'Task progress updated successfully',
                'data' => MaintenanceTaskResource::make($maintenanceTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating maintenance task progress: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Duplicate a maintenance task.
     */
    public function duplicate(MaintenanceTask $maintenanceTask): JsonResponse
    {
        try {
            DB::beginTransaction();

            $newTask = $maintenanceTask->replicate();
            $newTask->title = $newTask->title . ' (Copia)';
            $newTask->status = 'pending';
            $newTask->started_at = null;
            $newTask->completed_at = null;
            $newTask->paused_at = null;
            $newTask->cancelled_at = null;
            $newTask->progress_percentage = 0;
            $newTask->actual_hours = null;
            $newTask->actual_cost = null;
            $newTask->quality_score = null;
            $newTask->assigned_by = auth()->id();
            $newTask->save();

            $newTask->load(['assignedTo', 'assignedBy', 'equipment', 'location', 'schedule', 'organization']);

            DB::commit();

            Log::info('Maintenance task duplicated successfully', [
                'original_task_id' => $maintenanceTask->id,
                'new_task_id' => $newTask->id
            ]);

            return response()->json([
                'message' => 'Maintenance task duplicated successfully',
                'data' => MaintenanceTaskResource::make($newTask)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating maintenance task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get maintenance task statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_tasks' => MaintenanceTask::count(),
                'pending_tasks' => MaintenanceTask::where('status', 'pending')->count(),
                'in_progress_tasks' => MaintenanceTask::where('status', 'in_progress')->count(),
                'completed_tasks' => MaintenanceTask::where('status', 'completed')->count(),
                'paused_tasks' => MaintenanceTask::where('status', 'paused')->count(),
                'cancelled_tasks' => MaintenanceTask::where('status', 'cancelled')->count(),
                'overdue_tasks' => MaintenanceTask::overdue()->count(),
                'high_priority_tasks' => MaintenanceTask::whereIn('priority', ['high', 'urgent', 'critical'])->count(),
                'total_estimated_hours' => MaintenanceTask::where('status', '!=', 'cancelled')->sum('estimated_hours'),
                'total_actual_hours' => MaintenanceTask::where('status', 'completed')->sum('actual_hours'),
                'total_estimated_cost' => MaintenanceTask::where('status', '!=', 'cancelled')->sum('estimated_cost'),
                'total_actual_cost' => MaintenanceTask::where('status', 'completed')->sum('actual_cost'),
                'average_quality_score' => MaintenanceTask::where('status', 'completed')->avg('quality_score'),
                'tasks_by_type' => MaintenanceTask::selectRaw('task_type, COUNT(*) as count')
                    ->groupBy('task_type')
                    ->pluck('count', 'task_type'),
                'tasks_by_status' => MaintenanceTask::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'tasks_by_priority' => MaintenanceTask::selectRaw('priority, COUNT(*) as count')
                    ->groupBy('priority')
                    ->pluck('count', 'priority'),
            ];

            return response()->json([
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching maintenance task statistics: ' . $e->getMessage());
            throw $e;
        }
    }
}
