<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleOrderResource\Pages;
use App\Filament\Resources\SaleOrderResource\RelationManagers;
use App\Models\SaleOrder;
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
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleOrderResource extends Resource
{
    protected static ?string $model = SaleOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Marketing y Ventas';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Órdenes de Venta';

    protected static ?string $modelLabel = 'Orden de Venta';

    protected static ?string $pluralModelLabel = 'Órdenes de Venta';

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
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Número único de la orden')
                                    ->copyable()
                                    ->tooltip('Haz clic para copiar'),
                                Select::make('order_type')
                                    ->label('Tipo de Orden')
                                    ->options(SaleOrder::getOrderTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(SaleOrder::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('payment_status')
                                    ->label('Estado de Pago')
                                    ->options(SaleOrder::getPaymentStatuses())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('shipping_status')
                                    ->label('Estado de Envío')
                                    ->options(SaleOrder::getShippingStatuses())
                                    ->searchable(),
                                Select::make('currency')
                                    ->label('Moneda')
                                    ->options([
                                        'USD' => 'Dólar Estadounidense',
                                        'EUR' => 'Euro',
                                        'MXN' => 'Peso Mexicano',
                                        'CAD' => 'Dólar Canadiense',
                                        'GBP' => 'Libra Esterlina',
                                        'JPY' => 'Yen Japonés',
                                        'other' => 'Otra',
                                    ])
                                    ->default('USD')
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Cliente y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Cliente que realiza la orden'),
                                Select::make('affiliate_id')
                                    ->label('Afiliado')
                                    ->relationship('affiliate', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Afiliado que generó la venta'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('processed_by')
                                    ->label('Procesado por')
                                    ->relationship('processedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalles Financieros')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Subtotal antes de descuentos'),
                                TextInput::make('tax_amount')
                                    ->label('Impuestos')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto de impuestos'),
                                TextInput::make('shipping_amount')
                                    ->label('Costo de Envío')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Costo del envío'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('discount_amount')
                                    ->label('Descuento')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto del descuento'),
                                TextInput::make('total_amount')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Total final de la orden'),
                                TextInput::make('exchange_rate')
                                    ->label('Tipo de Cambio')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.000001)
                                    ->default(1)
                                    ->helperText('Tipo de cambio si aplica'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('paid_amount')
                                    ->label('Monto Pagado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto ya pagado'),
                                TextInput::make('refunded_amount')
                                    ->label('Monto Reembolsado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto reembolsado'),
                                TextInput::make('outstanding_amount')
                                    ->label('Monto Pendiente')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto pendiente de pago'),
                            ]),
                        Placeholder::make('payment_summary')
                            ->content(function ($get) {
                                $total = $get('total_amount') ?: 0;
                                $paid = $get('paid_amount') ?: 0;
                                $refunded = $get('refunded_amount') ?: 0;
                                $outstanding = $get('outstanding_amount') ?: 0;
                                
                                return "**Total: $" . number_format($total, 2) . " | Pagado: $" . number_format($paid, 2) . " | Reembolsado: $" . number_format($refunded, 2) . " | Pendiente: $" . number_format($outstanding, 2) . "**";
                            })
                            ->visible(fn ($get) => $get('total_amount') > 0),
                    ])
                    ->collapsible(),

                Section::make('Estado de Pago')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('payment_method')
                                    ->label('Método de Pago')
                                    ->options([
                                        'credit_card' => 'Tarjeta de Crédito',
                                        'debit_card' => 'Tarjeta de Débito',
                                        'bank_transfer' => 'Transferencia Bancaria',
                                        'paypal' => 'PayPal',
                                        'stripe' => 'Stripe',
                                        'cash' => 'Efectivo',
                                        'check' => 'Cheque',
                                        'crypto' => 'Criptomonedas',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('payment_reference')
                                    ->label('Referencia de Pago')
                                    ->maxLength(255)
                                    ->helperText('Referencia o número de transacción'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('payment_date')
                                    ->label('Fecha de Pago')
                                    ->helperText('Cuándo se realizó el pago'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Envío y Entrega')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('shipping_method')
                                    ->label('Método de Envío')
                                    ->options([
                                        'standard' => 'Estándar',
                                        'express' => 'Express',
                                        'overnight' => 'Durante la Noche',
                                        'pickup' => 'Recogida en Tienda',
                                        'local_delivery' => 'Entrega Local',
                                        'international' => 'Internacional',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('tracking_number')
                                    ->label('Número de Seguimiento')
                                    ->maxLength(255)
                                    ->helperText('Número de seguimiento del envío'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('shipped_date')
                                    ->label('Enviado el')
                                    ->helperText('Cuándo se envió la orden'),
                                DateTimePicker::make('delivered_date')
                                    ->label('Entregado el')
                                    ->helperText('Cuándo se entregó la orden'),
                                DateTimePicker::make('expected_delivery_date')
                                    ->label('Entrega Esperada')
                                    ->helperText('Fecha esperada de entrega'),
                            ]),
                        RichEditor::make('shipping_address')
                            ->label('Dirección de Envío')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Dirección completa de envío'),
                        RichEditor::make('billing_address')
                            ->label('Dirección de Facturación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Dirección de facturación'),
                    ])
                    ->collapsible(),

                Section::make('Descuentos y Promociones')
                    ->schema([
                        RichEditor::make('applied_discounts')
                            ->label('Descuentos Aplicados')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Detalles de descuentos aplicados'),
                    ])
                    ->collapsible(),

                Section::make('Productos y Servicios')
                    ->schema([
                        RichEditor::make('order_items')
                            ->label('Elementos de la Orden')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Lista de productos o servicios incluidos'),
                    ])
                    ->collapsible(),

                Section::make('Detalles Adicionales')
                    ->schema([
                        RichEditor::make('special_instructions')
                            ->label('Instrucciones Especiales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Instrucciones especiales para la orden'),
                        RichEditor::make('internal_notes')
                            ->label('Notas Internas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas internas del equipo'),
                        RichEditor::make('customer_notes')
                            ->label('Notas del Cliente')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas proporcionadas por el cliente'),
                    ])
                    ->collapsible(),

                Section::make('Información de Envío')
                    ->schema([
                        RichEditor::make('shipping_details')
                            ->label('Detalles de Envío')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Información adicional sobre el envío'),
                    ])
                    ->collapsible(),

                Section::make('Asignaciones del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('shipped_by')
                                    ->label('Enviado por')
                                    ->relationship('shippedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('delivered_by')
                                    ->label('Entregado por')
                                    ->relationship('deliveredBy', 'name')
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
                    ->copyable()
                    ->tooltip('Haz clic para copiar')
                    ->limit(15),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'processing',
                        'danger' => 'cancelled',
                        'info' => 'pending',
                        'secondary' => 'on_hold',
                        'gray' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => SaleOrder::getStatuses()[$state] ?? $state),
                BadgeColumn::make('order_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'retail',
                        'success' => 'wholesale',
                        'warning' => 'online',
                        'danger' => 'phone',
                        'info' => 'email',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => SaleOrder::getOrderTypes()[$state] ?? $state),
                BadgeColumn::make('payment_status')
                    ->label('Pago')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'failed',
                        'info' => 'partially_paid',
                        'secondary' => 'refunded',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => SaleOrder::getPaymentStatuses()[$state] ?? $state),
                BadgeColumn::make('shipping_status')
                    ->label('Envío')
                    ->colors([
                        'success' => 'delivered',
                        'warning' => 'shipped',
                        'danger' => 'cancelled',
                        'info' => 'pending',
                        'secondary' => 'in_transit',
                        'gray' => 'returned',
                    ])
                    ->formatStateUsing(fn (string $state): string => SaleOrder::getShippingStatuses()[$state] ?? $state),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('paid_amount')
                    ->label('Pagado')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('outstanding_amount')
                    ->label('Pendiente')
                    ->money('USD')
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
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('affiliate.name')
                    ->label('Afiliado')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipped_date')
                    ->label('Enviado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expected_delivery_date')
                    ->label('Entrega Est.')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(SaleOrder::getStatuses())
                    ->multiple(),
                SelectFilter::make('order_type')
                    ->label('Tipo de Orden')
                    ->options(SaleOrder::getOrderTypes())
                    ->multiple(),
                SelectFilter::make('payment_status')
                    ->label('Estado de Pago')
                    ->options(SaleOrder::getPaymentStatuses())
                    ->multiple(),
                SelectFilter::make('shipping_status')
                    ->label('Estado de Envío')
                    ->options(SaleOrder::getShippingStatuses())
                    ->multiple(),
                SelectFilter::make('currency')
                    ->label('Moneda')
                    ->options([
                        'USD' => 'Dólar Estadounidense',
                        'EUR' => 'Euro',
                        'MXN' => 'Peso Mexicano',
                        'CAD' => 'Dólar Canadiense',
                        'GBP' => 'Libra Esterlina',
                        'JPY' => 'Yen Japonés',
                        'other' => 'Otra',
                    ])
                    ->multiple(),
                Filter::make('high_value')
                    ->query(fn (Builder $query): Builder => $query->where('total_amount', '>=', 1000))
                    ->label('Alto Valor (≥$1000)'),
                Filter::make('low_value')
                    ->query(fn (Builder $query): Builder => $query->where('total_amount', '<', 100))
                    ->label('Bajo Valor (<$100)'),
                Filter::make('pending_payment')
                    ->query(fn (Builder $query): Builder => $query->where('payment_status', 'pending'))
                    ->label('Pago Pendiente'),
                Filter::make('pending_shipping')
                    ->query(fn (Builder $query): Builder => $query->where('shipping_status', 'pending'))
                    ->label('Envío Pendiente'),
                Filter::make('outstanding_payment')
                    ->query(fn (Builder $query): Builder => $query->where('outstanding_amount', '>', 0))
                    ->label('Con Saldo Pendiente'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('shipped_from')
                            ->label('Enviado desde'),
                        DateTimePicker::make('shipped_until')
                            ->label('Enviado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['shipped_from'],
                                fn (Builder $query, $date): Builder => $query->where('shipped_date', '>=', $date),
                            )
                            ->when(
                                $data['shipped_until'],
                                fn (Builder $query, $date): Builder => $query->where('shipped_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Envío'),
                Filter::make('amount_range')
                    ->form([
                        TextInput::make('min_amount')
                            ->label('Monto mínimo')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_amount')
                            ->label('Monto máximo')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '<=', $amount),
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
                Tables\Actions\Action::make('mark_shipped')
                    ->label('Marcar Enviado')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (SaleOrder $record) => $record->shipping_status === 'pending')
                    ->action(function (SaleOrder $record) {
                        $record->update([
                            'shipping_status' => 'shipped',
                            'shipped_date' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('mark_delivered')
                    ->label('Marcar Entregado')
                    ->icon('heroicon-o-home')
                    ->color('success')
                    ->visible(fn (SaleOrder $record) => $record->shipping_status === 'shipped')
                    ->action(function (SaleOrder $record) {
                        $record->update([
                            'shipping_status' => 'delivered',
                            'delivered_date' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Marcar Pagado')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (SaleOrder $record) => $record->payment_status === 'pending')
                    ->action(function (SaleOrder $record) {
                        $record->update([
                            'payment_status' => 'paid',
                            'payment_date' => now(),
                            'paid_amount' => $record->total_amount,
                            'outstanding_amount' => 0,
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (SaleOrder $record) {
                        $newRecord = $record->replicate();
                        $newRecord->order_number = $newRecord->order_number . '_copy';
                        $newRecord->status = 'pending';
                        $newRecord->payment_status = 'pending';
                        $newRecord->shipping_status = 'pending';
                        $newRecord->paid_amount = 0;
                        $newRecord->refunded_amount = 0;
                        $newRecord->outstanding_amount = $newRecord->total_amount;
                        $newRecord->shipped_date = null;
                        $newRecord->delivered_date = null;
                        $newRecord->payment_date = null;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_shipped_all')
                        ->label('Marcar Enviadas Todas')
                        ->icon('heroicon-o-truck')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->shipping_status === 'pending') {
                                    $record->update([
                                        'shipping_status' => 'shipped',
                                        'shipped_date' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_delivered_all')
                        ->label('Marcar Entregadas Todas')
                        ->icon('heroicon-o-home')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->shipping_status === 'shipped') {
                                    $record->update([
                                        'shipping_status' => 'delivered',
                                        'delivered_date' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_paid_all')
                        ->label('Marcar Pagadas Todas')
                        ->icon('heroicon-o-credit-card')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->payment_status === 'pending') {
                                    $record->update([
                                        'payment_status' => 'paid',
                                        'payment_date' => now(),
                                        'paid_amount' => $record->total_amount,
                                        'outstanding_amount' => 0,
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
                                ->options(SaleOrder::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_payment_status')
                        ->label('Actualizar Estado de Pago')
                        ->icon('heroicon-o-credit-card')
                        ->form([
                            Select::make('payment_status')
                                ->label('Estado de Pago')
                                ->options(SaleOrder::getPaymentStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['payment_status' => $data['payment_status']]);
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
            'index' => Pages\ListSaleOrders::route('/'),
            'create' => Pages\CreateSaleOrder::route('/create'),
            'edit' => Pages\EditSaleOrder::route('/{record}/edit'),
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
        $pendingPaymentCount = static::getModel()::where('payment_status', 'pending')->count();
        
        if ($pendingPaymentCount > 0) {
            return 'warning';
        }
        
        $pendingShippingCount = static::getModel()::where('shipping_status', 'pending')->count();
        
        if ($pendingShippingCount > 0) {
            return 'info';
        }
        
        return 'success';
    }
}
