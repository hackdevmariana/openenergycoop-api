<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAssetResource\Pages;
use App\Models\UserAsset;
use App\Models\User;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserAssetResource extends Resource
{
    protected static ?string $model = UserAsset::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Tienda Energética';
    protected static ?string $modelLabel = 'Activo de Usuario';
    protected static ?string $pluralModelLabel = 'Activos de Usuarios';
    protected static ?int $navigationSort = 3;

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
                        
                        Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('purchase_price', $product->base_purchase_price);
                                        $set('current_value', $product->base_sale_price);
                                    }
                                }
                            }),
                        
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->required()
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0),
                        
                        Forms\Components\Select::make('source_type')
                            ->label('Origen')
                            ->required()
                            ->options(UserAsset::SOURCE_TYPES)
                            ->native(false),
                        
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->required()
                            ->options(UserAsset::STATUSES)
                            ->native(false)
                            ->live(),
                    ])->columns(2),

                Forms\Components\Section::make('Fechas y Duración')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Fecha de Fin')
                            ->after('start_date'),
                    ])->columns(2),

                Forms\Components\Section::make('Valores Financieros')
                    ->schema([
                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Precio de Compra')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        
                        Forms\Components\TextInput::make('current_value')
                            ->label('Valor Actual')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                    ])->columns(2),

                Forms\Components\Section::make('Rendimiento y Eficiencia')
                    ->schema([
                        Forms\Components\TextInput::make('daily_yield')
                            ->label('Rendimiento Diario')
                            ->numeric()
                            ->step(0.0001)
                            ->suffix('por día'),
                        
                        Forms\Components\TextInput::make('total_yield_generated')
                            ->label('Rendimiento Total Generado')
                            ->numeric()
                            ->step(0.0001)
                            ->default(0)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('efficiency_rating')
                            ->label('Calificación de Eficiencia')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.1),
                        
                        Forms\Components\TextInput::make('maintenance_cost')
                            ->label('Costo de Mantenimiento')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                    ])->columns(2),

                Forms\Components\Section::make('Auto-reinversión')
                    ->schema([
                        Forms\Components\Toggle::make('auto_reinvest')
                            ->label('Auto-reinversión Activada')
                            ->live(),
                        
                        Forms\Components\TextInput::make('reinvest_threshold')
                            ->label('Umbral de Reinversión')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get): bool => $get('auto_reinvest')),
                        
                        Forms\Components\TextInput::make('reinvest_percentage')
                            ->label('Porcentaje a Reinvertir')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.1)
                            ->visible(fn (Forms\Get $get): bool => $get('auto_reinvest')),
                    ])->columns(3),

                Forms\Components\Section::make('Configuración Adicional')
                    ->schema([
                        Forms\Components\Toggle::make('is_transferable')
                            ->label('Transferible')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_delegatable')
                            ->label('Delegable'),
                        
                        Forms\Components\Select::make('delegated_to_user_id')
                            ->label('Delegado a Usuario')
                            ->relationship('delegatedToUser', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get): bool => $get('is_delegatable')),
                        
                        Forms\Components\Toggle::make('notifications_enabled')
                            ->label('Notificaciones Habilitadas')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Mantenimiento')
                    ->schema([
                        Forms\Components\DatePicker::make('last_maintenance_date')
                            ->label('Última Fecha de Mantenimiento'),
                        
                        Forms\Components\DatePicker::make('next_maintenance_date')
                            ->label('Próxima Fecha de Mantenimiento'),
                    ])->columns(2),
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
                
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->formatStateUsing(fn (UserAsset $record): string => 
                        number_format($record->quantity, 4) . ' ' . $record->product->unit
                    )
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Origen')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'purchase' => 'success',
                        'bonus' => 'warning',
                        'transfer' => 'info',
                        'donation' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => UserAsset::SOURCE_TYPES[$state] ?? $state),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'expired' => 'danger',
                        'suspended' => 'gray',
                        'transferred' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => UserAsset::STATUSES[$state] ?? $state),
                
                Tables\Columns\TextColumn::make('daily_yield')
                    ->label('Rendimiento Diario')
                    ->formatStateUsing(fn (?float $state, UserAsset $record): string => 
                        $state ? number_format($state, 4) . ' ' . $record->product->unit : 'N/A'
                    )
                    ->color('success')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('roi')
                    ->label('ROI')
                    ->getStateUsing(fn (UserAsset $record): string => 
                        number_format($record->calculateROI(), 1) . '%'
                    )
                    ->badge()
                    ->color(fn (UserAsset $record): string => match (true) {
                        $record->calculateROI() >= 10 => 'success',
                        $record->calculateROI() >= 5 => 'warning',
                        $record->calculateROI() >= 0 => 'info',
                        default => 'danger',
                    }),
                
                Tables\Columns\IconColumn::make('auto_reinvest')
                    ->label('Auto-reinversión')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-pause')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\TextColumn::make('current_value')
                    ->label('Valor Actual')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('efficiency_rating')
                    ->label('Eficiencia')
                    ->suffix('%')
                    ->color(fn (?float $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Vence')
                    ->date()
                    ->color('success')
                    ->sortable(),
                
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
                
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(UserAsset::STATUSES),
                
                Tables\Filters\SelectFilter::make('source_type')
                    ->label('Origen')
                    ->options(UserAsset::SOURCE_TYPES),
                
                Tables\Filters\TernaryFilter::make('auto_reinvest')
                    ->label('Auto-reinversión')
                    ->trueLabel('Con auto-reinversión')
                    ->falseLabel('Sin auto-reinversión'),
                
                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Vencen Pronto (30 días)')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('end_date')
                              ->where('end_date', '>=', now())
                              ->where('end_date', '<=', now()->addDays(30))
                    ),
                
                Tables\Filters\Filter::make('high_yield')
                    ->label('Alto Rendimiento')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('daily_yield', '>', 5)
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    
                    Tables\Actions\Action::make('generate_yield')
                        ->label('Generar Rendimiento')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->action(function (UserAsset $record) {
                            $yield = $record->generateDailyYield();
                            if ($yield > 0) {
                                $record->addYield($yield);
                                \Filament\Notifications\Notification::make()
                                    ->title('Rendimiento Generado')
                                    ->body("Se generaron {$yield} {$record->product->unit}")
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Sin Rendimiento')
                                    ->body('No se pudo generar rendimiento para este activo')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->visible(fn (UserAsset $record): bool => $record->isActive() && $record->daily_yield > 0),
                    
                    Tables\Actions\Action::make('update_value')
                        ->label('Actualizar Valor')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (UserAsset $record) {
                            $oldValue = $record->current_value;
                            $record->updateCurrentValue();
                            $newValue = $record->fresh()->current_value;
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Valor Actualizado')
                                ->body("Valor anterior: €{$oldValue} → Nuevo valor: €{$newValue}")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('update_values')
                        ->label('Actualizar Valores')
                        ->icon('heroicon-o-arrow-path')
                        ->action(fn ($records) => $records->each->updateCurrentValue())
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUserAssets::route('/'),
            'create' => Pages\CreateUserAsset::route('/create'),
            'edit' => Pages\EditUserAsset::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'product.name'];
    }
}