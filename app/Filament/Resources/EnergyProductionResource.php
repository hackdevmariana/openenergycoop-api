<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyProductionResource\Pages;
use App\Models\EnergyProduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnergyProductionResource extends Resource
{
    protected static ?string $model = EnergyProduction::class;

    protected static ?string $navigationIcon = 'heroicon-o-sun';
    
    protected static ?string $navigationLabel = 'Producción Energética';
    
    protected static ?string $modelLabel = 'Producción Energética';
    
    protected static ?string $pluralModelLabel = 'Producciones Energéticas';
    
    protected static ?string $navigationGroup = 'Energía y Comercio';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                Forms\Components\Select::make('user_asset_id')
                                    ->label('Activo del Usuario')
                                    ->relationship('userAsset', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('system_id')
                                    ->label('ID del Sistema')
                                    ->maxLength(255),
                                
                                Forms\Components\DateTimePicker::make('production_datetime')
                                    ->label('Fecha y Hora de Producción')
                                    ->required(),
                                
                                Forms\Components\Select::make('period_type')
                                    ->label('Tipo de Período')
                                    ->options([
                                        'instant' => 'Instantáneo',
                                        'hourly' => 'Por Hora',
                                        'daily' => 'Diario',
                                        'monthly' => 'Mensual',
                                        'annual' => 'Anual'
                                    ])
                                    ->required(),
                            ]),
                        
                        Forms\Components\Select::make('energy_source')
                            ->label('Fuente de Energía')
                            ->options([
                                'solar_pv' => 'Solar Fotovoltaica',
                                'solar_thermal' => 'Solar Térmica',
                                'wind' => 'Eólica',
                                'hydro' => 'Hidroeléctrica',
                                'biomass' => 'Biomasa',
                                'geothermal' => 'Geotérmica',
                                'biogas' => 'Biogás',
                                'combined' => 'Combinada'
                            ])
                            ->required(),
                    ]),
                
                Forms\Components\Section::make('Mediciones de Producción')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('production_kwh')
                                    ->label('Producción')
                                    ->numeric()
                                    ->suffix('kWh')
                                    ->required(),
                                
                                Forms\Components\TextInput::make('peak_power_kw')
                                    ->label('Potencia Pico')
                                    ->numeric()
                                    ->suffix('kW'),
                                
                                Forms\Components\TextInput::make('average_power_kw')
                                    ->label('Potencia Promedio')
                                    ->numeric()
                                    ->suffix('kW'),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Distribución de Energía')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('self_consumption_kwh')
                                    ->label('Autoconsumo')
                                    ->numeric()
                                    ->suffix('kWh')
                                    ->default(0),
                                
                                Forms\Components\TextInput::make('grid_injection_kwh')
                                    ->label('Inyección a Red')
                                    ->numeric()
                                    ->suffix('kWh')
                                    ->default(0),
                                
                                Forms\Components\TextInput::make('storage_charge_kwh')
                                    ->label('Carga de Almacenamiento')
                                    ->numeric()
                                    ->suffix('kWh')
                                    ->default(0),
                                
                                Forms\Components\TextInput::make('curtailed_kwh')
                                    ->label('Energía Vertida')
                                    ->numeric()
                                    ->suffix('kWh')
                                    ->default(0),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Eficiencia del Sistema')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('system_efficiency')
                                    ->label('Eficiencia del Sistema')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),
                                
                                Forms\Components\TextInput::make('inverter_efficiency')
                                    ->label('Eficiencia del Inversor')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),
                                
                                Forms\Components\TextInput::make('performance_ratio')
                                    ->label('Performance Ratio')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),
                                
                                Forms\Components\TextInput::make('capacity_factor')
                                    ->label('Factor de Capacidad')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Condiciones Ambientales')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('irradiance_w_m2')
                                    ->label('Irradiancia')
                                    ->numeric()
                                    ->suffix('W/m²'),
                                
                                Forms\Components\TextInput::make('ambient_temperature')
                                    ->label('Temperatura Ambiente')
                                    ->numeric()
                                    ->suffix('°C'),
                                
                                Forms\Components\TextInput::make('wind_speed_ms')
                                    ->label('Velocidad del Viento')
                                    ->numeric()
                                    ->suffix('m/s'),
                                
                                Forms\Components\TextInput::make('humidity_percentage')
                                    ->label('Humedad')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Información Económica')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('feed_in_tariff_eur_kwh')
                                    ->label('Tarifa de Inyección')
                                    ->numeric()
                                    ->prefix('€'),
                                
                                Forms\Components\TextInput::make('market_price_eur_kwh')
                                    ->label('Precio de Mercado')
                                    ->numeric()
                                    ->prefix('€'),
                                
                                Forms\Components\TextInput::make('revenue_eur')
                                    ->label('Ingresos')
                                    ->numeric()
                                    ->prefix('€'),
                                
                                Forms\Components\TextInput::make('savings_eur')
                                    ->label('Ahorros')
                                    ->numeric()
                                    ->prefix('€'),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Sostenibilidad')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('co2_avoided_kg')
                                    ->label('CO2 Evitado')
                                    ->numeric()
                                    ->suffix('kg'),
                                
                                Forms\Components\TextInput::make('renewable_percentage')
                                    ->label('% Renovable')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(100),
                                
                                Forms\Components\Select::make('operational_status')
                                    ->label('Estado Operativo')
                                    ->options([
                                        'online' => 'En Línea',
                                        'offline' => 'Fuera de Línea',
                                        'maintenance' => 'Mantenimiento',
                                        'error' => 'Error',
                                        'curtailed' => 'Limitado',
                                        'standby' => 'En Espera'
                                    ])
                                    ->default('online'),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Calidad de Datos')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('data_quality')
                                    ->label('Calidad de Datos')
                                    ->options([
                                        'measured' => 'Medido',
                                        'estimated' => 'Estimado',
                                        'interpolated' => 'Interpolado',
                                        'forecasted' => 'Pronosticado'
                                    ])
                                    ->default('measured'),
                                
                                Forms\Components\Toggle::make('is_validated')
                                    ->label('Datos Validados')
                                    ->default(true),
                            ]),
                        
                        Forms\Components\Textarea::make('validation_notes')
                            ->label('Notas de Validación')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('production_datetime')
                    ->label('Fecha y Hora')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('energy_source')
                    ->label('Fuente')
                    ->colors([
                        'warning' => 'solar_pv',
                        'success' => 'solar_thermal',
                        'info' => 'wind',
                        'primary' => 'hydro',
                        'secondary' => 'biomass',
                        'danger' => 'geothermal',
                        'gray' => 'biogas',
                        'dark' => 'combined',
                    ])
                    ->icons([
                        'heroicon-o-sun' => 'solar_pv',
                        'heroicon-o-fire' => 'solar_thermal',
                        'heroicon-s-arrow-trending-up' => 'wind',
                        'heroicon-o-beaker' => 'hydro',
                        'heroicon-o-leaf' => 'biomass',
                        'heroicon-o-bolt' => 'geothermal',
                        'heroicon-o-cloud' => 'biogas',
                        'heroicon-o-puzzle-piece' => 'combined',
                    ]),
                
                Tables\Columns\BadgeColumn::make('period_type')
                    ->label('Período')
                    ->colors([
                        'danger' => 'instant',
                        'warning' => 'hourly',
                        'success' => 'daily',
                        'primary' => 'monthly',
                        'secondary' => 'annual',
                    ]),
                
                Tables\Columns\TextColumn::make('production_kwh')
                    ->label('Producción')
                    ->suffix(' kWh')
                    ->numeric(2)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('system_efficiency')
                    ->label('Eficiencia')
                    ->suffix('%')
                    ->numeric(1)
                    ->sortable()
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 75 ? 'warning' : 'danger')),
                
                Tables\Columns\TextColumn::make('revenue_eur')
                    ->label('Ingresos')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('co2_avoided_kg')
                    ->label('CO2 Evitado')
                    ->suffix(' kg')
                    ->numeric(1)
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('operational_status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'online',
                        'secondary' => 'offline',
                        'warning' => 'maintenance',
                        'danger' => 'error',
                        'info' => 'curtailed',
                        'gray' => 'standby',
                    ]),
                
                Tables\Columns\BadgeColumn::make('data_quality')
                    ->label('Calidad')
                    ->colors([
                        'success' => 'measured',
                        'warning' => 'estimated',
                        'info' => 'interpolated',
                        'secondary' => 'forecasted',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('energy_source')
                    ->label('Fuente de Energía')
                    ->options([
                        'solar_pv' => 'Solar Fotovoltaica',
                        'solar_thermal' => 'Solar Térmica',
                        'wind' => 'Eólica',
                        'hydro' => 'Hidroeléctrica',
                        'biomass' => 'Biomasa',
                        'geothermal' => 'Geotérmica',
                        'biogas' => 'Biogás',
                        'combined' => 'Combinada'
                    ]),
                
                Tables\Filters\SelectFilter::make('period_type')
                    ->label('Tipo de Período')
                    ->options([
                        'instant' => 'Instantáneo',
                        'hourly' => 'Por Hora',
                        'daily' => 'Diario',
                        'monthly' => 'Mensual',
                        'annual' => 'Anual'
                    ]),
                
                Tables\Filters\SelectFilter::make('operational_status')
                    ->label('Estado Operativo')
                    ->options([
                        'online' => 'En Línea',
                        'offline' => 'Fuera de Línea',
                        'maintenance' => 'Mantenimiento',
                        'error' => 'Error',
                        'curtailed' => 'Limitado',
                        'standby' => 'En Espera'
                    ]),
                
                Tables\Filters\Filter::make('high_production')
                    ->label('Alta Producción (>500 kWh)')
                    ->query(fn (Builder $query): Builder => $query->where('production_kwh', '>', 500)),
                
                Tables\Filters\Filter::make('high_efficiency')
                    ->label('Alta Eficiencia (>90%)')
                    ->query(fn (Builder $query): Builder => $query->where('system_efficiency', '>', 90)),
                
                Tables\Filters\Filter::make('renewable_only')
                    ->label('Solo Renovables (100%)')
                    ->query(fn (Builder $query): Builder => $query->where('renewable_percentage', 100)),
                
                Tables\Filters\Filter::make('this_month')
                    ->label('Este Mes')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('production_datetime', now()->month)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('maintenance')
                    ->label('Programar Mantenimiento')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->action(function (EnergyProduction $record) {
                        $record->update(['operational_status' => 'maintenance']);
                    })
                    ->visible(fn (EnergyProduction $record): bool => $record->operational_status === 'online'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('validate_data')
                        ->label('Validar Datos')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'is_validated' => true,
                                    'processed_at' => now(),
                                ]);
                            });
                        }),
                    
                    Tables\Actions\BulkAction::make('set_online')
                        ->label('Marcar como En Línea')
                        ->icon('heroicon-o-signal')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['operational_status' => 'online']);
                            });
                        }),
                ]),
            ])
            ->defaultSort('production_datetime', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds for real-time data
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
            'index' => Pages\ListEnergyProductions::route('/'),
            'create' => Pages\CreateEnergyProduction::route('/create'),
            'view' => Pages\ViewEnergyProduction::route('/{record}'),
            'edit' => Pages\EditEnergyProduction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'userAsset']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('operational_status', 'error')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('operational_status', 'error')->count() > 0 ? 'danger' : 'success';
    }
}