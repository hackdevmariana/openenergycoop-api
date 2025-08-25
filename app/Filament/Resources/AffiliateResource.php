<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Filament\Resources\AffiliateResource\RelationManagers;
use App\Models\Affiliate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Marketing y Ventas';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Afiliados';

    protected static ?string $modelLabel = 'Afiliado';

    protected static ?string $pluralModelLabel = 'Afiliados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Código de Afiliado')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Código único del afiliado'),
                                Select::make('commission_type')
                                    ->label('Tipo de Comisión')
                                    ->options([
                                        'percentage' => 'Porcentaje',
                                        'fixed' => 'Fijo',
                                    ])
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('commission_value')
                                    ->label('Valor de Comisión')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Valor de la comisión'),
                                Select::make('tier_level')
                                    ->label('Nivel')
                                    ->options(Affiliate::getTierLevels())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('monthly_target')
                                    ->label('Meta Mensual')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Meta de ventas mensual'),
                                TextInput::make('payment_threshold')
                                    ->label('Umbral de Pago')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Monto mínimo para realizar pago'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('performance_bonus')
                                    ->label('Bono de Rendimiento')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Bono por buen rendimiento'),
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->helperText('Estado del afiliado'),
                            ]),
                        Select::make('payout_method')
                            ->label('Método de Pago')
                            ->options([
                                'bank_transfer' => 'Transferencia Bancaria',
                                'paypal' => 'PayPal',
                                'check' => 'Cheque',
                                'crypto' => 'Criptomonedas',
                            ])
                            ->searchable()
                            ->helperText('Método preferido de pago'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('commission_type')
                    ->label('Tipo de Comisión')
                    ->colors([
                        'success' => 'percentage',
                        'warning' => 'fixed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'percentage' => 'Porcentaje',
                        'fixed' => 'Fijo',
                        default => $state,
                    }),
                BadgeColumn::make('tier_level')
                    ->label('Nivel')
                    ->colors([
                        'primary' => 'bronze',
                        'warning' => 'silver',
                        'success' => 'gold',
                        'danger' => 'platinum',
                        'info' => 'diamond',
                    ])
                    ->formatStateUsing(fn (string $state): string => Affiliate::getTierLevels()[$state] ?? $state),
                TextColumn::make('commission_value')
                    ->label('Valor de Comisión')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('monthly_target')
                    ->label('Meta Mensual')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('performance_bonus')
                    ->label('Bono de Rendimiento')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('payment_threshold')
                    ->label('Umbral de Pago')
                    ->money('USD')
                    ->sortable(),
                BadgeColumn::make('payout_method')
                    ->label('Método de Pago')
                    ->colors([
                        'success' => 'bank_transfer',
                        'info' => 'paypal',
                        'warning' => 'check',
                        'danger' => 'crypto',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'bank_transfer' => 'Transferencia Bancaria',
                        'paypal' => 'PayPal',
                        'check' => 'Cheque',
                        'crypto' => 'Criptomonedas',
                        default => $state,
                    }),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('commission_type')
                    ->label('Tipo de Comisión')
                    ->options([
                        'percentage' => 'Porcentaje',
                        'fixed' => 'Fijo',
                    ]),
                SelectFilter::make('tier_level')
                    ->label('Nivel')
                    ->options(Affiliate::getTierLevels()),
                SelectFilter::make('payout_method')
                    ->label('Método de Pago')
                    ->options([
                        'bank_transfer' => 'Transferencia Bancaria',
                        'paypal' => 'PayPal',
                        'check' => 'Cheque',
                        'crypto' => 'Criptomonedas',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Estado Activo'),
                Filter::make('commission_value_range')
                    ->form([
                        TextInput::make('min_commission')
                            ->label('Comisión mínima')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_commission')
                            ->label('Comisión máxima')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_commission'],
                                fn (Builder $query, $commission): Builder => $query->where('commission_value', '>=', $commission),
                            )
                            ->when(
                                $data['max_commission'],
                                fn (Builder $query, $commission): Builder => $query->where('commission_value', '<=', $commission),
                            );
                    })
                    ->label('Rango de Comisión'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Affiliate $record) => !$record->is_active)
                    ->action(function (Affiliate $record) {
                        $record->update(['is_active' => true]);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Affiliate $record) => $record->is_active)
                    ->action(function (Affiliate $record) {
                        $record->update(['is_active' => false]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (Affiliate $record) {
                        $newRecord = $record->replicate();
                        $newRecord->code = $newRecord->code . '_copy';
                        $newRecord->is_active = false;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate_all')
                        ->label('Activar Todos')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate_all')
                        ->label('Desactivar Todos')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_tier')
                        ->label('Actualizar Nivel')
                        ->icon('heroicon-o-star')
                        ->form([
                            Select::make('tier_level')
                                ->label('Nivel')
                                ->options(Affiliate::getTierLevels())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['tier_level' => $data['tier_level']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_commission')
                        ->label('Actualizar Comisión')
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            TextInput::make('commission_value')
                                ->label('Valor de Comisión')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['commission_value' => $data['commission_value']]);
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
            'index' => Pages\ListAffiliates::route('/'),
            'create' => Pages\CreateAffiliate::route('/create'),
            'edit' => Pages\EditAffiliate::route('/{record}/edit'),
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
        $suspendedCount = static::getModel()::where('status', 'suspended')->count();
        
        if ($suspendedCount > 0) {
            return 'warning';
        }
        
        $pendingCount = static::getModel()::where('status', 'pending_approval')->count();
        
        if ($pendingCount > 0) {
            return 'info';
        }
        
        return 'success';
    }
}
