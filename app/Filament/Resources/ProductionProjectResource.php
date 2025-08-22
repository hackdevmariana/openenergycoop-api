<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionProjectResource\Pages;
use App\Filament\Resources\ProductionProjectResource\RelationManagers;
use App\Models\ProductionProject;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionProjectResource extends Resource
{
    protected static ?string $model = ProductionProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Producción de Energía';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Proyectos de Producción';

    protected static ?string $modelLabel = 'Proyecto de Producción';

    protected static ?string $pluralModelLabel = 'Proyectos de Producción';

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
                                    ->helperText('Nombre del proyecto'),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('URL amigable del proyecto'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada del proyecto'),
                        Grid::make(3)
                            ->schema([
                                Select::make('project_type')
                                    ->label('Tipo de Proyecto')
                                    ->options(ProductionProject::getProjectTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('technology_type')
                                    ->label('Tipo de Tecnología')
                                    ->options(ProductionProject::getTechnologyTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(ProductionProject::getStatuses())
                                    ->required()
                                    ->searchable(),
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
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Organización responsable'),
                                Select::make('owner_user_id')
                                    ->label('Propietario')
                                    ->relationship('ownerUser', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('energy_source_id')
                                    ->label('Fuente de Energía')
                                    ->relationship('energySource', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Capacidad y Rendimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('capacity_kw')
                                    ->label('Capacidad (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Capacidad instalada en kilovatios'),
                                TextInput::make('estimated_annual_production')
                                    ->label('Producción Anual Estimada (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('Producción anual estimada'),
                                TextInput::make('efficiency_rating')
                                    ->label('Eficiencia (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Factor de eficiencia'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('peak_power_kw')
                                    ->label('Potencia Pico (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Potencia máxima en condiciones óptimas'),
                                TextInput::make('capacity_factor')
                                    ->label('Factor de Capacidad (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Factor de utilización de la capacidad'),
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
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('manufacturer')
                                    ->label('Fabricante')
                                    ->maxLength(255)
                                    ->helperText('Fabricante principal del equipamiento'),
                                TextInput::make('model')
                                    ->label('Modelo')
                                    ->maxLength(255)
                                    ->helperText('Modelo del equipamiento'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Ubicación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location_address')
                                    ->label('Dirección')
                                    ->maxLength(255)
                                    ->helperText('Dirección física del proyecto'),
                                TextInput::make('location_city')
                                    ->label('Ciudad')
                                    ->maxLength(255)
                                    ->helperText('Ciudad donde se ubica'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('location_region')
                                    ->label('Región/Estado')
                                    ->maxLength(255)
                                    ->helperText('Región o estado'),
                                TextInput::make('location_country')
                                    ->label('País')
                                    ->maxLength(2)
                                    ->default('ES')
                                    ->required()
                                    ->helperText('Código de país (ISO 3166-1 alpha-2)'),
                                TextInput::make('location_postal_code')
                                    ->label('Código Postal')
                                    ->maxLength(20)
                                    ->helperText('Código postal'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->helperText('Coordenada de latitud'),
                                TextInput::make('longitude')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->helperText('Coordenada de longitud'),
                            ]),
                        RichEditor::make('location_metadata')
                            ->label('Metadatos de Ubicación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Información adicional sobre la ubicación'),
                    ])
                    ->collapsible(),

                Section::make('Cronograma del Proyecto')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('planning_start_date')
                                    ->label('Inicio de Planificación')
                                    ->helperText('Cuándo comenzó la planificación'),
                                DatePicker::make('construction_start_date')
                                    ->label('Inicio de Construcción')
                                    ->helperText('Cuándo comenzó la construcción'),
                                DatePicker::make('construction_end_date')
                                    ->label('Fin de Construcción')
                                    ->helperText('Cuándo terminó la construcción'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('operational_start_date')
                                    ->label('Inicio Operacional')
                                    ->helperText('Cuándo comenzó a operar'),
                                DatePicker::make('expected_end_date')
                                    ->label('Fecha de Finalización Esperada')
                                    ->helperText('Cuándo se espera que termine'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('completion_percentage')
                                    ->label('Porcentaje de Completado (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->default(0)
                                    ->required()
                                    ->helperText('Porcentaje de avance del proyecto'),
                                TextInput::make('estimated_duration_months')
                                    ->label('Duración Estimada (meses)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Duración estimada en meses'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Aspectos Financieros')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_investment')
                                    ->label('Inversión Total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Inversión total del proyecto'),
                                TextInput::make('cost_per_kw')
                                    ->label('Costo por kW')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Costo por kilovatio instalado'),
                                TextInput::make('estimated_roi_percentage')
                                    ->label('ROI Estimado (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(1000)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Retorno de inversión estimado'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payback_period_years')
                                    ->label('Período de Recuperación (años)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->helperText('Tiempo para recuperar la inversión'),
                                TextInput::make('annual_operating_cost')
                                    ->label('Costo Operativo Anual')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Costo operativo anual'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Crowdfunding e Inversión')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('accepts_crowdfunding')
                                    ->label('Acepta Crowdfunding')
                                    ->helperText('¿El proyecto acepta financiamiento colectivo?'),
                                Toggle::make('is_investment_ready')
                                    ->label('Listo para Inversión')
                                    ->helperText('¿El proyecto está listo para recibir inversiones?'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('crowdfunding_target')
                                    ->label('Meta de Crowdfunding')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Meta de financiamiento colectivo'),
                                TextInput::make('crowdfunding_raised')
                                    ->label('Crowdfunding Recaudado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->required()
                                    ->helperText('Monto ya recaudado'),
                                TextInput::make('min_investment')
                                    ->label('Inversión Mínima')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Inversión mínima permitida'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_investment')
                                    ->label('Inversión Máxima')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Inversión máxima permitida'),
                                TextInput::make('investment_terms')
                                    ->label('Términos de Inversión')
                                    ->maxLength(255)
                                    ->helperText('Términos y condiciones de inversión'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Impacto Ambiental')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('co2_avoided_tons_year')
                                    ->label('CO2 Evitado (ton/año)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Toneladas de CO2 evitadas por año'),
                                TextInput::make('renewable_percentage')
                                    ->label('Porcentaje Renovable (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de energía renovable'),
                                TextInput::make('environmental_score')
                                    ->label('Puntuación Ambiental')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->helperText('Puntuación ambiental del proyecto'),
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
                        RichEditor::make('sustainability_metrics')
                            ->label('Métricas de Sostenibilidad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Permisos y Regulaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('regulatory_approved')
                                    ->label('Aprobado Regulatoriamente')
                                    ->helperText('¿El proyecto tiene aprobación regulatoria?'),
                                Toggle::make('permits_complete')
                                    ->label('Permisos Completos')
                                    ->helperText('¿Todos los permisos están completos?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('regulatory_approval_date')
                                    ->label('Fecha de Aprobación Regulatoria')
                                    ->helperText('Cuándo se obtuvo la aprobación'),
                                TextInput::make('regulatory_authority')
                                    ->label('Autoridad Regulatoria')
                                    ->maxLength(255)
                                    ->helperText('Autoridad que aprobó el proyecto'),
                            ]),
                        RichEditor::make('permits_required')
                            ->label('Permisos Requeridos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Lista de permisos requeridos'),
                        RichEditor::make('permits_obtained')
                            ->label('Permisos Obtenidos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Permisos ya obtenidos'),
                    ])
                    ->collapsible(),

                Section::make('Mantenimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('annual_maintenance_cost')
                                    ->label('Costo de Mantenimiento Anual')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Costo anual de mantenimiento'),
                                TextInput::make('maintenance_interval_months')
                                    ->label('Intervalo de Mantenimiento (meses)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Cada cuántos meses se realiza mantenimiento'),
                                TextInput::make('maintenance_provider')
                                    ->label('Proveedor de Mantenimiento')
                                    ->maxLength(255)
                                    ->helperText('Empresa responsable del mantenimiento'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('last_maintenance_date')
                                    ->label('Última Fecha de Mantenimiento')
                                    ->helperText('Cuándo se realizó el último mantenimiento'),
                                DatePicker::make('next_maintenance_date')
                                    ->label('Próxima Fecha de Mantenimiento')
                                    ->helperText('Cuándo se realizará el próximo mantenimiento'),
                            ]),
                        RichEditor::make('maintenance_requirements')
                            ->label('Requisitos de Mantenimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Requisitos específicos de mantenimiento'),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_public')
                                    ->label('Público')
                                    ->helperText('¿El proyecto es visible públicamente?'),
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->required()
                                    ->helperText('¿El proyecto está activo?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label('Destacado')
                                    ->helperText('¿Mostrar como proyecto destacado?'),
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para cambios?'),
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
                        FileUpload::make('images')
                            ->label('Imágenes')
                            ->multiple()
                            ->image()
                            ->directory('production-projects')
                            ->maxFiles(20)
                            ->maxSize(5120)
                            ->helperText('Máximo 20 imágenes de 5MB cada una'),
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('production-projects/documents')
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
                    ->limit(30),
                TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                BadgeColumn::make('project_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'solar',
                        'success' => 'wind',
                        'warning' => 'hydro',
                        'danger' => 'biomass',
                        'info' => 'geothermal',
                        'secondary' => 'nuclear',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => ProductionProject::getProjectTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'in_progress',
                        'danger' => 'cancelled',
                        'info' => 'planning',
                        'secondary' => 'on_hold',
                        'gray' => 'maintenance',
                    ])
                    ->formatStateUsing(fn (string $state): string => ProductionProject::getStatuses()[$state] ?? $state),
                BadgeColumn::make('technology_type')
                    ->label('Tecnología')
                    ->colors([
                        'primary' => 'photovoltaic',
                        'success' => 'concentrated_solar',
                        'warning' => 'wind_turbine',
                        'danger' => 'hydroelectric',
                        'info' => 'biomass_plant',
                        'secondary' => 'geothermal_plant',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => ProductionProject::getTechnologyTypes()[$state] ?? $state),
                TextColumn::make('capacity_kw')
                    ->label('Capacidad (kW)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' kW')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' kW'),
                    ]),
                TextColumn::make('completion_percentage')
                    ->label('Completado (%)')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 50 => 'warning',
                        $state >= 25 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('total_investment')
                    ->label('Inversión Total')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estimated_annual_production')
                    ->label('Producción Anual (kWh)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' kWh')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('efficiency_rating')
                    ->label('Eficiencia (%)')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location_city')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location_country')
                    ->label('País')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('construction_start_date')
                    ->label('Inicio Construcción')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('operational_start_date')
                    ->label('Inicio Operacional')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                ToggleColumn::make('is_public')
                    ->label('Público'),
                ToggleColumn::make('accepts_crowdfunding')
                    ->label('Crowdfunding'),
                ToggleColumn::make('regulatory_approved')
                    ->label('Aprobado'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_type')
                    ->label('Tipo de Proyecto')
                    ->options(ProductionProject::getProjectTypes())
                    ->multiple(),
                SelectFilter::make('technology_type')
                    ->label('Tipo de Tecnología')
                    ->options(ProductionProject::getTechnologyTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ProductionProject::getStatuses())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('public')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true))
                    ->label('Solo Públicos'),
                Filter::make('crowdfunding')
                    ->query(fn (Builder $query): Builder => $query->where('accepts_crowdfunding', true))
                    ->label('Aceptan Crowdfunding'),
                Filter::make('regulatory_approved')
                    ->query(fn (Builder $query): Builder => $query->where('regulatory_approved', true))
                    ->label('Aprobados Regulatoriamente'),
                Filter::make('high_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('capacity_kw', '>=', 1000))
                    ->label('Alta Capacidad (≥1000 kW)'),
                Filter::make('low_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('capacity_kw', '<', 100))
                    ->label('Baja Capacidad (<100 kW)'),
                Filter::make('high_completion')
                    ->query(fn (Builder $query): Builder => $query->where('completion_percentage', '>=', 75))
                    ->label('Alto Completado (≥75%)'),
                Filter::make('low_completion')
                    ->query(fn (Builder $query): Builder => $query->where('completion_percentage', '<', 25))
                    ->label('Bajo Completado (<25%)'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('construction_from')
                            ->label('Construcción desde'),
                        DatePicker::make('construction_until')
                            ->label('Construcción hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['construction_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('construction_start_date', '>=', $date),
                            )
                            ->when(
                                $data['construction_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('construction_start_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Construcción'),
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
                                fn (Builder $query, $capacity): Builder => $query->where('capacity_kw', '>=', $capacity),
                            )
                            ->when(
                                $data['max_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('capacity_kw', '<=', $capacity),
                            );
                    })
                    ->label('Rango de Capacidad'),
                Filter::make('investment_range')
                    ->form([
                        TextInput::make('min_investment')
                            ->label('Inversión mínima')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_investment')
                            ->label('Inversión máxima')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_investment'],
                                fn (Builder $query, $investment): Builder => $query->where('total_investment', '>=', $investment),
                            )
                            ->when(
                                $data['max_investment'],
                                fn (Builder $query, $investment): Builder => $query->where('total_investment', '<=', $investment),
                            );
                    })
                    ->label('Rango de Inversión'),
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
                    ->visible(fn (ProductionProject $record) => !$record->is_active)
                    ->action(function (ProductionProject $record) {
                        $record->update(['is_active' => true]);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (ProductionProject $record) => $record->is_active)
                    ->action(function (ProductionProject $record) {
                        $record->update(['is_active' => false]);
                    }),
                Tables\Actions\Action::make('mark_public')
                    ->label('Hacer Público')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (ProductionProject $record) => !$record->is_public)
                    ->action(function (ProductionProject $record) {
                        $record->update(['is_public' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (ProductionProject $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->slug = $newRecord->slug . '-copy';
                        $newRecord->status = 'planning';
                        $newRecord->completion_percentage = 0;
                        $newRecord->is_active = false;
                        $newRecord->is_public = false;
                        $newRecord->crowdfunding_raised = 0;
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
                    Tables\Actions\BulkAction::make('mark_public_all')
                        ->label('Hacer Públicos Todos')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_public' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(ProductionProject::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_project_type')
                        ->label('Actualizar Tipo de Proyecto')
                        ->icon('heroicon-o-cog')
                        ->form([
                            Select::make('project_type')
                                ->label('Tipo de Proyecto')
                                ->options(ProductionProject::getProjectTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['project_type' => $data['project_type']]);
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
            'index' => Pages\ListProductionProjects::route('/'),
            'create' => Pages\CreateProductionProject::route('/create'),
            'edit' => Pages\EditProductionProject::route('/{record}/edit'),
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
        $inactiveCount = static::getModel()::where('is_active', false)->count();
        
        if ($inactiveCount > 0) {
            return 'warning';
        }
        
        $planningCount = static::getModel()::where('status', 'planning')->count();
        
        if ($planningCount > 0) {
            return 'info';
        }
        
        return 'success';
    }
}
