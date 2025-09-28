<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnergyRightPreSale;
use App\Models\User;
use App\Models\EnergyInstallation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnergyRightPreSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyRightPreSale::with(['user', 'installation']);

            // Filtros
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('zone_name')) {
                $query->where('zone_name', 'like', '%' . $request->zone_name . '%');
            }

            if ($request->filled('postal_code')) {
                $query->where('postal_code', 'like', '%' . $request->postal_code . '%');
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('energy_installation_id')) {
                $query->where('energy_installation_id', $request->energy_installation_id);
            }

            if ($request->filled('with_installation')) {
                $query->whereNotNull('energy_installation_id');
            }

            if ($request->filled('without_installation')) {
                $query->whereNull('energy_installation_id');
            }

            if ($request->filled('active')) {
                $query->active();
            }

            if ($request->filled('expired')) {
                $query->expired();
            }

            if ($request->filled('expiring_soon')) {
                $days = $request->get('expiring_soon_days', 30);
                $query->expiringSoon($days);
            }

            if ($request->filled('min_kwh')) {
                $query->where('kwh_per_month_reserved', '>=', $request->min_kwh);
            }

            if ($request->filled('max_kwh')) {
                $query->where('kwh_per_month_reserved', '<=', $request->max_kwh);
            }

            if ($request->filled('min_price')) {
                $query->where('price_per_kwh', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price_per_kwh', '<=', $request->max_price);
            }

            // Búsqueda general
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('zone_name', 'like', "%{$search}%")
                      ->orWhere('postal_code', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('installation', function ($installationQuery) use ($search) {
                          $installationQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Ordenación
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $preSales = $query->paginate($perPage);

            // Transformar datos
            $preSales->getCollection()->transform(function ($preSale) {
                return [
                    'id' => $preSale->id,
                    'user' => $preSale->user ? [
                        'id' => $preSale->user->id,
                        'name' => $preSale->user->name,
                        'email' => $preSale->user->email,
                    ] : null,
                    'installation' => $preSale->installation ? [
                        'id' => $preSale->installation->id,
                        'name' => $preSale->installation->name,
                        'postal_code' => $preSale->installation->postal_code,
                    ] : null,
                    'zone_name' => $preSale->zone_name,
                    'postal_code' => $preSale->postal_code,
                    'full_zone_name' => $preSale->full_zone_name,
                    'kwh_per_month_reserved' => $preSale->kwh_per_month_reserved,
                    'price_per_kwh' => $preSale->price_per_kwh,
                    'total_value' => $preSale->total_value,
                    'total_value_formatted' => $preSale->total_value_formatted,
                    'status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'expires_at' => $preSale->expires_at,
                    'is_expired' => $preSale->is_expired,
                    'is_expiring_soon' => $preSale->is_expiring_soon,
                    'days_until_expiration' => $preSale->days_until_expiration,
                    'expiration_status' => $preSale->expiration_status,
                    'notes' => $preSale->notes,
                    'created_at' => $preSale->created_at,
                    'updated_at' => $preSale->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Preventas de derechos energéticos obtenidas exitosamente',
                'data' => $preSales,
                'meta' => [
                    'total_presales' => EnergyRightPreSale::count(),
                    'system_summary' => EnergyRightPreSale::getSystemSummary(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener preventas de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preventas de derechos energéticos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'energy_installation_id' => 'nullable|exists:energy_installations,id',
                'zone_name' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'kwh_per_month_reserved' => 'required|numeric|min:0.01',
                'price_per_kwh' => 'required|numeric|min:0.0001',
                'status' => 'nullable|in:pending,confirmed,cancelled',
                'signed_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after:now',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validaciones de negocio
            if (!$request->filled('energy_installation_id') && (!$request->filled('zone_name') || !$request->filled('postal_code'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se debe especificar una instalación o una zona con código postal',
                    'errors' => [
                        'energy_installation_id' => ['Este campo es requerido si no se especifica zona'],
                        'zone_name' => ['Este campo es requerido si no se especifica instalación'],
                        'postal_code' => ['Este campo es requerido si no se especifica instalación']
                    ]
                ], 422);
            }

            DB::beginTransaction();

            $preSaleData = $request->only([
                'user_id', 'energy_installation_id', 'zone_name', 'postal_code',
                'kwh_per_month_reserved', 'price_per_kwh', 'status', 'signed_at', 'expires_at', 'notes'
            ]);

            // Establecer estado por defecto
            if (!isset($preSaleData['status'])) {
                $preSaleData['status'] = EnergyRightPreSale::STATUS_PENDING;
            }

            // Si se confirma, establecer fecha de firma
            if ($preSaleData['status'] === EnergyRightPreSale::STATUS_CONFIRMED && !$preSaleData['signed_at']) {
                $preSaleData['signed_at'] = now();
            }

            $preSale = EnergyRightPreSale::create($preSaleData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Preventa de derechos energéticos creada exitosamente',
                'data' => [
                    'id' => $preSale->id,
                    'user' => $preSale->user ? [
                        'id' => $preSale->user->id,
                        'name' => $preSale->user->name,
                        'email' => $preSale->user->email,
                    ] : null,
                    'installation' => $preSale->installation ? [
                        'id' => $preSale->installation->id,
                        'name' => $preSale->installation->name,
                        'postal_code' => $preSale->installation->postal_code,
                    ] : null,
                    'zone_name' => $preSale->zone_name,
                    'postal_code' => $preSale->postal_code,
                    'full_zone_name' => $preSale->full_zone_name,
                    'kwh_per_month_reserved' => $preSale->kwh_per_month_reserved,
                    'price_per_kwh' => $preSale->price_per_kwh,
                    'total_value' => $preSale->total_value,
                    'total_value_formatted' => $preSale->total_value_formatted,
                    'status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'expires_at' => $preSale->expires_at,
                    'is_expired' => $preSale->is_expired,
                    'is_expiring_soon' => $preSale->is_expiring_soon,
                    'days_until_expiration' => $preSale->days_until_expiration,
                    'expiration_status' => $preSale->expiration_status,
                    'notes' => $preSale->notes,
                    'created_at' => $preSale->created_at,
                    'updated_at' => $preSale->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la preventa de derechos energéticos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $preSale = EnergyRightPreSale::with(['user', 'installation'])->find($id);

            if (!$preSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preventa de derechos energéticos no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Preventa de derechos energéticos obtenida exitosamente',
                'data' => [
                    'id' => $preSale->id,
                    'user' => $preSale->user ? [
                        'id' => $preSale->user->id,
                        'name' => $preSale->user->name,
                        'email' => $preSale->user->email,
                    ] : null,
                    'installation' => $preSale->installation ? [
                        'id' => $preSale->installation->id,
                        'name' => $preSale->installation->name,
                        'postal_code' => $preSale->installation->postal_code,
                    ] : null,
                    'zone_name' => $preSale->zone_name,
                    'postal_code' => $preSale->postal_code,
                    'full_zone_name' => $preSale->full_zone_name,
                    'kwh_per_month_reserved' => $preSale->kwh_per_month_reserved,
                    'price_per_kwh' => $preSale->price_per_kwh,
                    'total_value' => $preSale->total_value,
                    'total_value_formatted' => $preSale->total_value_formatted,
                    'status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'expires_at' => $preSale->expires_at,
                    'is_expired' => $preSale->is_expired,
                    'is_expiring_soon' => $preSale->is_expiring_soon,
                    'days_until_expiration' => $preSale->days_until_expiration,
                    'expiration_status' => $preSale->expiration_status,
                    'notes' => $preSale->notes,
                    'pre_sale_summary' => $preSale->getPreSaleSummary(),
                    'created_at' => $preSale->created_at,
                    'updated_at' => $preSale->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la preventa de derechos energéticos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $preSale = EnergyRightPreSale::find($id);

            if (!$preSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preventa de derechos energéticos no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'energy_installation_id' => 'sometimes|nullable|exists:energy_installations,id',
                'zone_name' => 'sometimes|nullable|string|max:255',
                'postal_code' => 'sometimes|nullable|string|max:10',
                'kwh_per_month_reserved' => 'sometimes|required|numeric|min:0.01',
                'price_per_kwh' => 'sometimes|required|numeric|min:0.0001',
                'status' => 'sometimes|required|in:pending,confirmed,cancelled',
                'signed_at' => 'sometimes|nullable|date',
                'expires_at' => 'sometimes|nullable|date',
                'notes' => 'sometimes|nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $updateData = $request->only([
                'user_id', 'energy_installation_id', 'zone_name', 'postal_code',
                'kwh_per_month_reserved', 'price_per_kwh', 'status', 'signed_at', 'expires_at', 'notes'
            ]);

            // Si se confirma, establecer fecha de firma si no existe
            if (isset($updateData['status']) && $updateData['status'] === EnergyRightPreSale::STATUS_CONFIRMED && !$updateData['signed_at']) {
                $updateData['signed_at'] = now();
            }

            $preSale->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Preventa de derechos energéticos actualizada exitosamente',
                'data' => [
                    'id' => $preSale->id,
                    'user' => $preSale->user ? [
                        'id' => $preSale->user->id,
                        'name' => $preSale->user->name,
                        'email' => $preSale->user->email,
                    ] : null,
                    'installation' => $preSale->installation ? [
                        'id' => $preSale->installation->id,
                        'name' => $preSale->installation->name,
                        'postal_code' => $preSale->installation->postal_code,
                    ] : null,
                    'zone_name' => $preSale->zone_name,
                    'postal_code' => $preSale->postal_code,
                    'full_zone_name' => $preSale->full_zone_name,
                    'kwh_per_month_reserved' => $preSale->kwh_per_month_reserved,
                    'price_per_kwh' => $preSale->price_per_kwh,
                    'total_value' => $preSale->total_value,
                    'total_value_formatted' => $preSale->total_value_formatted,
                    'status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'expires_at' => $preSale->expires_at,
                    'is_expired' => $preSale->is_expired,
                    'is_expiring_soon' => $preSale->is_expiring_soon,
                    'days_until_expiration' => $preSale->days_until_expiration,
                    'expiration_status' => $preSale->expiration_status,
                    'notes' => $preSale->notes,
                    'created_at' => $preSale->created_at,
                    'updated_at' => $preSale->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la preventa de derechos energéticos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
