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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
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
                                TextInput::make('affiliate_code')
                                    ->label('Código de Afiliado')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Código único del afiliado'),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(Affiliate::getStatuses())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('tier')
                                    ->label('Nivel')
                                    ->options(Affiliate::getTierLevels())
                                    ->required()
                                    ->searchable(),
                                TextInput::make('commission_rate')
                                    ->label('Tasa de Comisión (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Porcentaje de comisión'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_earnings')
                                    ->label('Ganancias Totales')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Total de ganancias acumuladas'),
                                TextInput::make('pending_earnings')
                                    ->label('Ganancias Pendientes')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Ganancias pendientes de pago'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('paid_earnings')
                                    ->label('Ganancias Pagadas')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Total de ganancias ya pagadas'),
                                TextInput::make('total_referrals')
                                    ->label('Total de Referidos')
                                    ->numeric()
                                    ->minValue(0)
                                    ->helperText('Número total de referidos'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('active_referrals')
                                    ->label('Referidos Activos')
                                    ->numeric()
                                    ->minValue(0)
                                    ->helperText('Número de referidos activos'),
                                TextInput::make('converted_referrals')
                                    ->label('Referidos Convertidos')
                                    ->numeric()
                                    ->minValue(0)
                                    ->helperText('Número de referidos convertidos'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('conversion_rate')
                                    ->label('Tasa de Conversión (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->helperText('Porcentaje de conversión'),
                                DatePicker::make('joined_date')
                                    ->label('Fecha de Ingreso')
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('last_activity_date')
                                    ->label('Última Actividad')
                                    ->helperText('Fecha de última actividad'),
                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Información de Pago')
                    ->schema([
                        Textarea::make('payment_instructions')
                            ->label('Instrucciones de Pago')
                            ->rows(3)
                            ->helperText('Instrucciones específicas para el pago'),
                        KeyValue::make('payment_methods')
                            ->label('Métodos de Pago')
                            ->keyLabel('Método')
                            ->valueLabel('Descripción')
                            ->helperText('Métodos de pago disponibles'),
                    ])
                    ->collapsible(),

                Section::make('Materiales de Marketing')
                    ->schema([
                        KeyValue::make('marketing_materials')
                            ->label('Materiales Disponibles')
                            ->keyLabel('Material')
                            ->valueLabel('Descripción')
                            ->helperText('Materiales de marketing disponibles'),
                    ])
                    ->collapsible(),

                Section::make('Métricas de Rendimiento')
                    ->schema([
                        KeyValue::make('performance_metrics')
                            ->label('Métricas')
                            ->keyLabel('Métrica')
                            ->valueLabel('Valor')
                            ->helperText('Métricas de rendimiento del afiliado'),
                    ])
                    ->collapsible(),

                Section::make('Información Adicional')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->helperText('Notas adicionales sobre el afiliado'),
                        Grid::make(2)
                            ->schema([
                                Select::make('referred_by')
                                    ->label('Referido por')
                                    ->relationship('referredBy', 'affiliate_code')
                                    ->searchable()
                                    ->helperText('Afiliado que lo refirió'),
                                Select::make('approved_by')
                                    ->label('Aprobado por')
                                    ->relationship('approvedBy', 'name')
                                    ->searchable()
                                    ->helperText('Usuario que aprobó el afiliado'),
                            ]),
                        DatePicker::make('approved_at')
                            ->label('Fecha de Aprobación')
                            ->helperText('Cuándo fue aprobado el afiliado'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('affiliate_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending_approval',
                        'danger' => 'suspended',
                        'gray' => 'inactive',
                    ])
                    ->formatStateUsing(fn (string $state): string => Affiliate::getStatuses()[$state] ?? $state),
                BadgeColumn::make('tier')
                    ->label('Nivel')
                    ->colors([
                        'gray' => 'bronze',
                        'info' => 'silver',
                        'warning' => 'gold',
                        'primary' => 'platinum',
                        'success' => 'diamond',
                    ])
                    ->formatStateUsing(fn (string $state): string => Affiliate::getTierLevels()[$state] ?? $state),
                TextColumn::make('commission_rate')
                    ->label('Comisión (%)')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('total_earnings')
                    ->label('Ganancias Totales')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ]),
                TextColumn::make('pending_earnings')
                    ->label('Ganancias Pendientes')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ]),
                TextColumn::make('total_referrals')
                    ->label('Total Referidos')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                TextColumn::make('conversion_rate')
                    ->label('Tasa Conversión (%)')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),
                TextColumn::make('joined_date')
                    ->label('Fecha Ingreso')
                    ->date()
                    ->sortable(),
                TextColumn::make('last_activity_date')
                    ->label('Última Actividad')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Affiliate::getStatuses()),
                SelectFilter::make('tier')
                    ->label('Nivel')
                    ->options(Affiliate::getTierLevels()),
                Filter::make('commission_rate_range')
                    ->form([
                        TextInput::make('min_commission')
                            ->label('Comisión mínima (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('max_commission')
                            ->label('Comisión máxima (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_commission'],
                                fn (Builder $query, $commission): Builder => $query->where('commission_rate', '>=', $commission),
                            )
                            ->when(
                                $data['max_commission'],
                                fn (Builder $query, $commission): Builder => $query->where('commission_rate', '<=', $commission),
                            );
                    })
                    ->label('Rango de Comisión'),
                Filter::make('earnings_range')
                    ->form([
                        TextInput::make('min_earnings')
                            ->label('Ganancias mínimas')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_earnings')
                            ->label('Ganancias máximas')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_earnings'],
                                fn (Builder $query, $earnings): Builder => $query->where('total_earnings', '>=', $earnings),
                            )
                            ->when(
                                $data['max_earnings'],
                                fn (Builder $query, $earnings): Builder => $query->where('total_earnings', '<=', $earnings),
                            );
                    })
                    ->label('Rango de Ganancias'),
                Filter::make('high_performers')
                    ->label('Alto Rendimiento')
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '>=', 70))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Affiliate $record) => $record->status === 'pending_approval')
                    ->action(function (Affiliate $record) {
                        $record->update([
                            'status' => 'active',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspender')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Affiliate $record) => $record->status === 'active')
                    ->action(function (Affiliate $record) {
                        $record->update(['status' => 'suspended']);
                    }),
                Tables\Actions\Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Affiliate $record) => in_array($record->status, ['inactive', 'suspended']))
                    ->action(function (Affiliate $record) {
                        $record->update(['status' => 'active']);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (Affiliate $record) {
                        $newRecord = $record->replicate();
                        $newRecord->affiliate_code = $newRecord->affiliate_code . '_copy';
                        $newRecord->status = 'pending_approval';
                        $newRecord->approved_by = null;
                        $newRecord->approved_at = null;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_all')
                        ->label('Aprobar Todos')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'active',
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('suspend_all')
                        ->label('Suspender Todos')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'suspended']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_tier')
                        ->label('Actualizar Nivel')
                        ->icon('heroicon-o-star')
                        ->form([
                            Select::make('tier')
                                ->label('Nivel')
                                ->options(Affiliate::getTierLevels())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['tier' => $data['tier']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_commission')
                        ->label('Actualizar Comisión')
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            TextInput::make('commission_rate')
                                ->label('Tasa de Comisión (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['commission_rate' => $data['commission_rate']]);
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
