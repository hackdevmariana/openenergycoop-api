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
use Filament\Forms\Components\FileUpload;
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
                                Select::make('calculation_type')
                                    ->label('Tipo de Cálculo')
                                    ->options(TaxCalculation::getCalculationTypes())
                                    ->required()
                                    ->searchable(),
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
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tax_code')
                                    ->label('Código de Impuesto')
                                    ->maxLength(50)
                                    ->required()
                                    ->helperText('Código único del impuesto'),
                                TextInput::make('tax_name')
                                    ->label('Nombre del Impuesto')
                                    ->maxLength(255)
                                    ->required()
                                    ->helperText('Nombre descriptivo del impuesto'),
                            ]),
                        Grid::make(2)
                            ->schema([
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
                                TextInput::make('tax_configuration_version')
                                    ->label('Versión de Configuración')
                                    ->maxLength(50)
                                    ->helperText('Versión de la configuración fiscal'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('calculable_type')
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
                                TextInput::make('calculable_id')
                                    ->label('ID de la Entidad')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('ID de la entidad relacionada'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('calculated_by_user_id')
                                    ->label('Calculado por')
                                    ->relationship('calculatedByUser', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('applied_by_user_id')
                                    ->label('Aplicado por')
                                    ->relationship('appliedByUser', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('verified_by_user_id')
                                    ->label('Verificado por')
                                    ->relationship('verifiedByUser', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Cálculos Fiscales')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('base_amount')
                                    ->label('Monto Base')
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
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_amount')
                                    ->label('Monto Total')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Monto total incluyendo impuestos'),
                                TextInput::make('effective_tax_rate')
                                    ->label('Tasa Efectiva (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Tasa efectiva considerando exenciones'),
                            ]),
                        Placeholder::make('calculation_summary')
                            ->content(function ($get) {
                                $base = $get('base_amount') ?: 0;
                                $rate = $get('tax_rate') ?: 0;
                                $tax = $get('tax_amount') ?: 0;
                                $total = $get('total_amount') ?: 0;
                                
                                $calculatedTax = $base * ($rate / 100);
                                $calculatedTotal = $base + $calculatedTax;
                                
                                return "**Resumen del Cálculo:**\n" .
                                       "Base: {$base} | Tasa: {$rate}% | Impuesto: {$calculatedTax} | Total: {$calculatedTotal}";
                            })
                            ->visible(fn ($get) => $get('base_amount') > 0 && $get('tax_rate') > 0),
                    ])
                    ->collapsible(),

                Section::make('Exenciones y Deducciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('exemptions_amount')
                                    ->label('Monto de Exenciones')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Monto exento de impuestos'),
                                TextInput::make('deductions_amount')
                                    ->label('Monto de Deducciones')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Monto deducible'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('exemptions_percentage')
                                    ->label('Porcentaje de Exenciones (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje exento'),
                                TextInput::make('deductions_percentage')
                                    ->label('Porcentaje de Deducciones (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje deducible'),
                            ]),
                        RichEditor::make('exemptions_applied')
                            ->label('Exenciones Aplicadas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Detalle de las exenciones aplicadas'),
                        RichEditor::make('deductions_applied')
                            ->label('Deducciones Aplicadas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Detalle de las deducciones aplicadas'),
                    ])
                    ->collapsible(),

                Section::make('Escalas y Brackets Fiscales')
                    ->schema([
                        RichEditor::make('tax_brackets')
                            ->label('Escalas Fiscales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Escalas y brackets fiscales aplicables'),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('bracket_start')
                                    ->label('Inicio de Bracket')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Valor inicial del bracket'),
                                TextInput::make('bracket_end')
                                    ->label('Fin de Bracket')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Valor final del bracket'),
                                TextInput::make('bracket_rate')
                                    ->label('Tasa del Bracket (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Tasa aplicable al bracket'),
                            ]),
                        TextInput::make('progressive_tax')
                            ->label('Impuesto Progresivo')
                                    ->options([
                                        'yes' => 'Sí',
                                        'no' => 'No',
                                        'partial' => 'Parcial',
                                    ])
                                    ->searchable(),
                    ])
                    ->collapsible(),

                Section::make('Detalles del Cálculo')
                    ->schema([
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
                        Grid::make(2)
                            ->schema([
                                TextInput::make('calculation_method')
                                    ->label('Método de Cálculo')
                                    ->options([
                                        'standard' => 'Estándar',
                                        'progressive' => 'Progresivo',
                                        'flat_rate' => 'Tasa Plana',
                                        'marginal' => 'Marginal',
                                        'effective' => 'Efectivo',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('calculation_formula')
                                    ->label('Fórmula de Cálculo')
                                    ->maxLength(255)
                                    ->helperText('Fórmula utilizada para el cálculo'),
                            ]),
                        RichEditor::make('configuration_snapshot')
                            ->label('Configuración del Sistema')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Configuración del sistema al momento del cálculo'),
                    ])
                    ->collapsible(),

                Section::make('Estado y Verificación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(TaxCalculation::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Toggle::make('is_verified')
                                    ->label('Verificado')
                                    ->default(false)
                                    ->required()
                                    ->helperText('¿El cálculo ha sido verificado?'),
                                TextInput::make('verification_score')
                                    ->label('Puntuación de Verificación')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Puntuación de la verificación'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('calculated_at')
                                    ->label('Calculado el')
                                    ->helperText('Cuándo se realizó el cálculo'),
                                DateTimePicker::make('applied_at')
                                    ->label('Aplicado el')
                                    ->helperText('Cuándo se aplicó el cálculo'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('verified_at')
                                    ->label('Verificado el')
                                    ->helperText('Cuándo se verificó el cálculo'),
                                TextInput::make('calculation_hash')
                                    ->label('Hash del Cálculo')
                                    ->maxLength(64)
                                    ->helperText('Hash único del cálculo'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->helperText('¿El cálculo está activo?'),
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('auto_calculate')
                                    ->label('Cálculo Automático')
                                    ->helperText('¿Se calcula automáticamente?'),
                                Toggle::make('is_template')
                                    ->label('Es Plantilla')
                                    ->helperText('¿Es una plantilla reutilizable?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('priority')
                                    ->label('Prioridad')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->step(1)
                                    ->default(5)
                                    ->helperText('Prioridad del cálculo (1-10)'),
                                TextInput::make('sort_order')
                                    ->label('Orden de Clasificación')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Orden para mostrar en listas'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos y Etiquetas')
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

                Section::make('Documentos y Archivos')
                    ->schema([
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('tax-calculations')
                            ->maxFiles(20)
                            ->maxSize(10240)
                            ->helperText('Máximo 20 documentos de 10MB cada uno'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tax_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                TextColumn::make('tax_name')
                    ->label('Nombre del Impuesto')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                BadgeColumn::make('calculation_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'income_tax',
                        'success' => 'sales_tax',
                        'warning' => 'property_tax',
                        'danger' => 'excise_tax',
                        'info' => 'energy_tax',
                        'secondary' => 'carbon_tax',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => TaxCalculation::getCalculationTypes()[$state] ?? $state),
                BadgeColumn::make('tax_jurisdiction')
                    ->label('Jurisdicción')
                    ->colors([
                        'primary' => 'ES',
                        'success' => 'EU',
                        'warning' => 'US',
                        'danger' => 'UK',
                        'info' => 'MX',
                        'secondary' => 'CA',
                        'gray' => 'other',
                    ]),
                TextColumn::make('base_amount')
                    ->label('Base (€)')
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
                    ->label('Impuesto (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ]),
                TextColumn::make('total_amount')
                    ->label('Total (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ]),
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
                TextColumn::make('currency')
                    ->label('Moneda')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                TextColumn::make('effective_tax_rate')
                    ->label('Tasa Efectiva (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('exemptions_amount')
                    ->label('Exenciones (€)')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deductions_amount')
                    ->label('Deducciones (€)')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('calculated_at')
                    ->label('Calculado')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_verified')
                    ->label('Verificado'),
                TextColumn::make('verified_at')
                    ->label('Verificado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('calculation_type')
                    ->label('Tipo de Cálculo')
                    ->options(TaxCalculation::getCalculationTypes())
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
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(TaxCalculation::getStatuses())
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
                Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->where('is_verified', true))
                    ->label('Solo Verificados'),
                Filter::make('unverified')
                    ->query(fn (Builder $query): Builder => $query->where('is_verified', false))
                    ->label('Solo No Verificados'),
                Filter::make('high_tax_rate')
                    ->query(fn (Builder $query): Builder => $query->where('tax_rate', '>=', 20))
                    ->label('Alta Tasa (≥20%)'),
                Filter::make('low_tax_rate')
                    ->query(fn (Builder $query): Builder => $query->where('tax_rate', '<=', 5))
                    ->label('Baja Tasa (≤5%)'),
                Filter::make('high_base_amount')
                    ->query(fn (Builder $query): Builder => $query->where('base_amount', '>=', 10000))
                    ->label('Alta Base (≥€10000)'),
                Filter::make('low_base_amount')
                    ->query(fn (Builder $query): Builder => $query->where('base_amount', '<', 1000))
                    ->label('Baja Base (<€1000)'),
                Filter::make('recent_calculations')
                    ->query(fn (Builder $query): Builder => $query->where('calculated_at', '>=', now()->subDays(30)))
                    ->label('Cálculos Recientes (≤30 días)'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('calculated_from')
                            ->label('Calculado desde'),
                        DateTimePicker::make('calculated_until')
                            ->label('Calculado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['calculated_from'],
                                fn (Builder $query, $date): Builder => $query->where('calculated_at', '>=', $date),
                            )
                            ->when(
                                $data['calculated_until'],
                                fn (Builder $query, $date): Builder => $query->where('calculated_at', '<=', $date),
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
                                fn (Builder $query, $amount): Builder => $query->where('base_amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('base_amount', '<=', $amount),
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
                Tables\Actions\Action::make('verify_calculation')
                    ->label('Verificar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (TaxCalculation $record) => !$record->is_verified)
                    ->action(function (TaxCalculation $record) {
                        $record->update([
                            'is_verified' => true,
                            'verified_at' => now(),
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
                Tables\Actions\Action::make('recalculate')
                    ->label('Recalcular')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (TaxCalculation $record) {
                        $record->update([
                            'calculated_at' => now(),
                            'calculation_hash' => md5(now() . $record->id),
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (TaxCalculation $record) {
                        $newRecord = $record->replicate();
                        $newRecord->is_verified = false;
                        $newRecord->verified_at = null;
                        $newRecord->applied_at = null;
                        $newRecord->calculation_hash = md5(now() . $newRecord->id);
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify_all')
                        ->label('Verificar Todos')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'is_verified' => true,
                                    'verified_at' => now(),
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
                    Tables\Actions\BulkAction::make('recalculate_all')
                        ->label('Recalcular Todos')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'calculated_at' => now(),
                                    'calculation_hash' => md5(now() . $record->id),
                                ]);
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
                    Tables\Actions\BulkAction::make('update_calculation_type')
                        ->label('Actualizar Tipo de Cálculo')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('calculation_type')
                                ->label('Tipo de Cálculo')
                                ->options(TaxCalculation::getCalculationTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['calculation_type' => $data['calculation_type']]);
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $unverifiedCount = static::getModel()::where('is_verified', false)->count();
        
        if ($unverifiedCount > 0) {
            return 'warning';
        }
        
        $errorCount = static::getModel()::where('status', 'error')->count();
        
        if ($errorCount > 0) {
            return 'danger';
        }
        
        return 'success';
    }
}
