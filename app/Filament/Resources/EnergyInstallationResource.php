<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyInstallationResource\Pages;
use App\Filament\Resources\EnergyInstallationResource\RelationManagers;
use App\Models\EnergyInstallation;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnergyInstallationResource extends Resource
{
    protected static ?string $model = EnergyInstallation::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Instalaciones de Energía';

    protected static ?string $modelLabel = 'Instalación de Energía';

    protected static ?string $pluralModelLabel = 'Instalaciones de Energía';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('installation_number')
                                    ->label('Número de Instalación')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Identificador único de la instalación'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre descriptivo de la instalación'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada de la instalación'),
                        Grid::make(3)
                            ->schema([
                                Select::make('installation_type')
                                    ->label('Tipo de Instalación')
                                    ->options(EnergyInstallation::getInstallationTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(EnergyInstallation::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(EnergyInstallation::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Ubicación y Coordenadas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location_address')
                                    ->label('Dirección de Ubicación')
                                    ->maxLength(255)
                                    ->helperText('Dirección física de la instalación'),
                                TextInput::make('latitude')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->helperText('Coordenada de latitud (-90 a 90)'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('longitude')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->helperText('Coordenada de longitud (-180 a 180)'),
                                TextInput::make('altitude_meters')
                                    ->label('Altitud (metros)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->helperText('Altitud sobre el nivel del mar'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('orientation_degrees')
                                    ->label('Orientación (grados)')
                                    ->numeric()
                                    ->step(1)
                                    ->minValue(0)
                                    ->maxValue(360)
                                    ->helperText('Orientación en grados (0-360)'),
                                TextInput::make('tilt_degrees')
                                    ->label('Inclinación (grados)')
                                    ->numeric()
                                    ->step(1)
                                    ->minValue(0)
                                    ->maxValue(90)
                                    ->helperText('Inclinación en grados (0-90)'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Capacidad y Rendimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('installed_capacity_kw')
                                    ->label('Capacidad Instalada (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Capacidad nominal instalada'),
                                TextInput::make('operational_capacity_kw')
                                    ->label('Capacidad Operativa (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Capacidad actual operativa'),
                                TextInput::make('efficiency_rating')
                                    ->label('Eficiencia (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('%')
                                    ->helperText('Eficiencia de la instalación'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('annual_production_kwh')
                                    ->label('Producción Anual (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                                TextInput::make('monthly_production_kwh')
                                    ->label('Producción Mensual (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                                TextInput::make('daily_production_kwh')
                                    ->label('Producción Diaria (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('hourly_production_kwh')
                                    ->label('Producción Horaria (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                                TextInput::make('peak_power_kw')
                                    ->label('Potencia Pico (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas Importantes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('installation_date')
                                    ->label('Fecha de Instalación')
                                    ->required()
                                    ->helperText('Cuándo se instaló'),
                                DatePicker::make('commissioning_date')
                                    ->label('Fecha de Puesta en Marcha')
                                    ->required()
                                    ->helperText('Cuándo se puso en operación'),
                                DatePicker::make('warranty_expiry_date')
                                    ->label('Vencimiento de Garantía')
                                    ->helperText('Cuándo vence la garantía'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('expected_lifespan_start')
                                    ->label('Inicio de Vida Útil Esperada'),
                                DatePicker::make('expected_lifespan_end')
                                    ->label('Fin de Vida Útil Esperada'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Costos y Financiamiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('installation_cost')
                                    ->label('Costo de Instalación')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Costo total de la instalación'),
                                TextInput::make('operational_cost_per_kwh')
                                    ->label('Costo Operativo por kWh')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.0001),
                                TextInput::make('maintenance_cost_per_kwh')
                                    ->label('Costo de Mantenimiento por kWh')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.0001),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('annual_operational_cost')
                                    ->label('Costo Operativo Anual')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01),
                                TextInput::make('annual_maintenance_cost')
                                    ->label('Costo de Mantenimiento Anual')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01),
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
                            ])
                            ->helperText('Especificaciones técnicas detalladas'),
                        RichEditor::make('warranty_terms')
                            ->label('Términos de Garantía')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('maintenance_requirements')
                            ->label('Requisitos de Mantenimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('safety_features')
                            ->label('Características de Seguridad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('equipment_details')
                            ->label('Detalles del Equipamiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Programación de Mantenimiento')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('maintenance_schedule')
                                    ->label('Programa de Mantenimiento')
                                    ->helperText('Descripción del programa de mantenimiento'),
                                TextInput::make('maintenance_interval_days')
                                    ->label('Intervalo de Mantenimiento (días)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('last_maintenance_date')
                                    ->label('Última Fecha de Mantenimiento'),
                                DatePicker::make('next_maintenance_date')
                                    ->label('Próxima Fecha de Mantenimiento'),
                            ]),
                        RichEditor::make('maintenance_notes')
                            ->label('Notas de Mantenimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Monitoreo y Control')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('has_monitoring')
                                    ->label('Tiene Sistema de Monitoreo')
                                    ->helperText('¿La instalación tiene sistema de monitoreo?'),
                                Toggle::make('is_automated')
                                    ->label('Es Automatizada')
                                    ->helperText('¿La instalación funciona de forma automática?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('monitoring_system')
                                    ->label('Sistema de Monitoreo')
                                    ->maxLength(255),
                                TextInput::make('monitoring_url')
                                    ->label('URL de Monitoreo')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                        RichEditor::make('monitoring_credentials')
                            ->label('Credenciales de Monitoreo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Información de acceso al sistema de monitoreo'),
                    ])
                    ->collapsible(),

                Section::make('Certificaciones y Documentos')
                    ->schema([
                        RichEditor::make('certifications')
                            ->label('Certificaciones')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('regulatory_compliance')
                            ->label('Cumplimiento Regulatorio')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        FileUpload::make('installation_photos')
                            ->label('Fotos de la Instalación')
                            ->multiple()
                            ->image()
                            ->directory('energy-installations')
                            ->maxFiles(20)
                            ->maxSize(5120)
                            ->helperText('Máximo 20 fotos de 5MB cada una'),
                        FileUpload::make('technical_documents')
                            ->label('Documentos Técnicos')
                            ->multiple()
                            ->directory('energy-installations/docs')
                            ->maxFiles(50)
                            ->maxSize(10240)
                            ->helperText('Máximo 50 documentos de 10MB cada uno'),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('energy_source_id')
                                    ->label('Fuente de Energía')
                                    ->relationship('energySource', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('project_id')
                                    ->label('Proyecto')
                                    ->relationship('project', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('installed_by')
                                    ->label('Instalado por')
                                    ->relationship('installedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('managed_by')
                                    ->label('Gestionado por')
                                    ->relationship('managedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Select::make('approved_by')
                            ->label('Aprobado por')
                            ->relationship('approvedBy', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->collapsible(),

                Section::make('Metadatos y Etiquetas')
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
                TextColumn::make('installation_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('installation_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'solar_panel',
                        'success' => 'wind_turbine',
                        'warning' => 'hydro_turbine',
                        'danger' => 'biomass_plant',
                        'info' => 'geothermal_plant',
                        'secondary' => 'battery_storage',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyInstallation::getInstallationTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'operational',
                        'warning' => 'maintenance',
                        'danger' => 'offline',
                        'info' => 'testing',
                        'secondary' => 'decommissioned',
                        'gray' => 'planned',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyInstallation::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyInstallation::getPriorities()[$state] ?? $state),
                TextColumn::make('energySource.name')
                    ->label('Fuente de Energía')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('installed_capacity_kw')
                    ->label('Capacidad (kW)')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' kW'),
                    ]),
                TextColumn::make('operational_capacity_kw')
                    ->label('Cap. Operativa (kW)')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' kW'),
                    ]),
                TextColumn::make('efficiency_rating')
                    ->label('Eficiencia (%)')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('annual_production_kwh')
                    ->label('Producción Anual (kWh)')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' kWh'),
                    ]),
                TextColumn::make('installation_date')
                    ->label('Instalación')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('commissioning_date')
                    ->label('Puesta en Marcha')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('installation_type')
                    ->label('Tipo de Instalación')
                    ->options(EnergyInstallation::getInstallationTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergyInstallation::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(EnergyInstallation::getPriorities())
                    ->multiple(),
                SelectFilter::make('energy_source_id')
                    ->label('Fuente de Energía')
                    ->relationship('energySource', 'name')
                    ->multiple(),
                Filter::make('operational')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'operational'))
                    ->label('Solo Operativas'),
                Filter::make('maintenance_needed')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'maintenance'))
                    ->label('Necesitan Mantenimiento'),
                Filter::make('offline')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'offline'))
                    ->label('Fuera de Línea'),
                Filter::make('high_efficiency')
                    ->query(fn (Builder $query): Builder => $query->where('efficiency_rating', '>=', 80))
                    ->label('Alta Eficiencia (≥80%)'),
                Filter::make('low_efficiency')
                    ->query(fn (Builder $query): Builder => $query->where('efficiency_rating', '<', 60))
                    ->label('Baja Eficiencia (<60%)'),
                Filter::make('capacity_range')
                    ->form([
                        TextInput::make('min_capacity')
                            ->label('Capacidad mínima (kW)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_capacity')
                            ->label('Capacidad máxima (kW)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('installed_capacity_kw', '>=', $capacity),
                            )
                            ->when(
                                $data['max_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('installed_capacity_kw', '<=', $capacity),
                            );
                    })
                    ->label('Rango de Capacidad'),
                Filter::make('installation_date_range')
                    ->form([
                        DatePicker::make('installation_from')
                            ->label('Instalación desde'),
                        DatePicker::make('installation_until')
                            ->label('Instalación hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['installation_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('installation_date', '>=', $date),
                            )
                            ->when(
                                $data['installation_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('installation_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Instalación'),
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
                    ->visible(fn (EnergyInstallation $record) => $record->status !== 'operational')
                    ->action(function (EnergyInstallation $record) {
                        $record->update(['status' => 'operational']);
                    }),
                Tables\Actions\Action::make('maintenance_mode')
                    ->label('Modo Mantenimiento')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->visible(fn (EnergyInstallation $record) => $record->status === 'operational')
                    ->action(function (EnergyInstallation $record) {
                        $record->update(['status' => 'maintenance']);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyInstallation $record) {
                        $newRecord = $record->replicate();
                        $newRecord->installation_number = $newRecord->installation_number . '_copy';
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->status = 'planned';
                        $newRecord->installation_date = null;
                        $newRecord->commissioning_date = null;
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
                                $record->update(['status' => 'operational']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('maintenance_mode_all')
                        ->label('Modo Mantenimiento')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'maintenance']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('assign_project')
                        ->label('Asignar Proyecto')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Select::make('project_id')
                                ->label('Proyecto')
                                ->options(\App\Models\ProductionProject::pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['project_id' => $data['project_id']]);
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
            'index' => Pages\ListEnergyInstallations::route('/'),
            'create' => Pages\CreateEnergyInstallation::route('/create'),
            'edit' => Pages\EditEnergyInstallation::route('/{record}/edit'),
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
        $offlineCount = static::getModel()::where('status', 'offline')->count();
        
        if ($offlineCount > 0) {
            return 'danger';
        }
        
        $maintenanceCount = static::getModel()::where('status', 'maintenance')->count();
        
        if ($maintenanceCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
