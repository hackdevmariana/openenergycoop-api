<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppSettingResource;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/app-settings",
     *     summary="Obtener configuración global de la app",
     *     tags={"AppSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Configuración de la aplicación",
     *         @OA\JsonContent(ref="#/components/schemas/AppSetting")
     *     )
     * )
     */
    public function index()
    {
        $settings = AppSetting::with('organization')->first(); // asumiendo que sólo hay uno
        
        if (!$settings) {
            return response()->json(['data' => null]);
        }
        
        return new AppSettingResource($settings);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/app-settings/{id}",
     *     summary="Mostrar configuración por ID",
     *     tags={"AppSettings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuración encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/AppSetting")
     *     ),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show($id)
    {
        $setting = AppSetting::with('organization')->findOrFail($id);
        return new AppSettingResource($setting);
    }
}
