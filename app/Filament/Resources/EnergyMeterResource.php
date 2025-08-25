<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyMeterResource\Pages;
use App\Filament\Resources\EnergyMeterResource\RelationManagers;
use App\Models\EnergyMeter;
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

class EnergyMeterResource extends Resource
{
    protected static ?string $model = EnergyMeter::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Consumo de Energía';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Medidores de Energía';

    protected static ?string $modelLabel = 'Medidor de Energía';

    protected static ?string $pluralModelLabel = 'Medidores de Energía';

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
                                    ->helperText('Nombre del medidor'),
                                TextInput::make('serial_number')
                                    ->label('Número de Serie')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Número de serie único del medidor'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción del medidor'),
                        Grid::make(3)
                            ->schema([
                                Select::make('meter_type')
                                    ->label('Tipo de Medidor')
                                    ->options(EnergyMeter::getMeterTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('measurement_type')
                                    ->label('Tipo de Medición')
                                    ->options(EnergyMeter::getMeasurementTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(EnergyMeter::getStatuses())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('energy_installation_id')
                                    ->label('Instalación de Energía')
                                    ->relationship('energyInstallation', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Instalación asociada'),
                                Select::make('production_project_id')
                                    ->label('Proyecto de Producción')
                                    ->relationship('productionProject', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Proyecto asociado'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('owner_type')
                                    ->label('Tipo de Propietario')
                                    ->options([
                                        'user' => 'Usuario',
                                        'organization' => 'Organización',
                                        'company' => 'Empresa',
                                        'government' => 'Gobierno',
                                        'other' => 'Otro',
                                    ])
                                    ->required()
                                    ->searchable(),
                                TextInput::make('owner_id')
                                    ->label('ID del Propietario')
                                    ->numeric()
                                    ->required()
                                    ->helperText('ID del propietario del medidor'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('managed_by')
                                    ->label('Gestionado por')
                                    ->relationship('managedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Especificaciones Técnicas')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('technology')
                                    ->label('Tecnología')
                                    ->options(EnergyMeter::getTechnologies())
                                    ->required()
                                    ->searchable(),
                                TextInput::make('manufacturer')
                                    ->label('Fabricante')
                                    ->maxLength(255)
                                    ->helperText('Fabricante del medidor'),
                                TextInput::make('model')
                                    ->label('Modelo')
                                    ->maxLength(255)
                                    ->helperText('Modelo específico del medidor'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('firmware_version')
                                    ->label('Versión de Firmware')
                                    ->maxLength(255)
                                    ->helperText('Versión del firmware instalado'),
                                TextInput::make('accuracy_class')
                                    ->label('Clase de Precisión')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->step(0.1)
                                    ->helperText('Clase de precisión del medidor'),
                                TextInput::make('phases')
                                    ->label('Fases')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(4)
                                    ->step(1)
                                    ->default(1)
                                    ->required()
                                    ->helperText('Número de fases que mide'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_current_amps')
                                    ->label('Corriente Máxima (A)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->suffix(' A')
                                    ->helperText('Corriente máxima que puede medir'),
                                TextInput::make('voltage_rating')
                                    ->label('Voltaje Nominal (V)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->suffix(' V')
                                    ->helperText('Voltaje nominal del medidor'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Ubicación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location_description')
                                    ->label('Descripción de Ubicación')
                                    ->maxLength(255)
                                    ->helperText('Descripción de dónde está ubicado'),
                                TextInput::make('latitude')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->helperText('Coordenada de latitud'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('longitude')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->helperText('Coordenada de longitud'),
                                TextInput::make('elevation_m')
                                    ->label('Elevación (m)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix(' m')
                                    ->helperText('Elevación sobre el nivel del mar'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas Importantes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('installation_date')
                                    ->label('Fecha de Instalación')
                                    ->helperText('Cuándo se instaló el medidor'),
                                DatePicker::make('calibration_date')
                                    ->label('Fecha de Calibración')
                                    ->helperText('Cuándo se calibró por última vez'),
                                DatePicker::make('next_calibration_date')
                                    ->label('Próxima Calibración')
                                    ->helperText('Cuándo se debe calibrar'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('warranty_end_date')
                                    ->label('Fin de Garantía')
                                    ->helperText('Cuándo termina la garantía'),
                                DatePicker::make('last_maintenance_date')
                                    ->label('Último Mantenimiento')
                                    ->helperText('Cuándo se realizó el último mantenimiento'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Lecturas y Datos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('last_reading_at')
                                    ->label('Última Lectura')
                                    ->helperText('Cuándo se realizó la última lectura'),
                                TextInput::make('last_reading_value')
                                    ->label('Valor de Última Lectura')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->helperText('Valor de la última lectura'),
                                Select::make('last_reading_unit')
                                    ->label('Unidad de Lectura')
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
                                    ->default('kWh')
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('reading_interval_minutes')
                                    ->label('Intervalo de Lectura (minutos)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(1440)
                                    ->step(1)
                                    ->default(15)
                                    ->required()
                                    ->helperText('Cada cuántos minutos se lee'),
                                Toggle::make('auto_reading_enabled')
                                    ->label('Lectura Automática Habilitada')
                                    ->default(true)
                                    ->required()
                                    ->helperText('¿El medidor lee automáticamente?'),
                            ]),
                        RichEditor::make('reading_config')
                            ->label('Configuración de Lectura')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Configuración específica para las lecturas'),
                    ])
                    ->collapsible(),

                Section::make('Comunicación y Conectividad')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('communication_type')
                                    ->label('Tipo de Comunicación')
                                    ->options(EnergyMeter::getCommunicationTypes())
                                    ->searchable(),
                                TextInput::make('ip_address')
                                    ->label('Dirección IP')
                                    ->maxLength(255)
                                    ->helperText('Dirección IP del medidor'),
                                TextInput::make('mac_address')
                                    ->label('Dirección MAC')
                                    ->maxLength(255)
                                    ->helperText('Dirección MAC del medidor'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('port_number')
                                    ->label('Número de Puerto')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(65535)
                                    ->step(1)
                                    ->helperText('Puerto de comunicación'),
                                TextInput::make('baud_rate')
                                    ->label('Velocidad de Baudios')
                                    ->numeric()
                                    ->minValue(1200)
                                    ->maxValue(115200)
                                    ->step(1200)
                                    ->helperText('Velocidad de comunicación serial'),
                            ]),
                        RichEditor::make('communication_config')
                            ->label('Configuración de Comunicación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Configuración específica de comunicación'),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('remote_access_enabled')
                                    ->label('Acceso Remoto Habilitado')
                                    ->helperText('¿Se puede acceder remotamente?'),
                                Toggle::make('data_encryption_enabled')
                                    ->label('Encriptación de Datos Habilitada')
                                    ->helperText('¿Los datos están encriptados?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Tarifas y Facturación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tariff_type')
                                    ->label('Tipo de Tarifa')
                                    ->maxLength(255)
                                    ->helperText('Tipo de tarifa que aplica'),
                                TextInput::make('billing_cycle_days')
                                    ->label('Ciclo de Facturación (días)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(365)
                                    ->step(1)
                                    ->helperText('Duración del ciclo de facturación'),
                            ]),
                        RichEditor::make('tariff_periods')
                            ->label('Períodos Tarifarios')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Configuración de períodos tarifarios'),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('net_metering_enabled')
                                    ->label('Medición Neta Habilitada')
                                    ->helperText('¿Permite medición neta?'),
                                Toggle::make('billing_enabled')
                                    ->label('Facturación Habilitada')
                                    ->default(true)
                                    ->helperText('¿Genera facturas automáticamente?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Certificaciones y Calidad')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_certified')
                                    ->label('Certificado')
                                    ->helperText('¿El medidor está certificado?'),
                                Toggle::make('calibration_required')
                                    ->label('Requiere Calibración')
                                    ->default(true)
                                    ->helperText('¿Necesita calibración periódica?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('certification_number')
                                    ->label('Número de Certificación')
                                    ->maxLength(255)
                                    ->helperText('Número de certificación oficial'),
                                TextInput::make('calibration_interval_months')
                                    ->label('Intervalo de Calibración (meses)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(120)
                                    ->step(1)
                                    ->helperText('Cada cuántos meses se debe calibrar'),
                            ]),
                        RichEditor::make('certifications')
                            ->label('Certificaciones')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Lista de certificaciones obtenidas'),
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
                                    ->helperText('¿El medidor está activo?'),
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para cambios?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_public')
                                    ->label('Público')
                                    ->helperText('¿Visible públicamente?'),
                                Toggle::make('is_featured')
                                    ->label('Destacado')
                                    ->helperText('¿Mostrar como medidor destacado?'),
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
                                    ->helperText('Prioridad del medidor (1-10)'),
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
                            ->directory('energy-meters')
                            ->maxFiles(50)
                            ->maxSize(10240)
                            ->helperText('Máximo 50 documentos de 10MB cada uno'),
                        FileUpload::make('firmware_files')
                            ->label('Archivos de Firmware')
                            ->multiple()
                            ->directory('energy-meters/firmware')
                            ->maxFiles(20)
                            ->maxSize(51200)
                            ->helperText('Máximo 20 archivos de firmware de 50MB cada uno'),
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
                TextColumn::make('serial_number')
                    ->label('Número de Serie')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),
                BadgeColumn::make('meter_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'electricity',
                        'success' => 'gas',
                        'warning' => 'water',
                        'danger' => 'steam',
                        'info' => 'heat',
                        'secondary' => 'renewable',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyMeter::getMeterTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'maintenance',
                        'danger' => 'faulty',
                        'info' => 'calibrating',
                        'secondary' => 'offline',
                        'gray' => 'inactive',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyMeter::getStatuses()[$state] ?? $state),
                BadgeColumn::make('technology')
                    ->label('Tecnología')
                    ->colors([
                        'primary' => 'digital',
                        'success' => 'smart',
                        'warning' => 'analog',
                        'danger' => 'mechanical',
                        'info' => 'hybrid',
                        'secondary' => 'wireless',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyMeter::getTechnologies()[$state] ?? $state),
                TextColumn::make('manufacturer')
                    ->label('Fabricante')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('phases')
                    ->label('Fases')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 3 => 'success',
                        $state >= 2 => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('accuracy_class')
                    ->label('Precisión')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 0.5 => 'success',
                        $state <= 1.0 => 'primary',
                        $state <= 2.0 => 'warning',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_reading_value')
                    ->label('Última Lectura')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_reading_at')
                    ->label('Última Lectura')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reading_interval_minutes')
                    ->label('Intervalo (min)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' min')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('auto_reading_enabled')
                    ->label('Lectura Auto'),
                ToggleColumn::make('net_metering_enabled')
                    ->label('Medición Neta'),
                ToggleColumn::make('is_certified')
                    ->label('Certificado'),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                TextColumn::make('installation_date')
                    ->label('Instalación')
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
                SelectFilter::make('meter_type')
                    ->label('Tipo de Medidor')
                    ->options(EnergyMeter::getMeterTypes())
                    ->multiple(),
                SelectFilter::make('measurement_type')
                    ->label('Tipo de Medición')
                    ->options(EnergyMeter::getMeasurementTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergyMeter::getStatuses())
                    ->multiple(),
                SelectFilter::make('technology')
                    ->label('Tecnología')
                    ->options(EnergyMeter::getTechnologies())
                    ->multiple(),
                SelectFilter::make('communication_type')
                    ->label('Tipo de Comunicación')
                    ->options(EnergyMeter::getCommunicationTypes())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('certified')
                    ->query(fn (Builder $query): Builder => $query->where('is_certified', true))
                    ->label('Solo Certificados'),
                Filter::make('smart_meters')
                    ->query(fn (Builder $query): Builder => $query->where('technology', 'smart'))
                    ->label('Solo Medidores Inteligentes'),
                Filter::make('auto_reading')
                    ->query(fn (Builder $query): Builder => $query->where('auto_reading_enabled', true))
                    ->label('Con Lectura Automática'),
                Filter::make('net_metering')
                    ->query(fn (Builder $query): Builder => $query->where('net_metering_enabled', true))
                    ->label('Con Medición Neta'),
                Filter::make('high_accuracy')
                    ->query(fn (Builder $query): Builder => $query->where('accuracy_class', '<=', 1.0))
                    ->label('Alta Precisión (≤1.0)'),
                Filter::make('low_accuracy')
                    ->query(fn (Builder $query): Builder => $query->where('accuracy_class', '>', 2.0))
                    ->label('Baja Precisión (>2.0)'),
                Filter::make('multi_phase')
                    ->query(fn (Builder $query): Builder => $query->where('phases', '>=', 3))
                    ->label('Multifase (≥3)'),
                Filter::make('single_phase')
                    ->query(fn (Builder $query): Builder => $query->where('phases', '=', 1))
                    ->label('Monofásico'),
                Filter::make('needs_calibration')
                    ->query(fn (Builder $query): Builder => $query->where('next_calibration_date', '<=', now()->addDays(30)))
                    ->label('Necesita Calibración (≤30 días)'),
                Filter::make('warranty_expired')
                    ->query(fn (Builder $query): Builder => $query->where('warranty_end_date', '<', now()))
                    ->label('Garantía Expirada'),
                Filter::make('date_range')
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
                Filter::make('accuracy_range')
                    ->form([
                        TextInput::make('min_accuracy')
                            ->label('Precisión mínima')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10),
                        TextInput::make('max_accuracy')
                            ->label('Precisión máxima')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_accuracy'],
                                fn (Builder $query, $accuracy): Builder => $query->where('accuracy_class', '>=', $accuracy),
                            )
                            ->when(
                                $data['max_accuracy'],
                                fn (Builder $query, $accuracy): Builder => $query->where('accuracy_class', '<=', $accuracy),
                            );
                    })
                    ->label('Rango de Precisión'),
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
                    ->visible(fn (EnergyMeter $record) => !$record->is_active)
                    ->action(function (EnergyMeter $record) {
                        $record->update(['is_active' => true]);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (EnergyMeter $record) => $record->is_active)
                    ->action(function (EnergyMeter $record) {
                        $record->update(['is_active' => false]);
                    }),
                Tables\Actions\Action::make('enable_auto_reading')
                    ->label('Habilitar Lectura Auto')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (EnergyMeter $record) => !$record->auto_reading_enabled)
                    ->action(function (EnergyMeter $record) {
                        $record->update(['auto_reading_enabled' => true]);
                    }),
                Tables\Actions\Action::make('mark_certified')
                    ->label('Marcar como Certificado')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (EnergyMeter $record) => !$record->is_certified)
                    ->action(function (EnergyMeter $record) {
                        $record->update(['is_certified' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyMeter $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->serial_number = $newRecord->serial_number . '_copy';
                        $newRecord->is_active = false;
                        $newRecord->last_reading_value = 0;
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
                    Tables\Actions\BulkAction::make('enable_auto_reading_all')
                        ->label('Habilitar Lectura Auto')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['auto_reading_enabled' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_certified_all')
                        ->label('Marcar como Certificados')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_certified' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(EnergyMeter::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_meter_type')
                        ->label('Actualizar Tipo de Medidor')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('meter_type')
                                ->label('Tipo de Medidor')
                                ->options(EnergyMeter::getMeterTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['meter_type' => $data['meter_type']]);
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
            'index' => Pages\ListEnergyMeters::route('/'),
            'create' => Pages\CreateEnergyMeter::route('/create'),
            'edit' => Pages\EditEnergyMeter::route('/{record}/edit'),
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
        
        $faultyCount = static::getModel()::where('status', 'faulty')->count();
        
        if ($faultyCount > 0) {
            return 'danger';
        }
        
        return 'success';
    }
}
