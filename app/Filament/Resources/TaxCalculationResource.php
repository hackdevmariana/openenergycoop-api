<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxCalculationResource\Pages;
use App\Filament\Resources\TaxCalculationResource\RelationManagers;
use App\Models\TaxCalculation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaxCalculationResource extends Resource
{
    protected static ?string $model = TaxCalculation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Finanzas y Contabilidad';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Cálculos de Impuestos';

    protected static ?string $modelLabel = 'Cálculo de Impuestos';

    protected static ?string $pluralModelLabel = 'Cálculos de Impuestos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('calculation_number')
                                    ->label('Número de Cálculo')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Número único del cálculo')
                                    ->copyable()
                                    ->tooltip('Haz clic para copiar'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre descriptivo del cálculo'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada del cálculo'),
                        Grid::make(3)
                            ->schema([
                                Select::make('tax_type')
                                    ->label('Tipo de Impuesto')
                                    ->options(TaxCalculation::getTaxTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('calculation_type')
                                    ->label('Tipo de Cálculo')
                                    ->options(TaxCalculation::getCalculationTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(TaxCalculation::getStatuses())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(TaxCalculation::getPriorities())
                                    ->required()
                                    ->searchable(),
                                Select::make('currency')
                                    ->label('Moneda')
                                    ->options([
                                        'EUR' => 'Euro (€)',
                                        'USD' => 'Dólar Estadounidense ($)',
                                        'GBP' => 'Libra Esterlina (£)',
                                        'MXN' => 'Peso Mexicano ($)',
                                        'CAD' => 'Dólar Canadiense ($)',
                                        'AUD' => 'Dólar Australiano ($)',
                                        'JPY' => 'Yen Japonés (¥)',
                                        'other' => 'Otra',
                                    ])
                                    ->default('EUR')
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Entidad y Transacción')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('entity_type')
                                    ->label('Tipo de Entidad')
                                    ->options([
                                        'App\Models\SaleOrder' => 'Orden de Venta',
                                        'App\Models\EnergyTradingOrder' => 'Orden de Comercio',
                                        'App\Models\EnergyTransfer' => 'Transferencia de Energía',
                                        'App\Models\EnergyPool' => 'Pool de Energía',
                                        'App\Models\ProductionProject' => 'Proyecto de Producción',
                                        'App\Models\MaintenanceTask' => 'Tarea de Mantenimiento',
                                        'App\Models\EnergyInstallation' => 'Instalación de Energía',
                                        'other' => 'Otra',
                                    ])
                                    ->searchable(),
                                TextInput::make('entity_id')
                                    ->label('ID de la Entidad')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('ID de la entidad relacionada'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('transaction_type')
                                    ->label('Tipo de Transacción')
                                    ->options([
                                        'sale' => 'Venta',
                                        'purchase' => 'Compra',
                                        'transfer' => 'Transferencia',
                                        'service' => 'Servicio',
                                        'rental' => 'Alquiler',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('transaction_id')
                                    ->label('ID de la Transacción')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('ID de la transacción relacionada'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Período Fiscal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('tax_period_start')
                                    ->label('Inicio del Período Fiscal')
                                    ->required()
                                    ->helperText('Fecha de inicio del período fiscal'),
                                DatePicker::make('tax_period_end')
                                    ->label('Fin del Período Fiscal')
                                    ->required()
                                    ->helperText('Fecha de fin del período fiscal'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('calculation_date')
                                    ->label('Fecha de Cálculo')
                                    ->required()
                                    ->helperText('Cuándo se realizó el cálculo'),
                                DatePicker::make('due_date')
                                    ->label('Fecha de Vencimiento')
                                    ->required()
                                    ->helperText('Cuándo vence el pago'),
                            ]),
                        DatePicker::make('payment_date')
                            ->label('Fecha de Pago')
                            ->helperText('Cuándo se realizó el pago'),
                    ])
                    ->collapsible(),

                Section::make('Cálculos Fiscales')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('taxable_amount')
                                    ->label('Monto Tributable')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Monto base para el cálculo'),
                                TextInput::make('tax_rate')
                                    ->label('Tasa de Impuesto (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('%')
                                    ->helperText('Tasa de impuesto aplicable'),
                                TextInput::make('tax_amount')
                                    ->label('Monto del Impuesto')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Monto calculado del impuesto'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('tax_base_amount')
                                    ->label('Base Impositiva')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Base imponible para el cálculo'),
                                TextInput::make('exemption_amount')
                                    ->label('Monto de Exenciones')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto exento de impuestos'),
                                TextInput::make('deduction_amount')
                                    ->label('Monto de Deducciones')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto deducible'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('credit_amount')
                                    ->label('Monto de Créditos')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Créditos fiscales aplicables'),
                                TextInput::make('net_tax_amount')
                                    ->label('Impuesto Neto')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Impuesto neto después de exenciones y deducciones'),
                                TextInput::make('total_amount_due')
                                    ->label('Total a Pagar')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Total final a pagar'),
                            ]),
                        Placeholder::make('calculation_summary')
                            ->content(function ($get) {
                                $taxable = $get('taxable_amount') ?: 0;
                                $rate = $get('tax_rate') ?: 0;
                                $exemption = $get('exemption_amount') ?: 0;
                                $deduction = $get('deduction_amount') ?: 0;
                                $credit = $get('credit_amount') ?: 0;
                                
                                $calculatedTax = $taxable * ($rate / 100);
                                $netTax = $calculatedTax - $exemption - $deduction - $credit;
                                
                                return "**Resumen del Cálculo:**\n" .
                                       "Tributable: {$taxable} | Tasa: {$rate}% | Impuesto: {$calculatedTax} | " .
                                       "Exenciones: {$exemption} | Deducciones: {$deduction} | Créditos: {$credit} | " .
                                       "Neto: {$netTax}";
                            })
                            ->visible(fn ($get) => $get('taxable_amount') > 0 && $get('tax_rate') > 0),
                    ])
                    ->collapsible(),

                Section::make('Penalizaciones e Intereses')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('penalty_amount')
                                    ->label('Monto de Penalización')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Penalizaciones por pago tardío'),
                                TextInput::make('interest_amount')
                                    ->label('Monto de Intereses')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Intereses acumulados'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Estado de Pago')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount_paid')
                                    ->label('Monto Pagado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto ya pagado'),
                                TextInput::make('amount_remaining')
                                    ->label('Monto Restante')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Monto pendiente de pago'),
                                TextInput::make('exchange_rate')
                                    ->label('Tipo de Cambio')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.000001)
                                    ->default(1)
                                    ->helperText('Tipo de cambio si aplica'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Información Jurisdiccional')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('tax_jurisdiction')
                                    ->label('Jurisdicción Fiscal')
                                    ->options([
                                        'ES' => 'España',
                                        'EU' => 'Unión Europea',
                                        'US' => 'Estados Unidos',
                                        'UK' => 'Reino Unido',
                                        'MX' => 'México',
                                        'CA' => 'Canadá',
                                        'AU' => 'Australia',
                                        'JP' => 'Japón',
                                        'other' => 'Otra',
                                    ])
                                    ->default('ES')
                                    ->required()
                                    ->searchable(),
                                TextInput::make('tax_authority')
                                    ->label('Autoridad Fiscal')
                                    ->maxLength(255)
                                    ->helperText('Autoridad fiscal responsable'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tax_registration_number')
                                    ->label('Número de Registro Fiscal')
                                    ->maxLength(255)
                                    ->helperText('Número de registro fiscal'),
                                Select::make('tax_filing_frequency')
                                    ->label('Frecuencia de Declaración')
                                    ->options([
                                        'monthly' => 'Mensual',
                                        'quarterly' => 'Trimestral',
                                        'semi_annual' => 'Semestral',
                                        'annual' => 'Anual',
                                        'other' => 'Otra',
                                    ])
                                    ->searchable(),
                            ]),
                        Select::make('tax_filing_method')
                            ->label('Método de Declaración')
                            ->options([
                                'electronic' => 'Electrónico',
                                'paper' => 'Papel',
                                'phone' => 'Teléfono',
                                'other' => 'Otro',
                            ])
                            ->searchable(),
                    ])
                    ->collapsible(),

                Section::make('Estado del Cálculo')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_estimated')
                                    ->label('Es Estimado')
                                    ->default(false)
                                    ->helperText('¿Es un cálculo estimado?'),
                                Toggle::make('is_final')
                                    ->label('Es Final')
                                    ->default(false)
                                    ->helperText('¿Es el cálculo final?'),
                                Toggle::make('is_amended')
                                    ->label('Es Modificado')
                                    ->default(false)
                                    ->helperText('¿Ha sido modificado?'),
                            ]),
                        TextInput::make('amendment_reason')
                            ->label('Razón de Modificación')
                            ->maxLength(255)
                            ->helperText('Razón de la modificación si aplica'),
                    ])
                    ->collapsible(),

                Section::make('Detalles del Cálculo')
                    ->schema([
                        RichEditor::make('calculation_notes')
                            ->label('Notas del Cálculo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas sobre el cálculo realizado'),
                        RichEditor::make('calculation_details')
                            ->label('Detalles del Cálculo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Detalle paso a paso del cálculo'),
                        RichEditor::make('tax_breakdown')
                            ->label('Desglose del Impuesto')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Desglose detallado del impuesto'),
                    ])
                    ->collapsible(),

                Section::make('Revisión y Aprobación')
                    ->schema([
                        RichEditor::make('review_notes')
                            ->label('Notas de Revisión')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas de la revisión'),
                        RichEditor::make('approval_notes')
                            ->label('Notas de Aprobación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas de la aprobación'),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('reviewed_at')
                                    ->label('Revisado el')
                                    ->helperText('Cuándo fue revisado'),
                                DateTimePicker::make('approved_at')
                                    ->label('Aprobado el')
                                    ->helperText('Cuándo fue aprobado'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Documentos y Auditoría')
                    ->schema([
                        RichEditor::make('supporting_documents')
                            ->label('Documentos de Apoyo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Documentos que respaldan el cálculo'),
                        RichEditor::make('audit_trail')
                            ->label('Traza de Auditoría')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Traza de auditoría del cálculo'),
                    ])
                    ->collapsible(),

                Section::make('Asignaciones del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('calculated_by')
                                    ->label('Calculado por')
                                    ->relationship('calculatedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('reviewed_by')
                                    ->label('Revisado por')
                                    ->relationship('reviewedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('approved_by')
                                    ->label('Aprobado por')
                                    ->relationship('approvedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('applied_by')
                                    ->label('Aplicado por')
                                    ->relationship('appliedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                DateTimePicker::make('applied_at')
                                    ->label('Aplicado el')
                                    ->helperText('Cuándo se aplicó el cálculo'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos')
                    ->schema([
                        TagsInput::make('tags')
                            ->label('Etiquetas')
                            ->separator(','),
                        RichEditor::make('notes')
                            ->label('Notas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('calculation_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Haz clic para copiar')
                    ->limit(15),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                BadgeColumn::make('tax_type')
                    ->label('Tipo de Impuesto')
                    ->colors([
                        'primary' => 'income_tax',
                        'success' => 'sales_tax',
                        'warning' => 'property_tax',
                        'danger' => 'excise_tax',
                        'info' => 'energy_tax',
                        'secondary' => 'carbon_tax',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => TaxCalculation::getTaxTypes()[$state] ?? $state),
                BadgeColumn::make('calculation_type')
                    ->label('Tipo de Cálculo')
                    ->colors([
                        'primary' => 'standard',
                        'success' => 'progressive',
                        'warning' => 'flat_rate',
                        'danger' => 'marginal',
                        'info' => 'effective',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => TaxCalculation::getCalculationTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'calculated',
                        'warning' => 'pending',
                        'danger' => 'error',
                        'info' => 'applied',
                        'secondary' => 'verified',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => TaxCalculation::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'gray' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'purple' => 'urgent',
                        'red' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => TaxCalculation::getPriorities()[$state] ?? $state),
                TextColumn::make('taxable_amount')
                    ->label('Monto Tributable')
                    ->money('EUR')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 10000 => 'success',
                        $state >= 5000 => 'primary',
                        $state >= 1000 => 'warning',
                        $state >= 100 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('tax_rate')
                    ->label('Tasa (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 25 => 'danger',
                        $state >= 15 => 'warning',
                        $state >= 8 => 'primary',
                        $state >= 4 => 'info',
                        default => 'success',
                    }),
                TextColumn::make('tax_amount')
                    ->label('Impuesto')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ]),
                TextColumn::make('total_amount_due')
                    ->label('Total a Pagar')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ]),
                TextColumn::make('amount_paid')
                    ->label('Pagado')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount_remaining')
                    ->label('Pendiente')
                    ->money('EUR')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state > 0 => 'danger',
                        $state == 0 => 'success',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('currency')
                    ->label('Moneda')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                TextColumn::make('tax_jurisdiction')
                    ->label('Jurisdicción')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('calculation_date')
                    ->label('Fecha de Cálculo')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_estimated')
                    ->label('Estimado'),
                ToggleColumn::make('is_final')
                    ->label('Final'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tax_type')
                    ->label('Tipo de Impuesto')
                    ->options(TaxCalculation::getTaxTypes())
                    ->multiple(),
                SelectFilter::make('calculation_type')
                    ->label('Tipo de Cálculo')
                    ->options(TaxCalculation::getCalculationTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(TaxCalculation::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(TaxCalculation::getPriorities())
                    ->multiple(),
                SelectFilter::make('currency')
                    ->label('Moneda')
                    ->options([
                        'EUR' => 'Euro (€)',
                        'USD' => 'Dólar Estadounidense ($)',
                        'GBP' => 'Libra Esterlina (£)',
                        'MXN' => 'Peso Mexicano ($)',
                        'CAD' => 'Dólar Canadiense ($)',
                        'AUD' => 'Dólar Australiano ($)',
                        'JPY' => 'Yen Japonés (¥)',
                        'other' => 'Otra',
                    ])
                    ->multiple(),
                SelectFilter::make('tax_jurisdiction')
                    ->label('Jurisdicción Fiscal')
                    ->options([
                        'ES' => 'España',
                        'EU' => 'Unión Europea',
                        'US' => 'Estados Unidos',
                        'UK' => 'Reino Unido',
                        'MX' => 'México',
                        'CA' => 'Canadá',
                        'AU' => 'Australia',
                        'JP' => 'Japón',
                        'other' => 'Otra',
                    ])
                    ->multiple(),
                Filter::make('estimated')
                    ->query(fn (Builder $query): Builder => $query->where('is_estimated', true))
                    ->label('Solo Estimados'),
                Filter::make('final')
                    ->query(fn (Builder $query): Builder => $query->where('is_final', true))
                    ->label('Solo Finales'),
                Filter::make('amended')
                    ->query(fn (Builder $query): Builder => $query->where('is_amended', true))
                    ->label('Solo Modificados'),
                Filter::make('high_tax_rate')
                    ->query(fn (Builder $query): Builder => $query->where('tax_rate', '>=', 20))
                    ->label('Alta Tasa (≥20%)'),
                Filter::make('low_tax_rate')
                    ->query(fn (Builder $query): Builder => $query->where('tax_rate', '<=', 5))
                    ->label('Baja Tasa (≤5%)'),
                Filter::make('high_taxable_amount')
                    ->query(fn (Builder $query): Builder => $query->where('taxable_amount', '>=', 10000))
                    ->label('Alta Base (≥€10000)'),
                Filter::make('low_taxable_amount')
                    ->query(fn (Builder $query): Builder => $query->where('taxable_amount', '<', 1000))
                    ->label('Baja Base (<€1000)'),
                Filter::make('outstanding_payment')
                    ->query(fn (Builder $query): Builder => $query->where('amount_remaining', '>', 0))
                    ->label('Con Saldo Pendiente'),
                Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now()))
                    ->label('Vencidos'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('calculation_from')
                            ->label('Cálculo desde'),
                        DatePicker::make('calculation_until')
                            ->label('Cálculo hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['calculation_from'],
                                fn (Builder $query, $date): Builder => $query->where('calculation_date', '>=', $date),
                            )
                            ->when(
                                $data['calculation_until'],
                                fn (Builder $query, $date): Builder => $query->where('calculation_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Cálculo'),
                Filter::make('amount_range')
                    ->form([
                        TextInput::make('min_amount')
                            ->label('Monto mínimo (€)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_amount')
                            ->label('Monto máximo (€)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('taxable_amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('taxable_amount', '<=', $amount),
                            );
                    })
                    ->label('Rango de Montos'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('mark_final')
                    ->label('Marcar Final')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (TaxCalculation $record) => !$record->is_final)
                    ->action(function (TaxCalculation $record) {
                        $record->update([
                            'is_final' => true,
                            'is_estimated' => false,
                        ]);
                    }),
                Tables\Actions\Action::make('apply_calculation')
                    ->label('Aplicar')
                    ->icon('heroicon-o-check')
                    ->color('primary')
                    ->visible(fn (TaxCalculation $record) => $record->status === 'calculated')
                    ->action(function (TaxCalculation $record) {
                        $record->update([
                            'status' => 'applied',
                            'applied_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Marcar Pagado')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (TaxCalculation $record) => $record->amount_remaining > 0)
                    ->action(function (TaxCalculation $record) {
                        $record->update([
                            'amount_paid' => $record->total_amount_due,
                            'amount_remaining' => 0,
                            'payment_date' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (TaxCalculation $record) {
                        $newRecord = $record->replicate();
                        $newRecord->calculation_number = $newRecord->calculation_number . '_copy';
                        $newRecord->status = 'pending';
                        $newRecord->is_estimated = true;
                        $newRecord->is_final = false;
                        $newRecord->is_amended = false;
                        $newRecord->amount_paid = 0;
                        $newRecord->amount_remaining = $newRecord->total_amount_due;
                        $newRecord->payment_date = null;
                        $newRecord->applied_at = null;
                        $newRecord->reviewed_at = null;
                        $newRecord->approved_at = null;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_final_all')
                        ->label('Marcar Finales Todos')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'is_final' => true,
                                    'is_estimated' => false,
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('apply_all')
                        ->label('Aplicar Todos')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'calculated') {
                                    $record->update([
                                        'status' => 'applied',
                                        'applied_at' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_paid_all')
                        ->label('Marcar Pagados Todos')
                        ->icon('heroicon-o-credit-card')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->amount_remaining > 0) {
                                    $record->update([
                                        'amount_paid' => $record->total_amount_due,
                                        'amount_remaining' => 0,
                                        'payment_date' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(TaxCalculation::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_priority')
                        ->label('Actualizar Prioridad')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority')
                                ->label('Prioridad')
                                ->options(TaxCalculation::getPriorities())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['priority' => $data['priority']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxCalculations::route('/'),
            'create' => Pages\CreateTaxCalculation::route('/create'),
            'edit' => Pages\EditTaxCalculation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $overdueCount = static::getModel()::where('due_date', '<', now())->count();
        
        if ($overdueCount > 0) {
            return 'danger';
        }
        
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        
        if ($pendingCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
