<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EnergyTransfer\StoreEnergyTransferRequest;
use App\Http\Requests\Api\V1\EnergyTransfer\UpdateEnergyTransferRequest;
use App\Http\Resources\Api\V1\EnergyTransfer\EnergyTransferCollection;
use App\Http\Resources\Api\V1\EnergyTransfer\EnergyTransferResource;
use App\Models\EnergyTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Energy Transfers",
 *     description="API Endpoints for Energy Transfer management"
 * )
 */
class EnergyTransferController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers",
     *     summary="List energy transfers",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", description="Items per page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Search term", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", description="Sort field", @OA\Schema(type="string")),
     *     @OA\Parameter(name="order", in="query", description="Sort order", @OA\Schema(type="string", enum={"asc", "desc"})),
     *     @OA\Parameter(name="transfer_type", in="query", description="Filter by transfer type", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="priority", in="query", description="Filter by priority", @OA\Schema(type="string")),
     *     @OA\Parameter(name="source_id", in="query", description="Filter by source ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="destination_id", in="query", description="Filter by destination ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="is_automated", in="query", description="Filter by automation status", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="requires_approval", in="query", description="Filter by approval requirement", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="is_approved", in="query", description="Filter by approval status", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="is_verified", in="query", description="Filter by verification status", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="currency", in="query", description="Filter by currency", @OA\Schema(type="string")),
     *     @OA\Parameter(name="min_amount", in="query", description="Minimum transfer amount", @OA\Schema(type="number")),
     *     @OA\Parameter(name="max_amount", in="query", description="Maximum transfer amount", @OA\Schema(type="number")),
     *     @OA\Parameter(name="min_efficiency", in="query", description="Minimum efficiency percentage", @OA\Schema(type="number")),
     *     @OA\Parameter(name="max_efficiency", in="query", description="Maximum efficiency percentage", @OA\Schema(type="number")),
     *     @OA\Parameter(name="scheduled_start_from", in="query", description="Scheduled start from date", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="scheduled_start_to", in="query", description="Scheduled start to date", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="scheduled_end_from", in="query", description="Scheduled end from date", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="scheduled_end_to", in="query", description="Scheduled end to date", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/EnergyTransferCollection")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyTransfer::query()
                ->with(['source', 'destination', 'sourceMeter', 'destinationMeter', 'scheduledBy', 'initiatedBy', 'approvedBy', 'verifiedBy', 'completedBy', 'createdBy']);

            // Filtros
            if ($request->filled('transfer_type')) {
                $query->byTransferType($request->transfer_type);
            }

            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }

            if ($request->filled('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->filled('source_id')) {
                $query->bySource($request->source_id, $request->source_type);
            }

            if ($request->filled('destination_id')) {
                $query->byDestination($request->destination_id, $request->destination_type);
            }

            if ($request->filled('source_meter_id')) {
                $query->bySourceMeter($request->source_meter_id);
            }

            if ($request->filled('destination_meter_id')) {
                $query->byDestinationMeter($request->destination_meter_id);
            }

            if ($request->filled('is_automated')) {
                $query->where('is_automated', $request->boolean('is_automated'));
            }

            if ($request->filled('requires_approval')) {
                $query->where('requires_approval', $request->boolean('requires_approval'));
            }

            if ($request->filled('is_approved')) {
                $query->where('is_approved', $request->boolean('is_approved'));
            }

            if ($request->filled('is_verified')) {
                $query->where('is_verified', $request->boolean('is_verified'));
            }

            if ($request->filled('currency')) {
                $query->byCurrency($request->currency);
            }

            if ($request->filled('min_amount') || $request->filled('max_amount')) {
                $minAmount = $request->get('min_amount', 0);
                $maxAmount = $request->get('max_amount', 999999999);
                $query->byAmountRange($minAmount, $maxAmount);
            }

            if ($request->filled('min_efficiency') || $request->filled('max_efficiency')) {
                $minEfficiency = $request->get('min_efficiency', 0);
                $maxEfficiency = $request->get('max_efficiency', 100);
                $query->byEfficiencyRange($minEfficiency, $maxEfficiency);
            }

            if ($request->filled('scheduled_start_from') || $request->filled('scheduled_start_to')) {
                $from = $request->get('scheduled_start_from');
                $to = $request->get('scheduled_start_to');
                if ($from && $to) {
                    $query->whereBetween('scheduled_start_time', [$from, $to]);
                } elseif ($from) {
                    $query->where('scheduled_start_time', '>=', $from);
                } elseif ($to) {
                    $query->where('scheduled_start_time', '<=', $to);
                }
            }

            if ($request->filled('scheduled_end_from') || $request->filled('scheduled_end_to')) {
                $from = $request->get('scheduled_end_from');
                $to = $request->get('scheduled_end_to');
                if ($from && $to) {
                    $query->whereBetween('scheduled_end_time', [$from, $to]);
                } elseif ($from) {
                    $query->where('scheduled_end_time', '>=', $from);
                } elseif ($to) {
                    $query->where('scheduled_end_time', '<=', $to);
                }
            }

            // Búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('transfer_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortField = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Paginación
            $limit = min($request->get('limit', 15), 100);
            $transfers = $query->paginate($limit);

            Log::info('Energy transfers retrieved', [
                'user_id' => auth()->id(),
                'filters' => $request->all(),
                'count' => $transfers->count()
            ]);

            return response()->json(new EnergyTransferCollection($transfers));
        } catch (\Exception $e) {
            Log::error('Error retrieving energy transfers', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error retrieving energy transfers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/energy-transfers",
     *     summary="Create a new energy transfer",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreEnergyTransferRequest")),
     *     @OA\Response(response=201, description="Energy transfer created successfully", @OA\JsonContent(ref="#/components/schemas/EnergyTransferResource")),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreEnergyTransferRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['created_by'] = auth()->id();

            $transfer = EnergyTransfer::create($data);

            DB::commit();

            Log::info('Energy transfer created', [
                'user_id' => auth()->id(),
                'transfer_id' => $transfer->id,
                'transfer_number' => $transfer->transfer_number
            ]);

            return response()->json([
                'message' => 'Energy transfer created successfully',
                'data' => new EnergyTransferResource($transfer)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating energy transfer', [
                'user_id' => auth()->id(),
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error creating energy transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/{id}",
     *     summary="Get energy transfer details",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Energy transfer ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/EnergyTransferResource")),
     *     @OA\Response(response=404, description="Energy transfer not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(EnergyTransfer $energyTransfer): JsonResponse
    {
        try {
            $energyTransfer->load(['source', 'destination', 'sourceMeter', 'destinationMeter', 'scheduledBy', 'initiatedBy', 'approvedBy', 'verifiedBy', 'completedBy', 'createdBy']);

            Log::info('Energy transfer retrieved', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id
            ]);

            return response()->json([
                'data' => new EnergyTransferResource($energyTransfer)
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving energy transfer', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error retrieving energy transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/energy-transfers/{id}",
     *     summary="Update energy transfer",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Energy transfer ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateEnergyTransferRequest")),
     *     @OA\Response(response=200, description="Energy transfer updated successfully", @OA\JsonContent(ref="#/components/schemas/EnergyTransferResource")),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Energy transfer not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateEnergyTransferRequest $request, EnergyTransfer $energyTransfer): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $energyTransfer->update($data);

            DB::commit();

            Log::info('Energy transfer updated', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id
            ]);

            return response()->json([
                'message' => 'Energy transfer updated successfully',
                'data' => new EnergyTransferResource($energyTransfer)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating energy transfer', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error updating energy transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/energy-transfers/{id}",
     *     summary="Delete energy transfer",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Energy transfer ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Energy transfer deleted successfully"),
     *     @OA\Response(response=404, description="Energy transfer not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(EnergyTransfer $energyTransfer): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyTransfer->delete();

            DB::commit();

            Log::info('Energy transfer deleted', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id
            ]);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting energy transfer', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error deleting energy transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/statistics",
     *     summary="Get energy transfer statistics",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total' => EnergyTransfer::count(),
                'by_status' => EnergyTransfer::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'by_type' => EnergyTransfer::selectRaw('transfer_type, COUNT(*) as count')
                    ->groupBy('transfer_type')
                    ->pluck('count', 'transfer_type'),
                'by_priority' => EnergyTransfer::selectRaw('priority, COUNT(*) as count')
                    ->groupBy('priority')
                    ->pluck('count', 'priority'),
                'total_amount_kwh' => EnergyTransfer::sum('transfer_amount_kwh'),
                'total_amount_mwh' => EnergyTransfer::sum('transfer_amount_mwh'),
                'average_efficiency' => EnergyTransfer::avg('efficiency_percentage'),
                'average_loss' => EnergyTransfer::avg('loss_percentage'),
                'total_cost' => EnergyTransfer::sum('total_cost'),
                'automated_count' => EnergyTransfer::where('is_automated', true)->count(),
                'manual_count' => EnergyTransfer::where('is_automated', false)->count(),
                'pending_approval' => EnergyTransfer::where('requires_approval', true)->where('is_approved', false)->count(),
                'verified_count' => EnergyTransfer::where('is_verified', true)->count(),
            ];

            return response()->json(['data' => $stats]);
        } catch (\Exception $e) {
            Log::error('Error retrieving energy transfer statistics', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/types",
     *     summary="Get transfer types",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function types(): JsonResponse
    {
        return response()->json(['data' => EnergyTransfer::getTransferTypes()]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/statuses",
     *     summary="Get transfer statuses",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function statuses(): JsonResponse
    {
        return response()->json(['data' => EnergyTransfer::getStatuses()]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/priorities",
     *     summary="Get transfer priorities",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function priorities(): JsonResponse
    {
        return response()->json(['data' => EnergyTransfer::getPriorities()]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/energy-transfers/{id}/update-status",
     *     summary="Update transfer status",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Energy transfer ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"status"}, @OA\Property(property="status", type="string"))),
     *     @OA\Response(response=200, description="Status updated successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateStatus(Request $request, EnergyTransfer $energyTransfer): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:' . implode(',', array_keys(EnergyTransfer::getStatuses()))
            ]);

            $oldStatus = $energyTransfer->status;
            $energyTransfer->update(['status' => $request->status]);

            Log::info('Energy transfer status updated', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return response()->json([
                'message' => 'Status updated successfully',
                'data' => new EnergyTransferResource($energyTransfer)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating energy transfer status', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error updating status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/energy-transfers/{id}/duplicate",
     *     summary="Duplicate energy transfer",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Energy transfer ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Energy transfer duplicated successfully"),
     *     @OA\Response(response=404, description="Energy transfer not found")
     * )
     */
    public function duplicate(EnergyTransfer $energyTransfer): JsonResponse
    {
        try {
            DB::beginTransaction();

            $newTransfer = $energyTransfer->replicate();
            $newTransfer->status = EnergyTransfer::STATUS_PENDING;
            $newTransfer->actual_start_time = null;
            $newTransfer->actual_end_time = null;
            $newTransfer->completion_time = null;
            $newTransfer->approved_at = null;
            $newTransfer->verified_at = null;
            $newTransfer->completed_at = null;
            $newTransfer->is_approved = false;
            $newTransfer->is_verified = false;
            $newTransfer->created_by = auth()->id();
            $newTransfer->save();

            DB::commit();

            Log::info('Energy transfer duplicated', [
                'user_id' => auth()->id(),
                'original_transfer_id' => $energyTransfer->id,
                'new_transfer_id' => $newTransfer->id
            ]);

            return response()->json([
                'message' => 'Energy transfer duplicated successfully',
                'data' => new EnergyTransferResource($newTransfer)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error duplicating energy transfer', [
                'user_id' => auth()->id(),
                'transfer_id' => $energyTransfer->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error duplicating energy transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/overdue",
     *     summary="Get overdue transfers",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function overdue(): JsonResponse
    {
        $transfers = EnergyTransfer::overdue()->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/due-soon",
     *     summary="Get transfers due soon",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="hours", in="query", description="Hours threshold", @OA\Schema(type="integer", default=24)),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function dueSoon(Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $transfers = EnergyTransfer::dueSoon($hours)->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/by-type/{type}",
     *     summary="Get transfers by type",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="type", in="path", required=true, description="Transfer type", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function byType(string $type): JsonResponse
    {
        $transfers = EnergyTransfer::byTransferType($type)->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/high-priority",
     *     summary="Get high priority transfers",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function highPriority(): JsonResponse
    {
        $transfers = EnergyTransfer::highPriority()->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/automated",
     *     summary="Get automated transfers",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function automated(): JsonResponse
    {
        $transfers = EnergyTransfer::automated()->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/manual",
     *     summary="Get manual transfers",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function manual(): JsonResponse
    {
        $transfers = EnergyTransfer::manual()->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/requires-approval",
     *     summary="Get transfers requiring approval",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function requiresApproval(): JsonResponse
    {
        $transfers = EnergyTransfer::requiresApproval()->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/approved",
     *     summary="Get approved transfers",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function approved(): JsonResponse
    {
        $transfers = EnergyTransfer::approved()->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/verified",
     *     summary="Get verified transfers",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function verified(): JsonResponse
    {
        $transfers = EnergyTransfer::verified()->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/by-entity/{entityType}/{entityId}",
     *     summary="Get transfers by entity",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="entityType", in="path", required=true, description="Entity type", @OA\Schema(type="string")),
     *     @OA\Parameter(name="entityId", in="path", required=true, description="Entity ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function byEntity(string $entityType, int $entityId): JsonResponse
    {
        $transfers = EnergyTransfer::where('source_type', $entityType)
            ->where('source_id', $entityId)
            ->orWhere('destination_type', $entityType)
            ->where('destination_id', $entityId)
            ->with(['source', 'destination'])
            ->paginate(15);
        
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/by-currency/{currency}",
     *     summary="Get transfers by currency",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="currency", in="path", required=true, description="Currency code", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function byCurrency(string $currency): JsonResponse
    {
        $transfers = EnergyTransfer::byCurrency($currency)->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/by-amount-range",
     *     summary="Get transfers by amount range",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="min", in="query", description="Minimum amount", @OA\Schema(type="number")),
     *     @OA\Parameter(name="max", in="query", description="Maximum amount", @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function byAmountRange(Request $request): JsonResponse
    {
        $min = $request->get('min', 0);
        $max = $request->get('max', 999999999);
        
        $transfers = EnergyTransfer::byAmountRange($min, $max)->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/energy-transfers/by-efficiency-range",
     *     summary="Get transfers by efficiency range",
     *     tags={"Energy Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="min", in="query", description="Minimum efficiency", @OA\Schema(type="number")),
     *     @OA\Parameter(name="max", in="query", description="Maximum efficiency", @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function byEfficiencyRange(Request $request): JsonResponse
    {
        $min = $request->get('min', 0);
        $max = $request->get('max', 100);
        
        $transfers = EnergyTransfer::byEfficiencyRange($min, $max)->with(['source', 'destination'])->paginate(15);
        return response()->json(new EnergyTransferCollection($transfers));
    }
}
