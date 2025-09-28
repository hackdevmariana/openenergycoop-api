<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyPoolResource\Pages;
use App\Filament\Resources\EnergyPoolResource\RelationManagers;
use App\Models\EnergyPool;
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


class EnergyPoolResource extends Resource
{
    protected static ?string $model = EnergyPool::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pools de Energía';

    protected static ?string $modelLabel = 'Pool de Energía';

    protected static ?string $pluralModelLabel = 'Pools de Energía';

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
                                    ->helperText('Nombre del pool de energía'),
                                Select::make('pool_type')
                                    ->label('Tipo de Pool')
                                    ->options(EnergyPool::getPoolTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción del pool de energía'),
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(EnergyPool::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('energy_source_type')
                                    ->label('Tipo de Fuente')
                                    ->options([
                                        'solar' => 'Solar',
                                        'wind' => 'Eólica',
                                        'hydro' => 'Hidroeléctrica',
                                        'biomass' => 'Biomasa',
                                        'geothermal' => 'Geotérmica',
                                        'nuclear' => 'Nuclear',
                                        'mixed' => 'Mixta',
                                        'other' => 'Otra',
                                    ])
                                    ->searchable(),
                                TextInput::make('geographical_zone')
                                    ->label('Zona Geográfica')
                                    ->maxLength(255)
                                    ->helperText('Zona geográfica del pool'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('organization_id')
                                    ->label('Organización')
                                    ->relationship('organization', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Organización responsable'),
                                Select::make('created_by_user_id')
                                    ->label('Creado por')
                                    ->relationship('createdByUser', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Capacidad y Utilización')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_capacity_mw')
                                    ->label('Capacidad Compartida (MW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->required()
                                    ->suffix(' MW')
                                    ->helperText('Capacidad total del pool'),
                                TextInput::make('utilized_capacity_mw')
                                    ->label('Utilización Actual (MW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->default(0)
                                    ->required()
                                    ->suffix(' MW')
                                    ->helperText('Capacidad actualmente utilizada'),
                                TextInput::make('available_capacity_mw')
                                    ->label('Capacidad Disponible (MW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' MW')
                                    ->helperText('Capacidad disponible para uso'),
                            ]),
                        Placeholder::make('utilization_percentage')
                            ->content(function ($get) {
                                $shared = $get('total_capacity_mw') ?: 0;
                                $current = $get('utilized_capacity_mw') ?: 0;
                                
                                if ($shared > 0) {
                                    $percentage = ($current / $shared) * 100;
                                    return "**Porcentaje de Utilización: {$percentage}%**";
                                }
                                
                                return "**Porcentaje de Utilización: 0%**";
                            })
                            ->visible(fn ($get) => $get('total_capacity_mw') > 0),
                        Grid::make(2)
                            ->schema([
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Algoritmo de Distribución')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('distribution_algorithm')
                                    ->label('Algoritmo de Distribución')
                                    ->options(EnergyPool::getDistributionAlgorithms())
                                    ->required()
                                    ->searchable(),
                                TextInput::make('distribution_efficiency')
                                    ->label('Eficiencia de Distribución (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia del algoritmo de distribución'),
                            ]),
                        RichEditor::make('distribution_rules')
                            ->label('Reglas de Distribución')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Reglas específicas del algoritmo de distribución'),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('auto_balancing')
                                    ->label('Balanceo Automático')
                                    ->default(true)
                                    ->required()
                                    ->helperText('¿El pool se balancea automáticamente?'),
                                Toggle::make('load_forecasting')
                                    ->label('Pronóstico de Carga')
                                    ->helperText('¿Incluye pronóstico de carga?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->required()
                                    ->helperText('Cuándo comienza el pool'),
                                DatePicker::make('end_date')
                                    ->label('Fecha de Finalización')
                                    ->helperText('Cuándo termina el pool'),
                                DatePicker::make('operational_start')
                                    ->label('Inicio Operacional')
                                    ->helperText('Cuándo comienza a operar'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('last_maintenance')
                                    ->label('Último Mantenimiento')
                                    ->helperText('Cuándo se realizó el último mantenimiento'),
                                DatePicker::make('next_maintenance')
                                    ->label('Próximo Mantenimiento')
                                    ->helperText('Cuándo se realizará el próximo mantenimiento'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Participación y Financiamiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('min_participation_amount')
                                    ->label('Participación Mínima (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Monto mínimo para participar'),
                                TextInput::make('max_participation_amount')
                                    ->label('Participación Máxima (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Monto máximo para participar'),
                                TextInput::make('participation_fee')
                                    ->label('Tarifa de Participación (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Tarifa por participar en el pool'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('monthly_fee')
                                    ->label('Tarifa Mensual (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Tarifa mensual de mantenimiento'),
                                TextInput::make('transaction_fee')
                                    ->label('Tarifa por Transacción (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Tarifa por cada transacción'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Impacto Ambiental')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('renewable_percentage')
                                    ->label('Porcentaje Renovable (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de energía renovable'),
                                TextInput::make('carbon_footprint')
                                    ->label('Huella de Carbono (kg CO2/MWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kg CO2/MWh')
                                    ->helperText('Emisiones de CO2 por MWh'),
                                TextInput::make('environmental_score')
                                    ->label('Puntuación Ambiental')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->helperText('Puntuación ambiental del pool'),
                            ]),
                        RichEditor::make('environmental_certifications')
                            ->label('Certificaciones Ambientales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Certificaciones ambientales obtenidas'),
                    ])
                    ->collapsible(),

                Section::make('Reglas y Estándares')
                    ->schema([
                        RichEditor::make('pooling_rules')
                            ->label('Reglas del Pool')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Reglas específicas del pool'),
                        RichEditor::make('quality_standards')
                            ->label('Estándares de Calidad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Estándares de calidad de la energía'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('quality_threshold')
                                    ->label('Umbral de Calidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Umbral mínimo de calidad'),
                                TextInput::make('compliance_rate')
                                    ->label('Tasa de Cumplimiento (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Tasa de cumplimiento de estándares'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Monitoreo y Control')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('monitoring_enabled')
                                    ->label('Monitoreo Habilitado')
                                    ->default(true)
                                    ->required()
                                    ->helperText('¿El pool tiene monitoreo activo?'),
                                Toggle::make('real_time_tracking')
                                    ->label('Seguimiento en Tiempo Real')
                                    ->helperText('¿Incluye seguimiento en tiempo real?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('alert_system')
                                    ->label('Sistema de Alertas')
                                    ->helperText('¿Tiene sistema de alertas?'),
                                Toggle::make('performance_metrics')
                                    ->label('Métricas de Rendimiento')
                                    ->helperText('¿Incluye métricas de rendimiento?'),
                            ]),
                        RichEditor::make('monitoring_config')
                            ->label('Configuración de Monitoreo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Configuración específica del sistema de monitoreo'),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->helperText('¿El pool está activo?'),
                                Toggle::make('is_public')
                                    ->label('Público')
                                    ->helperText('¿El pool es visible públicamente?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para cambios?'),
                                Toggle::make('is_featured')
                                    ->label('Destacado')
                                    ->helperText('¿Mostrar como pool destacado?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('priority')
                                    ->label('Prioridad')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->step(1)
                                    ->default(5)
                                    ->helperText('Prioridad del pool (1-10)'),
                                TextInput::make('sort_order')
                                    ->label('Orden de Clasificación')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Orden para mostrar en listas'),
                            ]),
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

                Section::make('Documentos y Archivos')
                    ->schema([
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('energy-pools')
                            ->maxFiles(50)
                            ->maxSize(10240)
                            ->helperText('Máximo 50 documentos de 10MB cada uno'),
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
                TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                BadgeColumn::make('pool_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'community',
                        'success' => 'commercial',
                        'warning' => 'industrial',
                        'danger' => 'residential',
                        'info' => 'municipal',
                        'secondary' => 'cooperative',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyPool::getPoolTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'maintenance',
                        'danger' => 'inactive',
                        'info' => 'planning',
                        'secondary' => 'suspended',
                        'gray' => 'decommissioned',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyPool::getStatuses()[$state] ?? $state),
                TextColumn::make('total_capacity_mw')
                    ->label('Capacidad (MW)')
                    ->suffix(' MW')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 50 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('utilized_capacity_mw')
                    ->label('Utilización (MW)')
                    ->suffix(' MW')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' MW'),
                    ]),
                TextColumn::make('utilization_percentage')
                    ->label('Utilización (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 90 => 'danger',
                        $state >= 75 => 'warning',
                        $state >= 50 => 'primary',
                        $state >= 25 => 'info',
                        default => 'success',
                    })
                    ->getStateUsing(function ($record) {
                        if ($record->total_capacity_mw > 0) {
                            return ($record->utilized_capacity_mw / $record->total_capacity_mw) * 100;
                        }
                        return 0;
                    }),
                BadgeColumn::make('distribution_algorithm')
                    ->label('Algoritmo')
                    ->colors([
                        'primary' => 'round_robin',
                        'success' => 'load_balanced',
                        'warning' => 'priority_based',
                        'danger' => 'cost_optimized',
                        'info' => 'demand_driven',
                        'secondary' => 'fair_share',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyPool::getDistributionAlgorithms()[$state] ?? $state),
                TextColumn::make('renewable_percentage')
                    ->label('Renovable (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'primary',
                        $state >= 50 => 'warning',
                        $state >= 30 => 'info',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('carbon_footprint')
                    ->label('CO2 (kg/MWh)')
                    ->suffix(' kg/MWh')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 50 => 'success',
                        $state <= 100 => 'primary',
                        $state <= 200 => 'warning',
                        $state <= 500 => 'info',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('min_participation_amount')
                    ->label('Min. Participación (€)')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('monitoring_enabled')
                    ->label('Monitoreo'),
                ToggleColumn::make('auto_balancing')
                    ->label('Auto Balanceo'),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pool_type')
                    ->label('Tipo de Pool')
                    ->options(EnergyPool::getPoolTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergyPool::getStatuses())
                    ->multiple(),
                SelectFilter::make('distribution_algorithm')
                    ->label('Algoritmo de Distribución')
                    ->options(EnergyPool::getDistributionAlgorithms())
                    ->multiple(),
                SelectFilter::make('energy_source_type')
                    ->label('Tipo de Fuente')
                    ->options([
                        'solar' => 'Solar',
                        'wind' => 'Eólica',
                        'hydro' => 'Hidroeléctrica',
                        'biomass' => 'Biomasa',
                        'geothermal' => 'Geotérmica',
                        'nuclear' => 'Nuclear',
                        'mixed' => 'Mixta',
                        'other' => 'Otra',
                    ])
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('public')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true))
                    ->label('Solo Públicos'),
                Filter::make('monitoring_enabled')
                    ->query(fn (Builder $query): Builder => $query->where('monitoring_enabled', true))
                    ->label('Con Monitoreo'),
                Filter::make('auto_balancing')
                    ->query(fn (Builder $query): Builder => $query->where('auto_balancing', true))
                    ->label('Con Auto Balanceo'),
                Filter::make('high_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('total_capacity_mw', '>=', 500))
                    ->label('Alta Capacidad (≥500 MW)'),
                Filter::make('low_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('total_capacity_mw', '<', 100))
                    ->label('Baja Capacidad (<100 MW)'),
                Filter::make('high_utilization')
                    ->query(fn (Builder $query): Builder => $query->where('utilized_capacity_mw', '>=', 80))
                    ->label('Alta Utilización (≥80%)'),
                Filter::make('low_utilization')
                    ->query(fn (Builder $query): Builder => $query->where('utilized_capacity_mw', '<', 20))
                    ->label('Baja Utilización (<20%)'),
                Filter::make('high_renewable')
                    ->query(fn (Builder $query): Builder => $query->where('renewable_percentage', '>=', 80))
                    ->label('Alto Renovable (≥80%)'),
                Filter::make('low_renewable')
                    ->query(fn (Builder $query): Builder => $query->where('renewable_percentage', '<', 30))
                    ->label('Bajo Renovable (<30%)'),
                Filter::make('low_carbon')
                    ->query(fn (Builder $query): Builder => $query->where('carbon_footprint', '<=', 100))
                    ->label('Baja Huella de Carbono (≤100 kg/MWh)'),
                Filter::make('high_carbon')
                    ->query(fn (Builder $query): Builder => $query->where('carbon_footprint', '>', 500))
                    ->label('Alta Huella de Carbono (>500 kg/MWh)'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_from')
                            ->label('Inicio desde'),
                        DatePicker::make('start_until')
                            ->label('Inicio hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Inicio'),
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
                                fn (Builder $query, $capacity): Builder => $query->where('total_capacity_mw', '>=', $capacity),
                            )
                            ->when(
                                $data['max_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('total_capacity_mw', '<=', $capacity),
                            );
                    })
                    ->label('Rango de Capacidad'),
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
                    ->visible(fn (EnergyPool $record) => !$record->is_active)
                    ->action(function (EnergyPool $record) {
                        $record->update(['is_active' => true]);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (EnergyPool $record) => $record->is_active)
                    ->action(function (EnergyPool $record) {
                        $record->update(['is_active' => false]);
                    }),
                Tables\Actions\Action::make('enable_monitoring')
                    ->label('Habilitar Monitoreo')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (EnergyPool $record) => !$record->monitoring_enabled)
                    ->action(function (EnergyPool $record) {
                        $record->update(['monitoring_enabled' => true]);
                    }),
                Tables\Actions\Action::make('enable_auto_balancing')
                    ->label('Habilitar Auto Balanceo')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (EnergyPool $record) => !$record->auto_balancing)
                    ->action(function (EnergyPool $record) {
                        $record->update(['auto_balancing' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyPool $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->is_active = false;
                        $newRecord->utilized_capacity_mw = 0;
                        $newRecord->start_date = now();
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
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'inactive']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('enable_monitoring_all')
                        ->label('Habilitar Monitoreo')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['monitoring_enabled' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('enable_auto_balancing_all')
                        ->label('Habilitar Auto Balanceo')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['auto_balancing' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(EnergyPool::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_pool_type')
                        ->label('Actualizar Tipo de Pool')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('pool_type')
                                ->label('Tipo de Pool')
                                ->options(EnergyPool::getPoolTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['pool_type' => $data['pool_type']]);
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
            'index' => Pages\ListEnergyPools::route('/'),
            'create' => Pages\CreateEnergyPool::route('/create'),
            'edit' => Pages\EditEnergyPool::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
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
        
        $highUtilizationCount = static::getModel()::whereRaw('(utilized_capacity_mw / total_capacity_mw) >= 0.9')->count();
        
        if ($highUtilizationCount > 0) {
            return 'danger';
        }
        
        return 'success';
    }
}
