<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ConsentLog\StoreConsentLogRequest;
use App\Http\Resources\Api\V1\ConsentLogResource;
use App\Models\ConsentLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Consent Logs",
 *     description="Gestión de registros de consentimiento (GDPR)"
 * )
 */
class ConsentLogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/consent-logs",
     *     tags={"Consent Logs"},
     *     summary="Listar consentimientos del usuario",
     *     description="Obtiene el historial de consentimientos del usuario autenticado",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="consent_type",
     *         in="query",
     *         description="Filtrar por tipo de consentimiento",
     *         required=false,
     *         @OA\Schema(type="string", enum={"privacy_policy", "terms_of_service", "marketing", "cookies", "data_processing", "newsletter", "analytics"})
     *     ),
     *     @OA\Parameter(
     *         name="active_only",
     *         in="query",
     *         description="Solo consentimientos activos",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de consentimientos obtenida exitosamente"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ConsentLog::where('user_id', auth()->id())
            ->orderByDesc('created_at');

        if ($request->filled('consent_type')) {
            $query->where('consent_type', $request->consent_type);
        }

        if ($request->boolean('active_only')) {
            $query->whereNull('revoked_at');
        }

        $consents = $query->paginate(20);

        return ConsentLogResource::collection($consents);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/consent-logs",
     *     tags={"Consent Logs"},
     *     summary="Registrar nuevo consentimiento",
     *     description="Registra un nuevo consentimiento del usuario",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"consent_type", "consent_given"},
     *             @OA\Property(property="consent_type", type="string", enum={"privacy_policy", "terms_of_service", "marketing", "cookies", "data_processing", "newsletter", "analytics"}, example="privacy_policy"),
     *             @OA\Property(property="consent_given", type="boolean", example=true),
     *             @OA\Property(property="version", type="string", maxLength=50, example="1.0"),
     *             @OA\Property(property="purpose", type="string", maxLength=500, example="Procesamiento de datos personales"),
     *             @OA\Property(property="legal_basis", type="string", maxLength=200, example="Artículo 6.1.a GDPR"),
     *             @OA\Property(property="data_categories", type="array", @OA\Items(type="string"), example={"personal_data", "contact_info"}),
     *             @OA\Property(property="retention_period", type="string", maxLength=100, example="5 años"),
     *             @OA\Property(property="third_parties", type="array", @OA\Items(type="string"), example={"Google Analytics", "Mailchimp"}),
     *             @OA\Property(property="withdrawal_method", type="string", maxLength=200, example="Contactar a privacy@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Consentimiento registrado exitosamente"
     *     )
     * )
     */
    public function store(StoreConsentLogRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        $consent = ConsentLog::recordConsent(
            auth()->id(),
            $validated['consent_type'],
            $validated['consent_given'],
            $validated['version'] ?? null,
            array_filter([
                'purpose' => $validated['purpose'] ?? null,
                'legal_basis' => $validated['legal_basis'] ?? null,
                'data_categories' => $validated['data_categories'] ?? null,
                'retention_period' => $validated['retention_period'] ?? null,
                'third_parties' => $validated['third_parties'] ?? null,
                'withdrawal_method' => $validated['withdrawal_method'] ?? null,
                'ip_address' => $validated['ip_address'],
                'user_agent' => $validated['user_agent'],
            ])
        );

        return response()->json([
            'data' => new ConsentLogResource($consent),
            'message' => 'Consentimiento registrado exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/consent-logs/{id}",
     *     tags={"Consent Logs"},
     *     summary="Obtener consentimiento específico",
     *     description="Obtiene los detalles de un consentimiento específico",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del consentimiento",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consentimiento obtenido exitosamente"
     *     )
     * )
     */
    public function show(ConsentLog $consentLog): JsonResponse
    {
        // Verificar que el consentimiento pertenece al usuario autenticado
        if ($consentLog->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Consentimiento no encontrado'
            ], 404);
        }

        return response()->json([
            'data' => new ConsentLogResource($consentLog)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/consent-logs/{id}/revoke",
     *     tags={"Consent Logs"},
     *     summary="Revocar consentimiento",
     *     description="Revoca un consentimiento previamente otorgado",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del consentimiento",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", maxLength=500, example="El usuario retiró el consentimiento")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consentimiento revocado exitosamente"
     *     )
     * )
     */
    public function revoke(Request $request, ConsentLog $consentLog): JsonResponse
    {
        // Verificar que el consentimiento pertenece al usuario autenticado
        if ($consentLog->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Consentimiento no encontrado'
            ], 404);
        }

        // Verificar que no está ya revocado
        if ($consentLog->revoked_at) {
            return response()->json([
                'message' => 'El consentimiento ya ha sido revocado'
            ], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $consentLog->update([
            'revoked_at' => now(),
            'revocation_reason' => $request->reason
        ]);

        return response()->json([
            'data' => new ConsentLogResource($consentLog->fresh()),
            'message' => 'Consentimiento revocado exitosamente'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/consent-logs/current-status",
     *     tags={"Consent Logs"},
     *     summary="Obtener estado actual de consentimientos",
     *     description="Obtiene el estado actual de todos los tipos de consentimiento del usuario",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estado actual de consentimientos obtenido"
     *     )
     * )
     */
    public function currentStatus(): JsonResponse
    {
        $userId = auth()->id();
        $consentTypes = array_keys(\App\Enums\AppEnums::CONSENT_TYPES);
        
        $currentConsents = [];

        foreach ($consentTypes as $type) {
            $latestConsent = ConsentLog::where('user_id', $userId)
                ->where('consent_type', $type)
                ->orderByDesc('created_at')
                ->first();

            $currentConsents[$type] = [
                'given' => $latestConsent ? $latestConsent->consent_given && !$latestConsent->revoked_at : false,
                'version' => $latestConsent?->version,
                'granted_at' => $latestConsent?->created_at,
                'revoked_at' => $latestConsent?->revoked_at,
            ];
        }

        return response()->json([
            'data' => $currentConsents
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/consent-logs/history/{type}",
     *     tags={"Consent Logs"},
     *     summary="Obtener historial de un tipo de consentimiento",
     *     description="Obtiene todo el historial de cambios de un tipo específico de consentimiento",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         description="Tipo de consentimiento",
     *         required=true,
     *         @OA\Schema(type="string", enum={"privacy_policy", "terms_of_service", "marketing", "cookies", "data_processing", "newsletter", "analytics"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historial de consentimiento obtenido"
     *     )
     * )
     */
    public function history(string $type): JsonResponse
    {
        $consents = ConsentLog::forUser(auth()->id())
            ->ofType($type)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => ConsentLogResource::collection($consents)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/consent-logs/gdpr-report",
     *     tags={"Consent Logs"},
     *     summary="Generar reporte GDPR",
     *     description="Genera un reporte completo de todos los consentimientos para cumplimiento GDPR",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reporte GDPR generado exitosamente"
     *     )
     * )
     */
    public function gdprReport(): JsonResponse
    {
        $report = ConsentLog::generateGDPRReport(auth()->id());

        return response()->json([
            'data' => $report
        ]);
    }
}