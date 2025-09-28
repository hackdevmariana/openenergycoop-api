<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergySourceResource\Pages;
use App\Filament\Resources\EnergySourceResource\RelationManagers;
use App\Models\EnergySource;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnergySourceResource extends Resource
{
    protected static ?string $model = EnergySource::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Fuentes de Energía';

    protected static ?string $modelLabel = 'Fuente de Energía';

    protected static ?string $pluralModelLabel = 'Fuentes de Energía';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre de la fuente de energía'),
                                TextInput::make('description')
                                    ->label('Descripción')
                                    ->maxLength(65535)
                                    ->helperText('Descripción detallada de la fuente de energía'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('energy_category')
                                    ->label('Categoría')
                                    ->options(EnergySource::getEnergyCategories())
                                    ->required()
                                    ->searchable(),
                                Select::make('source_type')
                                    ->label('Tipo')
                                    ->options(EnergySource::getSourceTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(EnergySource::getStatuses())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Capacidad y Producción')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('installed_capacity_mw')
                                    ->label('Capacidad Instalada (MW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' MW')
                                    ->helperText('Capacidad total instalada'),
                                TextInput::make('operational_capacity_mw')
                                    ->label('Capacidad Operativa (MW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' MW')
                                    ->helperText('Capacidad actualmente operativa'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('efficiency_rating')
                                    ->label('Eficiencia (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Rating de eficiencia'),
                                TextInput::make('availability_factor')
                                    ->label('Factor de Disponibilidad (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de tiempo disponible'),
                                TextInput::make('capacity_factor')
                                    ->label('Factor de Capacidad (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de capacidad utilizada'),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextInput::make('annual_production_mwh')
                                    ->label('Producción Anual (MWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' MWh'),
                                TextInput::make('monthly_production_mwh')
                                    ->label('Producción Mensual (MWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' MWh'),
                                TextInput::make('daily_production_mwh')
                                    ->label('Producción Diaria (MWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' MWh'),
                                TextInput::make('hourly_production_mwh')
                                    ->label('Producción Horaria (MWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' MWh'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Ubicación')
                    ->schema([
                        Textarea::make('location_address')
                            ->label('Dirección de Ubicación')
                            ->rows(2)
                            ->maxLength(65535)
                            ->helperText('Dirección física de la fuente'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->helperText('Coordenada de latitud'),
                                TextInput::make('longitude')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->helperText('Coordenada de longitud'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('region')
                                    ->label('Región')
                                    ->maxLength(255)
                                    ->helperText('Región o provincia'),
                                TextInput::make('country')
                                    ->label('País')
                                    ->maxLength(255)
                                    ->helperText('País donde se ubica'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Vida Útil')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('commissioning_date')
                                    ->label('Fecha de Puesta en Marcha')
                                    ->helperText('Fecha cuando comenzó a operar'),
                                DatePicker::make('decommissioning_date')
                                    ->label('Fecha de Desmantelamiento')
                                    ->helperText('Fecha planificada de desmantelamiento'),
                            ]),
                        TextInput::make('expected_lifespan_years')
                            ->label('Vida Útil Esperada (años)')
                            ->numeric()
                            ->minValue(1)
                            ->step(1)
                            ->suffix(' años')
                            ->helperText('Años esperados de operación'),
                    ])
                    ->collapsible(),

                Section::make('Costos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('construction_cost')
                                    ->label('Costo de Construcción ($)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Costo total de construcción'),
                                TextInput::make('operational_cost_per_mwh')
                                    ->label('Costo Operativo ($/MWh)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix('/MWh')
                                    ->helperText('Costo operativo por MWh'),
                                TextInput::make('maintenance_cost_per_mwh')
                                    ->label('Costo de Mantenimiento ($/MWh)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix('/MWh')
                                    ->helperText('Costo de mantenimiento por MWh'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Especificaciones Técnicas')
                    ->schema([
                        RichEditor::make('technical_specifications')
                            ->label('Especificaciones Técnicas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Especificaciones técnicas detalladas'),
                        RichEditor::make('equipment_details')
                            ->label('Detalles del Equipamiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Detalles del equipamiento instalado'),
                    ])
                    ->collapsible(),

                Section::make('Impacto Ambiental')
                    ->schema([
                        RichEditor::make('environmental_impact')
                            ->label('Impacto Ambiental')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Descripción del impacto ambiental'),
                        RichEditor::make('environmental_data')
                            ->label('Datos Ambientales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Datos específicos sobre el impacto ambiental'),
                    ])
                    ->collapsible(),

                Section::make('Cumplimiento y Seguridad')
                    ->schema([
                        RichEditor::make('regulatory_compliance')
                            ->label('Cumplimiento Regulatorio')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Información sobre cumplimiento regulatorio'),
                        RichEditor::make('safety_features')
                            ->label('Características de Seguridad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Características de seguridad implementadas'),
                    ])
                    ->collapsible(),

                Section::make('Mantenimiento y Rendimiento')
                    ->schema([
                        RichEditor::make('maintenance_schedule')
                            ->label('Programa de Mantenimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Programa de mantenimiento planificado'),
                        RichEditor::make('performance_metrics')
                            ->label('Métricas de Rendimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Métricas clave de rendimiento'),
                    ])
                    ->collapsible(),

                Section::make('Documentos')
                    ->schema([
                        RichEditor::make('regulatory_documents')
                            ->label('Documentos Regulatorios')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Documentos regulatorios relacionados'),
                    ])
                    ->collapsible(),

                Section::make('Gestión y Aprobación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('managed_by')
                                    ->label('Gestionado por')
                                    ->numeric()
                                    ->helperText('ID del usuario que gestiona'),
                                TextInput::make('created_by')
                                    ->label('Creado por')
                                    ->numeric()
                                    ->required()
                                    ->helperText('ID del usuario que creó'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('approved_by')
                                    ->label('Aprobado por')
                                    ->numeric()
                                    ->helperText('ID del usuario que aprobó'),
                                DatePicker::make('approved_at')
                                    ->label('Fecha de Aprobación')
                                    ->helperText('Fecha cuando fue aprobado'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                BadgeColumn::make('energy_category')
                    ->label('Categoría')
                    ->colors([
                        'success' => 'renewable',
                        'danger' => 'non_renewable',
                        'info' => 'hybrid',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergySource::getEnergyCategories()[$state] ?? $state),
                BadgeColumn::make('source_type')
                    ->label('Tipo')
                    ->colors([
                        'warning' => 'solar',
                        'info' => 'wind',
                        'primary' => 'hydroelectric',
                        'success' => 'biomass',
                        'danger' => 'geothermal',
                        'secondary' => 'nuclear',
                        'gray' => 'fossil_fuel',
                        'purple' => 'hybrid',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergySource::getSourceTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'maintenance',
                        'danger' => 'inactive',
                        'info' => 'planned',
                        'secondary' => 'under_construction',
                        'gray' => 'decommissioned',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergySource::getStatuses()[$state] ?? $state),
                TextColumn::make('installed_capacity_mw')
                    ->label('Capacidad Instalada (MW)')
                    ->suffix(' MW')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('operational_capacity_mw')
                    ->label('Capacidad Operativa (MW)')
                    ->suffix(' MW')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('efficiency_rating')
                    ->label('Eficiencia (%)')
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'primary',
                        $state >= 40 => 'warning',
                        $state >= 20 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('annual_production_mwh')
                    ->label('Producción Anual (MWh)')
                    ->suffix(' MWh')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000000 => 'success',
                        $state >= 100000 => 'primary',
                        $state >= 10000 => 'warning',
                        $state >= 1000 => 'info',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('region')
                    ->label('Región')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country')
                    ->label('País')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commissioning_date')
                    ->label('Puesta en Marcha')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expected_lifespan_years')
                    ->label('Vida Útil (años)')
                    ->suffix(' años')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('energy_category')
                    ->label('Categoría')
                    ->options(EnergySource::getEnergyCategories())
                    ->multiple(),
                SelectFilter::make('source_type')
                    ->label('Tipo')
                    ->options(EnergySource::getSourceTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergySource::getStatuses())
                    ->multiple(),
                Filter::make('renewable')
                    ->query(fn (Builder $query): Builder => $query->where('energy_category', 'renewable'))
                    ->label('Solo Renovables'),
                Filter::make('high_efficiency')
                    ->query(fn (Builder $query): Builder => $query->where('efficiency_rating', '>=', 80))
                    ->label('Alta Eficiencia (≥80%)'),
                Filter::make('low_efficiency')
                    ->query(fn (Builder $query): Builder => $query->where('efficiency_rating', '<', 40))
                    ->label('Baja Eficiencia (<40%)'),
                Filter::make('high_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('installed_capacity_mw', '>=', 100))
                    ->label('Alta Capacidad (≥100 MW)'),
                Filter::make('low_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('installed_capacity_mw', '<', 10))
                    ->label('Baja Capacidad (<10 MW)'),
                Filter::make('efficiency_range')
                    ->form([
                        TextInput::make('min_efficiency')
                            ->label('Eficiencia mínima (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('max_efficiency')
                            ->label('Eficiencia máxima (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_efficiency'],
                                fn (Builder $query, $efficiency): Builder => $query->where('efficiency_rating', '>=', $efficiency),
                            )
                            ->when(
                                $data['max_efficiency'],
                                fn (Builder $query, $efficiency): Builder => $query->where('efficiency_rating', '<=', $efficiency),
                            );
                    })
                    ->label('Rango de Eficiencia'),
                Filter::make('capacity_range')
                    ->form([
                        TextInput::make('min_capacity')
                            ->label('Capacidad mínima (MW)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_capacity')
                            ->label('Capacidad máxima (MW)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('installed_capacity_mw', '>=', $capacity),
                            )
                            ->when(
                                $data['max_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('installed_capacity_mw', '<=', $capacity),
                            );
                    })
                    ->label('Rango de Capacidad'),
                Filter::make('cost_range')
                    ->form([
                        TextInput::make('min_cost')
                            ->label('Costo mínimo ($)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_cost')
                            ->label('Costo máximo ($)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_cost'],
                                fn (Builder $query, $cost): Builder => $query->where('construction_cost', '>=', $cost),
                            )
                            ->when(
                                $data['max_cost'],
                                fn (Builder $query, $cost): Builder => $query->where('construction_cost', '<=', $cost),
                            );
                    })
                    ->label('Rango de Costos'),
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
                    ->visible(fn (EnergySource $record) => $record->status !== 'active')
                    ->action(function (EnergySource $record) {
                        $record->update(['status' => 'active']);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (EnergySource $record) => $record->status === 'active')
                    ->action(function (EnergySource $record) {
                        $record->update(['status' => 'inactive']);
                    }),
                Tables\Actions\Action::make('maintenance')
                    ->label('Mantenimiento')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->visible(fn (EnergySource $record) => $record->status !== 'maintenance')
                    ->action(function (EnergySource $record) {
                        $record->update(['status' => 'maintenance']);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergySource $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->status = 'planned';
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate_all')
                        ->label('Activar Todas')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'active']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate_all')
                        ->label('Desactivar Todas')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'inactive']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('maintenance_all')
                        ->label('Mantenimiento Todas')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'maintenance']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(EnergySource::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_category')
                        ->label('Actualizar Categoría')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('energy_category')
                                ->label('Categoría')
                                ->options(EnergySource::getEnergyCategories())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['energy_category' => $data['energy_category']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('name', 'asc')
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
            'index' => Pages\ListEnergySources::route('/'),
            'create' => Pages\CreateEnergySource::route('/create'),
            'edit' => Pages\EditEnergySource::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $inactiveCount = static::getModel()::where('status', 'inactive')->count();
        
        if ($inactiveCount > 0) {
            return 'warning';
        }
        
        $maintenanceCount = static::getModel()::where('status', 'maintenance')->count();
        
        if ($maintenanceCount > 0) {
            return 'info';
        }
        
        return 'success';
    }
}
