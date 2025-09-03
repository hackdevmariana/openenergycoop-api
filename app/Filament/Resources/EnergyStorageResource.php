<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyStorageResource\Pages;
use App\Models\EnergyStorage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class EnergyStorageResource extends Resource
{
    protected static ?string $model = EnergyStorage::class;

    protected static ?string $navigationIcon = 'heroicon-o-battery-100';
    
    protected static ?string $navigationLabel = 'Almacenamiento Energético';
    
    protected static ?string $modelLabel = 'Sistema de Almacenamiento';
    
    protected static ?string $pluralModelLabel = 'Sistemas de Almacenamiento';
    
    protected static ?string $navigationGroup = 'Energía y Comercio';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Información del Sistema')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información Básica')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('Propietario')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        
                                        Forms\Components\Select::make('provider_id')
                                            ->label('Proveedor')
                                            ->relationship('provider', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ]),
                                
                                Forms\Components\TextInput::make('system_id')
                                    ->label('ID del Sistema')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return 'STOR-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
                                    }),
                                
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre del Sistema')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\Select::make('storage_type')
                                            ->label('Tipo de Almacenamiento')
                                            ->options([
                                                'battery_lithium' => 'Batería de Litio',
                                                'battery_lead_acid' => 'Batería de Plomo',
                                                'battery_flow' => 'Batería de Flujo',
                                                'pumped_hydro' => 'Bombeo Hidráulico',
                                                'compressed_air' => 'Aire Comprimido',
                                                'flywheel' => 'Volante de Inercia',
                                                'thermal' => 'Almacenamiento Térmico',
                                                'hydrogen' => 'Hidrógeno'
                                            ])
                                            ->required(),
                                    ]),
                                
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Especificaciones Técnicas')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('manufacturer')
                                            ->label('Fabricante')
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('model')
                                            ->label('Modelo')
                                            ->maxLength(255),
                                    ]),
                                
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('capacity_kwh')
                                            ->label('Capacidad Total')
                                            ->numeric()
                                            ->suffix('kWh')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('usable_capacity_kwh')
                                            ->label('Capacidad Utilizable')
                                            ->numeric()
                                            ->suffix('kWh')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('round_trip_efficiency')
                                            ->label('Eficiencia Round-Trip')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100),
                                    ]),
                                
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('max_charge_power_kw')
                                            ->label('Potencia Máxima de Carga')
                                            ->numeric()
                                            ->suffix('kW')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('max_discharge_power_kw')
                                            ->label('Potencia Máxima de Descarga')
                                            ->numeric()
                                            ->suffix('kW')
                                            ->required(),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Estado Actual')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('current_charge_kwh')
                                            ->label('Carga Actual')
                                            ->numeric()
                                            ->suffix('kWh')
                                            ->default(0),
                                        
                                        Forms\Components\TextInput::make('charge_level_percentage')
                                            ->label('Nivel de Carga')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(0),
                                        
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'online' => 'En Línea',
                                                'offline' => 'Fuera de Línea',
                                                'charging' => 'Cargando',
                                                'discharging' => 'Descargando',
                                                'standby' => 'En Espera',
                                                'maintenance' => 'Mantenimiento',
                                                'error' => 'Error'
                                            ])
                                            ->default('offline'),
                                    ]),
                                
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('cycle_count')
                                            ->label('Número de Ciclos')
                                            ->numeric()
                                            ->default(0),
                                        
                                        Forms\Components\TextInput::make('current_health_percentage')
                                            ->label('Salud Actual del Sistema')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(100),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Configuración Operativa')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('min_charge_level')
                                            ->label('Nivel Mínimo de Carga')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(10),
                                        
                                        Forms\Components\TextInput::make('max_charge_level')
                                            ->label('Nivel Máximo de Carga')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(95),
                                    ]),
                                
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('auto_management_enabled')
                                            ->label('Gestión Automática Habilitada')
                                            ->default(true),
                                        
                                        Forms\Components\Toggle::make('grid_tied')
                                            ->label('Conectado a Red')
                                            ->default(true),
                                    ]),
                                
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('remote_monitoring')
                                            ->label('Monitorización Remota')
                                            ->default(true),
                                        
                                        Forms\Components\Toggle::make('remote_control')
                                            ->label('Control Remoto')
                                            ->default(false),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Información Económica')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('installation_cost')
                                            ->label('Costo de Instalación')
                                            ->numeric()
                                            ->prefix('€'),
                                        
                                        Forms\Components\TextInput::make('maintenance_cost_annual')
                                            ->label('Costo Mantenimiento Anual')
                                            ->numeric()
                                            ->prefix('€'),
                                        
                                        Forms\Components\TextInput::make('replacement_cost')
                                            ->label('Costo de Reemplazo')
                                            ->numeric()
                                            ->prefix('€'),
                                    ]),
                                
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('insurance_value')
                                            ->label('Valor del Seguro')
                                            ->numeric()
                                            ->prefix('€'),
                                        
                                        Forms\Components\DatePicker::make('insurance_expiry_date')
                                            ->label('Fecha de Vencimiento del Seguro'),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Mantenimiento y Garantías')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('last_maintenance_date')
                                            ->label('Última Mantención'),
                                        
                                        Forms\Components\DatePicker::make('next_maintenance_date')
                                            ->label('Próxima Mantención'),
                                    ]),
                                
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('maintenance_interval_months')
                                            ->label('Intervalo de Mantenimiento')
                                            ->numeric()
                                            ->suffix('meses')
                                            ->default(12),
                                        
                                        Forms\Components\DatePicker::make('warranty_end_date')
                                            ->label('Fin de Garantía'),
                                    ]),
                                
                                Forms\Components\TextInput::make('warranty_provider')
                                    ->label('Proveedor de Garantía')
                                    ->maxLength(255),
                                
                                Forms\Components\Textarea::make('maintenance_notes')
                                    ->label('Notas de Mantenimiento')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('system_id')
                    ->label('ID Sistema')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->limit(25),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Propietario')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('storage_type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'battery_lithium',
                        'warning' => 'battery_lead_acid',
                        'info' => 'battery_flow',
                        'primary' => 'pumped_hydro',
                        'secondary' => 'compressed_air',
                        'danger' => 'flywheel',
                        'gray' => 'thermal',
                        'dark' => 'hydrogen',
                    ]),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'online',
                        'secondary' => 'offline',
                        'info' => 'charging',
                        'warning' => 'discharging',
                        'gray' => 'standby',
                        'danger' => 'maintenance',
                        'dark' => 'error',
                    ]),
                
                Tables\Columns\TextColumn::make('capacity_kwh')
                    ->label('Capacidad')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('charge_level_percentage')
                    ->label('Nivel de Carga')
                    ->suffix('%')
                    ->numeric()
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger'))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_health_percentage')
                    ->label('Salud del Sistema')
                    ->suffix('%')
                    ->numeric()
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger'))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('round_trip_efficiency')
                    ->label('Eficiencia')
                    ->suffix('%')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('storage_type')
                    ->label('Tipo de Almacenamiento')
                    ->options([
                        'battery_lithium' => 'Batería de Litio',
                        'battery_lead_acid' => 'Batería de Plomo',
                        'battery_flow' => 'Batería de Flujo',
                        'pumped_hydro' => 'Bombeo Hidráulico',
                        'compressed_air' => 'Aire Comprimido',
                        'flywheel' => 'Volante de Inercia',
                        'thermal' => 'Almacenamiento Térmico',
                        'hydrogen' => 'Hidrógeno'
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'online' => 'En Línea',
                        'offline' => 'Fuera de Línea',
                        'charging' => 'Cargando',
                        'discharging' => 'Descargando',
                        'standby' => 'En Espera',
                        'maintenance' => 'Mantenimiento',
                        'error' => 'Error'
                    ]),
                
                Tables\Filters\Filter::make('high_efficiency')
                    ->label('Alta Eficiencia (>90%)')
                    ->query(fn (Builder $query): Builder => $query->where('round_trip_efficiency', '>', 90)),
                
                Tables\Filters\Filter::make('needs_maintenance')
                    ->label('Requiere Mantenimiento')
                    ->query(fn (Builder $query): Builder => $query->where('next_maintenance_date', '<=', now()->addDays(30))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('charge')
                    ->label('Cargar')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->action(function (EnergyStorage $record) {
                        $record->update(['status' => 'charging']);
                    })
                    ->visible(fn (EnergyStorage $record): bool => in_array($record->status, ['online', 'standby'])),
                
                Tables\Actions\Action::make('discharge')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->action(function (EnergyStorage $record) {
                        $record->update(['status' => 'discharging']);
                    })
                    ->visible(fn (EnergyStorage $record): bool => in_array($record->status, ['online', 'standby'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('set_maintenance')
                        ->label('Programar Mantenimiento')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'maintenance',
                                    'next_maintenance_date' => now()->addMonths($record->maintenance_interval_months ?? 12)
                                ]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información del Sistema')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('system_id')
                                    ->label('ID del Sistema')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nombre'),
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Propietario'),
                                Infolists\Components\TextEntry::make('provider.name')
                                    ->label('Proveedor'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Estado Actual')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('charge_level_percentage')
                                    ->label('Nivel de Carga')
                                    ->suffix('%'),
                                Infolists\Components\TextEntry::make('current_health_percentage')
                                    ->label('Salud del Sistema')
                                    ->suffix('%'),
                                Infolists\Components\TextEntry::make('cycle_count')
                                    ->label('Ciclos Completados'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Capacidades')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('capacity_kwh')
                                    ->label('Capacidad Total')
                                    ->suffix(' kWh'),
                                Infolists\Components\TextEntry::make('usable_capacity_kwh')
                                    ->label('Capacidad Utilizable')
                                    ->suffix(' kWh'),
                                Infolists\Components\TextEntry::make('round_trip_efficiency')
                                    ->label('Eficiencia Round-Trip')
                                    ->suffix('%'),
                            ]),
                    ]),
            ]);
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
            'index' => Pages\ListEnergyStorages::route('/'),
            'create' => Pages\CreateEnergyStorage::route('/create'),
            'view' => Pages\ViewEnergyStorage::route('/{record}'),
            'edit' => Pages\EditEnergyStorage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'provider']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'error')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 'error')->count() > 0 ? 'danger' : 'success';
    }
}