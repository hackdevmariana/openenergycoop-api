<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EnergyBond\StoreEnergyBondRequest;
use App\Http\Requests\Api\V1\EnergyBond\UpdateEnergyBondRequest;
use App\Http\Resources\Api\V1\EnergyBond\EnergyBondResource;
use App\Http\Resources\Api\V1\EnergyBond\EnergyBondCollection;
use App\Models\EnergyBond;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EnergyBondController extends Controller
{
    /**
     * Display a listing of energy bonds.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        try {
            $query = EnergyBond::query()
                ->with(['createdBy', 'approvedBy', 'managedBy', 'organization'])
                ->when($request->filled('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%")
                          ->orWhere('bond_type', 'like', "%{$search}%");
                    });
                })
                ->when($request->filled('bond_type'), function ($query, $bondType) {
                    $query->where('bond_type', $bondType);
                })
                ->when($request->filled('status'), function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($request->filled('is_public'), function ($query, $isPublic) {
                    $query->where('is_public', filter_var($isPublic, FILTER_VALIDATE_BOOLEAN));
                })
                ->when($request->filled('is_featured'), function ($query, $isFeatured) {
                    $query->where('is_featured', filter_var($isFeatured, FILTER_VALIDATE_BOOLEAN));
                })
                ->when($request->filled('min_face_value'), function ($query, $minValue) {
                    $query->where('face_value', '>=', $minValue);
                })
                ->when($request->filled('max_face_value'), function ($query, $maxValue) {
                    $query->where('face_value', '<=', $maxValue);
                })
                ->when($request->filled('min_interest_rate'), function ($query, $minRate) {
                    $query->where('interest_rate', '>=', $minRate);
                })
                ->when($request->filled('max_interest_rate'), function ($query, $maxRate) {
                    $query->where('interest_rate', '<=', $maxRate);
                })
                ->when($request->filled('maturity_date_from'), function ($query, $date) {
                    $query->where('maturity_date', '>=', $date);
                })
                ->when($request->filled('maturity_date_to'), function ($date) {
                    $query->where('maturity_date', '<=', $date);
                })
                ->when($request->filled('organization_id'), function ($query, $orgId) {
                    $query->where('organization_id', $orgId);
                })
                ->when($request->filled('sort_by'), function ($query, $sortBy) {
                    $direction = $request->get('sort_direction', 'asc');
                    $query->orderBy($sortBy, $direction);
                }, function ($query) {
                    $query->orderBy('created_at', 'desc');
                });

            $perPage = $request->get('per_page', 15);
            $bonds = $query->paginate($perPage);

            return EnergyBondCollection::make($bonds);
        } catch (\Exception $e) {
            Log::error('Error fetching energy bonds: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a newly created energy bond.
     */
    public function store(StoreEnergyBondRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $bond = EnergyBond::create($request->validated());
            
            // Set created_by to current user
            $bond->created_by = auth()->id();
            $bond->save();

            // Load relationships
            $bond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond created successfully', ['bond_id' => $bond->id]);

            return response()->json([
                'message' => 'Energy bond created successfully',
                'data' => EnergyBondResource::make($bond)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating energy bond: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display the specified energy bond.
     */
    public function show(EnergyBond $energyBond): JsonResponse
    {
        try {
            $energyBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            return response()->json([
                'data' => EnergyBondResource::make($energyBond)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy bond: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update the specified energy bond.
     */
    public function update(UpdateEnergyBondRequest $request, EnergyBond $energyBond): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyBond->update($request->validated());
            
            // Load relationships
            $energyBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond updated successfully', ['bond_id' => $energyBond->id]);

            return response()->json([
                'message' => 'Energy bond updated successfully',
                'data' => EnergyBondResource::make($energyBond)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy bond: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove the specified energy bond.
     */
    public function destroy(EnergyBond $energyBond): JsonResponse
    {
        try {
            DB::beginTransaction();

            $bondId = $energyBond->id;
            $energyBond->delete();

            DB::commit();

            Log::info('Energy bond deleted successfully', ['bond_id' => $bondId]);

            return response()->json([
                'message' => 'Energy bond deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting energy bond: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get public energy bonds.
     */
    public function publicBonds(Request $request): AnonymousResourceCollection
    {
        try {
            $query = EnergyBond::query()
                ->public()
                ->with(['organization'])
                ->when($request->filled('bond_type'), function ($query, $bondType) {
                    $query->where('bond_type', $bondType);
                })
                ->when($request->filled('min_face_value'), function ($query, $minValue) {
                    $query->where('face_value', '>=', $minValue);
                })
                ->when($request->filled('max_face_value'), function ($query, $maxValue) {
                    $query->where('face_value', '<=', $maxValue);
                })
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc');

            $perPage = $request->get('per_page', 12);
            $bonds = $query->paginate($perPage);

            return EnergyBondCollection::make($bonds);
        } catch (\Exception $e) {
            Log::error('Error fetching public energy bonds: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get featured energy bonds.
     */
    public function featuredBonds(): JsonResponse
    {
        try {
            $bonds = EnergyBond::query()
                ->featured()
                ->public()
                ->with(['organization'])
                ->limit(6)
                ->get();

            return response()->json([
                'data' => EnergyBondResource::collection($bonds)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching featured energy bonds: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Approve an energy bond.
     */
    public function approve(EnergyBond $energyBond): JsonResponse
    {
        try {
            if ($energyBond->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending bonds can be approved.'
                ]);
            }

            DB::beginTransaction();

            $energyBond->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $energyBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond approved successfully', ['bond_id' => $energyBond->id]);

            return response()->json([
                'message' => 'Energy bond approved successfully',
                'data' => EnergyBondResource::make($energyBond)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving energy bond: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reject an energy bond.
     */
    public function reject(Request $request, EnergyBond $energyBond): JsonResponse
    {
        try {
            $request->validate([
                'rejection_reason' => 'required|string|max:500'
            ]);

            if ($energyBond->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending bonds can be rejected.'
                ]);
            }

            DB::beginTransaction();

            $energyBond->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $energyBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond rejected successfully', ['bond_id' => $energyBond->id]);

            return response()->json([
                'message' => 'Energy bond rejected successfully',
                'data' => EnergyBondResource::make($energyBond)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting energy bond: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark energy bond as featured.
     */
    public function markFeatured(EnergyBond $energyBond): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyBond->update(['is_featured' => true]);

            $energyBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond marked as featured', ['bond_id' => $energyBond->id]);

            return response()->json([
                'message' => 'Energy bond marked as featured successfully',
                'data' => EnergyBondResource::make($energyBond)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking energy bond as featured: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove featured status from energy bond.
     */
    public function removeFeatured(EnergyBond $energyBond): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyBond->update(['is_featured' => false]);

            $energyBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond featured status removed', ['bond_id' => $energyBond->id]);

            return response()->json([
                'message' => 'Featured status removed successfully',
                'data' => EnergyBondResource::make($energyBond)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing featured status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make energy bond public.
     */
    public function makePublic(EnergyBond $energyBond): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyBond->update(['is_public' => true]);

            $energyBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond made public', ['bond_id' => $energyBond->id]);

            return response()->json([
                'message' => 'Energy bond made public successfully',
                'data' => EnergyBondResource::make($energyBond)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error making energy bond public: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make energy bond private.
     */
    public function makePrivate(EnergyBond $energyBond): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyBond->update(['is_public' => false]);

            $energyBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond made private', ['bond_id' => $energyBond->id]);

            return response()->json([
                'message' => 'Energy bond made private successfully',
                'data' => EnergyBondResource::make($energyBond)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error making energy bond private: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Duplicate an energy bond.
     */
    public function duplicate(EnergyBond $energyBond): JsonResponse
    {
        try {
            DB::beginTransaction();

            $newBond = $energyBond->replicate();
            $newBond->name = $newBond->name . ' (Copia)';
            $newBond->status = 'draft';
            $newBond->is_public = false;
            $newBond->is_featured = false;
            $newBond->approved_by = null;
            $newBond->approved_at = null;
            $newBond->created_by = auth()->id();
            $newBond->save();

            $newBond->load(['createdBy', 'approvedBy', 'managedBy', 'organization']);

            DB::commit();

            Log::info('Energy bond duplicated successfully', [
                'original_bond_id' => $energyBond->id,
                'new_bond_id' => $newBond->id
            ]);

            return response()->json([
                'message' => 'Energy bond duplicated successfully',
                'data' => EnergyBondResource::make($newBond)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating energy bond: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get energy bond statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_bonds' => EnergyBond::count(),
                'active_bonds' => EnergyBond::where('status', 'active')->count(),
                'pending_bonds' => EnergyBond::where('status', 'pending')->count(),
                'approved_bonds' => EnergyBond::where('status', 'approved')->count(),
                'rejected_bonds' => EnergyBond::where('status', 'rejected')->count(),
                'public_bonds' => EnergyBond::where('is_public', true)->count(),
                'featured_bonds' => EnergyBond::where('is_featured', true)->count(),
                'total_face_value' => EnergyBond::where('status', 'active')->sum('face_value'),
                'average_interest_rate' => EnergyBond::where('status', 'active')->avg('interest_rate'),
                'bonds_by_type' => EnergyBond::selectRaw('bond_type, COUNT(*) as count')
                    ->groupBy('bond_type')
                    ->pluck('count', 'bond_type'),
                'bonds_by_status' => EnergyBond::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ];

            return response()->json([
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching energy bond statistics: ' . $e->getMessage());
            throw $e;
        }
    }
}
