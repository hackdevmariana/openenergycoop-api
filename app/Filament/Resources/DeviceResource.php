<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'IoT & Dispositivos';

    protected static ?string $navigationLabel = 'Dispositivos';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Dispositivo';

    protected static ?string $pluralModelLabel = 'Dispositivos';

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
                                    ->placeholder('Ej: Contador Solar Principal'),

                                Select::make('type')
                                    ->label('Tipo')
                                    ->required()
                                    ->options([
                                        Device::TYPE_SMART_METER => 'Contador Inteligente',
                                        Device::TYPE_BATTERY => 'Batería',
                                        Device::TYPE_EV_CHARGER => 'Cargador EV',
                                        Device::TYPE_SOLAR_PANEL => 'Panel Solar',
                                        Device::TYPE_WIND_TURBINE => 'Turbina Eólica',
                                        Device::TYPE_HEAT_PUMP => 'Bomba de Calor',
                                        Device::TYPE_THERMOSTAT => 'Termostato',
                                        Device::TYPE_SMART_PLUG => 'Enchufe Inteligente',
                                        Device::TYPE_ENERGY_MONITOR => 'Monitor de Energía',
                                        Device::TYPE_GRID_CONNECTION => 'Conexión a Red',
                                        Device::TYPE_OTHER => 'Otro'
                                    ])
                                    ->searchable(),

                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('consumption_point_id')
                                    ->label('Punto de Consumo')
                                    ->numeric()
                                    ->placeholder('ID del punto de consumo'),

                                TextInput::make('model')
                                    ->label('Modelo')
                                    ->maxLength(255)
                                    ->placeholder('Ej: SmartMeter Pro X1'),

                                TextInput::make('manufacturer')
                                    ->label('Fabricante')
                                    ->maxLength(255)
                                    ->placeholder('Ej: EnergyTech'),

                                TextInput::make('serial_number')
                                    ->label('Número de Serie')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Ej: DEV-12345678'),

                                TextInput::make('firmware_version')
                                    ->label('Versión Firmware')
                                    ->maxLength(255)
                                    ->placeholder('Ej: 1.2.3'),

                                TextInput::make('location')
                                    ->label('Ubicación')
                                    ->maxLength(255)
                                    ->placeholder('Ej: Sala de máquinas'),

                                Toggle::make('active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->helperText('Indica si el dispositivo está funcionando'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración API')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('api_endpoint')
                                    ->label('Endpoint API')
                                    ->url()
                                    ->placeholder('https://api.dispositivo.com')
                                    ->helperText('URL para comunicación con el dispositivo'),

                                KeyValue::make('api_credentials')
                                    ->label('Credenciales API')
                                    ->keyLabel('Clave')
                                    ->valueLabel('Valor')
                                    ->helperText('Credenciales para autenticación con el dispositivo'),

                                KeyValue::make('device_config')
                                    ->label('Configuración del Dispositivo')
                                    ->keyLabel('Clave')
                                    ->valueLabel('Valor')
                                    ->helperText('Configuración específica del dispositivo'),

                                KeyValue::make('capabilities')
                                    ->label('Capacidades')
                                    ->keyLabel('Capacidad')
                                    ->valueLabel('Habilitada')
                                    ->helperText('Capacidades disponibles del dispositivo'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Estado y Comunicación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('last_communication')
                                    ->label('Última Comunicación')
                                    ->helperText('Cuándo se comunicó por última vez'),

                                Textarea::make('notes')
                                    ->label('Notas')
                                    ->rows(3)
                                    ->placeholder('Notas adicionales sobre el dispositivo'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
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

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => Device::TYPE_SMART_METER,
                        'success' => Device::TYPE_SOLAR_PANEL,
                        'warning' => Device::TYPE_BATTERY,
                        'danger' => Device::TYPE_GRID_CONNECTION,
                        'info' => Device::TYPE_ENERGY_MONITOR,
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        Device::TYPE_SMART_METER => 'Contador',
                        Device::TYPE_BATTERY => 'Batería',
                        Device::TYPE_EV_CHARGER => 'Cargador EV',
                        Device::TYPE_SOLAR_PANEL => 'Panel Solar',
                        Device::TYPE_WIND_TURBINE => 'Turbina',
                        Device::TYPE_HEAT_PUMP => 'Bomba Calor',
                        Device::TYPE_THERMOSTAT => 'Termostato',
                        Device::TYPE_SMART_PLUG => 'Enchufe',
                        Device::TYPE_ENERGY_MONITOR => 'Monitor',
                        Device::TYPE_GRID_CONNECTION => 'Red',
                        Device::TYPE_OTHER => 'Otro',
                        default => $state
                    }),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

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

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'online',
                        'warning' => 'offline',
                        'danger' => 'inactive',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'online' => 'En Línea',
                        'offline' => 'Offline',
                        'inactive' => 'Inactivo',
                        default => $state
                    }),

                ToggleColumn::make('active')
                    ->label('Activo')
                    ->sortable(),

                TextColumn::make('last_communication')
                    ->label('Última Comunicación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->diffForHumans() : 'Nunca'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        Device::TYPE_SMART_METER => 'Contador Inteligente',
                        Device::TYPE_BATTERY => 'Batería',
                        Device::TYPE_EV_CHARGER => 'Cargador EV',
                        Device::TYPE_SOLAR_PANEL => 'Panel Solar',
                        Device::TYPE_WIND_TURBINE => 'Turbina Eólica',
                        Device::TYPE_HEAT_PUMP => 'Bomba de Calor',
                        Device::TYPE_THERMOSTAT => 'Termostato',
                        Device::TYPE_SMART_PLUG => 'Enchufe Inteligente',
                        Device::TYPE_ENERGY_MONITOR => 'Monitor de Energía',
                        Device::TYPE_GRID_CONNECTION => 'Conexión a Red',
                        Device::TYPE_OTHER => 'Otro'
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'online' => 'En Línea',
                        'offline' => 'Offline',
                        'inactive' => 'Inactivo'
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['values'])) {
                            if (in_array('online', $data['values'])) {
                                $query->whereNotNull('last_communication')
                                      ->where('last_communication', '>=', now()->subMinutes(5));
                            }
                            if (in_array('offline', $data['values'])) {
                                $query->where(function ($q) {
                                    $q->whereNull('last_communication')
                                      ->orWhere('last_communication', '<', now()->subMinutes(5));
                                });
                            }
                            if (in_array('inactive', $data['values'])) {
                                $query->where('active', false);
                            }
                        }
                        return $query;
                    }),

                SelectFilter::make('manufacturer')
                    ->label('Fabricante')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('active')
                    ->label('Solo Activos')
                    ->query(fn (Builder $query): Builder => $query->where('active', true))
                    ->toggle(),

                Filter::make('recent_communication')
                    ->label('Comunicación Reciente')
                    ->query(fn (Builder $query): Builder => $query->where('last_communication', '>=', now()->subHours(24)))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),

                Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Device $record) => $record->activate())
                    ->visible(fn (Device $record) => !$record->active),

                Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Device $record) => $record->deactivate())
                    ->visible(fn (Device $record) => $record->active),

                Action::make('update_communication')
                    ->label('Actualizar Comunicación')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(fn (Device $record) => $record->updateCommunication())
                    ->visible(fn (Device $record) => $record->active),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('activate_selected')
                        ->label('Activar Seleccionados')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->activate();
                        }),

                    BulkAction::make('deactivate_selected')
                        ->label('Desactivar Seleccionados')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->deactivate();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('active', true)->count() > 0 ? 'success' : 'danger';
    }
}
