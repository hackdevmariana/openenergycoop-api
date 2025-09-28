<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletTransactionResource\Pages;
use App\Models\WalletTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class WalletTransactionResource extends Resource
{
    protected static ?string $model = WalletTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Finanzas y Contabilidad';

    protected static ?string $modelLabel = 'Transacción de Wallet';

    protected static ?string $pluralModelLabel = 'Transacciones de Wallet';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        
        return match (true) {
            $count >= 100 => 'success',
            $count >= 50 => 'warning',
            $count >= 10 => 'info',
            default => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('transaction_code')
                                    ->label('Código')
                                    ->default(fn () => WalletTransaction::generateTransactionCode())
                                    ->required(),

                                Forms\Components\Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable(),

                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'credit' => 'Crédito',
                                        'debit' => 'Débito',
                                        'transfer_in' => 'Transferencia Entrante',
                                        'transfer_out' => 'Transferencia Saliente',
                                        'energy_credit' => 'Crédito Energético',
                                        'energy_debit' => 'Débito Energético',
                                        'reward' => 'Recompensa',
                                        'bonus' => 'Bonus'
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('token_type')
                                    ->label('Tipo de Token')
                                    ->options([
                                        'energy_credit' => 'Crédito Energético',
                                        'carbon_credit' => 'Crédito de Carbono',
                                        'loyalty_point' => 'Punto de Lealtad',
                                        'service_credit' => 'Crédito de Servicio'
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->step(0.000001),

                                Forms\Components\TextInput::make('equivalent_value')
                                    ->label('Valor Equivalente')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€'),
                            ]),
                    ]),

                Forms\Components\Section::make('Descripción')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3),
                    ]),
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
                        'credit', 'transfer_in', 'reward', 'bonus' => 'success',
                        'debit', 'transfer_out' => 'danger',
                        'energy_credit' => 'green',
                        'energy_debit' => 'orange',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('token_type')
                    ->label('Token')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Cantidad')
                    ->numeric(6)
                    ->sortable(),

                Tables\Columns\TextColumn::make('equivalent_value')
                    ->label('Valor')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'credit' => 'Crédito',
                        'debit' => 'Débito',
                        'energy_credit' => 'Crédito Energético'
                    ]),
                Tables\Filters\SelectFilter::make('token_type')
                    ->options([
                        'energy_credit' => 'Crédito Energético',
                        'carbon_credit' => 'Crédito de Carbono'
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWalletTransactions::route('/'),
            'create' => Pages\CreateWalletTransaction::route('/create'),
            'view' => Pages\ViewWalletTransaction::route('/{record}'),
            'edit' => Pages\EditWalletTransaction::route('/{record}/edit'),
        ];
    }
}