<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\EnergyTradingOrder\StoreEnergyTradingOrderRequest;
use App\Http\Requests\Api\V1\EnergyTradingOrder\UpdateEnergyTradingOrderRequest;
use App\Http\Resources\Api\V1\EnergyTradingOrder\EnergyTradingOrderResource;
use App\Http\Resources\Api\V1\EnergyTradingOrder\EnergyTradingOrderCollection;
use App\Models\EnergyTradingOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Energy Trading Order Management
 * APIs for managing energy trading orders
 */
class EnergyTradingOrderController extends Controller
{
    /**
     * Display a listing of energy trading orders.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in order_number, notes. Example: "ORDER-001"
     * @queryParam order_type string Filter by order type. Example: "buy"
     * @queryParam order_status string Filter by order status. Example: "active"
     * @queryParam order_side string Filter by order side. Example: "buy"
     * @queryParam trader_id integer Filter by trader ID. Example: 1
     * @queryParam pool_id integer Filter by pool ID. Example: 1
     * @queryParam counterparty_id integer Filter by counterparty ID. Example: 1
     * @queryParam price_type string Filter by price type. Example: "fixed"
     * @queryParam execution_type string Filter by execution type. Example: "immediate"
     * @queryParam priority string Filter by priority. Example: "high"
     * @queryParam is_negotiable boolean Filter by negotiable status. Example: true
     * @queryParam price_min float Minimum price per MWh. Example: 50.0
     * @queryParam price_max float Maximum price per MWh. Example: 100.0
     * @queryParam quantity_min float Minimum quantity in MWh. Example: 10.0
     * @queryParam quantity_max float Maximum quantity in MWh. Example: 1000.0
     * @queryParam valid_from string Filter by valid from date. Example: "2024-01-01"
     * @queryParam valid_until string Filter by valid until date. Example: "2024-12-31"
     * @queryParam sort_by string Sort field. Example: "created_at"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "desc"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORDER-001",
     *       "order_type": "buy",
     *       "order_status": "active",
     *       "order_side": "buy",
     *       "quantity_mwh": 100.00,
     *       "price_per_mwh": 75.50
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 25,
     *     "per_page": 15
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyTradingOrder::with(['trader', 'pool', 'counterparty', 'createdBy', 'approvedBy', 'executedBy']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            // Filtros por tipo, estado y lado
            if ($request->filled('order_type')) {
                $query->where('order_type', $request->order_type);
            }

            if ($request->filled('order_status')) {
                $query->where('order_status', $request->order_status);
            }

            if ($request->filled('order_side')) {
                $query->where('order_side', $request->order_side);
            }

            // Filtros por usuarios y pool
            if ($request->filled('trader_id')) {
                $query->where('trader_id', $request->trader_id);
            }

            if ($request->filled('pool_id')) {
                $query->where('pool_id', $request->pool_id);
            }

            if ($request->filled('counterparty_id')) {
                $query->where('counterparty_id', $request->counterparty_id);
            }

            // Filtros por precio y ejecución
            if ($request->filled('price_type')) {
                $query->where('price_type', $request->price_type);
            }

            if ($request->filled('execution_type')) {
                $query->where('execution_type', $request->execution_type);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // Filtro por negociable
            if ($request->has('is_negotiable')) {
                $query->where('is_negotiable', $request->boolean('is_negotiable'));
            }

            // Filtros por rango de precio
            if ($request->filled('price_min')) {
                $query->where('price_per_mwh', '>=', $request->price_min);
            }

            if ($request->filled('price_max')) {
                $query->where('price_per_mwh', '<=', $request->price_max);
            }

            // Filtros por rango de cantidad
            if ($request->filled('quantity_min')) {
                $query->where('quantity_mwh', '>=', $request->quantity_min);
            }

            if ($request->filled('quantity_max')) {
                $query->where('quantity_mwh', '<=', $request->quantity_max);
            }

            // Filtros por fechas
            if ($request->filled('valid_from')) {
                $query->whereDate('valid_from', $request->valid_from);
            }

            if ($request->filled('valid_until')) {
                $query->whereDate('valid_until', $request->valid_until);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['order_number', 'quantity_mwh', 'price_per_mwh', 'total_value', 'created_at', 'valid_from', 'valid_until'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $orders = $query->paginate($perPage);

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'last_page' => $orders->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy trading orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener las órdenes de comercio de energía',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created energy trading order.
     *
     * @authenticated
     * @bodyParam order_number string required The order number. Example: "ORDER-001"
     * @bodyParam order_type string required The order type. Example: "buy"
     * @bodyParam order_status string required The order status. Example: "pending"
     * @bodyParam order_side string required The order side. Example: "buy"
     * @bodyParam trader_id integer required The trader ID. Example: 1
     * @bodyParam pool_id integer required The pool ID. Example: 1
     * @bodyParam counterparty_id integer The counterparty ID. Example: 1
     * @bodyParam quantity_mwh numeric required The quantity in MWh. Example: 100.00
     * @bodyParam price_per_mwh numeric required The price per MWh. Example: 75.50
     * @bodyParam price_type string required The price type. Example: "fixed"
     * @bodyParam execution_type string required The execution type. Example: "immediate"
     * @bodyParam priority string required The priority. Example: "normal"
     * @bodyParam valid_from datetime required The valid from datetime. Example: "2024-01-01 00:00:00"
     * @bodyParam valid_until datetime The valid until datetime. Example: "2024-12-31 23:59:59"
     * @bodyParam is_negotiable boolean Whether the order is negotiable. Example: true
     * @bodyParam notes string Additional notes. Example: "Order for peak hours"
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "order_number": "ORDER-001",
     *     "order_type": "buy",
     *     "order_status": "pending",
     *     "quantity_mwh": 100.00,
     *     "price_per_mwh": 75.50
     *   }
     * }
     */
    public function store(StoreEnergyTradingOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Calcular valores totales
            $data['total_value'] = $data['quantity_mwh'] * $data['price_per_mwh'];
            $data['filled_quantity_mwh'] = 0;
            $data['remaining_quantity_mwh'] = $data['quantity_mwh'];
            $data['filled_value'] = 0;
            $data['remaining_value'] = $data['total_value'];

            $order = EnergyTradingOrder::create($data);

            Log::info('Energy trading order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyTradingOrderResource($order),
                'message' => 'Orden de comercio creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating energy trading order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al crear la orden de comercio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified energy trading order.
     *
     * @authenticated
     * @urlParam id integer required The order ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "order_number": "ORDER-001",
     *     "order_type": "buy",
     *     "order_status": "active",
     *     "quantity_mwh": 100.00,
     *     "price_per_mwh": 75.50,
     *     "fill_percentage": 0.00
     *   }
     * }
     */
    public function show(EnergyTradingOrder $energyTradingOrder): JsonResponse
    {
        try {
            $energyTradingOrder->load(['trader', 'pool', 'counterparty', 'createdBy', 'approvedBy', 'executedBy']);

            return response()->json([
                'data' => new EnergyTradingOrderResource($energyTradingOrder)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy trading order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la orden de comercio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified energy trading order.
     *
     * @authenticated
     * @urlParam id integer required The order ID. Example: 1
     * @bodyParam order_status string The order status. Example: "active"
     * @bodyParam price_per_mwh numeric The price per MWh. Example: 80.00
     * @bodyParam quantity_mwh numeric The quantity in MWh. Example: 150.00
     * @bodyParam notes string Additional notes. Example: "Updated order details"
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "order_number": "ORDER-001",
     *     "order_status": "active",
     *     "price_per_mwh": 80.00,
     *     "quantity_mwh": 150.00
     *   }
     * }
     */
    public function update(UpdateEnergyTradingOrderRequest $request, EnergyTradingOrder $energyTradingOrder): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Recalcular valores si cambia la cantidad o precio
            if (isset($data['quantity_mwh']) || isset($data['price_per_mwh'])) {
                $newQuantity = $data['quantity_mwh'] ?? $energyTradingOrder->quantity_mwh;
                $newPrice = $data['price_per_mwh'] ?? $energyTradingOrder->price_per_mwh;
                
                $data['total_value'] = $newQuantity * $newPrice;
                $data['remaining_quantity_mwh'] = $newQuantity - $energyTradingOrder->filled_quantity_mwh;
                $data['remaining_value'] = $data['remaining_quantity_mwh'] * $newPrice;
            }

            $energyTradingOrder->update($data);

            Log::info('Energy trading order updated', [
                'order_id' => $energyTradingOrder->id,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyTradingOrderResource($energyTradingOrder),
                'message' => 'Orden de comercio actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy trading order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar la orden de comercio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified energy trading order.
     *
     * @authenticated
     * @urlParam id integer required The order ID. Example: 1
     *
     * @response 200 {
     *   "message": "Orden de comercio eliminada exitosamente"
     * }
     */
    public function destroy(EnergyTradingOrder $energyTradingOrder): JsonResponse
    {
        try {
            DB::beginTransaction();

            $orderId = $energyTradingOrder->id;
            $orderNumber = $energyTradingOrder->order_number;

            $energyTradingOrder->delete();

            Log::info('Energy trading order deleted', [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Orden de comercio eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting energy trading order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al eliminar la orden de comercio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get trading order statistics.
     *
     * @authenticated
     * @response 200 {
     *   "total_orders": 150,
     *   "active_orders": 45,
     *   "filled_orders": 80,
     *   "cancelled_orders": 15,
     *   "by_type": {"buy": 80, "sell": 70},
     *   "by_status": {"pending": 20, "active": 45, "filled": 80},
     *   "by_side": {"buy": 80, "sell": 70}
     * }
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = [
                'total_orders' => EnergyTradingOrder::count(),
                'active_orders' => EnergyTradingOrder::active()->count(),
                'filled_orders' => EnergyTradingOrder::filled()->count(),
                'cancelled_orders' => EnergyTradingOrder::cancelled()->count(),
                'by_type' => EnergyTradingOrder::selectRaw('order_type, COUNT(*) as count')
                    ->groupBy('order_type')
                    ->pluck('count', 'order_type')
                    ->toArray(),
                'by_status' => EnergyTradingOrder::selectRaw('order_status, COUNT(*) as count')
                    ->groupBy('order_status')
                    ->pluck('count', 'order_status')
                    ->toArray(),
                'by_side' => EnergyTradingOrder::selectRaw('order_side, COUNT(*) as count')
                    ->groupBy('order_side')
                    ->pluck('count', 'order_side')
                    ->toArray(),
            ];

            return response()->json($statistics);

        } catch (\Exception $e) {
            Log::error('Error fetching trading order statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available order types.
     *
     * @authenticated
     * @response 200 {
     *   "buy": "Compra",
     *   "sell": "Venta",
     *   "bid": "Oferta",
     *   "ask": "Demanda"
     * }
     */
    public function types(): JsonResponse
    {
        return response()->json(EnergyTradingOrder::getOrderTypes());
    }

    /**
     * Get available order statuses.
     *
     * @authenticated
     * @response 200 {
     *   "pending": "Pendiente",
     *   "active": "Activo",
     *   "filled": "Completado",
     *   "cancelled": "Cancelado"
     * }
     */
    public function statuses(): JsonResponse
    {
        return response()->json(EnergyTradingOrder::getOrderStatuses());
    }

    /**
     * Get available order sides.
     *
     * @authenticated
     * @response 200 {
     *   "buy": "Compra",
     *   "sell": "Venta"
     * }
     */
    public function sides(): JsonResponse
    {
        return response()->json(EnergyTradingOrder::getOrderSides());
    }

    /**
     * Get available price types.
     *
     * @authenticated
     * @response 200 {
     *   "fixed": "Fijo",
     *   "floating": "Flotante",
     *   "indexed": "Indexado"
     * }
     */
    public function priceTypes(): JsonResponse
    {
        return response()->json(EnergyTradingOrder::getPriceTypes());
    }

    /**
     * Get available execution types.
     *
     * @authenticated
     * @response 200 {
     *   "immediate": "Inmediato",
     *   "good_till_cancelled": "Bueno hasta Cancelar",
     *   "fill_or_kill": "Llenar o Cancelar"
     * }
     */
    public function executionTypes(): JsonResponse
    {
        return response()->json(EnergyTradingOrder::getExecutionTypes());
    }

    /**
     * Get available priorities.
     *
     * @authenticated
     * @response 200 {
     *   "low": "Baja",
     *   "normal": "Normal",
     *   "high": "Alta",
     *   "urgent": "Urgente"
     * }
     */
    public function priorities(): JsonResponse
    {
        return response()->json(EnergyTradingOrder::getPriorities());
    }

    /**
     * Update order status.
     *
     * @authenticated
     * @urlParam id integer required The order ID. Example: 1
     * @bodyParam order_status string required The new status. Example: "active"
     * @bodyParam notes string Additional notes. Example: "Status updated by admin"
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "order_status": "active",
     *     "message": "Estado actualizado exitosamente"
     *   }
     * }
     */
    public function updateStatus(Request $request, EnergyTradingOrder $energyTradingOrder): JsonResponse
    {
        try {
            $request->validate([
                'order_status' => ['required', 'string', 'in:' . implode(',', array_keys(EnergyTradingOrder::getOrderStatuses()))],
                'notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            $energyTradingOrder->update([
                'order_status' => $request->order_status,
                'notes' => $request->notes ? $energyTradingOrder->notes . "\n" . $request->notes : $energyTradingOrder->notes
            ]);

            Log::info('Trading order status updated', [
                'order_id' => $energyTradingOrder->id,
                'old_status' => $energyTradingOrder->getOriginal('order_status'),
                'new_status' => $request->order_status,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyTradingOrderResource($energyTradingOrder),
                'message' => 'Estado actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating trading order status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar el estado',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cancel a trading order.
     *
     * @authenticated
     * @urlParam id integer required The order ID. Example: 1
     * @bodyParam notes string Cancellation reason. Example: "Order cancelled by user"
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "order_status": "cancelled",
     *     "message": "Orden cancelada exitosamente"
     *   }
     * }
     */
    public function cancel(Request $request, EnergyTradingOrder $energyTradingOrder): JsonResponse
    {
        try {
            if (!$energyTradingOrder->canBeCancelled()) {
                return response()->json([
                    'message' => 'La orden no puede ser cancelada en su estado actual'
                ], 400);
            }

            DB::beginTransaction();

            $energyTradingOrder->update([
                'order_status' => EnergyTradingOrder::ORDER_STATUS_CANCELLED,
                'notes' => $request->notes ? $energyTradingOrder->notes . "\n" . $request->notes : $energyTradingOrder->notes
            ]);

            Log::info('Trading order cancelled', [
                'order_id' => $energyTradingOrder->id,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyTradingOrderResource($energyTradingOrder),
                'message' => 'Orden cancelada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling trading order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al cancelar la orden',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate a trading order.
     *
     * @authenticated
     * @urlParam id integer required The order ID. Example: 1
     * @bodyParam quantity_mwh numeric New quantity. Example: 200.00
     * @bodyParam price_per_mwh numeric New price. Example: 80.00
     *
     * @response 201 {
     *   "data": {
     *     "id": 2,
     *     "order_number": "ORDER-002",
     *     "message": "Orden duplicada exitosamente"
     *   }
     * }
     */
    public function duplicate(Request $request, EnergyTradingOrder $energyTradingOrder): JsonResponse
    {
        try {
            $request->validate([
                'quantity_mwh' => 'nullable|numeric|min:0.01|max:999999.99',
                'price_per_mwh' => 'nullable|numeric|min:0.01|max:999999.99'
            ]);

            DB::beginTransaction();

            $newOrder = $energyTradingOrder->replicate();
            $newOrder->order_number = 'ORDER-' . str_pad(EnergyTradingOrder::max('id') + 1, 6, '0', STR_PAD_LEFT);
            $newOrder->order_status = EnergyTradingOrder::ORDER_STATUS_PENDING;
            $newOrder->filled_quantity_mwh = 0;
            $newOrder->remaining_quantity_mwh = $request->quantity_mwh ?? $energyTradingOrder->quantity_mwh;
            $newOrder->filled_value = 0;
            $newOrder->remaining_value = 0;
            $newOrder->approved_at = null;
            $newOrder->executed_at = null;
            $newOrder->approved_by = null;
            $newOrder->executed_by = null;

            if ($request->filled('quantity_mwh')) {
                $newOrder->quantity_mwh = $request->quantity_mwh;
            }

            if ($request->filled('price_per_mwh')) {
                $newOrder->price_per_mwh = $request->price_per_mwh;
            }

            $newOrder->total_value = $newOrder->quantity_mwh * $newOrder->price_per_mwh;
            $newOrder->remaining_value = $newOrder->total_value;
            $newOrder->save();

            Log::info('Trading order duplicated', [
                'original_order_id' => $energyTradingOrder->id,
                'new_order_id' => $newOrder->id,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'data' => new EnergyTradingOrderResource($newOrder),
                'message' => 'Orden duplicada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating trading order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al duplicar la orden',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get active orders.
     *
     * @authenticated
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORDER-001",
     *       "order_status": "active"
     *     }
     *   ]
     * }
     */
    public function active(): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::active()
                ->with(['trader', 'pool'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching active orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes activas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pending orders.
     *
     * @authenticated
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORDER-001",
     *       "order_status": "pending"
     *     }
     *   ]
     * }
     */
    public function pending(): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::pending()
                ->with(['trader', 'pool'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching pending orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes pendientes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get filled orders.
     *
     * @authenticated
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORDER-001",
     *       "order_status": "filled"
     *     }
     *   ]
     * }
     */
    public function filled(): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::filled()
                ->with(['trader', 'pool'])
                ->orderBy('executed_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching filled orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes completadas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get orders by type.
     *
     * @authenticated
     * @urlParam type string required The order type. Example: "buy"
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_type": "buy"
     *     }
     *   ]
     * }
     */
    public function byType(string $type): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::byOrderType($type)
                ->with(['trader', 'pool'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching orders by type: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes por tipo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get orders by side.
     *
     * @authenticated
     * @urlParam side string required The order side. Example: "buy"
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_side": "buy"
     *     }
     *   ]
     * }
     */
    public function bySide(string $side): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::byOrderSide($side)
                ->with(['trader', 'pool'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching orders by side: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes por lado',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get orders by trader.
     *
     * @authenticated
     * @urlParam trader_id integer required The trader ID. Example: 1
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "trader_id": 1
     *     }
     *   ]
     * }
     */
    public function byTrader(int $traderId): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::byTrader($traderId)
                ->with(['trader', 'pool'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching orders by trader: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes por trader',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get orders by pool.
     *
     * @authenticated
     * @urlParam pool_id integer required The pool ID. Example: 1
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "pool_id": 1
     *     }
     *   ]
     * }
     */
    public function byPool(int $poolId): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::byPool($poolId)
                ->with(['trader', 'pool'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching orders by pool: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes por pool',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get high priority orders.
     *
     * @authenticated
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "priority": "high"
     *     }
     *   ]
     * }
     */
    public function highPriority(): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::highPriority()
                ->with(['trader', 'pool'])
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching high priority orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes de alta prioridad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get negotiable orders.
     *
     * @authenticated
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "is_negotiable": true
     *     }
     *   ]
     * }
     */
    public function negotiable(): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::negotiable()
                ->with(['trader', 'pool'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching negotiable orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes negociables',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get expiring orders.
     *
     * @authenticated
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "expiry_time": "2024-12-31 23:59:59"
     *     }
     *   ]
     * }
     */
    public function expiring(): JsonResponse
    {
        try {
            $orders = EnergyTradingOrder::expired()
                ->with(['trader', 'pool'])
                ->orderBy('expiry_time', 'asc')
                ->get();

            return response()->json([
                'data' => EnergyTradingOrderResource::collection($orders)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching expiring orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener órdenes expirando',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
