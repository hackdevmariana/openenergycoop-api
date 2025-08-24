<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImpactMetricsResource\Pages;
use App\Models\ImpactMetrics;
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
use Filament\Forms\Components\DateTimePicker;
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

class ImpactMetricsResource extends Resource
{
    protected static ?string $model = ImpactMetrics::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Métricas y Reportes';

    protected static ?string $navigationLabel = 'Métricas de Impacto';

    protected static ?string $modelLabel = 'Métrica de Impacto';

    protected static ?string $pluralModelLabel = 'Métricas de Impacto';

    protected static ?string $slug = 'impact-metrics';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->description('Datos principales de la métrica de impacto')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Seleccionar usuario (opcional para métricas globales)')
                                    ->helperText('Dejar vacío para métricas globales del sistema'),
                                
                                Select::make('plant_group_id')
                                    ->label('Grupo de Plantas')
                                    ->relationship('plantGroup', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Seleccionar grupo de plantas (opcional)')
                                    ->helperText('Asociar con un grupo específico de plantas'),
                            ]),
                    ]),

                Section::make('Métricas de Producción')
                    ->description('Valores de energía producida y CO2 evitado')
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
                                    ->helperText('Cantidad total de energía producida'),
                                
                                TextInput::make('total_co2_avoided_kg')
                                    ->label('Total CO2 Evitado')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->minValue(0)
                                    ->required()
                                    ->suffix('kg CO₂')
                                    ->helperText('Cantidad total de CO2 evitado'),
                            ]),
                    ]),

                Section::make('Información Temporal')
                    ->description('Cuándo se generaron estas métricas')
                    ->schema([
                        DateTimePicker::make('generated_at')
                            ->label('Fecha de Generación')
                            ->required()
                            ->default(now())
                            ->helperText('Cuándo se calcularon estas métricas'),
                    ]),

                Section::make('Información del Sistema')
                    ->description('Datos automáticos del sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Creado')
                                    ->content(fn (ImpactMetrics $record): string => $record->created_at?->diffForHumans() ?? 'N/A'),
                                
                                Placeholder::make('updated_at')
                                    ->label('Actualizado')
                                    ->content(fn (ImpactMetrics $record): string => $record->updated_at?->diffForHumans() ?? 'N/A'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Actions::make([
                    FormAction::make('calculate_co2')
                        ->label('Calcular CO2 Automáticamente')
                        ->icon('heroicon-o-calculator')
                        ->color('success')
                        ->action(function (ImpactMetrics $record, array $data) {
                            if (isset($data['total_kwh_produced'])) {
                                $co2Avoided = $record->calculateCo2Avoided($data['total_kwh_produced']);
                                $record->total_co2_avoided_kg = $co2Avoided;
                                $record->save();
                                
                                Notification::make()
                                    ->title('CO2 calculado automáticamente')
                                    ->body("Se calculó: {$co2Avoided} kg CO₂")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (ImpactMetrics $record) => $record->exists && isset($record->total_kwh_produced)),
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

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Métricas Globales')
                    ->color('gray'),

                TextColumn::make('plantGroup.name')
                    ->label('Grupo de Plantas')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Sin grupo')
                    ->color('gray'),

                TextColumn::make('total_kwh_produced')
                    ->label('kWh Producidos')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => number_format($state, 2) . ' kWh')
                    ->color('success'),

                TextColumn::make('total_co2_avoided_kg')
                    ->label('CO2 Evitado')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => number_format($state, 2) . ' kg CO₂')
                    ->color('warning'),

                TextColumn::make('efficiency')
                    ->label('Eficiencia')
                    ->formatStateUsing(fn (ImpactMetrics $record): string => $record->formatted_efficiency)
                    ->color('info')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('total_co2_avoided_kg', $direction);
                    }),

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->getStateUsing(fn (ImpactMetrics $record): string => $record->is_individual ? 'Individual' : 'Global')
                    ->colors([
                        'success' => 'Individual',
                        'info' => 'Global',
                    ]),

                TextColumn::make('generated_at')
                    ->label('Generado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos los usuarios'),

                SelectFilter::make('plant_group_id')
                    ->label('Grupo de Plantas')
                    ->relationship('plantGroup', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos los grupos'),

                TernaryFilter::make('type')
                    ->label('Tipo de Métrica')
                    ->placeholder('Todas las métricas')
                    ->trueLabel('Individuales')
                    ->falseLabel('Globales')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('user_id'),
                        false: fn (Builder $query) => $query->whereNull('user_id'),
                        blank: fn (Builder $query) => $query,
                    ),

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
                                    ->placeholder('1000'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_co2'],
                                fn (Builder $query, $minCo2): Builder => $query->where('total_co2_avoided_kg', '>=', $minCo2),
                            )
                            ->when(
                                $data['max_co2'],
                                fn (Builder $query, $maxCo2): Builder => $query->where('total_co2_avoided_kg', '<=', $maxCo2),
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
                                    ->placeholder('10000'),
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
                                DateTimePicker::make('start_date')
                                    ->label('Fecha Inicio'),
                                
                                DateTimePicker::make('end_date')
                                    ->label('Fecha Fin'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $startDate): Builder => $query->where('generated_at', '>=', $startDate),
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $endDate): Builder => $query->where('generated_at', '<=', $endDate),
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
                    ->visible(fn (ImpactMetrics $record): bool => $record->trashed()),
                
                RestoreAction::make()
                    ->color('success')
                    ->visible(fn (ImpactMetrics $record): bool => $record->trashed()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('generated_at', 'desc')
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
            'index' => Pages\ListImpactMetrics::route('/'),
            'create' => Pages\CreateImpactMetrics::route('/create'),
            'edit' => Pages\EditImpactMetrics::route('/{record}/edit'),
            'view' => Pages\ViewImpactMetrics::route('/{record}'),
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
