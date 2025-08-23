<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TaxCalculation\StoreTaxCalculationRequest;
use App\Http\Requests\Api\V1\TaxCalculation\UpdateTaxCalculationRequest;
use App\Http\Resources\Api\V1\TaxCalculation\TaxCalculationCollection;
use App\Http\Resources\Api\V1\TaxCalculation\TaxCalculationResource;
use App\Models\TaxCalculation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * @group Tax Calculation Management
 *
 * APIs for managing tax calculations
 */
class TaxCalculationController extends Controller
{
    /**
     * Display a listing of tax calculations.
     *
     * @queryParam tax_type string Filter by tax type (income_tax, sales_tax, value_added_tax, property_tax, excise_tax, customs_duty, energy_tax, carbon_tax, environmental_tax, other). Example: income_tax
     * @queryParam calculation_type string Filter by calculation type (automatic, manual, scheduled, event_triggered, batch, real_time, other). Example: automatic
     * @queryParam status string Filter by status (draft, calculated, reviewed, approved, applied, cancelled, error). Example: calculated
     * @queryParam priority string Filter by priority (low, normal, high, urgent, critical). Example: high
     * @queryParam entity_id integer Filter by entity ID. Example: 1
     * @queryParam entity_type string Filter by entity type. Example: App\Models\EnergyInstallation
     * @queryParam transaction_id integer Filter by transaction ID. Example: 1
     * @queryParam transaction_type string Filter by transaction type. Example: App\Models\EnergyContract
     * @queryParam search string Search in name, description, and calculation_number. Example: income
     * @queryParam sort string Sort by field (name, tax_type, calculation_type, status, priority, total_amount_due, due_date, created_at). Example: -created_at
     * @queryParam limit integer Number of items per page. Example: 15
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TaxCalculation::query()
                ->with(['entity', 'transaction', 'calculatedBy', 'reviewedBy', 'approvedBy', 'appliedBy', 'createdBy']);

            // Filtros
            if ($request->filled('tax_type')) {
                $query->byTaxType($request->tax_type);
            }

            if ($request->filled('calculation_type')) {
                $query->byCalculationType($request->calculation_type);
            }

            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }

            if ($request->filled('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->filled('entity_id')) {
                $query->byEntity($request->entity_id, $request->entity_type);
            }

            if ($request->filled('transaction_id')) {
                $query->byTransaction($request->transaction_id, $request->transaction_type);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('calculation_number', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortField = $request->get('sort', '-created_at');
            if (str_starts_with($sortField, '-')) {
                $field = substr($sortField, 1);
                $direction = 'desc';
            } else {
                $field = $sortField;
                $direction = 'asc';
            }

            $allowedSortFields = ['name', 'tax_type', 'calculation_type', 'status', 'priority', 'total_amount_due', 'due_date', 'created_at'];
            if (in_array($field, $allowedSortFields)) {
                $query->orderBy($field, $direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Paginación
            $limit = min($request->get('limit', 15), 100);
            $calculations = $query->paginate($limit);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching tax calculations: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching tax calculations'], 500);
        }
    }

    /**
     * Store a newly created tax calculation.
     *
     * @bodyParam calculation_number string The calculation number. Example: TC-001
     * @bodyParam name string required The name of the tax calculation. Example: Income Tax Q1 2024
     * @bodyParam description string The description of the tax calculation. Example: Quarterly income tax calculation
     * @bodyParam tax_type string required The type of tax. Example: income_tax
     * @bodyParam calculation_type string required The type of calculation. Example: automatic
     * @bodyParam status string The status of the calculation. Example: draft
     * @bodyParam priority string The priority level. Example: normal
     * @bodyParam entity_id integer The entity ID. Example: 1
     * @bodyParam entity_type string The entity type. Example: App\Models\EnergyInstallation
     * @bodyParam transaction_id integer The transaction ID. Example: 1
     * @bodyParam transaction_type string The transaction type. Example: App\Models\EnergyContract
     * @bodyParam tax_period_start date required The start date of the tax period. Example: 2024-01-01
     * @bodyParam tax_period_end date required The end date of the tax period. Example: 2024-03-31
     * @bodyParam calculation_date date The calculation date. Example: 2024-04-01
     * @bodyParam due_date date The due date. Example: 2024-04-30
     * @bodyParam taxable_amount numeric required The taxable amount. Example: 10000.00
     * @bodyParam tax_rate numeric required The tax rate. Example: 25.00
     * @bodyParam currency string The currency. Example: EUR
     * @bodyParam tags array The tags for the calculation. Example: ["quarterly", "income", "high-priority"]
     */
    public function store(StoreTaxCalculationRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $calculation = TaxCalculation::create($request->validated());

            DB::commit();

            Log::info('Tax calculation created', ['calculation_id' => $calculation->id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Tax calculation created successfully',
                'data' => new TaxCalculationResource($calculation)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating tax calculation: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating tax calculation'], 500);
        }
    }

    /**
     * Display the specified tax calculation.
     *
     * @urlParam id integer required The ID of the calculation. Example: 1
     */
    public function show(TaxCalculation $calculation): JsonResponse
    {
        try {
            $calculation->load(['entity', 'transaction', 'calculatedBy', 'reviewedBy', 'approvedBy', 'appliedBy', 'createdBy']);

            return response()->json([
                'data' => new TaxCalculationResource($calculation)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching tax calculation: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching tax calculation'], 500);
        }
    }

    /**
     * Update the specified tax calculation.
     *
     * @urlParam id integer required The ID of the calculation. Example: 1
     * @bodyParam name string The name of the tax calculation. Example: Updated Income Tax Q1 2024
     * @bodyParam description string The description of the tax calculation. Example: Updated quarterly calculation
     * @bodyParam tax_type string The type of tax. Example: income_tax
     * @bodyParam calculation_type string The type of calculation. Example: automatic
     * @bodyParam status string The status of the calculation. Example: calculated
     * @bodyParam priority string The priority level. Example: high
     * @bodyParam taxable_amount numeric The taxable amount. Example: 12000.00
     * @bodyParam tax_rate numeric The tax rate. Example: 25.00
     * @bodyParam tags array The tags for the calculation. Example: ["quarterly", "income", "high-priority", "updated"]
     */
    public function update(UpdateTaxCalculationRequest $request, TaxCalculation $calculation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $calculation->update($request->validated());

            DB::commit();

            Log::info('Tax calculation updated', ['calculation_id' => $calculation->id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Tax calculation updated successfully',
                'data' => new TaxCalculationResource($calculation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating tax calculation: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating tax calculation'], 500);
        }
    }

    /**
     * Remove the specified tax calculation.
     *
     * @urlParam id integer required The ID of the calculation. Example: 1
     */
    public function destroy(TaxCalculation $calculation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $calculation->delete();

            DB::commit();

            Log::info('Tax calculation deleted', ['calculation_id' => $calculation->id, 'user_id' => auth()->id()]);

            return response()->json(['message' => 'Tax calculation deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting tax calculation: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting tax calculation'], 500);
        }
    }

    /**
     * Get statistics for tax calculations.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total' => TaxCalculation::count(),
                'by_type' => TaxCalculation::selectRaw('tax_type, COUNT(*) as count')
                    ->groupBy('tax_type')
                    ->pluck('count', 'tax_type'),
                'by_status' => TaxCalculation::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'by_calculation_type' => TaxCalculation::selectRaw('calculation_type, COUNT(*) as count')
                    ->groupBy('calculation_type')
                    ->pluck('count', 'calculation_type'),
                'by_priority' => TaxCalculation::selectRaw('priority, COUNT(*) as count')
                    ->groupBy('priority')
                    ->pluck('count', 'priority'),
                'by_currency' => TaxCalculation::selectRaw('currency, COUNT(*) as count')
                    ->groupBy('currency')
                    ->pluck('count', 'currency'),
                'recent' => TaxCalculation::where('created_at', '>=', now()->subDays(30))->count(),
                'overdue' => TaxCalculation::overdue()->count(),
                'due_soon' => TaxCalculation::dueSoon(30)->count(),
                'total_amount_due' => TaxCalculation::unpaid()->sum('total_amount_due'),
                'total_amount_paid' => TaxCalculation::paid()->sum('amount_paid'),
            ];

            return response()->json(['data' => $stats]);

        } catch (\Exception $e) {
            Log::error('Error fetching tax calculation statistics: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching statistics'], 500);
        }
    }

    /**
     * Get tax types.
     */
    public function types(): JsonResponse
    {
        return response()->json(['data' => TaxCalculation::getTaxTypes()]);
    }

    /**
     * Get calculation types.
     */
    public function calculationTypes(): JsonResponse
    {
        return response()->json(['data' => TaxCalculation::getCalculationTypes()]);
    }

    /**
     * Get statuses.
     */
    public function statuses(): JsonResponse
    {
        return response()->json(['data' => TaxCalculation::getStatuses()]);
    }

    /**
     * Get priorities.
     */
    public function priorities(): JsonResponse
    {
        return response()->json(['data' => TaxCalculation::getPriorities()]);
    }

    /**
     * Update calculation status.
     *
     * @urlParam id integer required The ID of the calculation. Example: 1
     * @bodyParam status string required The new status. Example: calculated
     */
    public function updateStatus(Request $request, TaxCalculation $calculation): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['required', Rule::in(array_keys(TaxCalculation::getStatuses()))],
            ]);

            DB::beginTransaction();

            $calculation->update(['status' => $request->status]);

            DB::commit();

            Log::info('Tax calculation status updated', [
                'calculation_id' => $calculation->id, 
                'status' => $request->status,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Calculation status updated successfully',
                'data' => new TaxCalculationResource($calculation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating calculation status: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating calculation status'], 500);
        }
    }

    /**
     * Duplicate a tax calculation.
     *
     * @urlParam id integer required The ID of the calculation to duplicate. Example: 1
     */
    public function duplicate(TaxCalculation $calculation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $newCalculation = $calculation->replicate();
            $newCalculation->name = $calculation->name . ' (Copy)';
            $newCalculation->status = 'draft';
            $newCalculation->calculation_number = 'TC-' . uniqid();
            $newCalculation->created_by = auth()->id();
            $newCalculation->reviewed_by = null;
            $newCalculation->reviewed_at = null;
            $newCalculation->approved_by = null;
            $newCalculation->approved_at = null;
            $newCalculation->applied_by = null;
            $newCalculation->applied_at = null;
            $newCalculation->save();

            DB::commit();

            Log::info('Tax calculation duplicated', [
                'original_id' => $calculation->id, 
                'new_id' => $newCalculation->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Tax calculation duplicated successfully',
                'data' => new TaxCalculationResource($newCalculation)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating tax calculation: ' . $e->getMessage());
            return response()->json(['message' => 'Error duplicating tax calculation'], 500);
        }
    }

    /**
     * Get overdue calculations.
     */
    public function overdue(): JsonResponse
    {
        try {
            $calculations = TaxCalculation::overdue()
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('due_date', 'asc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching overdue calculations: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching overdue calculations'], 500);
        }
    }

    /**
     * Get calculations due soon.
     */
    public function dueSoon(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 30);
            $calculations = TaxCalculation::dueSoon($days)
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('due_date', 'asc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching calculations due soon: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching calculations due soon'], 500);
        }
    }

