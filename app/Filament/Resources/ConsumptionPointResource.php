<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsumptionPointResource\Pages;
use App\Filament\Resources\ConsumptionPointResource\RelationManagers;
use App\Models\ConsumptionPoint;
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


class ConsumptionPointResource extends Resource
{
    protected static ?string $model = ConsumptionPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Consumo de Energía';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Puntos de Consumo';

    protected static ?string $modelLabel = 'Punto de Consumo';

    protected static ?string $pluralModelLabel = 'Puntos de Consumo';

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
                                    ->helperText('Nombre del punto de consumo'),
                                TextInput::make('cups_code')
                                    ->label('Código CUPS')
                                    ->maxLength(255)
                                    ->helperText('Código único del punto de suministro'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción del punto de consumo'),
                        Grid::make(3)
                            ->schema([
                                Select::make('consumption_type')
                                    ->label('Tipo de Consumo')
                                    ->options(ConsumptionPoint::getConsumptionTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('supply_type')
                                    ->label('Tipo de Suministro')
                                    ->options(ConsumptionPoint::getSupplyTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(ConsumptionPoint::getStatuses())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Propietario y Asignaciones')
                    ->schema([
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
                                    ->helperText('ID del propietario del punto'),
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

                Section::make('Ubicación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('address')
                                    ->label('Dirección')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Dirección completa'),
                                TextInput::make('city')
                                    ->label('Ciudad')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Ciudad donde se ubica'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('postal_code')
                                    ->label('Código Postal')
                                    ->required()
                                    ->maxLength(10)
                                    ->helperText('Código postal'),
                                TextInput::make('region')
                                    ->label('Región/Estado')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Región o estado'),
                                TextInput::make('country')
                                    ->label('País')
                                    ->required()
                                    ->maxLength(2)
                                    ->default('ES')
                                    ->helperText('Código de país (ISO 3166-1 alpha-2)'),
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
                    ])
                    ->collapsible(),

                Section::make('Características Técnicas')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('connection_type')
                                    ->label('Tipo de Conexión')
                                    ->options(ConsumptionPoint::getConnectionTypes())
                                    ->required()
                                    ->searchable(),
                                TextInput::make('service_type')
                                    ->label('Tipo de Servicio')
                                    ->options(ConsumptionPoint::getServiceTypes())
                                    ->required()
                                    ->searchable(),
                                TextInput::make('voltage_level')
                                    ->label('Nivel de Voltaje (V)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->suffix(' V')
                                    ->helperText('Nivel de voltaje del servicio'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('contracted_power_kw')
                                    ->label('Potencia Contratada (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->suffix(' kW')
                                    ->helperText('Potencia contratada'),
                                TextInput::make('max_power_kw')
                                    ->label('Potencia Máxima (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' kW')
                                    ->helperText('Potencia máxima disponible'),
                                TextInput::make('peak_demand_kw')
                                    ->label('Demanda Pico (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' kW')
                                    ->helperText('Demanda máxima registrada'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Tarifas y Facturación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('tariff_type')
                                    ->label('Tipo de Tarifa')
                                    ->options(ConsumptionPoint::getTariffTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('billing_frequency')
                                    ->label('Frecuencia de Facturación')
                                    ->options([
                                        'monthly' => 'Mensual',
                                        'bimonthly' => 'Bimestral',
                                        'quarterly' => 'Trimestral',
                                        'semiannual' => 'Semestral',
                                        'annual' => 'Anual',
                                        'other' => 'Otro',
                                    ])
                                    ->default('monthly')
                                    ->required()
                                    ->searchable(),
                                TextInput::make('billing_day')
                                    ->label('Día de Facturación')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(31)
                                    ->step(1)
                                    ->helperText('Día del mes para facturación'),
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
                            ->helperText('Descripción de los períodos tarifarios'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('monthly_fixed_cost')
                                    ->label('Costo Fijo Mensual')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Costo fijo mensual'),
                                TextInput::make('average_energy_cost')
                                    ->label('Costo Promedio de Energía')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->suffix('/kWh')
                                    ->helperText('Costo promedio por kWh'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Proveedores y Distribuidores')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('supplier_name')
                                    ->label('Nombre del Proveedor')
                                    ->maxLength(255)
                                    ->helperText('Empresa proveedora de energía'),
                                TextInput::make('supplier_code')
                                    ->label('Código del Proveedor')
                                    ->maxLength(255)
                                    ->helperText('Código identificador del proveedor'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('distributor_name')
                                    ->label('Nombre del Distribuidor')
                                    ->maxLength(255)
                                    ->helperText('Empresa distribuidora de energía'),
                                TextInput::make('distributor_code')
                                    ->label('Código del Distribuidor')
                                    ->maxLength(255)
                                    ->helperText('Código identificador del distribuidor'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Medidores y Lecturas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('main_meter_id')
                                    ->label('ID del Medidor Principal')
                                    ->numeric()
                                    ->helperText('ID del medidor principal'),
                                Select::make('meter_reading_frequency')
                                    ->label('Frecuencia de Lectura')
                                    ->options([
                                        'hourly' => 'Cada Hora',
                                        'daily' => 'Diaria',
                                        'weekly' => 'Semanal',
                                        'monthly' => 'Mensual',
                                        'quarterly' => 'Trimestral',
                                        'other' => 'Otro',
                                    ])
                                    ->default('monthly')
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('has_smart_meter')
                                    ->label('Medidor Inteligente')
                                    ->helperText('¿Tiene medidor inteligente?'),
                                Toggle::make('remote_reading_enabled')
                                    ->label('Lectura Remota Habilitada')
                                    ->helperText('¿Permite lectura remota?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas Importantes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('connection_date')
                                    ->label('Fecha de Conexión')
                                    ->helperText('Cuándo se conectó el servicio'),
                                DatePicker::make('contract_start_date')
                                    ->label('Inicio de Contrato')
                                    ->helperText('Cuándo comenzó el contrato'),
                                DatePicker::make('contract_end_date')
                                    ->label('Fin de Contrato')
                                    ->helperText('Cuándo termina el contrato'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('last_inspection_date')
                                    ->label('Última Inspección')
                                    ->helperText('Cuándo se realizó la última inspección'),
                                DatePicker::make('next_inspection_date')
                                    ->label('Próxima Inspección')
                                    ->helperText('Cuándo se realizará la próxima inspección'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Consumo y Eficiencia')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('annual_consumption_kwh')
                                    ->label('Consumo Anual (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->suffix(' kWh')
                                    ->helperText('Consumo anual en kilovatios-hora'),
                                TextInput::make('monthly_average_kwh')
                                    ->label('Promedio Mensual (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->suffix(' kWh')
                                    ->helperText('Consumo promedio mensual'),
                                TextInput::make('daily_average_kwh')
                                    ->label('Promedio Diario (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' kWh')
                                    ->helperText('Consumo promedio diario'),
                            ]),
                        RichEditor::make('consumption_profile')
                            ->label('Perfil de Consumo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Descripción del perfil de consumo'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('energy_efficiency_rating')
                                    ->label('Calificación de Eficiencia')
                                    ->maxLength(255)
                                    ->helperText('Calificación energética'),
                                DatePicker::make('efficiency_certificate_date')
                                    ->label('Fecha del Certificado de Eficiencia')
                                    ->helperText('Cuándo se emitió el certificado'),
                            ]),
                        RichEditor::make('efficiency_improvements')
                            ->label('Mejoras de Eficiencia')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Mejoras implementadas para la eficiencia'),
                    ])
                    ->collapsible(),

                Section::make('Características del Edificio')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('building_area_m2')
                                    ->label('Área del Edificio (m²)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix(' m²')
                                    ->helperText('Superficie total del edificio'),
                                TextInput::make('occupants_count')
                                    ->label('Número de Ocupantes')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('Número de personas que habitan'),
                            ]),
                        RichEditor::make('appliances_inventory')
                            ->label('Inventario de Electrodomésticos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Lista de electrodomésticos principales'),
                        RichEditor::make('heating_cooling_systems')
                            ->label('Sistemas de Calefacción/Refrigeración')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Sistemas de climatización instalados'),
                    ])
                    ->collapsible(),

                Section::make('Configuración Avanzada')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('net_metering_enabled')
                                    ->label('Medición Neta Habilitada')
                                    ->helperText('¿Permite medición neta?'),
                                Toggle::make('can_inject_energy')
                                    ->label('Puede Inyectar Energía')
                                    ->helperText('¿Puede devolver energía a la red?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->required()
                                    ->helperText('¿El punto está activo?'),
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
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('consumption-points')
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
                TextColumn::make('cups_code')
                    ->label('Código CUPS')
                    ->searchable()
                    ->sortable()
                    ->limit(15)
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),
                BadgeColumn::make('consumption_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'residential',
                        'success' => 'commercial',
                        'warning' => 'industrial',
                        'danger' => 'agricultural',
                        'info' => 'public',
                        'secondary' => 'mixed',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => ConsumptionPoint::getConsumptionTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'maintenance',
                        'danger' => 'disconnected',
                        'info' => 'pending',
                        'secondary' => 'suspended',
                        'gray' => 'inactive',
                    ])
                    ->formatStateUsing(fn (string $state): string => ConsumptionPoint::getStatuses()[$state] ?? $state),
                BadgeColumn::make('supply_type')
                    ->label('Suministro')
                    ->colors([
                        'primary' => 'electricity',
                        'success' => 'gas',
                        'warning' => 'water',
                        'danger' => 'steam',
                        'info' => 'district_heating',
                        'secondary' => 'renewable',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => ConsumptionPoint::getSupplyTypes()[$state] ?? $state),
                TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('contracted_power_kw')
                    ->label('Potencia (kW)')
                    ->suffix(' kW')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'primary',
                        $state >= 20 => 'warning',
                        $state >= 5 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('annual_consumption_kwh')
                    ->label('Consumo Anual (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' kWh'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('monthly_average_kwh')
                    ->label('Promedio Mensual (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('peak_demand_kw')
                    ->label('Demanda Pico (kW)')
                    ->suffix(' kW')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('monthly_fixed_cost')
                    ->label('Costo Fijo ($)')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('average_energy_cost')
                    ->label('Costo Energía ($/kWh)')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('has_smart_meter')
                    ->label('Medidor Inteligente'),
                ToggleColumn::make('remote_reading_enabled')
                    ->label('Lectura Remota'),
                ToggleColumn::make('net_metering_enabled')
                    ->label('Medición Neta'),
                ToggleColumn::make('can_inject_energy')
                    ->label('Puede Inyectar'),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                TextColumn::make('connection_date')
                    ->label('Conexión')
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
                SelectFilter::make('consumption_type')
                    ->label('Tipo de Consumo')
                    ->options(ConsumptionPoint::getConsumptionTypes())
                    ->multiple(),
                SelectFilter::make('supply_type')
                    ->label('Tipo de Suministro')
                    ->options(ConsumptionPoint::getSupplyTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ConsumptionPoint::getStatuses())
                    ->multiple(),
                SelectFilter::make('connection_type')
                    ->label('Tipo de Conexión')
                    ->options(ConsumptionPoint::getConnectionTypes())
                    ->multiple(),
                SelectFilter::make('service_type')
                    ->label('Tipo de Servicio')
                    ->options(ConsumptionPoint::getServiceTypes())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('smart_meter')
                    ->query(fn (Builder $query): Builder => $query->where('has_smart_meter', true))
                    ->label('Con Medidor Inteligente'),
                Filter::make('remote_reading')
                    ->query(fn (Builder $query): Builder => $query->where('remote_reading_enabled', true))
                    ->label('Con Lectura Remota'),
                Filter::make('net_metering')
                    ->query(fn (Builder $query): Builder => $query->where('net_metering_enabled', true))
                    ->label('Con Medición Neta'),
                Filter::make('can_inject')
                    ->query(fn (Builder $query): Builder => $query->where('can_inject_energy', true))
                    ->label('Pueden Inyectar Energía'),
                Filter::make('high_power')
                    ->query(fn (Builder $query): Builder => $query->where('contracted_power_kw', '>=', 100))
                    ->label('Alta Potencia (≥100 kW)'),
                Filter::make('low_power')
                    ->query(fn (Builder $query): Builder => $query->where('contracted_power_kw', '<', 10))
                    ->label('Baja Potencia (<10 kW)'),
                Filter::make('high_consumption')
                    ->query(fn (Builder $query): Builder => $query->where('annual_consumption_kwh', '>=', 50000))
                    ->label('Alto Consumo (≥50,000 kWh)'),
                Filter::make('low_consumption')
                    ->query(fn (Builder $query): Builder => $query->where('annual_consumption_kwh', '<', 5000))
                    ->label('Bajo Consumo (<5,000 kWh)'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('connection_from')
                            ->label('Conexión desde'),
                        DatePicker::make('connection_until')
                            ->label('Conexión hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['connection_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('connection_date', '>=', $date),
                            )
                            ->when(
                                $data['connection_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('connection_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Conexión'),
                Filter::make('power_range')
                    ->form([
                        TextInput::make('min_power')
                            ->label('Potencia mínima (kW)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_power')
                            ->label('Potencia máxima (kW)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_power'],
                                fn (Builder $query, $power): Builder => $query->where('contracted_power_kw', '>=', $power),
                            )
                            ->when(
                                $data['max_power'],
                                fn (Builder $query, $power): Builder => $query->where('contracted_power_kw', '<=', $power),
                            );
                    })
                    ->label('Rango de Potencia'),
                Filter::make('consumption_range')
                    ->form([
                        TextInput::make('min_consumption')
                            ->label('Consumo mínimo (kWh)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_consumption')
                            ->label('Consumo máximo (kWh)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_consumption'],
                                fn (Builder $query, $consumption): Builder => $query->where('annual_consumption_kwh', '>=', $consumption),
                            )
                            ->when(
                                $data['max_consumption'],
                                fn (Builder $query, $consumption): Builder => $query->where('annual_consumption_kwh', '<=', $consumption),
                            );
                    })
                    ->label('Rango de Consumo'),
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
                    ->visible(fn (ConsumptionPoint $record) => !$record->is_active)
                    ->action(function (ConsumptionPoint $record) {
                        $record->update(['is_active' => true]);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (ConsumptionPoint $record) => $record->is_active)
                    ->action(function (ConsumptionPoint $record) {
                        $record->update(['is_active' => false]);
                    }),
                Tables\Actions\Action::make('enable_smart_meter')
                    ->label('Habilitar Medidor Inteligente')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('info')
                    ->visible(fn (ConsumptionPoint $record) => !$record->has_smart_meter)
                    ->action(function (ConsumptionPoint $record) {
                        $record->update(['has_smart_meter' => true]);
                    }),
                Tables\Actions\Action::make('enable_remote_reading')
                    ->label('Habilitar Lectura Remota')
                    ->icon('heroicon-o-signal')
                    ->color('success')
                    ->visible(fn (ConsumptionPoint $record) => !$record->remote_reading_enabled)
                    ->action(function (ConsumptionPoint $record) {
                        $record->update(['remote_reading_enabled' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (ConsumptionPoint $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->cups_code = $newRecord->cups_code . '_copy';
                        $newRecord->is_active = false;
                        $newRecord->annual_consumption_kwh = 0;
                        $newRecord->monthly_average_kwh = 0;
                        $newRecord->daily_average_kwh = 0;
                        $newRecord->peak_demand_kw = 0;
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
                    Tables\Actions\BulkAction::make('enable_smart_meters_all')
                        ->label('Habilitar Medidores Inteligentes')
                        ->icon('heroicon-o-cpu-chip')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['has_smart_meter' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('enable_remote_reading_all')
                        ->label('Habilitar Lectura Remota')
                        ->icon('heroicon-o-signal')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['remote_reading_enabled' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(ConsumptionPoint::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_consumption_type')
                        ->label('Actualizar Tipo de Consumo')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('consumption_type')
                                ->label('Tipo de Consumo')
                                ->options(ConsumptionPoint::getConsumptionTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['consumption_type' => $data['consumption_type']]);
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
            'index' => Pages\ListConsumptionPoints::route('/'),
            'create' => Pages\CreateConsumptionPoint::route('/create'),
            'edit' => Pages\EditConsumptionPoint::route('/{record}/edit'),
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
        
        $disconnectedCount = static::getModel()::where('status', 'disconnected')->count();
        
        if ($disconnectedCount > 0) {
            return 'danger';
        }
        
        return 'success';
    }
}
