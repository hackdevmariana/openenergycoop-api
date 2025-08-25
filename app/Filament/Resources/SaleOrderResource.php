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
use Filament\Forms\Components\FileUpload;
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
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(SaleOrder::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(SaleOrder::getPriorities())
                                    ->required()
                                    ->searchable(),
                                Select::make('source')
                                    ->label('Origen')
                                    ->options(SaleOrder::getSources())
                                    ->searchable(),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción de la orden'),
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
                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario del sistema'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('sales_representative_id')
                                    ->label('Representante de Ventas')
                                    ->relationship('salesRepresentative', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Representante asignado'),
                                Select::make('affiliate_id')
                                    ->label('Afiliado')
                                    ->relationship('affiliate', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Afiliado que generó la venta'),
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
                                TextInput::make('discount_amount')
                                    ->label('Descuento')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto del descuento'),
                                TextInput::make('tax_amount')
                                    ->label('Impuestos')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto de impuestos'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('shipping_cost')
                                    ->label('Costo de Envío')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Costo del envío'),
                                TextInput::make('handling_fee')
                                    ->label('Cargo por Manejo')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Cargo adicional por manejo'),
                                TextInput::make('total_amount')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Total final de la orden'),
                            ]),
                        Grid::make(2)
                            ->schema([
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

                Section::make('Estado de Pago')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('payment_status')
                                    ->label('Estado de Pago')
                                    ->options(SaleOrder::getPaymentStatuses())
                                    ->required()
                                    ->searchable(),
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
                                TextInput::make('payment_notes')
                                    ->label('Notas de Pago')
                                    ->maxLength(255)
                                    ->helperText('Notas adicionales sobre el pago'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Envío y Entrega')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('shipping_status')
                                    ->label('Estado de Envío')
                                    ->options(SaleOrder::getShippingStatuses())
                                    ->searchable(),
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
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('shipped_at')
                                    ->label('Enviado el')
                                    ->helperText('Cuándo se envió la orden'),
                                DateTimePicker::make('delivered_at')
                                    ->label('Entregado el')
                                    ->helperText('Cuándo se entregó la orden'),
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
                        Grid::make(2)
                            ->schema([
                                Select::make('discount_code_id')
                                    ->label('Código de Descuento')
                                    ->relationship('discountCode', 'code')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Código de descuento aplicado'),
                                TextInput::make('discount_percentage')
                                    ->label('Porcentaje de Descuento (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de descuento'),
                            ]),
                        RichEditor::make('promotional_notes')
                            ->label('Notas Promocionales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas sobre promociones aplicadas'),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('order_date')
                                    ->label('Fecha de Orden')
                                    ->required()
                                    ->helperText('Cuándo se realizó la orden'),
                                DateTimePicker::make('confirmed_at')
                                    ->label('Confirmado el')
                                    ->helperText('Cuándo se confirmó la orden'),
                                DateTimePicker::make('processing_at')
                                    ->label('En Procesamiento el')
                                    ->helperText('Cuándo comenzó el procesamiento'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('estimated_delivery')
                                    ->label('Entrega Estimada')
                                    ->helperText('Fecha estimada de entrega'),
                                DateTimePicker::make('cancelled_at')
                                    ->label('Cancelado el')
                                    ->helperText('Cuándo se canceló la orden'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Productos y Servicios')
                    ->schema([
                        Repeater::make('order_items')
                            ->label('Elementos de la Orden')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('product_name')
                                            ->label('Nombre del Producto')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('quantity')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->minValue(1)
                                            ->step(1)
                                            ->required(),
                                        TextInput::make('unit_price')
                                            ->label('Precio Unitario')
                                            ->numeric()
                                            ->prefix('$')
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->required(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('total_price')
                                            ->label('Precio Total')
                                            ->numeric()
                                            ->prefix('$')
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->required(),
                                        TextInput::make('sku')
                                            ->label('SKU')
                                            ->maxLength(255),
                                    ]),
                                Textarea::make('item_notes')
                                    ->label('Notas del Elemento')
                                    ->rows(2)
                                    ->maxLength(500),
                            ])
                            ->defaultItems(1)
                            ->minItems(1)
                            ->maxItems(100)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? null),
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
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_urgent')
                                    ->label('Urgente')
                                    ->helperText('¿Es una orden urgente?'),
                                Toggle::make('is_bulk_order')
                                    ->label('Orden Masiva')
                                    ->helperText('¿Es parte de una orden masiva?'),
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
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => SaleOrder::getPriorities()[$state] ?? $state),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
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
                TextColumn::make('order_date')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('estimated_delivery')
                    ->label('Entrega Est.')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('salesRepresentative.name')
                    ->label('Vendedor')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('affiliate.name')
                    ->label('Afiliado')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                ToggleColumn::make('is_urgent')
                    ->label('Urgente'),
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
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(SaleOrder::getPriorities())
                    ->multiple(),
                SelectFilter::make('payment_status')
                    ->label('Estado de Pago')
                    ->options(SaleOrder::getPaymentStatuses())
                    ->multiple(),
                SelectFilter::make('shipping_status')
                    ->label('Estado de Envío')
                    ->options(SaleOrder::getShippingStatuses())
                    ->multiple(),
                SelectFilter::make('source')
                    ->label('Origen')
                    ->options(SaleOrder::getSources())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activas'),
                Filter::make('urgent')
                    ->query(fn (Builder $query): Builder => $query->where('is_urgent', true))
                    ->label('Solo Urgentes'),
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
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('order_from')
                            ->label('Orden desde'),
                        DateTimePicker::make('order_until')
                            ->label('Orden hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_from'],
                                fn (Builder $query, $date): Builder => $query->where('order_date', '>=', $date),
                            )
                            ->when(
                                $data['order_until'],
                                fn (Builder $query, $date): Builder => $query->where('order_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas'),
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
                Tables\Actions\Action::make('confirm_order')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (SaleOrder $record) => $record->status === 'pending')
                    ->action(function (SaleOrder $record) {
                        $record->update([
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('process_order')
                    ->label('Procesar')
                    ->icon('heroicon-o-cog')
                    ->color('warning')
                    ->visible(fn (SaleOrder $record) => $record->status === 'confirmed')
                    ->action(function (SaleOrder $record) {
                        $record->update([
                            'status' => 'processing',
                            'processing_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('ship_order')
                    ->label('Enviar')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (SaleOrder $record) => $record->status === 'processing')
                    ->action(function (SaleOrder $record) {
                        $record->update([
                            'status' => 'shipped',
                            'shipped_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('deliver_order')
                    ->label('Entregar')
                    ->icon('heroicon-o-home')
                    ->color('success')
                    ->visible(fn (SaleOrder $record) => $record->status === 'shipped')
                    ->action(function (SaleOrder $record) {
                        $record->update([
                            'status' => 'delivered',
                            'delivered_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('cancel_order')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (SaleOrder $record) => !in_array($record->status, ['cancelled', 'delivered']))
                    ->action(function (SaleOrder $record) {
                        $record->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
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
                        $newRecord->confirmed_at = null;
                        $newRecord->shipped_at = null;
                        $newRecord->delivered_at = null;
                        $newRecord->cancelled_at = null;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('confirm_all')
                        ->label('Confirmar Todas')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'confirmed',
                                    'confirmed_at' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('process_all')
                        ->label('Procesar Todas')
                        ->icon('heroicon-o-cog')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'processing',
                                    'processing_at' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('ship_all')
                        ->label('Enviar Todas')
                        ->icon('heroicon-o-truck')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'shipped',
                                    'shipped_at' => now(),
                                ]);
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
                    Tables\Actions\BulkAction::make('update_priority')
                        ->label('Actualizar Prioridad')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority')
                                ->label('Prioridad')
                                ->options(SaleOrder::getPriorities())
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
            'index' => Pages\ListSaleOrders::route('/'),
            'create' => Pages\CreateSaleOrder::route('/create'),
            'edit' => Pages\EditSaleOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $urgentCount = static::getModel()::where('is_urgent', true)->count();
        
        if ($urgentCount > 0) {
            return 'danger';
        }
        
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        
        if ($pendingCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
