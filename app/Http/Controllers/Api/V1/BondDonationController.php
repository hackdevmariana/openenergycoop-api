<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BondDonation\StoreBondDonationRequest;
use App\Http\Requests\Api\V1\BondDonation\UpdateBondDonationRequest;
use App\Http\Resources\Api\V1\BondDonation\BondDonationResource;
use App\Http\Resources\Api\V1\BondDonation\BondDonationCollection;
use App\Models\BondDonation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BondDonationController extends Controller
{
    /**
     * Display a listing of bond donations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        try {
            $query = BondDonation::query()
                ->with(['donor', 'energyBond', 'organization', 'campaign'])
                ->when($request->filled('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('donor_name', 'like', "%{$search}%")
                          ->orWhere('donor_email', 'like', "%{$search}%")
                          ->orWhere('message', 'like', "%{$search}%");
                    });
                })
                ->when($request->filled('donation_type'), function ($query, $type) {
                    $query->where('donation_type', $type);
                })
                ->when($request->filled('status'), function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($request->filled('energy_bond_id'), function ($query, $bondId) {
                    $query->where('energy_bond_id', $bondId);
                })
                ->when($request->filled('organization_id'), function ($query, $orgId) {
                    $query->where('organization_id', $orgId);
                })
                ->when($request->filled('campaign_id'), function ($query, $campaignId) {
                    $query->where('campaign_id', $campaignId);
                })
                ->when($request->filled('is_anonymous'), function ($query, $isAnonymous) {
                    if (filter_var($isAnonymous, FILTER_VALIDATE_BOOLEAN)) {
                        $query->where('is_anonymous', true);
                    }
                })
                ->when($request->filled('amount_min'), function ($query, $amount) {
                    $query->where('amount', '>=', $amount);
                })
                ->when($request->filled('amount_max'), function ($query, $amount) {
                    $query->where('amount', '<=', $amount);
                })
                ->when($request->filled('donation_date_from'), function ($query, $date) {
                    $query->where('donation_date', '>=', $date);
                })
                ->when($request->filled('donation_date_to'), function ($query, $date) {
                    $query->where('donation_date', '<=', $date);
                })
                ->when($request->filled('sort_by'), function ($query, $sortBy) {
                    $direction = $request->get('sort_direction', 'desc');
                    $query->orderBy($sortBy, $direction);
                }, function ($query) {
                    $query->orderBy('donation_date', 'desc');
                });

            $perPage = $request->get('per_page', 15);
            $donations = $query->paginate($perPage);

            return BondDonationCollection::make($donations);
        } catch (\Exception $e) {
            Log::error('Error fetching bond donations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a newly created bond donation.
     */
    public function store(StoreBondDonationRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $donation = BondDonation::create($request->validated());
            
            // Set donor_id to current user if not specified
            if (!$donation->donor_id && auth()->check()) {
                $donation->donor_id = auth()->id();
                $donation->save();
            }

            // Load relationships
            $donation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation created successfully', ['donation_id' => $donation->id]);

            return response()->json([
                'message' => 'Bond donation created successfully',
                'data' => BondDonationResource::make($donation)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display the specified bond donation.
     */
    public function show(BondDonation $bondDonation): JsonResponse
    {
        try {
            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            return response()->json([
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update the specified bond donation.
     */
    public function update(UpdateBondDonationRequest $request, BondDonation $bondDonation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $bondDonation->update($request->validated());
            
            // Load relationships
            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation updated successfully', ['donation_id' => $bondDonation->id]);

            return response()->json([
                'message' => 'Bond donation updated successfully',
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove the specified bond donation.
     */
    public function destroy(BondDonation $bondDonation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $donationId = $bondDonation->id;
            $bondDonation->delete();

            DB::commit();

            Log::info('Bond donation deleted successfully', ['donation_id' => $donationId]);

            return response()->json([
                'message' => 'Bond donation deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get public bond donations (for display on website).
     */
    public function publicDonations(Request $request): AnonymousResourceCollection
    {
        try {
            $query = BondDonation::query()
                ->where('is_public', true)
                ->where('status', 'confirmed')
                ->with(['energyBond', 'organization'])
                ->when($request->filled('energy_bond_id'), function ($query, $bondId) {
                    $query->where('energy_bond_id', $bondId);
                })
                ->when($request->filled('organization_id'), function ($query, $orgId) {
                    $query->where('organization_id', $orgId);
                })
                ->when($request->filled('campaign_id'), function ($query, $campaignId) {
                    $query->where('campaign_id', $campaignId);
                })
                ->orderBy('donation_date', 'desc');

            $perPage = $request->get('per_page', 20);
            $donations = $query->paginate($perPage);

            return BondDonationCollection::make($donations);
        } catch (\Exception $e) {
            Log::error('Error fetching public bond donations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get recent bond donations.
     */
    public function recentDonations(): JsonResponse
    {
        try {
            $donations = BondDonation::query()
                ->where('is_public', true)
                ->where('status', 'confirmed')
                ->with(['energyBond', 'organization'])
                ->orderBy('donation_date', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'data' => BondDonationResource::collection($donations)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching recent bond donations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get top donors.
     */
    public function topDonors(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $period = $request->get('period', 'all'); // all, month, year

            $query = BondDonation::query()
                ->selectRaw('donor_id, donor_name, SUM(amount) as total_donated, COUNT(*) as donation_count')
                ->where('status', 'confirmed')
                ->groupBy('donor_id', 'donor_name');

            if ($period === 'month') {
                $query->where('donation_date', '>=', now()->startOfMonth());
            } elseif ($period === 'year') {
                $query->where('donation_date', '>=', now()->startOfYear());
            }

            $topDonors = $query->orderBy('total_donated', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => $topDonors,
                'period' => $period,
                'total_donors' => $topDonors->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching top donors: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Confirm a bond donation.
     */
    public function confirm(BondDonation $bondDonation): JsonResponse
    {
        try {
            if ($bondDonation->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending donations can be confirmed.'
                ]);
            }

            DB::beginTransaction();

            $bondDonation->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id(),
            ]);

            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation confirmed successfully', ['donation_id' => $bondDonation->id]);

            return response()->json([
                'message' => 'Bond donation confirmed successfully',
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reject a bond donation.
     */
    public function reject(Request $request, BondDonation $bondDonation): JsonResponse
    {
        try {
            $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            if ($bondDonation->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending donations can be rejected.'
                ]);
            }

            DB::beginTransaction();

            $bondDonation->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation rejected successfully', ['donation_id' => $bondDonation->id]);

            return response()->json([
                'message' => 'Bond donation rejected successfully',
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process a bond donation (mark as processed).
     */
    public function process(BondDonation $bondDonation): JsonResponse
    {
        try {
            if ($bondDonation->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'status' => 'Only confirmed donations can be processed.'
                ]);
            }

            DB::beginTransaction();

            $bondDonation->update([
                'status' => 'processed',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ]);

            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation processed successfully', ['donation_id' => $bondDonation->id]);

            return response()->json([
                'message' => 'Bond donation processed successfully',
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Refund a bond donation.
     */
    public function refund(Request $request, BondDonation $bondDonation): JsonResponse
    {
        try {
            $request->validate([
                'refund_reason' => 'required|string|max:500',
                'refund_amount' => 'required|numeric|min:0|max:' . $bondDonation->amount,
            ]);

            if (!in_array($bondDonation->status, ['confirmed', 'processed'])) {
                throw ValidationException::withMessages([
                    'status' => 'Only confirmed or processed donations can be refunded.'
                ]);
            }

            DB::beginTransaction();

            $bondDonation->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refunded_by' => auth()->id(),
                'refund_reason' => $request->refund_reason,
                'refund_amount' => $request->refund_amount,
            ]);

            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation refunded successfully', ['donation_id' => $bondDonation->id]);

            return response()->json([
                'message' => 'Bond donation refunded successfully',
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error refunding bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make a donation public.
     */
    public function makePublic(BondDonation $bondDonation): JsonResponse
    {
        try {
            if ($bondDonation->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'status' => 'Only confirmed donations can be made public.'
                ]);
            }

            DB::beginTransaction();

            $bondDonation->update([
                'is_public' => true,
                'made_public_at' => now(),
                'made_public_by' => auth()->id(),
            ]);

            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation made public successfully', ['donation_id' => $bondDonation->id]);

            return response()->json([
                'message' => 'Bond donation made public successfully',
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error making bond donation public: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make a donation private.
     */
    public function makePrivate(BondDonation $bondDonation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $bondDonation->update([
                'is_public' => false,
                'made_private_at' => now(),
                'made_private_by' => auth()->id(),
            ]);

            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation made private successfully', ['donation_id' => $bondDonation->id]);

            return response()->json([
                'message' => 'Bond donation made private successfully',
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error making bond donation private: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send thank you message for a donation.
     */
    public function sendThankYou(Request $request, BondDonation $bondDonation): JsonResponse
    {
        try {
            $request->validate([
                'thank_you_message' => 'required|string|max:1000',
                'send_email' => 'boolean',
            ]);

            if ($bondDonation->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'status' => 'Only confirmed donations can receive thank you messages.'
                ]);
            }

            DB::beginTransaction();

            $bondDonation->update([
                'thank_you_message' => $request->thank_you_message,
                'thank_you_sent_at' => now(),
                'thank_you_sent_by' => auth()->id(),
                'thank_you_email_sent' => $request->get('send_email', false),
            ]);

            $bondDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Thank you message sent for bond donation', ['donation_id' => $bondDonation->id]);

            return response()->json([
                'message' => 'Thank you message sent successfully',
                'data' => BondDonationResource::make($bondDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error sending thank you message: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Duplicate a bond donation.
     */
    public function duplicate(BondDonation $bondDonation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $newDonation = $bondDonation->replicate();
            $newDonation->donation_date = now();
            $newDonation->status = 'pending';
            $newDonation->confirmed_at = null;
            $newDonation->confirmed_by = null;
            $newDonation->rejected_at = null;
            $newDonation->rejected_by = null;
            $newDonation->rejection_reason = null;
            $newDonation->processed_at = null;
            $newDonation->processed_by = null;
            $newDonation->refunded_at = null;
            $newDonation->refunded_by = null;
            $newDonation->refund_reason = null;
            $newDonation->refund_amount = null;
            $newDonation->thank_you_message = null;
            $newDonation->thank_you_sent_at = null;
            $newDonation->thank_you_sent_by = null;
            $newDonation->thank_you_email_sent = false;
            $newDonation->is_public = false;
            $newDonation->made_public_at = null;
            $newDonation->made_public_by = null;
            $newDonation->made_private_at = null;
            $newDonation->made_private_by = null;
            $newDonation->save();

            $newDonation->load(['donor', 'energyBond', 'organization', 'campaign']);

            DB::commit();

            Log::info('Bond donation duplicated successfully', [
                'original_donation_id' => $bondDonation->id,
                'new_donation_id' => $newDonation->id
            ]);

            return response()->json([
                'message' => 'Bond donation duplicated successfully',
                'data' => BondDonationResource::make($newDonation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating bond donation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get bond donation statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_donations' => BondDonation::count(),
                'total_amount' => BondDonation::where('status', '!=', 'refunded')->sum('amount'),
                'total_refunded' => BondDonation::where('status', 'refunded')->sum('refund_amount'),
                'pending_donations' => BondDonation::where('status', 'pending')->count(),
                'confirmed_donations' => BondDonation::where('status', 'confirmed')->count(),
                'processed_donations' => BondDonation::where('status', 'processed')->count(),
                'rejected_donations' => BondDonation::where('status', 'rejected')->count(),
                'refunded_donations' => BondDonation::where('status', 'refunded')->count(),
                'public_donations' => BondDonation::where('is_public', true)->count(),
                'anonymous_donations' => BondDonation::where('is_anonymous', true)->count(),
                'average_donation_amount' => BondDonation::where('status', '!=', 'refunded')->avg('amount'),
                'donations_by_type' => BondDonation::selectRaw('donation_type, COUNT(*) as count, SUM(amount) as total_amount')
                    ->where('status', '!=', 'refunded')
                    ->groupBy('donation_type')
                    ->get(),
                'donations_by_status' => BondDonation::selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                    ->groupBy('status')
                    ->get(),
                'monthly_donations' => BondDonation::selectRaw('DATE_FORMAT(donation_date, "%Y-%m") as month, COUNT(*) as count, SUM(amount) as total_amount')
                    ->where('status', '!=', 'refunded')
                    ->groupBy('month')
                    ->orderBy('month', 'desc')
                    ->limit(12)
                    ->get(),
                'top_donors' => BondDonation::selectRaw('donor_id, donor_name, COUNT(*) as donation_count, SUM(amount) as total_donated')
                    ->where('status', '!=', 'refunded')
                    ->groupBy('donor_id', 'donor_name')
                    ->orderBy('total_donated', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching bond donation statistics: ' . $e->getMessage());
            throw $e;
        }
    }
}