    /**
     * Get calculations by type.
     *
     * @queryParam type string required The tax type. Example: income_tax
     */
    public function byType(Request $request): JsonResponse
    {
        try {
            $request->validate(['type' => 'required|string']);

            $calculations = TaxCalculation::byTaxType($request->type)
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching calculations by type: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching calculations by type'], 500);
        }
    }

    /**
     * Get calculations by calculation type.
     *
     * @queryParam calculation_type string required The calculation type. Example: automatic
     */
    public function byCalculationType(Request $request): JsonResponse
    {
        try {
            $request->validate(['calculation_type' => 'required|string']);

            $calculations = TaxCalculation::byCalculationType($request->calculation_type)
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching calculations by calculation type: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching calculations by calculation type'], 500);
        }
    }

    /**
     * Get high priority calculations.
     */
    public function highPriority(): JsonResponse
    {
        try {
            $calculations = TaxCalculation::highPriority()
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('priority', 'desc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching high priority calculations: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching high priority calculations'], 500);
        }
    }

    /**
     * Get estimated calculations.
     */
    public function estimated(): JsonResponse
    {
        try {
            $calculations = TaxCalculation::estimated()
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching estimated calculations: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching estimated calculations'], 500);
        }
    }

    /**
     * Get final calculations.
     */
    public function final(): JsonResponse
    {
        try {
            $calculations = TaxCalculation::final()
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching final calculations: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching final calculations'], 500);
        }
    }

    /**
     * Get calculations by entity.
     *
     * @queryParam entity_id integer required The entity ID. Example: 1
     * @queryParam entity_type string The entity type. Example: App\Models\EnergyInstallation
     */
    public function byEntity(Request $request): JsonResponse
    {
        try {
            $request->validate(['entity_id' => 'required|integer']);

            $calculations = TaxCalculation::byEntity($request->entity_id, $request->entity_type)
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching calculations by entity: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching calculations by entity'], 500);
        }
    }

    /**
     * Get calculations by transaction.
     *
     * @queryParam transaction_id integer required The transaction ID. Example: 1
     * @queryParam transaction_type string The transaction type. Example: App\Models\EnergyContract
     */
    public function byTransaction(Request $request): JsonResponse
    {
        try {
            $request->validate(['transaction_id' => 'required|integer']);

            $calculations = TaxCalculation::byTransaction($request->transaction_id, $request->transaction_type)
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching calculations by transaction: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching calculations by transaction'], 500);
        }
    }

    /**
     * Get calculations by currency.
     *
     * @queryParam currency string required The currency. Example: EUR
     */
    public function byCurrency(Request $request): JsonResponse
    {
        try {
            $request->validate(['currency' => 'required|string']);

            $calculations = TaxCalculation::byCurrency($request->currency)
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching calculations by currency: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching calculations by currency'], 500);
        }
    }

    /**
     * Get calculations by amount range.
     *
     * @queryParam min_amount numeric required The minimum amount. Example: 1000
     * @queryParam max_amount numeric required The maximum amount. Example: 10000
     */
    public function byAmountRange(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'min_amount' => 'required|numeric',
                'max_amount' => 'required|numeric|gte:min_amount'
            ]);

            $calculations = TaxCalculation::byAmountRange($request->min_amount, $request->max_amount)
                ->with(['entity', 'transaction', 'createdBy'])
                ->orderBy('total_amount_due', 'asc')
                ->paginate(15);

            return response()->json(new TaxCalculationCollection($calculations));

        } catch (\Exception $e) {
            Log::error('Error fetching calculations by amount range: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching calculations by amount range'], 500);
        }
    }
}
