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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;


class EnergySourceResource extends Resource
{
    protected static ?string $model = EnergySource::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Producción de Energía';

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
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('URL amigable de la fuente'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada de la fuente de energía'),
                        Grid::make(3)
                            ->schema([
                                Select::make('category')
                                    ->label('Categoría')
                                    ->options(EnergySource::getCategories())
                                    ->required()
                                    ->searchable(),
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options(EnergySource::getTypes())
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

                Section::make('Especificaciones Técnicas')
                    ->schema([
                        RichEditor::make('technical_specs')
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
                        Grid::make(3)
                            ->schema([
                                TextInput::make('efficiency_min')
                                    ->label('Eficiencia Mínima (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia mínima esperada'),
                                TextInput::make('efficiency_max')
                                    ->label('Eficiencia Máxima (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia máxima esperada'),
                                TextInput::make('efficiency_typical')
                                    ->label('Eficiencia Típica (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia típica en condiciones normales'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('capacity_min')
                                    ->label('Capacidad Mínima (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' kW')
                                    ->helperText('Capacidad mínima instalable'),
                                TextInput::make('capacity_max')
                                    ->label('Capacidad Máxima (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' kW')
                                    ->helperText('Capacidad máxima instalable'),
                                TextInput::make('capacity_typical')
                                    ->label('Capacidad Típica (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' kW')
                                    ->helperText('Capacidad típica instalada'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('lifespan_years')
                                    ->label('Vida Útil (años)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->suffix(' años')
                                    ->helperText('Vida útil esperada en años'),
                                TextInput::make('degradation_rate')
                                    ->label('Tasa de Degradación (%/año)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->step(0.01)
                                    ->suffix('%/año')
                                    ->helperText('Tasa de degradación anual'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Impacto Ambiental')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('carbon_footprint_kg_kwh')
                                    ->label('Huella de Carbono (kg CO2/kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kg CO2/kWh')
                                    ->helperText('Emisiones de CO2 por kWh generado'),
                                TextInput::make('water_consumption_l_kwh')
                                    ->label('Consumo de Agua (L/kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' L/kWh')
                                    ->helperText('Consumo de agua por kWh generado'),
                                TextInput::make('land_use_m2_kw')
                                    ->label('Uso de Tierra (m²/kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' m²/kW')
                                    ->helperText('Superficie requerida por kW instalado'),
                            ]),
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
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_renewable')
                                    ->label('Renovable')
                                    ->helperText('¿Es una fuente de energía renovable?'),
                                Toggle::make('is_clean')
                                    ->label('Limpia')
                                    ->helperText('¿Es una fuente de energía limpia?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('renewable_certificate')
                                    ->label('Certificado Renovable')
                                    ->maxLength(255)
                                    ->helperText('Certificado de energía renovable'),
                                TextInput::make('environmental_rating')
                                    ->label('Calificación Ambiental')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('/100')
                                    ->helperText('Puntuación ambiental (0-100)'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Aspectos Financieros')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('installation_cost_per_kw')
                                    ->label('Costo de Instalación ($/kW)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix('/kW')
                                    ->helperText('Costo de instalación por kilovatio'),
                                TextInput::make('maintenance_cost_annual')
                                    ->label('Costo de Mantenimiento Anual ($/kW)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix('/kW/año')
                                    ->helperText('Costo anual de mantenimiento por kW'),
                                TextInput::make('operational_cost_per_kwh')
                                    ->label('Costo Operativo ($/kWh)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->suffix('/kWh')
                                    ->helperText('Costo operativo por kWh generado'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('levelized_cost_kwh')
                                    ->label('Costo Nivelado ($/kWh)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->suffix('/kWh')
                                    ->helperText('Costo nivelado de energía'),
                                TextInput::make('payback_period_years')
                                    ->label('Período de Recuperación (años)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->suffix(' años')
                                    ->helperText('Tiempo para recuperar la inversión'),
                            ]),
                        RichEditor::make('financial_notes')
                            ->label('Notas Financieras')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas adicionales sobre aspectos financieros'),
                    ])
                    ->collapsible(),

                Section::make('Disponibilidad y Dependencias')
                    ->schema([
                        RichEditor::make('geographic_availability')
                            ->label('Disponibilidad Geográfica')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Regiones donde está disponible esta fuente'),
                        RichEditor::make('weather_dependencies')
                            ->label('Dependencias Climáticas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Cómo afecta el clima a la generación'),
                        RichEditor::make('seasonal_variations')
                            ->label('Variaciones Estacionales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Variaciones en la generación por estación'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('capacity_factor_min')
                                    ->label('Factor de Capacidad Mínimo (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Factor de capacidad mínimo anual'),
                                TextInput::make('capacity_factor_max')
                                    ->label('Factor de Capacidad Máximo (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Factor de capacidad máximo anual'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Tecnología y Equipamiento')
                    ->schema([
                        RichEditor::make('technology_description')
                            ->label('Descripción de la Tecnología')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Descripción de la tecnología utilizada'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('manufacturer')
                                    ->label('Fabricante Principal')
                                    ->maxLength(255)
                                    ->helperText('Fabricante principal del equipamiento'),
                                TextInput::make('model_series')
                                    ->label('Serie de Modelos')
                                    ->maxLength(255)
                                    ->helperText('Serie de modelos disponibles'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('warranty_years')
                                    ->label('Garantía (años)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.5)
                                    ->suffix(' años')
                                    ->helperText('Período de garantía'),
                                TextInput::make('certification_standards')
                                    ->label('Estándares de Certificación')
                                    ->maxLength(255)
                                    ->helperText('Estándares de certificación aplicables'),
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
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true)
                                    ->required()
                                    ->helperText('¿La fuente está activa?'),
                                Toggle::make('is_featured')
                                    ->label('Destacada')
                                    ->helperText('¿Mostrar como fuente destacada?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_public')
                                    ->label('Pública')
                                    ->helperText('¿Visible públicamente?'),
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para cambios?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icono')
                                    ->maxLength(255)
                                    ->helperText('Clase del icono (ej: heroicon-o-bolt)'),
                                ColorPicker::make('color')
                                    ->label('Color')
                                    ->helperText('Color representativo de la fuente'),
                            ]),
                        TextInput::make('sort_order')
                            ->label('Orden de Clasificación')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->default(0)
                            ->required()
                            ->helperText('Orden para mostrar en listas'),
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
                            ->directory('energy-sources')
                            ->maxFiles(15)
                            ->maxSize(5120)
                            ->helperText('Máximo 15 imágenes de 5MB cada una'),
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('energy-sources/documents')
                            ->maxFiles(30)
                            ->maxSize(10240)
                            ->helperText('Máximo 30 documentos de 10MB cada uno'),
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
                BadgeColumn::make('category')
                    ->label('Categoría')
                    ->colors([
                        'primary' => 'renewable',
                        'success' => 'solar',
                        'warning' => 'wind',
                        'danger' => 'fossil',
                        'info' => 'nuclear',
                        'secondary' => 'hydro',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergySource::getCategories()[$state] ?? $state),
                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'photovoltaic',
                        'success' => 'concentrated_solar',
                        'warning' => 'wind_turbine',
                        'danger' => 'coal_plant',
                        'info' => 'nuclear_reactor',
                        'secondary' => 'hydroelectric',
                        'gray' => 'biomass',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergySource::getTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'maintenance',
                        'danger' => 'inactive',
                        'info' => 'development',
                        'secondary' => 'testing',
                        'gray' => 'deprecated',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergySource::getStatuses()[$state] ?? $state),
                TextColumn::make('efficiency_typical')
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
                TextColumn::make('capacity_typical')
                    ->label('Capacidad Típica (kW)')
                    ->suffix(' kW')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('lifespan_years')
                    ->label('Vida Útil (años)')
                    ->suffix(' años')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('carbon_footprint_kg_kwh')
                    ->label('CO2 (kg/kWh)')
                    ->suffix(' kg CO2/kWh')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 0.1 => 'success',
                        $state <= 0.5 => 'primary',
                        $state <= 1.0 => 'warning',
                        $state <= 2.0 => 'info',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('installation_cost_per_kw')
                    ->label('Costo Instalación ($/kW)')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('operational_cost_per_kwh')
                    ->label('Costo Operativo ($/kWh)')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_renewable')
                    ->label('Renovable'),
                ToggleColumn::make('is_clean')
                    ->label('Limpia'),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                ToggleColumn::make('is_featured')
                    ->label('Destacada'),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(EnergySource::getCategories())
                    ->multiple(),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(EnergySource::getTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergySource::getStatuses())
                    ->multiple(),
                Filter::make('renewable')
                    ->query(fn (Builder $query): Builder => $query->where('is_renewable', true))
                    ->label('Solo Renovables'),
                Filter::make('clean')
                    ->query(fn (Builder $query): Builder => $query->where('is_clean', true))
                    ->label('Solo Limpias'),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activas'),
                Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Solo Destacadas'),
                Filter::make('high_efficiency')
                    ->query(fn (Builder $query): Builder => $query->where('efficiency_typical', '>=', 80))
                    ->label('Alta Eficiencia (≥80%)'),
                Filter::make('low_efficiency')
                    ->query(fn (Builder $query): Builder => $query->where('efficiency_typical', '<', 40))
                    ->label('Baja Eficiencia (<40%)'),
                Filter::make('high_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('capacity_typical', '>=', 1000))
                    ->label('Alta Capacidad (≥1000 kW)'),
                Filter::make('low_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('capacity_typical', '<', 100))
                    ->label('Baja Capacidad (<100 kW)'),
                Filter::make('low_carbon')
                    ->query(fn (Builder $query): Builder => $query->where('carbon_footprint_kg_kwh', '<=', 0.1))
                    ->label('Baja Huella de Carbono (≤0.1 kg/kWh)'),
                Filter::make('high_carbon')
                    ->query(fn (Builder $query): Builder => $query->where('carbon_footprint_kg_kwh', '>', 1.0))
                    ->label('Alta Huella de Carbono (>1.0 kg/kWh)'),
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
                                fn (Builder $query, $efficiency): Builder => $query->where('efficiency_typical', '>=', $efficiency),
                            )
                            ->when(
                                $data['max_efficiency'],
                                fn (Builder $query, $efficiency): Builder => $query->where('efficiency_typical', '<=', $efficiency),
                            );
                    })
                    ->label('Rango de Eficiencia'),
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
                                fn (Builder $query, $capacity): Builder => $query->where('capacity_typical', '>=', $capacity),
                            )
                            ->when(
                                $data['max_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('capacity_typical', '<=', $capacity),
                            );
                    })
                    ->label('Rango de Capacidad'),
                Filter::make('cost_range')
                    ->form([
                        TextInput::make('min_cost')
                            ->label('Costo mínimo ($/kW)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_cost')
                            ->label('Costo máximo ($/kW)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_cost'],
                                fn (Builder $query, $cost): Builder => $query->where('installation_cost_per_kw', '>=', $cost),
                            )
                            ->when(
                                $data['max_cost'],
                                fn (Builder $query, $cost): Builder => $query->where('installation_cost_per_kw', '<=', $cost),
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
                    ->visible(fn (EnergySource $record) => !$record->is_active)
                    ->action(function (EnergySource $record) {
                        $record->update(['is_active' => true]);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (EnergySource $record) => $record->is_active)
                    ->action(function (EnergySource $record) {
                        $record->update(['is_active' => false]);
                    }),
                Tables\Actions\Action::make('mark_featured')
                    ->label('Destacar')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (EnergySource $record) => !$record->is_featured)
                    ->action(function (EnergySource $record) {
                        $record->update(['is_featured' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergySource $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->slug = $newRecord->slug . '-copy';
                        $newRecord->is_active = false;
                        $newRecord->is_featured = false;
                        $newRecord->sort_order = $newRecord->sort_order + 1;
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
                                $record->update(['is_active' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate_all')
                        ->label('Desactivar Todas')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_featured_all')
                        ->label('Destacar Todas')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_featured' => true]);
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
                            Select::make('category')
                                ->label('Categoría')
                                ->options(EnergySource::getCategories())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['category' => $data['category']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
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
        return parent::getEloquentQuery();
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
        
        $featuredCount = static::getModel()::where('is_featured', true)->count();
        
        if ($featuredCount > 0) {
            return 'success';
        }
        
        return 'info';
    }
}
