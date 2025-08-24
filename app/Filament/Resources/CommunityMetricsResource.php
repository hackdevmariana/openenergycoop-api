<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityMetricsResource\Pages;
use App\Models\CommunityMetrics;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Notifications\Notification;

class CommunityMetricsResource extends Resource
{
    protected static ?string $model = CommunityMetrics::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Métricas y Reportes';

    protected static ?string $navigationLabel = 'Métricas Comunitarias';

    protected static ?string $modelLabel = 'Métrica Comunitaria';

    protected static ?string $pluralModelLabel = 'Métricas Comunitarias';

    protected static ?string $slug = 'community-metrics';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Organización')
                    ->description('Datos de la organización para las métricas comunitarias')
                    ->schema([
                        Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Seleccionar la organización para estas métricas'),
                    ]),

                Section::make('Métricas de Usuarios')
                    ->description('Cantidad de usuarios activos en la organización')
                    ->schema([
                        TextInput::make('total_users')
                            ->label('Total de Usuarios')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->suffix('usuarios')
                            ->helperText('Número total de usuarios activos en la organización'),
                    ]),

                Section::make('Métricas de Producción')
                    ->description('Valores de energía producida y CO2 evitado por la comunidad')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_kwh_produced')
                                    ->label('Total kWh Producidos')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->minValue(0)
                                    ->required()
                                    ->suffix('kWh')
                                    ->helperText('Cantidad total de energía producida por la comunidad'),
                                
