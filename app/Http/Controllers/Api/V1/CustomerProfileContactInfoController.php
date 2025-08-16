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
     *         name="province",
     *         in="query",
     *         description="Filter by province",
     *         required=false,
     *         @OA\Schema(type="string")
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

            if ($request->filled('province')) {
                $query->byProvince($request->province);
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
     *             required={"customer_profile_id", "address", "postal_code", "city", "province", "valid_from"},
     *             @OA\Property(property="customer_profile_id", type="integer", description="ID of the customer profile"),
     *             @OA\Property(property="billing_email", type="string", format="email", description="Billing email address"),
     *             @OA\Property(property="technical_email", type="string", format="email", description="Technical contact email"),
     *             @OA\Property(property="address", type="string", description="Physical address"),
     *             @OA\Property(property="postal_code", type="string", description="Postal code"),
     *             @OA\Property(property="city", type="string", description="City"),
     *             @OA\Property(property="province", type="string", description="Province or region"),
     *             @OA\Property(property="iban", type="string", description="Spanish IBAN (24 characters)"),
     *             @OA\Property(property="cups", type="string", description="Spanish CUPS code (22 characters)"),
     *             @OA\Property(property="valid_from", type="string", format="date", description="Valid from date"),
     *             @OA\Property(property="valid_to", type="string", format="date", description="Valid until date")
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
                'billing_email' => 'nullable|email|max:255',
                'technical_email' => 'nullable|email|max:255',
                'address' => 'required|string|max:500',
                'postal_code' => 'required|string|max:10',
                'city' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'iban' => 'nullable|string|size:24|regex:/^ES\d{22}$/',
                'cups' => 'nullable|string|size:22|regex:/^ES\d{18}[A-Z]{2}$/',
                'valid_from' => 'required|date',
                'valid_to' => 'nullable|date|after:valid_from',
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
     *             @OA\Property(property="billing_email", type="string", format="email"),
     *             @OA\Property(property="technical_email", type="string", format="email"),
     *             @OA\Property(property="address", type="string", maxLength=500),
     *             @OA\Property(property="postal_code", type="string", maxLength=10),
     *             @OA\Property(property="city", type="string", maxLength=100),
     *             @OA\Property(property="province", type="string", maxLength=100),
     *             @OA\Property(property="iban", type="string", description="Spanish IBAN"),
     *             @OA\Property(property="cups", type="string", description="Spanish CUPS code"),
     *             @OA\Property(property="valid_from", type="string", format="date"),
     *             @OA\Property(property="valid_to", type="string", format="date")
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
                'billing_email' => 'sometimes|nullable|email|max:255',
                'technical_email' => 'sometimes|nullable|email|max:255',
                'address' => 'sometimes|string|max:500',
                'postal_code' => 'sometimes|string|max:10',
                'city' => 'sometimes|string|max:100',
                'province' => 'sometimes|string|max:100',
                'iban' => 'sometimes|nullable|string|size:24|regex:/^ES\d{22}$/',
                'cups' => 'sometimes|nullable|string|size:22|regex:/^ES\d{18}[A-Z]{2}$/',
                'valid_from' => 'sometimes|date',
                'valid_to' => 'sometimes|nullable|date|after:valid_from',
            ]);

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
