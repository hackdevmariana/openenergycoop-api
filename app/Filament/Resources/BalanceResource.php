<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceResource\Pages;
use App\Models\Balance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Energía y Comercio';
    protected static ?string $modelLabel = 'Balance';
    protected static ?string $pluralModelLabel = 'Balances';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Balance')
                            ->required()
                            ->options(Balance::TYPES)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Auto-configurar moneda según el tipo
                                match ($state) {
                                    'wallet' => $set('currency', 'EUR'),
                                    'mining_ths' => $set('currency', 'BTC'),
                                    default => $set('currency', 'EUR'),
                                };
                            }),
                        
                        Forms\Components\Select::make('currency')
                            ->label('Moneda')
                            ->required()
                            ->options(Balance::CURRENCIES)
                            ->native(false),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Cantidad')
                            ->required()
                            ->numeric()
                            ->step(0.000001)
                            ->minValue(0),
                    ])->columns(2),

                Forms\Components\Section::make('Límites y Configuración')
                    ->schema([
                        Forms\Components\TextInput::make('daily_limit')
                            ->label('Límite Diario')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->helperText('Dejar vacío para sin límite'),
                        
                        Forms\Components\TextInput::make('monthly_limit')
                            ->label('Límite Mensual')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->helperText('Dejar vacío para sin límite'),
                        
                        Forms\Components\Toggle::make('is_frozen')
                            ->label('Balance Congelado')
                            ->helperText('Los balances congelados no pueden realizar gastos'),
                    ])->columns(3),

                Forms\Components\Section::make('Metadatos')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Información Adicional')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'success',
                        'energy_kwh' => 'warning',
                        'mining_ths' => 'info',
                        'storage_capacity' => 'purple',
                        'carbon_credits' => 'orange',
                        'production_rights' => 'pink',
                        'loyalty_points' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Balance::TYPES[$state] ?? $state),
                
                Tables\Columns\TextColumn::make('formatted_amount')
                    ->label('Cantidad')
                    ->getStateUsing(fn (Balance $record): string => $record->getFormattedAmount())
                    ->weight('bold')
                    ->color(fn (Balance $record): string => $record->amount > 0 ? 'success' : 'danger'),
                
                Tables\Columns\TextColumn::make('currency')
                    ->label('Moneda')
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('daily_spent')
                    ->label('Gastado Hoy')
                    ->getStateUsing(fn (Balance $record): string => '€' . number_format($record->getDailySpent(), 2))
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('remaining_daily_limit')
                    ->label('Límite Diario Restante')
                    ->getStateUsing(fn (Balance $record): string => 
                        $record->daily_limit ? '€' . number_format($record->getRemainingDailyLimit(), 2) : 'Sin límite'
                    )
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('is_frozen')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),
                
                Tables\Columns\TextColumn::make('last_transaction_at')
                    ->label('Última Transacción')
                    ->dateTime()
                    ->since()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('Transacciones')
                    ->counts('transactions')
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(Balance::TYPES),
                
                Tables\Filters\SelectFilter::make('currency')
                    ->label('Moneda')
                    ->options(Balance::CURRENCIES),
                
                Tables\Filters\TernaryFilter::make('is_frozen')
                    ->label('Estado')
                    ->trueLabel('Congelados')
                    ->falseLabel('Activos'),
                
                Tables\Filters\Filter::make('positive_balance')
                    ->label('Con Saldo Positivo')
                    ->query(fn (Builder $query): Builder => $query->where('amount', '>', 0)),
                
                Tables\Filters\Filter::make('with_limits')
                    ->label('Con Límites Configurados')
                    ->query(fn (Builder $query): Builder => 
                        $query->where(function ($q) {
                            $q->whereNotNull('daily_limit')->orWhereNotNull('monthly_limit');
                        })
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    
                    Tables\Actions\Action::make('freeze')
                        ->label('Congelar')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Motivo del Congelamiento')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Balance $record, array $data) {
                            $record->freeze($data['reason']);
                            \Filament\Notifications\Notification::make()
                                ->title('Balance Congelado')
                                ->body('El balance ha sido congelado exitosamente')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Balance $record): bool => !$record->is_frozen),
                    
                    Tables\Actions\Action::make('unfreeze')
                        ->label('Descongelar')
                        ->icon('heroicon-o-lock-open')
                        ->color('success')
                        ->action(function (Balance $record) {
                            $record->unfreeze();
                            \Filament\Notifications\Notification::make()
                                ->title('Balance Descongelado')
                                ->body('El balance ha sido descongelado exitosamente')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Balance $record): bool => $record->is_frozen),
                    
                    Tables\Actions\Action::make('add_transaction')
                        ->label('Agregar Transacción')
                        ->icon('heroicon-o-plus')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('type')
                                ->label('Tipo')
                                ->options([
                                    'income' => 'Ingreso',
                                    'expense' => 'Gasto',
                                    'adjustment' => 'Ajuste',
                                ])
                                ->required(),
                            
                            Forms\Components\TextInput::make('amount')
                                ->label('Cantidad')
                                ->required()
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0.01),
                            
                            Forms\Components\Textarea::make('description')
                                ->label('Descripción')
                                ->required()
                                ->rows(2),
                        ])
                        ->action(function (Balance $record, array $data) {
                            try {
                                $transaction = $record->addTransaction(
                                    $data['type'],
                                    $data['amount'],
                                    $data['description']
                                );
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Transacción Creada')
                                    ->body("Transacción de €{$data['amount']} creada exitosamente")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('freeze_balances')
                        ->label('Congelar Balances')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Motivo')
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(fn ($record) => $record->freeze($data['reason']));
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('amount', 'desc');
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
            'index' => Pages\ListBalances::route('/'),
            'create' => Pages\CreateBalance::route('/create'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_frozen', false)->count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'type'];
    }
}