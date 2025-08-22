<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyTradingOrderResource\Pages;
use App\Filament\Resources\EnergyTradingOrderResource\RelationManagers;
use App\Models\EnergyTradingOrder;
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

class EnergyTradingOrderResource extends Resource
{
    protected static ?string $model = EnergyTradingOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Comercio de Energía';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Órdenes de Comercio';

    protected static ?string $modelLabel = 'Orden de Comercio';

    protected static ?string $pluralModelLabel = 'Órdenes de Comercio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que realiza la orden'),
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options(EnergyTradingOrder::getTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('order_type')
                                    ->label('Tipo de Orden')
                                    ->options(EnergyTradingOrder::getOrderTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(EnergyTradingOrder::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(EnergyTradingOrder::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('energy_source_type')
                                    ->label('Tipo de Fuente')
                                    ->options([
                                        'solar' => 'Solar',
                                        'wind' => 'Eólica',
                                        'hydro' => 'Hidroeléctrica',
                                        'biomass' => 'Biomasa',
                                        'geothermal' => 'Geotérmica',
                                        'nuclear' => 'Nuclear',
                                        'mixed' => 'Mixta',
                                        'other' => 'Otra',
                                    ])
                                    ->searchable(),
                                TextInput::make('geographical_zone')
                                    ->label('Zona Geográfica')
                                    ->maxLength(255)
                                    ->helperText('Zona geográfica de la orden'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalles de la Orden')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('quantity_kwh')
                                    ->label('Cantidad (kWh)')
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->required()
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad de energía solicitada'),
                                TextInput::make('price_per_kwh')
                                    ->label('Precio por kWh (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->required()
                                    ->prefix('€')
                                    ->suffix('/kWh')
                                    ->helperText('Precio por kilovatio hora'),
                                TextInput::make('total_amount')
                                    ->label('Monto Total (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->prefix('€')
                                    ->helperText('Monto total de la orden'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('filled_quantity')
                                    ->label('Cantidad Ejecutada (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->default(0)
                                    ->required()
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad ya ejecutada'),
                                TextInput::make('remaining_quantity')
                                    ->label('Cantidad Restante (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->required()
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad pendiente de ejecutar'),
                                TextInput::make('execution_price')
                                    ->label('Precio de Ejecución (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->prefix('€')
                                    ->suffix('/kWh')
                                    ->helperText('Precio real de ejecución'),
                            ]),
                        Placeholder::make('execution_percentage')
                            ->content(function ($get) {
                                $total = $get('quantity_kwh') ?: 0;
                                $filled = $get('filled_quantity') ?: 0;
                                
                                if ($total > 0) {
                                    $percentage = ($filled / $total) * 100;
                                    return "**Porcentaje de Ejecución: {$percentage}%**";
                                }
                                
                                return "**Porcentaje de Ejecución: 0%**";
                            })
                            ->visible(fn ($get) => $get('quantity_kwh') > 0),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Ventanas de Entrega')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('expires_at')
                                    ->label('Expira el')
                                    ->required()
                                    ->helperText('Cuándo expira la orden'),
                                DatePicker::make('delivery_date')
                                    ->label('Fecha de Entrega')
                                    ->helperText('Fecha de entrega de la energía'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('delivery_window_start')
                                    ->label('Inicio de Ventana de Entrega')
                                    ->helperText('Inicio de la ventana de entrega'),
                                DateTimePicker::make('delivery_window_end')
                                    ->label('Fin de Ventana de Entrega')
                                    ->helperText('Fin de la ventana de entrega'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('matched_at')
                                    ->label('Emparejado el')
                                    ->helperText('Cuándo se emparejó la orden'),
                                DateTimePicker::make('executed_at')
                                    ->label('Ejecutado el')
                                    ->helperText('Cuándo se ejecutó la orden'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Requisitos de Calidad')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('renewable_only')
                                    ->label('Solo Renovable')
                                    ->default(false)
                                    ->required()
                                    ->helperText('¿Solo acepta energía renovable?'),
                                TextInput::make('carbon_footprint_max')
                                    ->label('Huella de Carbono Máxima (kg CO2/MWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kg CO2/MWh')
                                    ->helperText('Máxima huella de carbono aceptada'),
                            ]),
                        RichEditor::make('quality_requirements')
                            ->label('Requisitos de Calidad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Requisitos específicos de calidad'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_efficiency')
                                    ->label('Eficiencia Mínima (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia mínima requerida'),
                                TextInput::make('certification_required')
                                    ->label('Certificación Requerida')
                                    ->maxLength(255)
                                    ->helperText('Certificación específica requerida'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Términos de Pago y Liquidación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('settlement_method')
                                    ->label('Método de Liquidación')
                                    ->options([
                                        'immediate' => 'Inmediata',
                                        'net_30' => 'Neto 30 días',
                                        'net_60' => 'Neto 60 días',
                                        'net_90' => 'Neto 90 días',
                                        'escrow' => 'Depósito en Garantía',
                                        'letter_of_credit' => 'Carta de Crédito',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('counterparty_user_id')
                                    ->label('Contraparte')
                                    ->relationship('counterpartyUser', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario contraparte de la transacción'),
                            ]),
                        RichEditor::make('payment_terms')
                            ->label('Términos de Pago')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Términos específicos de pago'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('execution_fee')
                                    ->label('Comisión de Ejecución (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Comisión por la ejecución'),
                                TextInput::make('platform_fee')
                                    ->label('Comisión de Plataforma (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Comisión de la plataforma'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true)
                                    ->helperText('¿La orden está activa?'),
                                Toggle::make('auto_match')
                                    ->label('Emparejamiento Automático')
                                    ->helperText('¿Se empareja automáticamente?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación?'),
                                Toggle::make('is_urgent')
                                    ->label('Urgente')
                                    ->helperText('¿Es una orden urgente?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_counterparties')
                                    ->label('Máximo de Contrapartes')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->step(1)
                                    ->default(1)
                                    ->helperText('Máximo número de contrapartes'),
                                TextInput::make('min_fill_percentage')
                                    ->label('Porcentaje Mínimo de Llenado (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->default(100)
                                    ->suffix('%')
                                    ->helperText('Porcentaje mínimo para considerar llena'),
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

                Section::make('Asignaciones del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('approved_by')
                                    ->label('Aprobado por')
                                    ->relationship('approvedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'buy',
                        'success' => 'sell',
                        'warning' => 'exchange',
                        'danger' => 'auction',
                        'info' => 'bilateral',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getTypes()[$state] ?? $state),
                BadgeColumn::make('order_type')
                    ->label('Tipo de Orden')
                    ->colors([
                        'primary' => 'market',
                        'success' => 'limit',
                        'warning' => 'stop',
                        'danger' => 'stop_limit',
                        'info' => 'fill_or_kill',
                        'secondary' => 'good_till_cancelled',
                        'gray' => 'day_order',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getOrderTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'filled',
                        'warning' => 'partially_filled',
                        'danger' => 'cancelled',
                        'info' => 'pending',
                        'secondary' => 'expired',
                        'gray' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getPriorities()[$state] ?? $state),
                TextColumn::make('quantity_kwh')
                    ->label('Cantidad (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 10000 => 'success',
                        $state >= 5000 => 'primary',
                        $state >= 1000 => 'warning',
                        $state >= 100 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('price_per_kwh')
                    ->label('Precio (€/kWh)')
                    ->money('EUR')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 0.05 => 'success',
                        $state <= 0.10 => 'primary',
                        $state <= 0.20 => 'warning',
                        $state <= 0.50 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('total_amount')
                    ->label('Total (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ]),
                TextColumn::make('filled_quantity')
                    ->label('Ejecutado (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' kWh'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('execution_percentage')
                    ->label('Ejecutado (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 50 => 'warning',
                        $state >= 25 => 'info',
                        default => 'danger',
                    })
                    ->getStateUsing(function ($record) {
                        if ($record->quantity_kwh > 0) {
                            return ($record->filled_quantity / $record->quantity_kwh) * 100;
                        }
                        return 0;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label('Expira')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('delivery_date')
                    ->label('Entrega')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('renewable_only')
                    ->label('Solo Renovable'),
                TextColumn::make('carbon_footprint_max')
                    ->label('CO2 Max (kg/MWh)')
                    ->suffix(' kg/MWh')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('execution_price')
                    ->label('Precio Ejec. (€/kWh)')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('execution_fee')
                    ->label('Comisión (€)')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(EnergyTradingOrder::getTypes())
                    ->multiple(),
                SelectFilter::make('order_type')
                    ->label('Tipo de Orden')
                    ->options(EnergyTradingOrder::getOrderTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergyTradingOrder::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(EnergyTradingOrder::getPriorities())
                    ->multiple(),
                SelectFilter::make('energy_source_type')
                    ->label('Tipo de Fuente')
                    ->options([
                        'solar' => 'Solar',
                        'wind' => 'Eólica',
                        'hydro' => 'Hidroeléctrica',
                        'biomass' => 'Biomasa',
                        'geothermal' => 'Geotérmica',
                        'nuclear' => 'Nuclear',
                        'mixed' => 'Mixta',
                        'other' => 'Otra',
                    ])
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activas'),
                Filter::make('renewable_only')
                    ->query(fn (Builder $query): Builder => $query->where('renewable_only', true))
                    ->label('Solo Renovables'),
                Filter::make('urgent')
                    ->query(fn (Builder $query): Builder => $query->where('is_urgent', true))
                    ->label('Solo Urgentes'),
                Filter::make('high_value')
                    ->query(fn (Builder $query): Builder => $query->where('total_amount', '>=', 1000))
                    ->label('Alto Valor (≥€1000)'),
                Filter::make('low_value')
                    ->query(fn (Builder $query): Builder => $query->where('total_amount', '<', 100))
                    ->label('Bajo Valor (<€100)'),
                Filter::make('high_quantity')
                    ->query(fn (Builder $query): Builder => $query->where('quantity_kwh', '>=', 5000))
                    ->label('Alta Cantidad (≥5000 kWh)'),
                Filter::make('low_quantity')
                    ->query(fn (Builder $query): Builder => $query->where('quantity_kwh', '<', 100))
                    ->label('Baja Cantidad (<100 kWh)'),
                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now()))
                    ->label('Expiradas'),
                Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<=', now()->addHours(24)))
                    ->label('Expiran Pronto'),
                Filter::make('partially_filled')
                    ->query(fn (Builder $query): Builder => $query->where('filled_quantity', '>', 0)->where('filled_quantity', '<', 'quantity_kwh'))
                    ->label('Parcialmente Llenas'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('created_from')
                            ->label('Creado desde'),
                        DateTimePicker::make('created_until')
                            ->label('Creado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Creación'),
                Filter::make('price_range')
                    ->form([
                        TextInput::make('min_price')
                            ->label('Precio mínimo (€/kWh)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_price')
                            ->label('Precio máximo (€/kWh)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_kwh', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_kwh', '<=', $price),
                            );
                    })
                    ->label('Rango de Precios'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('cancel_order')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (EnergyTradingOrder $record) => in_array($record->status, ['pending', 'partially_filled']))
                    ->action(function (EnergyTradingOrder $record) {
                        $record->update(['status' => 'cancelled']);
                    }),
                Tables\Actions\Action::make('expire_order')
                    ->label('Expirar')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn (EnergyTradingOrder $record) => $record->status === 'pending')
                    ->action(function (EnergyTradingOrder $record) {
                        $record->update(['status' => 'expired']);
                    }),
                Tables\Actions\Action::make('fill_order')
                    ->label('Llenar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (EnergyTradingOrder $record) => in_array($record->status, ['pending', 'partially_filled']))
                    ->action(function (EnergyTradingOrder $record) {
                        $record->update([
                            'status' => 'filled',
                            'filled_quantity' => $record->quantity_kwh,
                            'remaining_quantity' => 0,
                            'executed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyTradingOrder $record) {
                        $newRecord = $record->replicate();
                        $newRecord->status = 'pending';
                        $newRecord->filled_quantity = 0;
                        $newRecord->remaining_quantity = $record->quantity_kwh;
                        $newRecord->matched_at = null;
                        $newRecord->executed_at = null;
                        $newRecord->expires_at = now()->addDays(7);
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('cancel_all')
                        ->label('Cancelar Todas')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (in_array($record->status, ['pending', 'partially_filled'])) {
                                    $record->update(['status' => 'cancelled']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('expire_all')
                        ->label('Expirar Todas')
                        ->icon('heroicon-o-clock')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update(['status' => 'expired']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(EnergyTradingOrder::getStatuses())
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
                                ->options(EnergyTradingOrder::getPriorities())
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
            'index' => Pages\ListEnergyTradingOrders::route('/'),
            'create' => Pages\CreateEnergyTradingOrder::route('/create'),
            'edit' => Pages\EditEnergyTradingOrder::route('/{record}/edit'),
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
        $expiredCount = static::getModel()::where('expires_at', '<', now())->count();
        
        if ($expiredCount > 0) {
            return 'danger';
        }
        
        $expiringSoonCount = static::getModel()::where('expires_at', '<=', now()->addHours(24))->count();
        
        if ($expiringSoonCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
