<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Legal Documents",
 *     description="API Endpoints for managing legal documents"
 * )
 */
class LegalDocumentController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of legal documents.
     *
     * @OA\Get(
     *     path="/api/v1/legal-documents",
     *     summary="List all legal documents",
     *     description="Returns a paginated list of legal documents with filtering options",
     *     operationId="indexLegalDocuments",
     *     tags={"Legal Documents"},
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
     *         name="type",
     *         in="query",
     *         description="Filter by document type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dni", "iban_receipt", "contract", "invoice", "other"})
     *     ),
     *     @OA\Parameter(
     *         name="verified",
     *         in="query",
     *         description="Filter by verification status",
     *         required=false,
     *         @OA\Schema(type="boolean", description="true for verified documents, false for pending")
     *     ),
     *     @OA\Parameter(
     *         name="verifier_user_id",
     *         in="query",
     *         description="Filter by verifier user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LegalDocument")),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', LegalDocument::class);

            $query = LegalDocument::query()
                ->with(['customerProfile', 'verifier', 'organization']);

            // Apply filters
            if ($request->filled('customer_profile_id')) {
                $query->where('customer_profile_id', $request->customer_profile_id);
            }

            if ($request->filled('type')) {
                $query->byType($request->type);
            }

            if ($request->has('verified')) {
                if ($request->boolean('verified')) {
                    $query->verified();
                } else {
                    $query->pendingVerification();
                }
            }

            if ($request->filled('verifier_user_id')) {
                $query->where('verifier_user_id', $request->verifier_user_id);
            }

            // Apply organization scope
            if (auth()->user()->organization_id) {
                $query->forCurrentOrganization();
            }

            $perPage = $request->get('per_page', 15);
            $documents = $query->paginate($perPage);

            return response()->json([
                'data' => $documents->items(),
                'links' => [
                    'first' => $documents->url(1),
                    'last' => $documents->url($documents->lastPage()),
                    'prev' => $documents->previousPageUrl(),
                    'next' => $documents->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $documents->currentPage(),
                    'from' => $documents->firstItem(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'to' => $documents->lastItem(),
                    'total' => $documents->total(),
                ]
            ]);

        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to view legal documents.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving legal documents.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Store a newly created legal document.
     *
     * @OA\Post(
     *     path="/api/v1/legal-documents",
     *     summary="Create a new legal document",
     *     description="Creates a new legal document with file upload",
     *     operationId="storeLegalDocument",
     *     tags={"Legal Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"customer_profile_id", "type", "document_file"},
     *                 @OA\Property(property="customer_profile_id", type="integer", description="ID of the customer profile"),
     *                 @OA\Property(property="type", type="string", enum={"dni", "iban_receipt", "contract", "invoice", "other"}, description="Type of legal document"),
     *                 @OA\Property(property="document_file", type="string", format="binary", description="The document file to upload"),
     *                 @OA\Property(property="version", type="string", description="Document version", example="1.0"),
     *                 @OA\Property(property="expires_at", type="string", format="date", description="Document expiry date"),
     *                 @OA\Property(property="notes", type="string", description="Additional notes about the document", maxLength=1000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Legal document created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Legal document created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LegalDocument")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer profile not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', LegalDocument::class);

            $validated = $request->validate([
                'customer_profile_id' => 'required|integer|exists:customer_profiles,id',
                'type' => 'required|string|in:dni,iban_receipt,contract,invoice,other',
                'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
                'version' => 'nullable|string|max:10',
                'expires_at' => 'nullable|date|after:today',
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

            // Set organization ID and upload timestamp
            $validated['organization_id'] = auth()->user()->organization_id;
            $validated['uploaded_at'] = now();
            
            // Auto-generate version if not provided
            if (!isset($validated['version'])) {
                $latestVersion = LegalDocument::where('customer_profile_id', $validated['customer_profile_id'])
                                            ->where('type', $validated['type'])
                                            ->max('version');
                $validated['version'] = $latestVersion ? ((float) $latestVersion + 0.1) : '1.0';
            }

            // Create the legal document
            $legalDocument = LegalDocument::create($validated);

            // Handle file upload using Spatie Media Library
            if ($request->hasFile('document_file')) {
                $legalDocument->addMediaFromRequest('document_file')
                    ->toMediaCollection('legal_documents');
            }

            return response()->json([
                'message' => 'Legal document created successfully',
                'data' => $legalDocument->load(['customerProfile', 'verifier', 'organization'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error' => 'validation_error'
            ], 400);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to create legal documents.',
                'error' => 'forbidden'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Customer profile not found.',
                'error' => 'not_found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the legal document.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Display the specified legal document.
     *
     * @OA\Get(
     *     path="/api/v1/legal-documents/{id}",
     *     summary="Get legal document by ID",
     *     description="Returns a specific legal document with its media files",
     *     operationId="showLegalDocument",
     *     tags={"Legal Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Legal document ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/LegalDocument")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Legal document not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $legalDocument = LegalDocument::with(['customerProfile', 'verifier', 'organization'])
                ->findOrFail($id);

            $this->authorize('view', $legalDocument);

            // Load media files
            $legalDocument->loadMedia('legal_documents');

            return response()->json([
                'data' => $legalDocument
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Legal document not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to view this legal document.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving the legal document.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Update the specified legal document.
     *
     * @OA\Put(
     *     path="/api/v1/legal-documents/{id}",
     *     summary="Update legal document",
     *     description="Updates an existing legal document",
     *     operationId="updateLegalDocument",
     *     tags={"Legal Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Legal document ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"dni", "iban_receipt", "contract", "invoice", "other"}),
     *             @OA\Property(property="expires_at", type="string", format="date"),
     *             @OA\Property(property="notes", type="string", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Legal document updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Legal document updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LegalDocument")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Legal document not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $legalDocument = LegalDocument::findOrFail($id);
            $this->authorize('update', $legalDocument);

            $validated = $request->validate([
                'type' => 'sometimes|string|in:dni,iban_receipt,contract,invoice,other',
                'expires_at' => 'nullable|date|after:today',
                'notes' => 'nullable|string|max:1000',
            ]);

            $legalDocument->update($validated);

            return response()->json([
                'message' => 'Legal document updated successfully',
                'data' => $legalDocument->load(['customerProfile', 'verifier', 'organization'])
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error' => 'validation_error'
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Legal document not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to update this legal document.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the legal document.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Remove the specified legal document.
     *
     * @OA\Delete(
     *     path="/api/v1/legal-documents/{id}",
     *     summary="Delete legal document",
     *     description="Deletes a legal document and its associated media files",
     *     operationId="destroyLegalDocument",
     *     tags={"Legal Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Legal document ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Legal document deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Legal document deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Legal document not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $legalDocument = LegalDocument::findOrFail($id);
            $this->authorize('delete', $legalDocument);

            // Delete associated media files
            $legalDocument->clearMediaCollection('legal_documents');

            $legalDocument->delete();

            return response()->json([
                'message' => 'Legal document deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Legal document not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to delete this legal document.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the legal document.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Verify a legal document.
     *
     * @OA\Post(
     *     path="/api/v1/legal-documents/{id}/verify",
     *     summary="Verify a legal document",
     *     description="Updates the verification status of a legal document",
     *     operationId="verifyLegalDocument",
     *     tags={"Legal Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Legal document ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="notes", type="string", description="Notes about the verification", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Legal document verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Legal document verified successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LegalDocument")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Legal document not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        try {
            $legalDocument = LegalDocument::findOrFail($id);
            $this->authorize('verify', $legalDocument);

            $validated = $request->validate([
                'notes' => 'nullable|string|max:1000',
            ]);

            // Use the model's markAsVerified method
            $legalDocument->markAsVerified(auth()->user(), $validated['notes'] ?? null);

            return response()->json([
                'message' => 'Legal document verified successfully',
                'data' => $legalDocument->load(['customerProfile', 'verifier', 'organization'])
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error' => 'validation_error'
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Legal document not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to verify this legal document.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while verifying the legal document.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Upload a new version of an existing document.
     *
     * @OA\Post(
     *     path="/api/v1/legal-documents/{id}/new-version",
     *     summary="Upload new version of document",
     *     description="Creates a new version of an existing legal document",
     *     operationId="newVersionLegalDocument",
     *     tags={"Legal Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Legal document ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"document_file"},
     *                 @OA\Property(property="document_file", type="string", format="binary", description="The new document file"),
     *                 @OA\Property(property="version", type="string", description="Version number (auto-incremented if not provided)"),
     *                 @OA\Property(property="notes", type="string", description="Notes about this version", maxLength=1000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="New version created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="New document version created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LegalDocument")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Legal document not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function newVersion(Request $request, int $id): JsonResponse
    {
        try {
            $legalDocument = LegalDocument::findOrFail($id);
            $this->authorize('update', $legalDocument);

            $validated = $request->validate([
                'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'version' => 'nullable|string|max:10',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Upload new version using model method
            $newDocument = $legalDocument->uploadNewVersion(
                $request->file('document_file'),
                $validated['version'] ?? null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'message' => 'New document version created successfully',
                'data' => $newDocument->load(['customerProfile', 'verifier', 'organization'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error' => 'validation_error'
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Legal document not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to create new versions.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the new version.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Get all versions of a document type for a customer.
     *
     * @OA\Get(
     *     path="/api/v1/legal-documents/{id}/versions",
     *     summary="Get all document versions",
     *     description="Returns all versions of the same document type for the customer",
     *     operationId="versionsLegalDocument",
     *     tags={"Legal Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Legal document ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LegalDocument"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Legal document not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function versions(int $id): JsonResponse
    {
        try {
            $legalDocument = LegalDocument::findOrFail($id);
            $this->authorize('view', $legalDocument);

            $versions = $legalDocument->getAllVersions();

            return response()->json([
                'data' => $versions
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Legal document not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to view document versions.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving document versions.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }

    /**
     * Download a legal document.
     *
     * @OA\Get(
     *     path="/api/v1/legal-documents/{id}/download",
     *     summary="Download legal document",
     *     description="Downloads the document file with security token",
     *     operationId="downloadLegalDocument",
     *     tags={"Legal Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Legal document ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="conversion",
     *         in="query",
     *         description="Media conversion to download (e.g., thumbnail)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File download",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Document or file not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function download(Request $request, int $id): JsonResponse
    {
        try {
            $legalDocument = LegalDocument::findOrFail($id);
            $this->authorize('view', $legalDocument);

            $url = $legalDocument->getSecureUrl($request->get('conversion'));
            
            if (!$url) {
                return response()->json([
                    'message' => 'Document file not found.',
                    'error' => 'not_found'
                ], 404);
            }

            return response()->json([
                'download_url' => $url,
                'expires_in' => 3600 // URL valid for 1 hour
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Legal document not found.',
                'error' => 'not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'You are not authorized to download this document.',
                'error' => 'forbidden'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while generating download URL.',
                'error' => 'internal_server_error'
            ], 500);
        }
    }
}
