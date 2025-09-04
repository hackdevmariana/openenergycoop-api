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

    protected static ?string $navigationLabel = 'Órdenes de compraventa';

    protected static ?string $modelLabel = 'Orden de compraventa';

    protected static ?string $pluralModelLabel = 'Órdenes de compraventa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('order_number')
                                    ->label('Número de Orden')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Número único de la orden'),
                                Select::make('order_type')
                                    ->label('Tipo de Orden')
                                    ->options(EnergyTradingOrder::getOrderTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('order_status')
                                    ->label('Estado de la Orden')
                                    ->options(EnergyTradingOrder::getOrderStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('order_side')
                                    ->label('Lado de la Orden')
                                    ->options(EnergyTradingOrder::getOrderSides())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(EnergyTradingOrder::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Participantes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('trader_id')
                                    ->label('Trader')
                                    ->relationship('trader', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que realiza la orden'),
                                Select::make('pool_id')
                                    ->label('Pool de Energía')
                                    ->relationship('pool', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Pool donde se ejecuta la orden'),
                                Select::make('counterparty_id')
                                    ->label('Contraparte')
                                    ->relationship('counterparty', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario contraparte de la transacción'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Cantidad y Precio')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('quantity_mwh')
                                    ->label('Cantidad (MWh)')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->required()
                                    ->suffix(' MWh')
                                    ->helperText('Cantidad de energía solicitada'),
                                TextInput::make('price_per_mwh')
                                    ->label('Precio por MWh (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->prefix('€')
                                    ->suffix('/MWh')
                                    ->helperText('Precio por megavatio hora'),
                                TextInput::make('total_value')
                                    ->label('Valor Total (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->prefix('€')
                                    ->helperText('Valor total de la orden'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('filled_quantity_mwh')
                                    ->label('Cantidad Ejecutada (MWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->required()
                                    ->suffix(' MWh')
                                    ->helperText('Cantidad ya ejecutada'),
                                TextInput::make('remaining_quantity_mwh')
                                    ->label('Cantidad Restante (MWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->suffix(' MWh')
                                    ->helperText('Cantidad pendiente de ejecutar'),
                                TextInput::make('filled_value')
                                    ->label('Valor Ejecutado (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Valor ya ejecutado'),
                            ]),
                        Placeholder::make('execution_percentage')
                            ->content(function ($get) {
                                $total = $get('quantity_mwh') ?: 0;
                                $filled = $get('filled_quantity_mwh') ?: 0;
                                
                                if ($total > 0) {
                                    $percentage = ($filled / $total) * 100;
                                    return "**Porcentaje de Ejecución: {$percentage}%**";
                                }
                                
                                return "**Porcentaje de Ejecución: 0%**";
                            })
                            ->visible(fn ($get) => $get('quantity_mwh') > 0),
                    ])
                    ->collapsible(),

                Section::make('Configuración de Precio')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('price_type')
                                    ->label('Tipo de Precio')
                                    ->options(EnergyTradingOrder::getPriceTypes())
                                    ->required()
                                    ->searchable(),
                                TextInput::make('price_index')
                                    ->label('Índice de Precio')
                                    ->maxLength(255)
                                    ->helperText('Índice de precio de referencia'),
                                TextInput::make('price_adjustment')
                                    ->label('Ajuste de Precio (€)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Ajuste al precio base'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Tiempos')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('valid_from')
                                    ->label('Válido desde')
                                    ->required()
                                    ->helperText('Cuándo comienza a ser válida la orden'),
                                DateTimePicker::make('valid_until')
                                    ->label('Válido hasta')
                                    ->helperText('Cuándo deja de ser válida la orden'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('execution_time')
                                    ->label('Tiempo de Ejecución')
                                    ->helperText('Cuándo se debe ejecutar la orden'),
                                DateTimePicker::make('expiry_time')
                                    ->label('Tiempo de Expiración')
                                    ->required()
                                    ->helperText('Cuándo expira la orden'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('approved_at')
                                    ->label('Aprobado el')
                                    ->helperText('Cuándo fue aprobada la orden'),
                                DateTimePicker::make('executed_at')
                                    ->label('Ejecutado el')
                                    ->helperText('Cuándo se ejecutó la orden'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración de Ejecución')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('execution_type')
                                    ->label('Tipo de Ejecución')
                                    ->options(EnergyTradingOrder::getExecutionTypes())
                                    ->required()
                                    ->searchable(),
                                Toggle::make('is_negotiable')
                                    ->label('Negociable')
                                    ->default(false)
                                    ->helperText('¿La orden es negociable?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Términos y Condiciones')
                    ->schema([
                        RichEditor::make('negotiation_terms')
                            ->label('Términos de Negociación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Términos específicos de negociación'),
                        RichEditor::make('special_conditions')
                            ->label('Condiciones Especiales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Condiciones especiales de la orden'),
                        RichEditor::make('delivery_requirements')
                            ->label('Requisitos de Entrega')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Requisitos específicos de entrega'),
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
                    ])
                    ->collapsible(),

                Section::make('Restricciones y Metadatos')
                    ->schema([
                        RichEditor::make('order_conditions')
                            ->label('Condiciones de la Orden')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Condiciones específicas de la orden'),
                        RichEditor::make('order_restrictions')
                            ->label('Restricciones de la Orden')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Restricciones aplicables'),
                        RichEditor::make('order_metadata')
                            ->label('Metadatos de la Orden')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Metadatos adicionales'),
                    ])
                    ->collapsible(),

                Section::make('Gestión y Aprobación')
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
                        Grid::make(2)
                            ->schema([
                                Select::make('executed_by')
                                    ->label('Ejecutado por')
                                    ->relationship('executedBy', 'name')
                                    ->searchable()
                                    ->preload(),
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
                TextColumn::make('order_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                TextColumn::make('trader.name')
                    ->label('Trader')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                BadgeColumn::make('order_type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'buy',
                        'danger' => 'sell',
                        'primary' => 'bid',
                        'warning' => 'ask',
                        'info' => 'market',
                        'secondary' => 'limit',
                        'gray' => 'stop',
                        'purple' => 'stop_limit',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getOrderTypes()[$state] ?? $state),
                BadgeColumn::make('order_status')
                    ->label('Estado')
                    ->colors([
                        'info' => 'pending',
                        'success' => 'active',
                        'success' => 'filled',
                        'warning' => 'partially_filled',
                        'gray' => 'cancelled',
                        'danger' => 'rejected',
                        'warning' => 'expired',
                        'primary' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getOrderStatuses()[$state] ?? $state),
                BadgeColumn::make('order_side')
                    ->label('Lado')
                    ->colors([
                        'success' => 'buy',
                        'danger' => 'sell',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getOrderSides()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'gray' => 'low',
                        'primary' => 'normal',
                        'warning' => 'high',
                        'warning' => 'urgent',
                        'danger' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getPriorities()[$state] ?? $state),
                TextColumn::make('quantity_mwh')
                    ->label('Cantidad (MWh)')
                    ->suffix(' MWh')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('price_per_mwh')
                    ->label('Precio (€/MWh)')
                    ->money('EUR')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 50 => 'success',
                        $state <= 100 => 'primary',
                        $state <= 200 => 'warning',
                        $state <= 500 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('total_value')
                    ->label('Total (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ]),
                TextColumn::make('filled_quantity_mwh')
                    ->label('Ejecutado (MWh)')
                    ->suffix(' MWh')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' MWh'),
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
                        if ($record->quantity_mwh > 0) {
                            return ($record->filled_quantity_mwh / $record->quantity_mwh) * 100;
                        }
                        return 0;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expiry_time')
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
                BadgeColumn::make('price_type')
                    ->label('Tipo Precio')
                    ->colors([
                        'primary' => 'fixed',
                        'warning' => 'floating',
                        'success' => 'indexed',
                        'purple' => 'formula',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getPriceTypes()[$state] ?? $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('execution_type')
                    ->label('Ejecución')
                    ->colors([
                        'primary' => 'immediate',
                        'success' => 'good_till_cancelled',
                        'warning' => 'good_till_date',
                        'warning' => 'fill_or_kill',
                        'purple' => 'all_or_nothing',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTradingOrder::getExecutionTypes()[$state] ?? $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_negotiable')
                    ->label('Negociable'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_type')
                    ->label('Tipo de Orden')
                    ->options(EnergyTradingOrder::getOrderTypes())
                    ->multiple(),
                SelectFilter::make('order_status')
                    ->label('Estado')
                    ->options(EnergyTradingOrder::getOrderStatuses())
                    ->multiple(),
                SelectFilter::make('order_side')
                    ->label('Lado')
                    ->options(EnergyTradingOrder::getOrderSides())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(EnergyTradingOrder::getPriorities())
                    ->multiple(),
                SelectFilter::make('price_type')
                    ->label('Tipo de Precio')
                    ->options(EnergyTradingOrder::getPriceTypes())
                    ->multiple(),
                SelectFilter::make('execution_type')
                    ->label('Tipo de Ejecución')
                    ->options(EnergyTradingOrder::getExecutionTypes())
                    ->multiple(),
                Filter::make('buy_orders')
                    ->query(fn (Builder $query): Builder => $query->where('order_side', 'buy'))
                    ->label('Solo Compras'),
                Filter::make('sell_orders')
                    ->query(fn (Builder $query): Builder => $query->where('order_side', 'sell'))
                    ->label('Solo Ventas'),
                Filter::make('negotiable')
                    ->query(fn (Builder $query): Builder => $query->where('is_negotiable', true))
                    ->label('Solo Negociables'),
                Filter::make('high_value')
                    ->query(fn (Builder $query): Builder => $query->where('total_value', '>=', 10000))
                    ->label('Alto Valor (≥€10,000)'),
                Filter::make('low_value')
                    ->query(fn (Builder $query): Builder => $query->where('total_value', '<', 1000))
                    ->label('Bajo Valor (<€1,000)'),
                Filter::make('high_quantity')
                    ->query(fn (Builder $query): Builder => $query->where('quantity_mwh', '>=', 500))
                    ->label('Alta Cantidad (≥500 MWh)'),
                Filter::make('low_quantity')
                    ->query(fn (Builder $query): Builder => $query->where('quantity_mwh', '<', 10))
                    ->label('Baja Cantidad (<10 MWh)'),
                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expiry_time', '<', now()))
                    ->label('Expiradas'),
                Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder => $query->where('expiry_time', '<=', now()->addHours(24)))
                    ->label('Expiran Pronto'),
                Filter::make('partially_filled')
                    ->query(fn (Builder $query): Builder => $query->where('filled_quantity_mwh', '>', 0)->where('filled_quantity_mwh', '<', 'quantity_mwh'))
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
                            ->label('Precio mínimo (€/MWh)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_price')
                            ->label('Precio máximo (€/MWh)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_mwh', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_mwh', '<=', $price),
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
                    ->visible(fn (EnergyTradingOrder $record) => in_array($record->order_status, ['pending', 'active', 'partially_filled']))
                    ->action(function (EnergyTradingOrder $record) {
                        $record->update(['order_status' => 'cancelled']);
                    }),
                Tables\Actions\Action::make('expire_order')
                    ->label('Expirar')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn (EnergyTradingOrder $record) => $record->order_status === 'pending')
                    ->action(function (EnergyTradingOrder $record) {
                        $record->update(['order_status' => 'expired']);
                    }),
                Tables\Actions\Action::make('fill_order')
                    ->label('Llenar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (EnergyTradingOrder $record) => in_array($record->order_status, ['pending', 'active', 'partially_filled']))
                    ->action(function (EnergyTradingOrder $record) {
                        $record->update([
                            'order_status' => 'filled',
                            'filled_quantity_mwh' => $record->quantity_mwh,
                            'remaining_quantity_mwh' => 0,
                            'executed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyTradingOrder $record) {
                        $newRecord = $record->replicate();
                        $newRecord->order_status = 'pending';
                        $newRecord->filled_quantity_mwh = 0;
                        $newRecord->remaining_quantity_mwh = $record->quantity_mwh;
                        $newRecord->approved_at = null;
                        $newRecord->executed_at = null;
                        $newRecord->expiry_time = now()->addDays(7);
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
                                if (in_array($record->order_status, ['pending', 'active', 'partially_filled'])) {
                                    $record->update(['order_status' => 'cancelled']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('expire_all')
                        ->label('Expirar Todas')
                        ->icon('heroicon-o-clock')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->order_status === 'pending') {
                                    $record->update(['order_status' => 'expired']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('order_status')
                                ->label('Estado')
                                ->options(EnergyTradingOrder::getOrderStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['order_status' => $data['order_status']]);
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
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $expiredCount = static::getModel()::where('expiry_time', '<', now())->count();
        
        if ($expiredCount > 0) {
            return 'danger';
        }
        
        $expiringSoonCount = static::getModel()::where('expiry_time', '<=', now()->addHours(24))->count();
        
        if ($expiringSoonCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
