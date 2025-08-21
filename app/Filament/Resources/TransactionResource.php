<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $modelLabel = 'Transacción';

    protected static ?string $pluralModelLabel = 'Transacciones';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('transaction_code')
                                    ->label('Código de Transacción')
                                    ->default(fn () => Transaction::generateTransactionCode())
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                Forms\Components\TextInput::make('reference')
                                    ->label('Referencia')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('batch_id')
                                    ->label('ID de Lote')
                                    ->maxLength(255),

                                Forms\Components\Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('payment_id')
                                    ->label('Pago')
                                    ->relationship('payment', 'payment_code')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('invoice_id')
                                    ->label('Factura')
                                    ->relationship('invoice', 'invoice_number')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),

                Forms\Components\Section::make('Tipo y Estado')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'payment' => 'Pago',
                                        'refund' => 'Reembolso',
                                        'transfer' => 'Transferencia',
                                        'fee' => 'Comisión',
                                        'commission' => 'Comisión',
                                        'bonus' => 'Bonus',
                                        'penalty' => 'Penalización',
                                        'adjustment' => 'Ajuste',
                                        'energy_purchase' => 'Compra de Energía',
                                        'energy_sale' => 'Venta de Energía',
                                        'subscription_fee' => 'Cuota de Suscripción',
                                        'membership_fee' => 'Cuota de Membresía',
                                        'deposit' => 'Depósito',
                                        'withdrawal' => 'Retiro'
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('category')
                                    ->label('Categoría')
                                    ->options([
                                        'energy' => 'Energía',
                                        'financial' => 'Financiera',
                                        'administrative' => 'Administrativa',
                                        'penalty' => 'Penalización',
                                        'bonus' => 'Bonus',
                                        'membership' => 'Membresía',
                                        'service' => 'Servicio',
                                        'adjustment' => 'Ajuste',
                                        'fee' => 'Comisión',
                                        'commission' => 'Comisión'
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'pending' => 'Pendiente',
                                        'processing' => 'Procesando',
                                        'completed' => 'Completada',
                                        'failed' => 'Fallida',
                                        'cancelled' => 'Cancelada',
                                        'reversed' => 'Revertida'
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Financiera')
                    ->schema([
                        Forms\Components\Grid::make(4)
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
                                        'EUR' => 'EUR',
                                        'USD' => 'USD',
                                        'GBP' => 'GBP'
                                    ])
                                    ->default('EUR')
                                    ->required(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('balance_before')
                                    ->label('Balance Anterior')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('balance_after')
                                    ->label('Balance Posterior')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€'),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Energética')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('energy_amount_kwh')
                                    ->label('Cantidad de Energía')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('kWh'),

                                Forms\Components\TextInput::make('energy_price_per_kwh')
                                    ->label('Precio por kWh')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('energy_contract_id')
                                    ->label('ID del Contrato Energético')
                                    ->maxLength(255),

                                Forms\Components\DateTimePicker::make('energy_delivery_date')
                                    ->label('Fecha de Entrega de Energía'),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Descripción')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),

                        Forms\Components\TextInput::make('failure_reason')
                            ->label('Razón del Fallo')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Configuración')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('is_internal')
                                    ->label('Es Interna')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_test')
                                    ->label('Es de Prueba')
                                    ->default(false),

                                Forms\Components\Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_reversible')
                                    ->label('Es Reversible')
                                    ->default(true),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'payment' => 'primary',
                        'refund' => 'warning',
                        'transfer' => 'info',
                        'energy_purchase', 'energy_sale' => 'success',
                        'fee', 'commission' => 'gray',
                        'bonus' => 'green',
                        'penalty' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        'reversed' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Neto')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('energy_amount_kwh')
                    ->label('Energía (kWh)')
                    ->numeric(2)
                    ->suffix(' kWh')
                    ->toggleable()
                    ->placeholder('N/A'),

                Tables\Columns\IconColumn::make('requires_approval')
                    ->label('Aprobación')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_internal')
                    ->label('Interna')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Procesada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
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
                        'completed' => 'Completada',
                        'failed' => 'Fallida',
                        'cancelled' => 'Cancelada',
                        'reversed' => 'Revertida'
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'payment' => 'Pago',
                        'refund' => 'Reembolso',
                        'transfer' => 'Transferencia',
                        'energy_purchase' => 'Compra de Energía',
                        'energy_sale' => 'Venta de Energía',
                        'fee' => 'Comisión',
                        'bonus' => 'Bonus',
                        'penalty' => 'Penalización'
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'energy' => 'Energía',
                        'financial' => 'Financiera',
                        'administrative' => 'Administrativa',
                        'membership' => 'Membresía'
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_internal')
                    ->label('Es Interna'),

                Tables\Filters\TernaryFilter::make('requires_approval')
                    ->label('Requiere Aprobación'),

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
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Transaction $record) => $record->requiresApproval())
                    ->requiresConfirmation()
                    ->action(function (Transaction $record) {
                        $record->approve(auth()->user());
                        $record->markAsProcessed();
                    }),
                Tables\Actions\Action::make('reverse')
                    ->label('Revertir')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Transaction $record) => $record->isReversible())
                    ->requiresConfirmation()
                    ->modalHeading('Revertir Transacción')
                    ->modalDescription('¿Estás seguro de que quieres revertir esta transacción?')
                    ->form([
                        Forms\Components\Textarea::make('reversal_reason')
                            ->label('Razón de la Reversión')
                            ->required(),
                    ]),
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
            RelationManagers\WalletTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'payment', 'invoice', 'energyCooperative']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('requires_approval', true)->whereNull('approved_at')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::where('requires_approval', true)->whereNull('approved_at')->count();
        return $count > 0 ? 'warning' : 'success';
    }
}