<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Gestión Financiera';

    protected static ?string $modelLabel = 'Pago';

    protected static ?string $pluralModelLabel = 'Pagos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Pago')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('payment_code')
                                    ->label('Código de Pago')
                                    ->default(fn () => Payment::generatePaymentCode())
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('external_id')
                                    ->label('ID Externo')
                                    ->helperText('ID del gateway de pago')
                                    ->maxLength(255),

                                Forms\Components\Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('invoice_id')
                                    ->label('Factura')
                                    ->relationship('invoice', 'invoice_number')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('energy_cooperative_id')
                                    ->label('Cooperativa Energética')
                                    ->relationship('energyCooperative', 'name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'payment' => 'Pago',
                                        'refund' => 'Reembolso',
                                        'charge' => 'Cargo',
                                        'credit' => 'Crédito',
                                        'debit' => 'Débito'
                                    ])
                                    ->default('payment')
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'pending' => 'Pendiente',
                                        'processing' => 'Procesando',
                                        'completed' => 'Completado',
                                        'failed' => 'Fallido',
                                        'cancelled' => 'Cancelado',
                                        'expired' => 'Expirado'
                                    ])
                                    ->default('pending')
                                    ->required(),

                                Forms\Components\Select::make('method')
                                    ->label('Método de Pago')
                                    ->options([
                                        'card' => 'Tarjeta',
                                        'bank_transfer' => 'Transferencia Bancaria',
                                        'paypal' => 'PayPal',
                                        'stripe' => 'Stripe',
                                        'energy_credits' => 'Créditos Energéticos',
                                        'wallet' => 'Wallet'
                                    ])
                                    ->default('card')
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Financiera')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Monto')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('fee')
                                    ->label('Comisión')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('net_amount')
                                    ->label('Monto Neto')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\Select::make('currency')
                                    ->label('Moneda')
                                    ->options([
                                        'EUR' => 'EUR - Euro',
                                        'USD' => 'USD - Dólar',
                                        'GBP' => 'GBP - Libra'
                                    ])
                                    ->default('EUR')
                                    ->required(),

                                Forms\Components\TextInput::make('exchange_rate')
                                    ->label('Tasa de Cambio')
                                    ->numeric()
                                    ->step(0.000001),

                                Forms\Components\TextInput::make('original_amount')
                                    ->label('Monto Original')
                                    ->numeric()
                                    ->step(0.01),
                            ]),
                    ]),

                Forms\Components\Section::make('Gateway de Pago')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('gateway')
                                    ->label('Gateway')
                                    ->helperText('stripe, paypal, etc.')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('gateway_transaction_id')
                                    ->label('ID de Transacción del Gateway')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('card_last_four')
                                    ->label('Últimos 4 dígitos')
                                    ->maxLength(4)
                                    ->placeholder('1234'),

                                Forms\Components\TextInput::make('card_brand')
                                    ->label('Marca de Tarjeta')
                                    ->placeholder('Visa, Mastercard, etc.')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('gateway_response')
                                    ->label('Respuesta del Gateway')
                                    ->helperText('JSON de respuesta')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Información Energética')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('energy_contract_id')
                                    ->label('ID del Contrato Energético')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('energy_amount_kwh')
                                    ->label('Cantidad de Energía (kWh)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('kWh'),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Fechas Importantes')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('processed_at')
                                    ->label('Procesado el'),

                                Forms\Components\DateTimePicker::make('failed_at')
                                    ->label('Falló el'),

                                Forms\Components\DateTimePicker::make('expires_at')
                                    ->label('Expira el'),

                                Forms\Components\DateTimePicker::make('authorized_at')
                                    ->label('Autorizado el'),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2),

                        Forms\Components\TextInput::make('reference')
                            ->label('Referencia')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),

                        Forms\Components\TextInput::make('failure_reason')
                            ->label('Razón del Fallo')
                            ->maxLength(255),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_test')
                                    ->label('Es de Prueba')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_recurring')
                                    ->label('Es Recurrente')
                                    ->default(false),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Factura')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'payment' => 'primary',
                        'credit' => 'success',
                        'charge' => 'warning',
                        'debit' => 'danger',
                        'refund' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'cancelled', 'expired' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('method')
                    ->label('Método')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'card' => 'primary',
                        'bank_transfer' => 'success',
                        'paypal' => 'warning',
                        'stripe' => 'info',
                        'energy_credits', 'wallet' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('fee')
                    ->label('Comisión')
                    ->money('EUR')
                    ->toggleable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Neto')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('Gateway')
                    ->toggleable()
                    ->placeholder('N/A'),

                Tables\Columns\IconColumn::make('is_test')
                    ->label('Prueba')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Procesado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                        'expired' => 'Expirado'
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'payment' => 'Pago',
                        'refund' => 'Reembolso',
                        'charge' => 'Cargo',
                        'credit' => 'Crédito',
                        'debit' => 'Débito'
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('method')
                    ->label('Método')
                    ->options([
                        'card' => 'Tarjeta',
                        'bank_transfer' => 'Transferencia Bancaria',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'energy_credits' => 'Créditos Energéticos',
                        'wallet' => 'Wallet'
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('amount_range')
                    ->label('Rango de Monto')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount_from')
                                    ->label('Desde')
                                    ->numeric()
                                    ->prefix('€'),
                                Forms\Components\TextInput::make('amount_to')
                                    ->label('Hasta')
                                    ->numeric()
                                    ->prefix('€'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),

                Tables\Filters\TernaryFilter::make('is_test')
                    ->label('Es de Prueba'),

                Tables\Filters\Filter::make('created_at')
                    ->label('Fecha de Creación')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('refund')
                    ->label('Reembolsar')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Payment $record) => $record->isRefundable())
                    ->url(fn (Payment $record) => route('filament.admin.resources.refunds.create', [
                        'payment_id' => $record->id
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
            RelationManagers\RefundsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'invoice', 'energyCooperative']);
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->payment_code;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Usuario' => $record->user?->name,
            'Monto' => '€' . number_format($record->amount, 2),
            'Estado' => $record->getFormattedStatus(),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : 'success';
    }
}