                                TextInput::make('total_co2_avoided')
                                    ->label('Total CO2 Evitado')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->minValue(0)
                                    ->required()
                                    ->suffix('kg CO₂')
                                    ->helperText('Cantidad total de CO2 evitado por la comunidad'),
                            ]),
                    ]),

                Section::make('Información del Sistema')
                    ->description('Datos automáticos del sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Creado')
                                    ->content(fn (CommunityMetrics $record): string => $record->created_at?->diffForHumans() ?? 'N/A'),
                                
                                Placeholder::make('updated_at')
                                    ->label('Actualizado')
                                    ->content(fn (CommunityMetrics $record): string => $record->updated_at?->diffForHumans() ?? 'N/A'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Actions::make([
                    FormAction::make('calculate_metrics')
                        ->label('Calcular Métricas Automáticamente')
                        ->icon('heroicon-o-calculator')
                        ->color('success')
                        ->action(function (CommunityMetrics $record, array $data) {
                            if (isset($data['total_users']) && isset($data['total_kwh_produced'])) {
                                // Calcular CO2 evitado basado en kWh producidos
                                $co2Factor = 0.5; // kg CO2 por kWh
                                $co2Avoided = $data['total_kwh_produced'] * $co2Factor;
                                
                                $record->total_co2_avoided = $co2Avoided;
                                $record->save();
                                
                                Notification::make()
                                    ->title('Métricas calculadas automáticamente')
                                    ->body("Se calculó: {$co2Avoided} kg CO₂")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (CommunityMetrics $record) => $record->exists && isset($record->total_users) && isset($record->total_kwh_produced)),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('organization.name')
                    ->label('Organización')
                    ->sortable()
                    ->searchable()
                    ->color('primary'),

                TextColumn::make('total_users')
                    ->label('Usuarios')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => number_format($state) . ' usuarios')
                    ->color('success'),

                TextColumn::make('total_kwh_produced')
                    ->label('kWh Producidos')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => number_format($state, 2) . ' kWh')
                    ->color('info'),

                TextColumn::make('total_co2_avoided')
                    ->label('CO2 Evitado')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => number_format($state, 2) . ' kg CO₂')
                    ->color('warning'),

                TextColumn::make('efficiency')
                    ->label('Eficiencia por Usuario')
                    ->formatStateUsing(fn (CommunityMetrics $record): string => $record->formatted_efficiency)
                    ->color('success')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('total_co2_avoided', $direction);
                    }),

                TextColumn::make('production_per_user')
                    ->label('Producción por Usuario')
                    ->formatStateUsing(fn (CommunityMetrics $record): string => $record->formatted_production_per_user)
                    ->color('info')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('total_kwh_produced', $direction);
                    }),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->getStateUsing(fn (CommunityMetrics $record): string => $record->is_active ? 'Activa' : 'Inactiva')
                    ->colors([
                        'success' => 'Activa',
                        'danger' => 'Inactiva',
                    ]),

                TextColumn::make('ranking')
                    ->label('Ranking')
                    ->formatStateUsing(fn (CommunityMetrics $record): string => $record->formatted_ranking)
                    ->color('warning'),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todas las organizaciones'),

                TernaryFilter::make('status')
                    ->label('Estado')
                    ->placeholder('Todas las organizaciones')
                    ->trueLabel('Activas')
                    ->falseLabel('Inactivas')
                    ->queries(
                        true: fn (Builder $query) => $query->where('total_users', '>', 0),
                        false: fn (Builder $query) => $query->where('total_users', 0),
                        blank: fn (Builder $query) => $query,
                    ),

                Filter::make('user_range')
                    ->label('Rango de Usuarios')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_users')
                                    ->label('Mínimo')
                                    ->numeric()
                                    ->placeholder('0'),
                                
                                TextInput::make('max_users')
                                    ->label('Máximo')
                                    ->placeholder('1000'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_users'],
                                fn (Builder $query, $minUsers): Builder => $query->where('total_users', '>=', $minUsers),
                            )
                            ->when(
                                $data['max_users'],
                                fn (Builder $query, $maxUsers): Builder => $query->where('total_users', '<=', $maxUsers),
                            );
                    }),

                Filter::make('co2_range')
                    ->label('Rango de CO2 (kg)')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_co2')
                                    ->label('Mínimo')
                                    ->numeric()
                                    ->placeholder('0'),
                                
                                TextInput::make('max_co2')
                                    ->label('Máximo')
                                    ->numeric()
                                    ->placeholder('10000'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_co2'],
                                fn (Builder $query, $minCo2): Builder => $query->where('total_co2_avoided', '>=', $minCo2),
                            )
                            ->when(
                                $data['max_co2'],
                                fn (Builder $query, $maxCo2): Builder => $query->where('total_co2_avoided', '<=', $maxCo2),
                            );
                    }),

                Filter::make('kwh_range')
                    ->label('Rango de kWh')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_kwh')
                                    ->label('Mínimo')
                                    ->numeric()
                                    ->placeholder('0'),
                                
                                TextInput::make('max_kwh')
                                    ->label('Máximo')
                                    ->numeric()
                                    ->placeholder('100000'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_kwh'],
                                fn (Builder $query, $minKwh): Builder => $query->where('total_kwh_produced', '>=', $minKwh),
                            )
                            ->when(
                                $data['max_kwh'],
                                fn (Builder $query, $maxKwh): Builder => $query->where('total_kwh_produced', '<=', $maxKwh),
                            );
                    }),

                Filter::make('date_range')
                    ->label('Rango de Fechas')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('start_date')
                                    ->label('Fecha Inicio')
                                    ->type('date'),
                                
                                TextInput::make('end_date')
                                    ->label('Fecha Fin')
                                    ->type('date'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $startDate): Builder => $query->where('updated_at', '>=', $startDate),
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $endDate): Builder => $query->where('updated_at', '<=', $endDate),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->color('info'),
                
                EditAction::make()
                    ->color('warning'),
                
                DeleteAction::make()
                    ->color('danger'),
                
                ForceDeleteAction::make()
                    ->color('danger')
                    ->visible(fn (CommunityMetrics $record): bool => $record->trashed()),
                
                RestoreAction::make()
                    ->color('success')
                    ->visible(fn (CommunityMetrics $record): bool => $record->trashed()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('total_co2_avoided', 'desc')
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
            'index' => Pages\ListCommunityMetrics::route('/'),
            'create' => Pages\CreateCommunityMetrics::route('/create'),
            'edit' => Pages\EditCommunityMetrics::route('/{record}/edit'),
            'view' => Pages\ViewCommunityMetrics::route('/{record}'),
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

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 0 ? 'success' : 'gray';
    }
}
