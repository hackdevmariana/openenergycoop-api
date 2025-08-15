<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfileContactInfo;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * @OA\Tag(
 *     name="Customer Profile Contact Info",
 *     description="API Endpoints for managing customer profile contact information"
 * )
 */
class CustomerProfileContactInfoController extends Controller
{
    /**
     * Display a listing of customer profile contact info records.
     *
     * @OA\Get(
     *     path="/api/v1/customer-profile-contact-infos",
     *     summary="List all customer profile contact info records",
     *     description="Returns a paginated list of customer profile contact information records",
     *     operationId="indexCustomerProfileContactInfos",
     *     tags={"Customer Profile Contact Info"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="customer_profile_id",
     *         in="query",
     *         description="Filter by customer profile ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="contact_type",
     *         in="query",
     *         description="Filter by contact type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"email", "phone", "address", "social_media"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', CustomerProfileContactInfo::class);

            $query = CustomerProfileContactInfo::query()
                ->with(['customerProfile', 'organization']);

            // Apply filters
            if ($request->filled('customer_profile_id')) {
                $query->where('customer_profile_id', $request->customer_profile_id);
            }

            if ($request->filled('contact_type')) {
                $query->where('contact_type', $request->contact_type);
            }

            // Apply organization scope
            if (auth()->user()->organization_id) {
                $query->forCurrentOrganization();
            }

            $perPage = $request->get('per_page', 15);
            $contactInfos = $query->paginate($perPage);

            return response()->json([
                'data' => $contactInfos->items(),
                'links' => [
                    'first' => $contactInfos->url(1),
                    'last' => $contactInfos->url($contactInfos->lastPage()),
                    'prev' => $contactInfos->previousPageUrl(),
                    'next' => $contactInfos->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $contactInfos->currentPage(),
                    'from' => $contactInfos->firstItem(),
                    'last_page' => $contactInfos->lastPage(),
                    'per_page' => $contactInfos->perPage(),
                    'to' => $contactInfos->lastItem(),
                    'total' => $contactInfos->total(),
                ]
            ]);

        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to view customer profile contact information.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving customer profile contact information.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Store a newly created customer profile contact info record.
     *
     * @OA\Post(
     *     path="/api/v1/customer-profile-contact-infos",
     *     summary="Create a new customer profile contact info record",
     *     description="Creates a new customer profile contact information record",
     *     operationId="storeCustomerProfileContactInfo",
     *     tags={"Customer Profile Contact Info"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_profile_id", "contact_type", "contact_value"},
     *             @OA\Property(property="customer_profile_id", type="integer", description="ID of the customer profile"),
     *             @OA\Property(property="contact_type", type="string", enum={"email", "phone", "address", "social_media"}, description="Type of contact information"),
     *             @OA\Property(property="contact_value", type="string", description="The contact value (email, phone, address, etc.)"),
     *             @OA\Property(property="is_primary", type="boolean", description="Whether this is the primary contact method", default=false),
     *             @OA\Property(property="is_active", type="boolean", description="Whether this contact method is active", default=true),
     *             @OA\Property(property="notes", type="string", description="Additional notes about this contact method")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact info created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer profile contact info created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not found")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', CustomerProfileContactInfo::class);

            $validated = $request->validate([
                'customer_profile_id' => 'required|integer|exists:customer_profiles,id',
                'contact_type' => 'required|string|in:email,phone,address,social_media',
                'contact_value' => 'required|string|max:255',
                'is_primary' => 'boolean',
                'is_active' => 'boolean',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Verify customer profile belongs to current organization
            $customerProfile = CustomerProfile::findOrFail($validated['customer_profile_id']);
            if (auth()->user()->organization_id && $customerProfile->organization_id !== auth()->user()->organization_id) {
                return response()->json([
                    'message' => 'Customer profile not found.',
                    'error' => 'not_found'
                ], 404);
            }

            // Set organization ID
            $validated['organization_id'] = auth()->user()->organization_id;

            // If this is primary, unset other primary contacts of the same type
            if ($validated['is_primary'] ?? false) {
                CustomerProfileContactInfo::where('customer_profile_id', $validated['customer_profile_id'])
                    ->where('contact_type', $validated['contact_type'])
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $contactInfo = CustomerProfileContactInfo::create($validated);

            return response()->json([
                'message' => 'Customer profile contact info created successfully',
                'data' => $contactInfo->load(['customerProfile', 'organization'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error' => 'validation_error'
            ], 400);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to create customer profile contact information.',
                'error' => 'forbidden'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Customer profile not found.',
                'error' => 'not_found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating customer profile contact information.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Display the specified customer profile contact info record.
     *
     * @OA\Get(
     *     path="/api/v1/customer-profile-contact-infos/{id}",
     *     summary="Get customer profile contact info by ID",
     *     description="Returns a specific customer profile contact information record",
     *     operationId="showCustomerProfileContactInfo",
     *     tags={"Customer Profile Contact Info"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contact info ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact info not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $contactInfo = CustomerProfileContactInfo::with(['customerProfile', 'organization'])
                ->findOrFail($id);

            $this->authorize('view', $contactInfo);

            return response()->json([
                'data' => $contactInfo
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Customer profile contact info not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to view this customer profile contact information.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving customer profile contact information.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Update the specified customer profile contact info record.
     *
     * @OA\Put(
     *     path="/api/v1/customer-profile-contact-infos/{id}",
     *     summary="Update customer profile contact info",
     *     description="Updates an existing customer profile contact information record",
     *     operationId="updateCustomerProfileContactInfo",
     *     tags={"Customer Profile Contact Info"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contact info ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="contact_type", type="string", enum={"email", "phone", "address", "social_media"}),
     *             @OA\Property(property="contact_value", type="string", maxLength=255),
     *             @OA\Property(property="is_primary", type="boolean"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="notes", type="string", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact info updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer profile contact info updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact info not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not found")
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $contactInfo = CustomerProfileContactInfo::findOrFail($id);
            $this->authorize('update', $contactInfo);

            $validated = $request->validate([
                'contact_type' => 'sometimes|string|in:email,phone,address,social_media',
                'contact_value' => 'sometimes|string|max:255',
                'is_primary' => 'boolean',
                'is_active' => 'boolean',
                'notes' => 'nullable|string|max:1000',
            ]);

            // If this is primary, unset other primary contacts of the same type
            if (isset($validated['is_primary']) && $validated['is_primary']) {
                CustomerProfileContactInfo::where('customer_profile_id', $contactInfo->customer_profile_id)
                    ->where('contact_type', $validated['contact_type'] ?? $contactInfo->contact_type)
                    ->where('id', '!=', $id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $contactInfo->update($validated);

            return response()->json([
                'message' => 'Customer profile contact info updated successfully',
                'data' => $contactInfo->load(['customerProfile', 'organization'])
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error' => 'validation_error'
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Customer profile contact info not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to update this customer profile contact information.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating customer profile contact information.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Remove the specified customer profile contact info record.
     *
     * @OA\Delete(
     *     path="/api/v1/customer-profile-contact-infos/{id}",
     *     summary="Delete customer profile contact info",
     *     description="Deletes a customer profile contact information record",
     *     operationId="destroyCustomerProfileContactInfo",
     *     tags={"Customer Profile Contact Info"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contact info ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact info deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer profile contact info deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact info not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not found")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $contactInfo = CustomerProfileContactInfo::findOrFail($id);
            $this->authorize('delete', $contactInfo);

            $contactInfo->delete();

            return response()->json([
                'message' => 'Customer profile contact info deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Customer profile contact info not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to delete this customer profile contact information.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting customer profile contact information.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }
}
