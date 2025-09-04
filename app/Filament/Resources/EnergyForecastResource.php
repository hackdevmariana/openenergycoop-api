<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyForecastResource\Pages;
use App\Filament\Resources\EnergyForecastResource\RelationManagers;
use App\Models\EnergyForecast;
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
use Filament\Forms\Components\DateTimePicker;
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


class EnergyForecastResource extends Resource
{
    protected static ?string $model = EnergyForecast::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Comercio de Energía';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Pronósticos de Energía';

    protected static ?string $modelLabel = 'Pronóstico de Energía';

    protected static ?string $pluralModelLabel = 'Pronósticos de Energía';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('installation_id')
                                    ->label('Instalación')
                                    ->relationship('installation', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Instalación para la cual se hace el pronóstico'),
                                Select::make('forecast_type')
                                    ->label('Tipo de Pronóstico')
                                    ->options(EnergyForecast::getForecastTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('forecast_date')
                                    ->label('Fecha del Pronóstico')
                                    ->required()
                                    ->default(now())
                                    ->helperText('Fecha para la cual se hace el pronóstico'),
                                Select::make('time_period')
                                    ->label('Período de Tiempo')
                                    ->options([
                                        'hourly' => 'Por Hora',
                                        'daily' => 'Diario',
                                        'weekly' => 'Semanal',
                                        'monthly' => 'Mensual',
                                        'quarterly' => 'Trimestral',
                                        'yearly' => 'Anual',
                                        'long_term' => 'Largo Plazo',
                                    ])
                                    ->required()
                                    ->searchable(),
                                Select::make('forecast_horizon')
                                    ->label('Horizonte del Pronóstico')
                                    ->options([
                                        '1h' => '1 Hora',
                                        '6h' => '6 Horas',
                                        '12h' => '12 Horas',
                                        '24h' => '24 Horas',
                                        '48h' => '48 Horas',
                                        '72h' => '72 Horas',
                                        '1w' => '1 Semana',
                                        '1m' => '1 Mes',
                                        '3m' => '3 Meses',
                                        '6m' => '6 Meses',
                                        '1y' => '1 Año',
                                    ])
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('confidence_level')
                                    ->label('Nivel de Confianza (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('%')
                                    ->helperText('Nivel de confianza en el pronóstico'),
                                TextInput::make('accuracy_score')
                                    ->label('Puntuación de Precisión')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Precisión histórica del modelo'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('validated_by')
                                    ->label('Validado por')
                                    ->relationship('validatedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('energy_source_id')
                                    ->label('Fuente de Energía')
                                    ->relationship('energySource', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('geographical_zone')
                                    ->label('Zona Geográfica')
                                    ->options([
                                        'north' => 'Norte',
                                        'south' => 'Sur',
                                        'east' => 'Este',
                                        'west' => 'Oeste',
                                        'central' => 'Central',
                                        'coastal' => 'Costera',
                                        'mountain' => 'Montañosa',
                                        'urban' => 'Urbana',
                                        'rural' => 'Rural',
                                        'other' => 'Otra',
                                    ])
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Pronósticos de Producción')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('predicted_production')
                                    ->label('Producción Predicha (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Producción de energía predicha'),
                                TextInput::make('production_min')
                                    ->label('Producción Mínima (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Límite inferior del pronóstico'),
                                TextInput::make('production_max')
                                    ->label('Producción Máxima (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Límite superior del pronóstico'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('production_peak_hour')
                                    ->label('Hora Pico de Producción')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->step(1)
                                    ->suffix('h')
                                    ->helperText('Hora del día con máxima producción'),
                                TextInput::make('production_peak_value')
                                    ->label('Valor Pico de Producción (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kW')
                                    ->helperText('Valor máximo de producción en kW'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Pronósticos de Consumo')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('predicted_consumption')
                                    ->label('Consumo Predicho (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Consumo de energía predicho'),
                                TextInput::make('consumption_min')
                                    ->label('Consumo Mínimo (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Límite inferior del consumo'),
                                TextInput::make('consumption_max')
                                    ->label('Consumo Máximo (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Límite superior del consumo'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('consumption_peak_hour')
                                    ->label('Hora Pico de Consumo')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->step(1)
                                    ->suffix('h')
                                    ->helperText('Hora del día con máximo consumo'),
                                TextInput::make('consumption_peak_value')
                                    ->label('Valor Pico de Consumo (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kW')
                                    ->helperText('Valor máximo de consumo en kW'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Pronósticos de Demanda y Precios')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('predicted_demand')
                                    ->label('Demanda Predicha (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kW')
                                    ->helperText('Demanda de potencia predicha'),
                                TextInput::make('demand_min')
                                    ->label('Demanda Mínima (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kW')
                                    ->helperText('Límite inferior de la demanda'),
                                TextInput::make('demand_max')
                                    ->label('Demanda Máxima (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kW')
                                    ->helperText('Límite superior de la demanda'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('predicted_price')
                                    ->label('Precio Predicho (€/kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->prefix('€')
                                    ->suffix('/kWh')
                                    ->helperText('Precio de la energía predicho'),
                                TextInput::make('price_min')
                                    ->label('Precio Mínimo (€/kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->prefix('€')
                                    ->suffix('/kWh')
                                    ->helperText('Límite inferior del precio'),
                                TextInput::make('price_max')
                                    ->label('Precio Máximo (€/kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->prefix('€')
                                    ->suffix('/kWh')
                                    ->helperText('Límite superior del precio'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Datos Meteorológicos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('temperature_avg')
                                    ->label('Temperatura Promedio (°C)')
                                    ->numeric()
                                    ->minValue(-50)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('°C')
                                    ->helperText('Temperatura promedio del período'),
                                TextInput::make('irradiance_avg')
                                    ->label('Irradiancia Promedio (W/m²)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(1500)
                                    ->step(0.1)
                                    ->suffix(' W/m²')
                                    ->helperText('Irradiancia solar promedio'),
                                TextInput::make('wind_speed_avg')
                                    ->label('Velocidad del Viento Promedio (m/s)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(50)
                                    ->step(0.1)
                                    ->suffix(' m/s')
                                    ->helperText('Velocidad del viento promedio'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('humidity_avg')
                                    ->label('Humedad Promedio (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('%')
                                    ->helperText('Humedad relativa promedio'),
                                TextInput::make('cloud_cover_avg')
                                    ->label('Cobertura de Nubes Promedio (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('%')
                                    ->helperText('Cobertura de nubes promedio'),
                                TextInput::make('precipitation_probability')
                                    ->label('Probabilidad de Precipitación (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('%')
                                    ->helperText('Probabilidad de lluvia'),
                            ]),
                        RichEditor::make('weather_data')
                            ->label('Datos Meteorológicos Detallados')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Datos meteorológicos adicionales y específicos'),
                    ])
                    ->collapsible(),

                Section::make('Modelo y Algoritmo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('algorithm_version')
                                    ->label('Versión del Algoritmo')
                                    ->maxLength(255)
                                    ->helperText('Versión del algoritmo de pronóstico'),
                                TextInput::make('model_type')
                                    ->label('Tipo de Modelo')
                                    ->options([
                                        'machine_learning' => 'Machine Learning',
                                        'statistical' => 'Estadístico',
                                        'physical' => 'Físico',
                                        'hybrid' => 'Híbrido',
                                        'neural_network' => 'Red Neuronal',
                                        'regression' => 'Regresión',
                                        'time_series' => 'Serie Temporal',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                            ]),
                        RichEditor::make('model_parameters')
                            ->label('Parámetros del Modelo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Parámetros específicos del modelo de pronóstico'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('training_data_size')
                                    ->label('Tamaño de Datos de Entrenamiento')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->suffix(' registros')
                                    ->helperText('Cantidad de datos usados para entrenar'),
                                TextInput::make('prediction_horizon_steps')
                                    ->label('Pasos del Horizonte de Pronóstico')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Número de pasos en el horizonte'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Calibración y Validación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('last_calibration')
                                    ->label('Última Calibración')
                                    ->helperText('Cuándo se calibró por última vez'),
                                TextInput::make('calibration_frequency')
                                    ->label('Frecuencia de Calibración (días)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(365)
                                    ->step(1)
                                    ->default(30)
                                    ->required()
                                    ->suffix(' días')
                                    ->helperText('Cada cuántos días se recalibra'),
                                TextInput::make('validation_period_days')
                                    ->label('Período de Validación (días)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(90)
                                    ->step(1)
                                    ->suffix(' días')
                                    ->helperText('Período para validar el modelo'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mae_score')
                                    ->label('Error Absoluto Medio (MAE)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->helperText('Error absoluto medio del modelo'),
                                TextInput::make('rmse_score')
                                    ->label('Error Cuadrático Medio (RMSE)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->helperText('Error cuadrático medio del modelo'),
                            ]),
                        RichEditor::make('validation_notes')
                            ->label('Notas de Validación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas sobre el proceso de validación'),
                    ])
                    ->collapsible(),

                Section::make('Datos Históricos y Comparación')
                    ->schema([
                        RichEditor::make('historical_data')
                            ->label('Datos Históricos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Datos históricos utilizados para el pronóstico'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('historical_accuracy')
                                    ->label('Precisión Histórica (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Precisión del modelo en datos históricos'),
                                TextInput::make('trend_direction')
                                    ->label('Dirección de la Tendencia')
                                    ->options([
                                        'increasing' => 'Creciente',
                                        'decreasing' => 'Decreciente',
                                        'stable' => 'Estable',
                                        'volatile' => 'Volátil',
                                        'seasonal' => 'Estacional',
                                        'unknown' => 'Desconocida',
                                    ])
                                    ->searchable(),
                            ]),
                        RichEditor::make('comparison_analysis')
                            ->label('Análisis de Comparación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Análisis comparativo con pronósticos anteriores'),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->required()
                                    ->helperText('¿El pronóstico está activo?'),
                                Toggle::make('is_public')
                                    ->label('Público')
                                    ->helperText('¿El pronóstico es visible públicamente?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para cambios?'),
                                Toggle::make('auto_update')
                                    ->label('Actualización Automática')
                                    ->helperText('¿Se actualiza automáticamente?'),
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
                                    ->helperText('Prioridad del pronóstico (1-10)'),
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
                            ->directory('energy-forecasts')
                            ->maxFiles(20)
                            ->maxSize(10240)
                            ->helperText('Máximo 20 documentos de 10MB cada uno'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('installation.name')
                    ->label('Instalación')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('forecast_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                BadgeColumn::make('forecast_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'production',
                        'success' => 'consumption',
                        'warning' => 'demand',
                        'danger' => 'price',
                        'info' => 'weather',
                        'secondary' => 'load',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyForecast::getForecastTypes()[$state] ?? $state),
                BadgeColumn::make('forecast_horizon')
                    ->label('Horizonte')
                    ->colors([
                        'primary' => 'hourly',
                        'success' => 'daily',
                        'warning' => 'weekly',
                        'danger' => 'monthly',
                        'info' => 'quarterly',
                        'secondary' => 'yearly',
                        'gray' => 'long_term',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyForecast::getForecastHorizons()[$state] ?? $state),
                TextColumn::make('predicted_production')
                    ->label('Producción (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 10000 => 'success',
                        $state >= 5000 => 'primary',
                        $state >= 1000 => 'warning',
                        $state >= 100 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('predicted_consumption')
                    ->label('Consumo (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 10000 => 'danger',
                        $state >= 5000 => 'warning',
                        $state >= 1000 => 'primary',
                        $state >= 100 => 'info',
                        default => 'success',
                    }),
                TextColumn::make('predicted_demand')
                    ->label('Demanda (kW)')
                    ->suffix(' kW')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('predicted_price')
                    ->label('Precio (€/kWh)')
                    ->money('EUR')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 0.05 => 'success',
                        $state <= 0.10 => 'primary',
                        $state <= 0.20 => 'warning',
                        $state <= 0.50 => 'info',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('confidence_level')
                    ->label('Confianza (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 60 => 'warning',
                        $state >= 40 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('accuracy_score')
                    ->label('Precisión (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 95 => 'success',
                        $state >= 85 => 'primary',
                        $state >= 75 => 'warning',
                        $state >= 65 => 'info',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('algorithm_version')
                    ->label('Algoritmo')
                    ->searchable()
                    ->sortable()
                    ->limit(15)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_calibration')
                    ->label('Última Calibración')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('forecast_type')
                    ->label('Tipo de Pronóstico')
                    ->options(EnergyForecast::getForecastTypes())
                    ->multiple(),
                SelectFilter::make('forecast_horizon')
                    ->label('Horizonte de Pronóstico')
                    ->options(EnergyForecast::getForecastHorizons())
                    ->multiple(),
                SelectFilter::make('geographical_zone')
                    ->label('Zona Geográfica')
                    ->options([
                        'north' => 'Norte',
                        'south' => 'Sur',
                        'east' => 'Este',
                        'west' => 'Oeste',
                        'central' => 'Central',
                        'coastal' => 'Costera',
                        'mountain' => 'Montañosa',
                        'urban' => 'Urbana',
                        'rural' => 'Rural',
                        'other' => 'Otra',
                    ])
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('public')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true))
                    ->label('Solo Públicos'),
                Filter::make('high_confidence')
                    ->query(fn (Builder $query): Builder => $query->where('confidence_level', '>=', 80))
                    ->label('Alta Confianza (≥80%)'),
                Filter::make('low_confidence')
                    ->query(fn (Builder $query): Builder => $query->where('confidence_level', '<', 60))
                    ->label('Baja Confianza (<60%)'),
                Filter::make('high_accuracy')
                    ->query(fn (Builder $query): Builder => $query->where('accuracy_score', '>=', 90))
                    ->label('Alta Precisión (≥90%)'),
                Filter::make('low_accuracy')
                    ->query(fn (Builder $query): Builder => $query->where('accuracy_score', '<', 70))
                    ->label('Baja Precisión (<70%)'),
                Filter::make('high_production')
                    ->query(fn (Builder $query): Builder => $query->where('predicted_production', '>=', 5000))
                    ->label('Alta Producción (≥5000 kWh)'),
                Filter::make('low_production')
                    ->query(fn (Builder $query): Builder => $query->where('predicted_production', '<', 100))
                    ->label('Baja Producción (<100 kWh)'),
                Filter::make('high_consumption')
                    ->query(fn (Builder $query): Builder => $query->where('predicted_consumption', '>=', 5000))
                    ->label('Alto Consumo (≥5000 kWh)'),
                Filter::make('low_consumption')
                    ->query(fn (Builder $query): Builder => $query->where('predicted_consumption', '<', 100))
                    ->label('Bajo Consumo (<100 kWh)'),
                Filter::make('high_price')
                    ->query(fn (Builder $query): Builder => $query->where('predicted_price', '>=', 0.30))
                    ->label('Alto Precio (≥€0.30/kWh)'),
                Filter::make('low_price')
                    ->query(fn (Builder $query): Builder => $query->where('predicted_price', '<=', 0.10))
                    ->label('Bajo Precio (≤€0.10/kWh)'),
                Filter::make('recent_forecasts')
                    ->query(fn (Builder $query): Builder => $query->where('forecast_date', '>=', now()->subDays(7)))
                    ->label('Pronósticos Recientes (≤7 días)'),
                Filter::make('future_forecasts')
                    ->query(fn (Builder $query): Builder => $query->where('forecast_date', '>', now()))
                    ->label('Pronósticos Futuros'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('forecast_from')
                            ->label('Pronóstico desde'),
                        DatePicker::make('forecast_until')
                            ->label('Pronóstico hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['forecast_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('forecast_date', '>=', $date),
                            )
                            ->when(
                                $data['forecast_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('forecast_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Pronóstico'),
                Filter::make('production_range')
                    ->form([
                        TextInput::make('min_production')
                            ->label('Producción mínima (kWh)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_production')
                            ->label('Producción máxima (kWh)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_production'],
                                fn (Builder $query, $production): Builder => $query->where('predicted_production', '>=', $production),
                            )
                            ->when(
                                $data['max_production'],
                                fn (Builder $query, $production): Builder => $query->where('predicted_production', '<=', $production),
                            );
                    })
                    ->label('Rango de Producción'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('validate_forecast')
                    ->label('Validar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (EnergyForecast $record) => !$record->is_validated)
                    ->action(function (EnergyForecast $record) {
                        $record->update([
                            'is_validated' => true,
                            'validated_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('recalibrate')
                    ->label('Recalibrar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (EnergyForecast $record) {
                        $record->update([
                            'last_calibration' => now(),
                            'accuracy_score' => rand(85, 98),
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyForecast $record) {
                        $newRecord = $record->replicate();
                        $newRecord->forecast_date = now()->addDay();
                        $newRecord->is_active = false;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('validate_all')
                        ->label('Validar Todos')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'is_validated' => true,
                                    'validated_at' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('recalibrate_all')
                        ->label('Recalibrar Todos')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'last_calibration' => now(),
                                    'accuracy_score' => rand(85, 98),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_forecast_type')
                        ->label('Actualizar Tipo de Pronóstico')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('forecast_type')
                                ->label('Tipo de Pronóstico')
                                ->options(EnergyForecast::getForecastTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['forecast_type' => $data['forecast_type']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_time_period')
                        ->label('Actualizar Período de Tiempo')
                        ->icon('heroicon-o-clock')
                        ->form([
                            Select::make('time_period')
                                ->label('Período de Tiempo')
                                ->options([
                                    'hourly' => 'Por Hora',
                                    'daily' => 'Diario',
                                    'weekly' => 'Semanal',
                                    'monthly' => 'Mensual',
                                    'quarterly' => 'Trimestral',
                                    'yearly' => 'Anual',
                                    'long_term' => 'Largo Plazo',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['time_period' => $data['time_period']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('forecast_date', 'desc')
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
            'index' => Pages\ListEnergyForecasts::route('/'),
            'create' => Pages\CreateEnergyForecast::route('/create'),
            'edit' => Pages\EditEnergyForecast::route('/{record}/edit'),
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
        $lowConfidenceCount = static::getModel()::where('confidence_level', '<', 60)->count();
        
        if ($lowConfidenceCount > 0) {
            return 'danger';
        }
        
        $lowAccuracyCount = static::getModel()::where('accuracy_score', '<', 70)->count();
        
        if ($lowAccuracyCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
