<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyReadingResource\Pages;
use App\Filament\Resources\EnergyReadingResource\RelationManagers;
use App\Models\EnergyReading;
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

class EnergyReadingResource extends Resource
{
    protected static ?string $model = EnergyReading::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Consumo de Energía';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Lecturas de Energía';

    protected static ?string $modelLabel = 'Lectura de Energía';

    protected static ?string $pluralModelLabel = 'Lecturas de Energía';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('energy_meter_id')
                                    ->label('Medidor de Energía')
                                    ->relationship('energyMeter', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Medidor que generó la lectura'),
                                Select::make('energy_installation_id')
                                    ->label('Instalación de Energía')
                                    ->relationship('energyInstallation', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Instalación asociada'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('reading_timestamp')
                                    ->label('Timestamp de Lectura')
                                    ->required()
                                    ->default(now())
                                    ->helperText('Cuándo se tomó la lectura'),
                                Select::make('reading_type')
                                    ->label('Tipo de Lectura')
                                    ->options(EnergyReading::getReadingTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('unit')
                                    ->label('Unidad')
                                    ->options([
                                        'kWh' => 'kWh',
                                        'MWh' => 'MWh',
                                        'GWh' => 'GWh',
                                        'W' => 'W',
                                        'kW' => 'kW',
                                        'MW' => 'MW',
                                        'A' => 'A',
                                        'V' => 'V',
                                        'Hz' => 'Hz',
                                        'PF' => 'PF',
                                        'kg' => 'kg',
                                        'm/s' => 'm/s',
                                        '°C' => '°C',
                                        '%' => '%',
                                    ])
                                    ->default('kWh')
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('reading_value')
                                    ->label('Valor de Lectura')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->required()
                                    ->helperText('Valor principal de la lectura'),
                                TextInput::make('confidence_level')
                                    ->label('Nivel de Confianza (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->default(100)
                                    ->required()
                                    ->suffix('%')
                                    ->helperText('Confianza en la precisión de la lectura'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Mediciones de Energía')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('active_energy')
                                    ->label('Energía Activa (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Energía activa consumida'),
                                TextInput::make('reactive_energy')
                                    ->label('Energía Reactiva (kVArh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kVArh')
                                    ->helperText('Energía reactiva'),
                                TextInput::make('apparent_energy')
                                    ->label('Energía Aparente (kVAh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kVAh')
                                    ->helperText('Energía aparente'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('instantaneous_power')
                                    ->label('Potencia Instantánea (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kW')
                                    ->helperText('Potencia en el momento de la lectura'),
                                TextInput::make('power_factor')
                                    ->label('Factor de Potencia')
                                    ->numeric()
                                    ->minValue(-1)
                                    ->maxValue(1)
                                    ->step(0.001)
                                    ->helperText('Factor de potencia (cos φ)'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Mediciones Eléctricas')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('voltage_l1')
                                    ->label('Voltaje L1 (V)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->suffix(' V')
                                    ->helperText('Voltaje en la línea 1'),
                                TextInput::make('voltage_l2')
                                    ->label('Voltaje L2 (V)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->suffix(' V')
                                    ->helperText('Voltaje en la línea 2'),
                                TextInput::make('voltage_l3')
                                    ->label('Voltaje L3 (V)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->suffix(' V')
                                    ->helperText('Voltaje en la línea 3'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('current_l1')
                                    ->label('Corriente L1 (A)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' A')
                                    ->helperText('Corriente en la línea 1'),
                                TextInput::make('current_l2')
                                    ->label('Corriente L2 (A)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' A')
                                    ->helperText('Corriente en la línea 2'),
                                TextInput::make('current_l3')
                                    ->label('Corriente L3 (A)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' A')
                                    ->helperText('Corriente en la línea 3'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('frequency')
                                    ->label('Frecuencia (Hz)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' Hz')
                                    ->helperText('Frecuencia de la red'),
                                TextInput::make('phase_angle')
                                    ->label('Ángulo de Fase (°)')
                                    ->numeric()
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->step(0.1)
                                    ->suffix('°')
                                    ->helperText('Ángulo de fase entre voltaje y corriente'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Información de Tarifas')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('tariff_period')
                                    ->label('Período Tarifario')
                                    ->maxLength(255)
                                    ->helperText('Período tarifario aplicable'),
                                TextInput::make('tariff_rate')
                                    ->label('Tarifa (€/kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->prefix('€')
                                    ->suffix('/kWh')
                                    ->helperText('Tarifa aplicada'),
                                TextInput::make('cost_amount')
                                    ->label('Costo (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Costo calculado'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Calidad de Datos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('data_source')
                                    ->label('Fuente de Datos')
                                    ->options(EnergyReading::getDataSources())
                                    ->required()
                                    ->searchable(),
                                Select::make('data_quality')
                                    ->label('Calidad de Datos')
                                    ->options(EnergyReading::getDataQualities())
                                    ->required()
                                    ->searchable(),
                                TextInput::make('accuracy_percentage')
                                    ->label('Precisión (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Precisión de la medición'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_validated')
                                    ->label('Validada')
                                    ->default(false)
                                    ->required()
                                    ->helperText('¿La lectura ha sido validada?'),
                                Toggle::make('is_estimated')
                                    ->label('Estimada')
                                    ->default(false)
                                    ->required()
                                    ->helperText('¿Es una lectura estimada?'),
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

                Section::make('Condiciones Ambientales')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('temperature_celsius')
                                    ->label('Temperatura (°C)')
                                    ->numeric()
                                    ->minValue(-50)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('°C')
                                    ->helperText('Temperatura ambiente'),
                                TextInput::make('humidity_percentage')
                                    ->label('Humedad (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('%')
                                    ->helperText('Humedad relativa'),
                                TextInput::make('pressure_hpa')
                                    ->label('Presión (hPa)')
                                    ->numeric()
                                    ->minValue(800)
                                    ->maxValue(1200)
                                    ->step(0.1)
                                    ->suffix(' hPa')
                                    ->helperText('Presión atmosférica'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('irradiance_w_m2')
                                    ->label('Irradiancia (W/m²)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(1500)
                                    ->step(0.1)
                                    ->suffix(' W/m²')
                                    ->helperText('Radiación solar'),
                                TextInput::make('wind_speed_ms')
                                    ->label('Velocidad del Viento (m/s)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(50)
                                    ->step(0.1)
                                    ->suffix(' m/s')
                                    ->helperText('Velocidad del viento'),
                                TextInput::make('wind_direction_degrees')
                                    ->label('Dirección del Viento (°)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(360)
                                    ->step(1)
                                    ->suffix('°')
                                    ->helperText('Dirección del viento'),
                            ]),
                        RichEditor::make('weather_data')
                            ->label('Datos Meteorológicos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Información meteorológica adicional'),
                    ])
                    ->collapsible(),

                Section::make('Estado del Sistema')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('inverter_status')
                                    ->label('Estado del Inversor')
                                    ->options([
                                        'online' => 'En Línea',
                                        'offline' => 'Desconectado',
                                        'error' => 'Error',
                                        'maintenance' => 'Mantenimiento',
                                        'standby' => 'En Espera',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('inverter_efficiency')
                                    ->label('Eficiencia del Inversor (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia del inversor'),
                                TextInput::make('system_losses')
                                    ->label('Pérdidas del Sistema (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Pérdidas del sistema'),
                            ]),
                        RichEditor::make('system_alerts')
                            ->label('Alertas del Sistema')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Alertas o notificaciones del sistema'),
                    ])
                    ->collapsible(),

                Section::make('Totales Acumulados')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('daily_total')
                                    ->label('Total Diario (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Total del día'),
                                TextInput::make('weekly_total')
                                    ->label('Total Semanal (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Total de la semana'),
                                TextInput::make('monthly_total')
                                    ->label('Total Mensual (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Total del mes'),
                                TextInput::make('yearly_total')
                                    ->label('Total Anual (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Total del año'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('co2_avoided_kg')
                                    ->label('CO2 Evitado (kg)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kg')
                                    ->helperText('CO2 evitado por la generación'),
                                TextInput::make('trees_equivalent')
                                    ->label('Equivalente en Árboles')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->helperText('Equivalente en árboles plantados'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Procesamiento y Validación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('processed_at')
                                    ->label('Procesado el')
                                    ->helperText('Cuándo se procesó la lectura'),
                                Select::make('processed_by_user_id')
                                    ->label('Procesado por')
                                    ->relationship('processedByUser', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        RichEditor::make('processing_notes')
                            ->label('Notas de Procesamiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas sobre el procesamiento'),
                        RichEditor::make('raw_data')
                            ->label('Datos Crudos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Datos originales sin procesar'),
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

                Section::make('Asignaciones del Sistema')
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
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('energyMeter.name')
                    ->label('Medidor')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('reading_timestamp')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reading_value')
                    ->label('Valor')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('unit')
                    ->label('Unidad')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                BadgeColumn::make('reading_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'consumption',
                        'success' => 'production',
                        'warning' => 'export',
                        'danger' => 'import',
                        'info' => 'net',
                        'secondary' => 'demand',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyReading::getReadingTypes()[$state] ?? $state),
                BadgeColumn::make('data_quality')
                    ->label('Calidad')
                    ->colors([
                        'success' => 'excellent',
                        'primary' => 'good',
                        'warning' => 'fair',
                        'danger' => 'poor',
                        'gray' => 'unknown',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyReading::getDataQualities()[$state] ?? $state),
                TextColumn::make('active_energy')
                    ->label('Energía Activa (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' kWh'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('instantaneous_power')
                    ->label('Potencia (kW)')
                    ->suffix(' kW')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('voltage_l1')
                    ->label('V L1 (V)')
                    ->suffix(' V')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('current_l1')
                    ->label('I L1 (A)')
                    ->suffix(' A')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('power_factor')
                    ->label('FP')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 0.95 => 'success',
                        $state >= 0.85 => 'primary',
                        $state >= 0.75 => 'warning',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('frequency')
                    ->label('Frecuencia (Hz)')
                    ->suffix(' Hz')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_amount')
                    ->label('Costo (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('confidence_level')
                    ->label('Confianza (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 95 => 'success',
                        $state >= 80 => 'primary',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_validated')
                    ->label('Validada'),
                ToggleColumn::make('is_estimated')
                    ->label('Estimada'),
                TextColumn::make('temperature_celsius')
                    ->label('Temp (°C)')
                    ->suffix('°C')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('daily_total')
                    ->label('Total Diario (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('co2_avoided_kg')
                    ->label('CO2 Evitado (kg)')
                    ->suffix(' kg')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' kg'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('reading_type')
                    ->label('Tipo de Lectura')
                    ->options(EnergyReading::getReadingTypes())
                    ->multiple(),
                SelectFilter::make('data_quality')
                    ->label('Calidad de Datos')
                    ->options(EnergyReading::getDataQualities())
                    ->multiple(),
                SelectFilter::make('data_source')
                    ->label('Fuente de Datos')
                    ->options(EnergyReading::getDataSources())
                    ->multiple(),
                SelectFilter::make('unit')
                    ->label('Unidad')
                    ->options([
                        'kWh' => 'kWh',
                        'MWh' => 'MWh',
                        'GWh' => 'GWh',
                        'W' => 'W',
                        'kW' => 'kW',
                        'MW' => 'MW',
                        'A' => 'A',
                        'V' => 'V',
                        'Hz' => 'Hz',
                        'PF' => 'PF',
                    ])
                    ->multiple(),
                Filter::make('validated')
                    ->query(fn (Builder $query): Builder => $query->where('is_validated', true))
                    ->label('Solo Validadas'),
                Filter::make('estimated')
                    ->query(fn (Builder $query): Builder => $query->where('is_estimated', true))
                    ->label('Solo Estimadas'),
                Filter::make('high_confidence')
                    ->query(fn (Builder $query): Builder => $query->where('confidence_level', '>=', 90))
                    ->label('Alta Confianza (≥90%)'),
                Filter::make('low_confidence')
                    ->query(fn (Builder $query): Builder => $query->where('confidence_level', '<', 70))
                    ->label('Baja Confianza (<70%)'),
                Filter::make('high_power')
                    ->query(fn (Builder $query): Builder => $query->where('instantaneous_power', '>=', 100))
                    ->label('Alta Potencia (≥100 kW)'),
                Filter::make('low_power')
                    ->query(fn (Builder $query): Builder => $query->where('instantaneous_power', '<', 10))
                    ->label('Baja Potencia (<10 kW)'),
                Filter::make('high_consumption')
                    ->query(fn (Builder $query): Builder => $query->where('active_energy', '>=', 100))
                    ->label('Alto Consumo (≥100 kWh)'),
                Filter::make('low_consumption')
                    ->query(fn (Builder $query): Builder => $query->where('active_energy', '<', 10))
                    ->label('Bajo Consumo (<10 kWh)'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('reading_from')
                            ->label('Lectura desde'),
                        DateTimePicker::make('reading_until')
                            ->label('Lectura hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['reading_from'],
                                fn (Builder $query, $date): Builder => $query->where('reading_timestamp', '>=', $date),
                            )
                            ->when(
                                $data['reading_until'],
                                fn (Builder $query, $date): Builder => $query->where('reading_timestamp', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Lectura'),
                Filter::make('value_range')
                    ->form([
                        TextInput::make('min_value')
                            ->label('Valor mínimo')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_value')
                            ->label('Valor máximo')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_value'],
                                fn (Builder $query, $value): Builder => $query->where('reading_value', '>=', $value),
                            )
                            ->when(
                                $data['max_value'],
                                fn (Builder $query, $value): Builder => $query->where('reading_value', '<=', $value),
                            );
                    })
                    ->label('Rango de Valores'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('validate_reading')
                    ->label('Validar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (EnergyReading $record) => !$record->is_validated)
                    ->action(function (EnergyReading $record) {
                        $record->update([
                            'is_validated' => true,
                            'validated_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('mark_estimated')
                    ->label('Marcar como Estimada')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->visible(fn (EnergyReading $record) => !$record->is_estimated)
                    ->action(function (EnergyReading $record) {
                        $record->update(['is_estimated' => true]);
                    }),
                Tables\Actions\Action::make('process_reading')
                    ->label('Procesar')
                    ->icon('heroicon-o-cog')
                    ->color('info')
                    ->visible(fn (EnergyReading $record) => !$record->processed_at)
                    ->action(function (EnergyReading $record) {
                        $record->update([
                            'processed_at' => now(),
                            'processed_by_user_id' => auth()->id(),
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyReading $record) {
                        $newRecord = $record->replicate();
                        $newRecord->reading_timestamp = now();
                        $newRecord->is_validated = false;
                        $newRecord->is_estimated = false;
                        $newRecord->processed_at = null;
                        $newRecord->processed_by_user_id = null;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('validate_all')
                        ->label('Validar Todas')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'is_validated' => true,
                                    'validated_at' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_estimated_all')
                        ->label('Marcar como Estimadas')
                        ->icon('heroicon-o-calculator')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_estimated' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('process_all')
                        ->label('Procesar Todas')
                        ->icon('heroicon-o-cog')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'processed_at' => now(),
                                    'processed_by_user_id' => auth()->id(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_reading_type')
                        ->label('Actualizar Tipo de Lectura')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('reading_type')
                                ->label('Tipo de Lectura')
                                ->options(EnergyReading::getReadingTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['reading_type' => $data['reading_type']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_data_quality')
                        ->label('Actualizar Calidad de Datos')
                        ->icon('heroicon-o-star')
                        ->form([
                            Select::make('data_quality')
                                ->label('Calidad de Datos')
                                ->options(EnergyReading::getDataQualities())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['data_quality' => $data['data_quality']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('reading_timestamp', 'desc')
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
            'index' => Pages\ListEnergyReadings::route('/'),
            'create' => Pages\CreateEnergyReading::route('/create'),
            'edit' => Pages\EditEnergyReading::route('/{record}/edit'),
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
        $unvalidatedCount = static::getModel()::where('is_validated', false)->count();
        
        if ($unvalidatedCount > 0) {
            return 'warning';
        }
        
        $estimatedCount = static::getModel()::where('is_estimated', true)->count();
        
        if ($estimatedCount > 0) {
            return 'info';
        }
        
        return 'success';
    }
}
