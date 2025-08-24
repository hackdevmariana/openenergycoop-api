<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\SaleOrder\StoreSaleOrderRequest;
use App\Http\Requests\Api\V1\SaleOrder\UpdateSaleOrderRequest;
use App\Http\Resources\Api\V1\SaleOrder\SaleOrderResource;
use App\Http\Resources\Api\V1\SaleOrder\SaleOrderCollection;
use App\Models\SaleOrder;
use App\Models\Organization;
use App\Models\User;
use App\Models\CustomerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Sale Order Management
 *
 * APIs for managing sales orders and transactions
 */
class SaleOrderController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of sale orders.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in order_number, customer_name, customer_email. Example: "ORD-001"
     * @queryParam status string Filter by order status. Example: "pending"
     * @queryParam payment_status string Filter by payment status. Example: "paid"
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam customer_id integer Filter by customer. Example: 1
     * @queryParam total_min float Minimum order total. Example: 100.0
     * @queryParam total_max float Maximum order total. Example: 1000.0
     * @queryParam created_at_from date Filter by creation date from. Example: "2024-01-01"
     * @queryParam created_at_to date Filter by creation date to. Example: "2024-12-31"
     * @queryParam sort_by string Sort field. Example: "created_at"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "desc"
     * @queryParam is_urgent boolean Filter by urgent status. Example: true
     * @queryParam has_discount boolean Filter by discount usage. Example: true
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORD-001",
     *       "customer_name": "John Doe",
     *       "customer_email": "john@example.com",
     *       "status": "pending",
     *       "payment_status": "pending",
     *       "total": 150.00,
     *       "created_at": "2024-01-15T10:00:00Z"
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
            $query = SaleOrder::with(['organization', 'customer', 'items', 'payments']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            // Filtros por estado
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtros por estado de pago
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            // Filtros por organización
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Filtros por cliente
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // Filtros por total
            if ($request->filled('total_min')) {
                $query->where('total', '>=', $request->total_min);
            }
            if ($request->filled('total_max')) {
                $query->where('total', '<=', $request->total_max);
            }

            // Filtros por fecha de creación
            if ($request->filled('created_at_from')) {
                $query->where('created_at', '>=', $request->created_at_from);
            }
            if ($request->filled('created_at_to')) {
                $query->where('created_at', '<=', $request->created_at_to . ' 23:59:59');
            }

            // Filtros por estado urgente
            if ($request->has('is_urgent')) {
                $query->where('is_urgent', $request->boolean('is_urgent'));
            }

            // Filtros por descuento
            if ($request->has('has_discount')) {
                if ($request->boolean('has_discount')) {
                    $query->where('discount_amount', '>', 0);
                } else {
                    $query->where('discount_amount', '=', 0);
                }
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $allowedSortFields = ['order_number', 'customer_name', 'status', 'payment_status', 'total', 'created_at'];
            
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100); // Limitar entre 1 y 100

            $saleOrders = $query->paginate($perPage);

            return response()->json([
                'data' => SaleOrderCollection::make($saleOrders),
                'message' => 'Sale orders retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving sale orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving sale orders',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created sale order.
     *
     * @authenticated
     * @bodyParam order_number string The order number. Example: "ORD-001"
     * @bodyParam customer_id integer required The customer ID. Example: 1
     * @bodyParam customer_name string required The customer name. Example: "John Doe"
     * @bodyParam customer_email string required The customer email. Example: "john@example.com"
     * @bodyParam customer_phone string The customer phone. Example: "+1-555-0123"
     * @bodyParam customer_address string The customer address. Example: "123 Main St"
     * @bodyParam customer_city string The customer city. Example: "New York"
     * @bodyParam customer_state string The customer state. Example: "NY"
     * @bodyParam customer_country string The customer country. Example: "USA"
     * @bodyParam customer_postal_code string The customer postal code. Example: "10001"
     * @bodyParam status string required The order status. Example: "pending"
     * @bodyParam payment_status string required The payment status. Example: "pending"
     * @bodyParam payment_method string The payment method. Example: "credit_card"
     * @bodyParam subtotal float The subtotal amount. Example: 100.00
     * @bodyParam tax_amount float The tax amount. Example: 10.00
     * @bodyParam shipping_amount float The shipping amount. Example: 15.00
     * @bodyParam discount_amount float The discount amount. Example: 20.00
     * @bodyParam total float required The total amount. Example: 105.00
     * @bodyParam currency string The currency code. Example: "USD"
     * @bodyParam notes text The order notes. Example: "Customer requested express shipping"
     * @bodyParam internal_notes text Internal notes. Example: "High-value customer"
     * @bodyParam is_urgent boolean Whether the order is urgent. Example: false
     * @bodyParam shipping_method string The shipping method. Example: "standard"
     * @bodyParam tracking_number string The tracking number. Example: "TRK123456"
     * @bodyParam expected_delivery_date date The expected delivery date. Example: "2024-01-20"
     * @bodyParam discount_code string The discount code used. Example: "SUMMER20"
     * @bodyParam organization_id integer The organization ID. Example: 1
     * @bodyParam items array required The order items. Example: [{"product_id": 1, "quantity": 2, "unit_price": 50.00}]
     * @bodyParam tags array The tags. Example: ["online", "new-customer"]
     *
     * @response 201 {
     *   "message": "Sale order created successfully",
     *   "data": {
     *     "id": 1,
     *     "order_number": "ORD-001",
     *     "customer_name": "John Doe",
     *     "status": "pending",
     *     "total": 105.00
     *   }
     * }
     */
    public function store(StoreSaleOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $saleOrder = SaleOrder::create($request->validated());

            // Crear items del pedido si se proporcionan
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    $saleOrder->items()->create($item);
                }
            }

            // Cargar relaciones para la respuesta
            $saleOrder->load(['organization', 'customer', 'items', 'payments']);

            DB::commit();

            return response()->json([
                'message' => 'Sale order created successfully',
                'data' => new SaleOrderResource($saleOrder)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sale order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error creating sale order',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified sale order.
     *
     * @authenticated
     * @urlParam saleOrder integer required The sale order ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "order_number": "ORD-001",
     *     "customer_name": "John Doe",
     *     "customer_email": "john@example.com",
     *     "status": "pending",
     *     "payment_status": "pending",
     *     "total": 105.00,
     *     "created_at": "2024-01-15T10:00:00Z",
     *     "links": {
     *       "self": "http://localhost/api/v1/sale-orders/1",
     *       "edit": "http://localhost/api/v1/sale-orders/1",
     *       "delete": "http://localhost/api/v1/sale-orders/1"
     *     }
     *   }
     * }
     */
    public function show(SaleOrder $saleOrder): JsonResponse
    {
        try {
            $saleOrder->load(['organization', 'customer', 'items', 'payments']);

            return response()->json([
                'data' => new SaleOrderResource($saleOrder)
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving sale order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving sale order',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified sale order.
     *
     * @authenticated
     * @urlParam saleOrder integer required The sale order ID. Example: 1
     * @bodyParam status string The order status. Example: "processing"
     * @bodyParam payment_status string The payment status. Example: "paid"
     * @bodyParam payment_method string The payment method. Example: "credit_card"
     * @bodyParam tracking_number string The tracking number. Example: "TRK123456"
     * @bodyParam expected_delivery_date date The expected delivery date. Example: "2024-01-22"
     * @bodyParam notes text The order notes. Example: "Order is being processed"
     * @bodyParam internal_notes text Internal notes. Example: "Customer called about delivery"
     * @bodyParam is_urgent boolean Whether the order is urgent. Example: true
     * @bodyParam shipping_method string The shipping method. Example: "express"
     * @bodyParam tags array The tags. Example: ["urgent", "express-shipping"]
     *
     * @response 200 {
     *   "message": "Sale order updated successfully",
     *   "data": {
     *     "id": 1,
     *     "order_number": "ORD-001",
     *     "status": "processing",
     *     "payment_status": "paid"
     *   }
     * }
     */
    public function update(UpdateSaleOrderRequest $request, SaleOrder $saleOrder): JsonResponse
    {
        try {
            DB::beginTransaction();

            $saleOrder->update($request->validated());

            // Cargar relaciones para la respuesta
            $saleOrder->load(['organization', 'customer', 'items', 'payments']);

            DB::commit();

            return response()->json([
                'message' => 'Sale order updated successfully',
                'data' => new SaleOrderResource($saleOrder)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sale order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating sale order',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified sale order.
     *
     * @authenticated
     * @urlParam saleOrder integer required The sale order ID. Example: 1
     *
     * @response 200 {
     *   "message": "Sale order deleted successfully"
     * }
     */
    public function destroy(SaleOrder $saleOrder): JsonResponse
    {
        try {
            DB::beginTransaction();

            $saleOrder->delete();

            DB::commit();

            return response()->json([
                'message' => 'Sale order deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sale order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deleting sale order',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pending sale orders.
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORD-001",
     *       "customer_name": "John Doe",
     *       "status": "pending",
     *       "total": 105.00
     *     }
     *   ]
     * }
     */
    public function pending(Request $request): JsonResponse
    {
        try {
            $query = SaleOrder::where('status', 'pending')
                             ->with(['organization', 'customer'])
                             ->orderBy('created_at', 'asc');

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $saleOrders = $query->paginate($perPage);

            return response()->json([
                'data' => SaleOrderCollection::make($saleOrders),
                'message' => 'Pending sale orders retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving pending sale orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving pending sale orders',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get urgent sale orders.
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORD-001",
     *       "customer_name": "John Doe",
     *       "status": "pending",
     *       "is_urgent": true,
     *       "total": 105.00
     *     }
     *   ]
     * }
     */
    public function urgent(Request $request): JsonResponse
    {
        try {
            $query = SaleOrder::where('is_urgent', true)
                             ->with(['organization', 'customer'])
                             ->orderBy('created_at', 'asc');

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $saleOrders = $query->paginate($perPage);

            return response()->json([
                'data' => SaleOrderCollection::make($saleOrders),
                'message' => 'Urgent sale orders retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving urgent sale orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving urgent sale orders',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get sale orders by status.
     *
     * @queryParam status string required The order status. Example: "processing"
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORD-001",
     *       "customer_name": "John Doe",
     *       "status": "processing",
     *       "total": 105.00
     *     }
     *   ]
     * }
     */
    public function byStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled,refunded'
            ]);

            $query = SaleOrder::where('status', $request->status)
                             ->with(['organization', 'customer']);

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Ordenar por fecha de creación
            $query->orderBy('created_at', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $saleOrders = $query->paginate($perPage);

            return response()->json([
                'data' => SaleOrderCollection::make($saleOrders),
                'message' => "Sale orders with status '{$request->status}' retrieved successfully"
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving sale orders by status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving sale orders by status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get sale orders by customer.
     *
     * @queryParam customer_id integer required The customer ID. Example: 1
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_number": "ORD-001",
     *       "status": "delivered",
     *       "total": 105.00,
     *       "created_at": "2024-01-15T10:00:00Z"
     *     }
     *   ]
     * }
     */
    public function byCustomer(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'customer_id' => 'required|exists:customer_profiles,id'
            ]);

            $query = SaleOrder::where('customer_id', $request->customer_id)
                             ->with(['organization', 'customer']);

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Ordenar por fecha de creación
            $query->orderBy('created_at', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $saleOrders = $query->paginate($perPage);

            return response()->json([
                'data' => SaleOrderCollection::make($saleOrders),
                'message' => 'Customer sale orders retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving customer sale orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving customer sale orders',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get sale order statistics.
     *
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam period string The period for statistics. Example: "month"
     * @queryParam customer_id integer Filter by customer. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total_orders": 150,
     *     "pending_orders": 25,
     *     "processing_orders": 30,
     *     "shipped_orders": 45,
     *     "delivered_orders": 40,
     *     "cancelled_orders": 10,
     *     "total_revenue": 15000.00,
     *     "average_order_value": 100.00,
     *     "orders_by_status": {
     *       "pending": 25,
     *       "processing": 30,
     *       "shipped": 45,
     *       "delivered": 40,
     *       "cancelled": 10
     *     },
     *     "revenue_by_status": {
     *       "pending": 2500.00,
     *       "processing": 3000.00,
     *       "shipped": 4500.00,
     *       "delivered": 4000.00,
     *       "cancelled": 1000.00
     *     }
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->get('organization_id');
            $period = $request->get('period', 'month');
            $customerId = $request->get('customer_id');

            $query = SaleOrder::query();

            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }

            if ($customerId) {
                $query->where('customer_id', $customerId);
            }

            $totalOrders = $query->count();
            $pendingOrders = (clone $query)->where('status', 'pending')->count();
            $processingOrders = (clone $query)->where('status', 'processing')->count();
            $shippedOrders = (clone $query)->where('status', 'shipped')->count();
            $deliveredOrders = (clone $query)->where('status', 'delivered')->count();
            $cancelledOrders = (clone $query)->where('status', 'cancelled')->count();

            // Estadísticas por estado
            $ordersByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Estadísticas de ingresos
            $totalRevenue = (clone $query)->sum('total');
            $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            // Ingresos por estado
            $revenueByStatus = (clone $query)
                ->selectRaw('status, SUM(total) as revenue')
                ->groupBy('status')
                ->pluck('revenue', 'status')
                ->toArray();

            return response()->json([
                'data' => [
                    'total_orders' => $totalOrders,
                    'pending_orders' => $pendingOrders,
                    'processing_orders' => $processingOrders,
                    'shipped_orders' => $shippedOrders,
                    'delivered_orders' => $deliveredOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'total_revenue' => round($totalRevenue, 2),
                    'average_order_value' => round($averageOrderValue, 2),
                    'orders_by_status' => $ordersByStatus,
                    'revenue_by_status' => array_map('round', $revenueByStatus),
                ],
                'period' => $period,
                'message' => 'Sale order statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving sale order statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving sale order statistics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update sale order status.
     *
     * @authenticated
     * @urlParam saleOrder integer required The sale order ID. Example: 1
     * @bodyParam status string required The new status. Example: "processing"
     * @bodyParam notes string Notes about the status change. Example: "Order is being processed"
     *
     * @response 200 {
     *   "message": "Sale order status updated successfully"
     * }
     */
    public function updateStatus(Request $request, SaleOrder $saleOrder): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled,refunded',
                'notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            $saleOrder->update([
                'status' => $request->status,
                'notes' => $request->notes ? $saleOrder->notes . "\n" . now()->format('Y-m-d H:i:s') . " - Status changed to {$request->status}: {$request->notes}" : $saleOrder->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Sale order status updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sale order status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating sale order status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update payment status.
     *
     * @authenticated
     * @urlParam saleOrder integer required The sale order ID. Example: 1
     * @bodyParam payment_status string required The new payment status. Example: "paid"
     * @bodyParam payment_method string The payment method. Example: "credit_card"
     * @bodyParam notes string Notes about the payment. Example: "Payment received via credit card"
     *
     * @response 200 {
     *   "message": "Payment status updated successfully"
     * }
     */
    public function updatePaymentStatus(Request $request, SaleOrder $saleOrder): JsonResponse
    {
        try {
            $request->validate([
                'payment_status' => 'required|string|in:pending,processing,paid,failed,cancelled,refunded',
                'payment_method' => 'nullable|string|max:100',
                'notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            $saleOrder->update([
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes ? $saleOrder->notes . "\n" . now()->format('Y-m-d H:i:s') . " - Payment status changed to {$request->payment_status}: {$request->notes}" : $saleOrder->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment status updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating payment status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mark sale order as urgent.
     *
     * @authenticated
     * @urlParam saleOrder integer required The sale order ID. Example: 1
     * @bodyParam is_urgent boolean Whether the order is urgent. Example: true
     * @bodyParam urgency_reason string Reason for urgency. Example: "Customer requested express processing"
     *
     * @response 200 {
     *   "message": "Sale order urgency updated successfully"
     * }
     */
    public function updateUrgency(Request $request, SaleOrder $saleOrder): JsonResponse
    {
        try {
            $request->validate([
                'is_urgent' => 'required|boolean',
                'urgency_reason' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            $saleOrder->update([
                'is_urgent' => $request->is_urgent,
                'notes' => $request->urgency_reason ? $saleOrder->notes . "\n" . now()->format('Y-m-d H:i:s') . " - Urgency updated: {$request->urgency_reason}" : $saleOrder->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Sale order urgency updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sale order urgency: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating sale order urgency',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate a sale order.
     *
     * @authenticated
     * @urlParam saleOrder integer required The sale order ID. Example: 1
     * @bodyParam customer_id integer The new customer ID. Example: 2
     * @bodyParam customer_name string The new customer name. Example: "Jane Smith"
     * @bodyParam customer_email string The new customer email. Example: "jane@example.com"
     *
     * @response 200 {
     *   "message": "Sale order duplicated successfully",
     *   "data": {
     *     "id": 2,
     *     "order_number": "ORD-002",
     *     "customer_name": "Jane Smith",
     *     "status": "pending"
     *   }
     * }
     */
    public function duplicate(Request $request, SaleOrder $saleOrder): JsonResponse
    {
        try {
            $request->validate([
                'customer_id' => 'nullable|exists:customer_profiles,id',
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
            ]);

            DB::beginTransaction();

            $newSaleOrder = $saleOrder->replicate();
            $newSaleOrder->order_number = $this->generateOrderNumber();
            $newSaleOrder->customer_id = $request->customer_id ?? $saleOrder->customer_id;
            $newSaleOrder->customer_name = $request->customer_name ?? $saleOrder->customer_name;
            $newSaleOrder->customer_email = $request->customer_email ?? $saleOrder->customer_email;
            $newSaleOrder->status = 'pending';
            $newSaleOrder->payment_status = 'pending';
            $newSaleOrder->tracking_number = null;
            $newSaleOrder->expected_delivery_date = null;
            $newSaleOrder->is_urgent = false;
            $newSaleOrder->notes = 'Duplicated from order ' . $saleOrder->order_number;
            $newSaleOrder->save();

            // Duplicar items del pedido
            foreach ($saleOrder->items as $item) {
                $newItem = $item->replicate();
                $newItem->sale_order_id = $newSaleOrder->id;
                $newItem->save();
            }

            // Cargar relaciones para la respuesta
            $newSaleOrder->load(['organization', 'customer', 'items']);

            DB::commit();

            return response()->json([
                'message' => 'Sale order duplicated successfully',
                'data' => new SaleOrderResource($newSaleOrder)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating sale order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error duplicating sale order',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $counter = SaleOrder::whereDate('created_at', today())->count() + 1;
        
        return "{$prefix}-{$date}-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }
}
