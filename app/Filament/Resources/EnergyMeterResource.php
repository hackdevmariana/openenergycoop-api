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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnergyMeterResource extends Resource
{
    protected static ?string $model = EnergyMeter::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Energía y Comercio';

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
                                TextInput::make('meter_number')
                                    ->label('Número de Medidor')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Número único del medidor'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre del medidor'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('serial_number')
                                    ->label('Número de Serie')
                                    ->maxLength(255)
                                    ->helperText('Número de serie del medidor'),
                                TextInput::make('description')
                                    ->label('Descripción')
                                    ->maxLength(65535)
                                    ->helperText('Descripción del medidor'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('meter_type')
                                    ->label('Tipo de Medidor')
                                    ->options(EnergyMeter::getMeterTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('meter_category')
                                    ->label('Categoría del Medidor')
                                    ->options(EnergyMeter::getMeterCategories())
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
                                Select::make('installation_id')
                                    ->label('Instalación de Energía')
                                    ->relationship('installation', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Instalación asociada'),
                                Select::make('consumption_point_id')
                                    ->label('Punto de Consumo')
                                    ->relationship('consumptionPoint', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Punto de consumo asociado'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Cliente propietario del medidor'),
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('installed_by')
                                    ->label('Instalado por')
                                    ->relationship('installedBy', 'name')
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
                                TextInput::make('manufacturer')
                                    ->label('Fabricante')
                                    ->maxLength(255)
                                    ->helperText('Fabricante del medidor'),
                                TextInput::make('model')
                                    ->label('Modelo')
                                    ->maxLength(255)
                                    ->helperText('Modelo específico del medidor'),
                                TextInput::make('firmware_version')
                                    ->label('Versión de Firmware')
                                    ->maxLength(255)
                                    ->helperText('Versión del firmware instalado'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('hardware_version')
                                    ->label('Versión de Hardware')
                                    ->maxLength(255)
                                    ->helperText('Versión del hardware'),
                                TextInput::make('accuracy_class')
                                    ->label('Clase de Precisión')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->step(0.1)
                                    ->helperText('Clase de precisión del medidor'),
                                TextInput::make('voltage_rating')
                                    ->label('Voltaje Nominal (V)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->suffix(' V')
                                    ->helperText('Voltaje nominal del medidor'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('current_rating')
                                    ->label('Corriente Nominal (A)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->suffix(' A')
                                    ->helperText('Corriente nominal del medidor'),
                                TextInput::make('phase_type')
                                    ->label('Tipo de Fase')
                                    ->maxLength(255)
                                    ->helperText('Tipo de fase (monofásico, trifásico, etc.)'),
                                TextInput::make('connection_type')
                                    ->label('Tipo de Conexión')
                                    ->maxLength(255)
                                    ->helperText('Tipo de conexión del medidor'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Ubicación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Textarea::make('location_address')
                                    ->label('Dirección de Ubicación')
                                    ->rows(3)
                                    ->maxLength(65535)
                                    ->helperText('Dirección donde está ubicado'),
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
                                    ->helperText('Cuándo se instaló el medidor'),
                                DatePicker::make('commissioning_date')
                                    ->label('Fecha de Puesta en Marcha')
                                    ->helperText('Cuándo se puso en marcha'),
                                DatePicker::make('last_calibration_date')
                                    ->label('Última Calibración')
                                    ->helperText('Cuándo se calibró por última vez'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('next_calibration_date')
                                    ->label('Próxima Calibración')
                                    ->helperText('Cuándo se debe calibrar'),
                                DatePicker::make('warranty_expiry_date')
                                    ->label('Fin de Garantía')
                                    ->helperText('Cuándo termina la garantía'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Características del Medidor')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_smart_meter')
                                    ->label('Medidor Inteligente')
                                    ->onIcon('heroicon-s-cpu-chip')
                                    ->offIcon('heroicon-s-cpu-chip')
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->helperText('¿Es un medidor inteligente?'),
                                Toggle::make('has_remote_reading')
                                    ->label('Lectura Remota')
                                    ->onIcon('heroicon-s-signal')
                                    ->offIcon('heroicon-s-signal')
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->helperText('¿Tiene capacidad de lectura remota?'),
                                Toggle::make('has_two_way_communication')
                                    ->label('Comunicación Bidireccional')
                                    ->onIcon('heroicon-s-arrow-path')
                                    ->offIcon('heroicon-s-arrow-path')
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->helperText('¿Tiene comunicación bidireccional?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('communication_protocol')
                                    ->label('Protocolo de Comunicación')
                                    ->maxLength(255)
                                    ->helperText('Protocolo usado para comunicación'),
                                TextInput::make('communication_frequency')
                                    ->label('Frecuencia de Comunicación')
                                    ->maxLength(255)
                                    ->helperText('Frecuencia de comunicación'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('data_logging_interval')
                                    ->label('Intervalo de Registro de Datos')
                                    ->maxLength(255)
                                    ->helperText('Intervalo para registrar datos'),
                                TextInput::make('data_retention_days')
                                    ->label('Retención de Datos (días)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(3650)
                                    ->step(1)
                                    ->helperText('Días que se retienen los datos'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Especificaciones Técnicas Detalladas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('measurement_range_min')
                                    ->label('Rango Mínimo de Medición')
                                    ->numeric()
                                    ->step(0.01)
                                    ->helperText('Valor mínimo que puede medir'),
                                TextInput::make('measurement_range_max')
                                    ->label('Rango Máximo de Medición')
                                    ->numeric()
                                    ->step(0.01)
                                    ->helperText('Valor máximo que puede medir'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('measurement_unit')
                                    ->label('Unidad de Medición')
                                    ->maxLength(255)
                                    ->helperText('Unidad de medida (kWh, m³, etc.)'),
                                TextInput::make('pulse_constant')
                                    ->label('Constante de Pulso')
                                    ->numeric()
                                    ->step(0.01)
                                    ->helperText('Constante de pulso del medidor'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('pulse_unit')
                                    ->label('Unidad de Pulso')
                                    ->maxLength(255)
                                    ->helperText('Unidad del pulso'),
                                TextInput::make('voltage_unit')
                                    ->label('Unidad de Voltaje')
                                    ->maxLength(255)
                                    ->helperText('Unidad de voltaje (V, kV)'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('current_unit')
                                    ->label('Unidad de Corriente')
                                    ->maxLength(255)
                                    ->helperText('Unidad de corriente (A, kA)'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración y Documentación')
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
                        RichEditor::make('calibration_requirements')
                            ->label('Requisitos de Calibración')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Requisitos específicos de calibración'),
                        RichEditor::make('maintenance_requirements')
                            ->label('Requisitos de Mantenimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Requisitos de mantenimiento'),
                        RichEditor::make('safety_features')
                            ->label('Características de Seguridad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Características de seguridad del medidor'),
                    ])
                    ->collapsible(),

                Section::make('Configuración Avanzada')
                    ->schema([
                        KeyValue::make('meter_features')
                            ->label('Características del Medidor')
                            ->keyLabel('Característica')
                            ->valueLabel('Valor')
                            ->helperText('Características específicas del medidor'),
                        KeyValue::make('communication_settings')
                            ->label('Configuración de Comunicación')
                            ->keyLabel('Configuración')
                            ->valueLabel('Valor')
                            ->helperText('Configuración de comunicación'),
                        KeyValue::make('alarm_settings')
                            ->label('Configuración de Alarmas')
                            ->keyLabel('Alarma')
                            ->valueLabel('Valor')
                            ->helperText('Configuración de alarmas'),
                        KeyValue::make('data_formats')
                            ->label('Formatos de Datos')
                            ->keyLabel('Formato')
                            ->valueLabel('Valor')
                            ->helperText('Formatos de datos soportados'),
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
                TextColumn::make('meter_number')
                    ->label('Número de Medidor')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),
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
                        'primary' => 'smart_meter',
                        'success' => 'digital_meter',
                        'warning' => 'analog_meter',
                        'danger' => 'prepaid_meter',
                        'info' => 'postpaid_meter',
                        'secondary' => 'bi_directional',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyMeter::getMeterTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'maintenance',
                        'danger' => 'faulty',
                        'secondary' => 'inactive',
                        'gray' => 'decommissioned',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'maintenance' => 'Mantenimiento',
                        'faulty' => 'Defectuoso',
                        'replaced' => 'Reemplazado',
                        'decommissioned' => 'Desmantelado',
                        'calibrating' => 'Calibrando',
                        default => $state,
                    }),
                BadgeColumn::make('meter_category')
                    ->label('Categoría')
                    ->colors([
                        'primary' => 'electricity',
                        'success' => 'water',
                        'warning' => 'gas',
                        'danger' => 'heat',
                        'info' => 'steam',
                        'secondary' => 'compressed_air',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyMeter::getMeterCategories()[$state] ?? $state),
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
                ToggleColumn::make('is_smart_meter')
                    ->label('Medidor Inteligente'),
                ToggleColumn::make('has_remote_reading')
                    ->label('Lectura Remota'),
                ToggleColumn::make('has_two_way_communication')
                    ->label('Comunicación Bidireccional'),
                TextColumn::make('installation_date')
                    ->label('Instalación')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('next_calibration_date')
                    ->label('Próxima Calibración')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state < now() => 'danger',
                        $state <= now()->addDays(30) => 'warning',
                        default => 'success',
                    })
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
                SelectFilter::make('meter_category')
                    ->label('Categoría del Medidor')
                    ->options(EnergyMeter::getMeterCategories())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergyMeter::getStatuses())
                    ->multiple(),
                Filter::make('active_meters')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active'))
                    ->label('Solo Activos'),
                Filter::make('smart_meters')
                    ->query(fn (Builder $query): Builder => $query->where('is_smart_meter', true))
                    ->label('Solo Medidores Inteligentes'),
                Filter::make('remote_reading')
                    ->query(fn (Builder $query): Builder => $query->where('has_remote_reading', true))
                    ->label('Solo con Lectura Remota'),
                Filter::make('two_way_communication')
                    ->query(fn (Builder $query): Builder => $query->where('has_two_way_communication', true))
                    ->label('Solo con Comunicación Bidireccional'),
                Filter::make('high_accuracy')
                    ->query(fn (Builder $query): Builder => $query->where('accuracy_class', '<=', 1.0))
                    ->label('Alta Precisión'),
                Filter::make('low_accuracy')
                    ->query(fn (Builder $query): Builder => $query->where('accuracy_class', '>', 2.0))
                    ->label('Baja Precisión (>2.0)'),
                Filter::make('needs_calibration')
                    ->query(fn (Builder $query): Builder => $query->where('next_calibration_date', '<=', now()->addDays(30)))
                    ->label('Necesita Calibración (≤30 días)'),
                Filter::make('warranty_expired')
                    ->query(fn (Builder $query): Builder => $query->where('warranty_expiry_date', '<', now()))
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
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EnergyMeter $record) => $record->status !== 'active')
                    ->action(function (EnergyMeter $record) {
                        $record->update(['status' => 'active']);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (EnergyMeter $record) => $record->status === 'active')
                    ->action(function (EnergyMeter $record) {
                        $record->update(['status' => 'inactive']);
                    }),
                Tables\Actions\Action::make('enable_remote_reading')
                    ->label('Habilitar Lectura Remota')
                    ->icon('heroicon-o-signal')
                    ->color('success')
                    ->visible(fn (EnergyMeter $record) => !$record->has_remote_reading)
                    ->action(function (EnergyMeter $record) {
                        $record->update(['has_remote_reading' => true]);
                    }),
                Tables\Actions\Action::make('enable_two_way_communication')
                    ->label('Habilitar Comunicación Bidireccional')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (EnergyMeter $record) => !$record->has_two_way_communication)
                    ->action(function (EnergyMeter $record) {
                        $record->update(['has_two_way_communication' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyMeter $record) {
                        $newRecord = $record->replicate();
                        $newRecord->meter_number = 'COPY-' . $record->meter_number;
                        $newRecord->name = $record->name . ' (Copia)';
                        $newRecord->status = 'inactive';
                        $newRecord->serial_number = $record->serial_number . '_COPY';
                        $newRecord->installation_date = now();
                        $newRecord->commissioning_date = null;
                        $newRecord->last_calibration_date = null;
                        $newRecord->next_calibration_date = null;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate_all')
                        ->label('Activar Todos')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'active']);
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
                    Tables\Actions\BulkAction::make('enable_remote_reading_all')
                        ->label('Habilitar Lectura Remota')
                        ->icon('heroicon-o-signal')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['has_remote_reading' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('enable_two_way_communication_all')
                        ->label('Habilitar Comunicación Bidireccional')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['has_two_way_communication' => true]);
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
        
        $faultyCount = static::getModel()::where('status', 'faulty')->count();
        
        if ($faultyCount > 0) {
            return 'danger';
        }
        
        return 'success';
    }
}
