<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyConsumptionResource\Pages;
use App\Models\EnergyConsumption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnergyConsumptionResource extends Resource
{
    protected static ?string $model = EnergyConsumption::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Consumo Energético';
    
    protected static ?string $modelLabel = 'Consumo Energético';
    
    protected static ?string $pluralModelLabel = 'Consumos Energéticos';
    
    protected static ?string $navigationGroup = 'Energía y Comercio';
    
    protected static ?int $navigationSort = 2;

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
                                
                                Forms\Components\Select::make('energy_contract_id')
                                    ->label('Contrato Energético')
                                    ->relationship('energyContract', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('meter_id')
                                    ->label('ID del Medidor')
                                    ->maxLength(255),
                                
                                Forms\Components\DateTimePicker::make('measurement_datetime')
                                    ->label('Fecha y Hora de Medición')
                                    ->required(),
                                
                                Forms\Components\Select::make('period_type')
                                    ->label('Tipo de Período')
                                    ->options([
                                        'instant' => 'Instantáneo',
                                        'hourly' => 'Por Hora',
                                        'daily' => 'Diario',
                                        'monthly' => 'Mensual',
                                        'billing_period' => 'Período de Facturación'
                                    ])
                                    ->required(),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Mediciones Energéticas')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('consumption_kwh')
                                    ->label('Consumo')
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
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('peak_hours_consumption')
                                    ->label('Consumo Horas Punta')
                                    ->numeric()
                                    ->suffix('kWh'),
                                
                                Forms\Components\TextInput::make('standard_hours_consumption')
                                    ->label('Consumo Horas Llano')
                                    ->numeric()
                                    ->suffix('kWh'),
                                
                                Forms\Components\TextInput::make('valley_hours_consumption')
                                    ->label('Consumo Horas Valle')
                                    ->numeric()
                                    ->suffix('kWh'),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Información Tarifaria')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('tariff_type')
                                    ->label('Tipo de Tarifa')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('unit_price_eur_kwh')
                                    ->label('Precio por kWh')
                                    ->numeric()
                                    ->prefix('€'),
                                
                                Forms\Components\TextInput::make('total_cost_eur')
                                    ->label('Costo Total')
                                    ->numeric()
                                    ->prefix('€'),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Sostenibilidad')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('renewable_percentage')
                                    ->label('% Energía Renovable')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0),
                                
                                Forms\Components\TextInput::make('grid_consumption_kwh')
                                    ->label('Consumo de Red')
                                    ->numeric()
                                    ->suffix('kWh')
                                    ->default(0),
                                
                                Forms\Components\TextInput::make('self_consumption_kwh')
                                    ->label('Autoconsumo')
                                    ->numeric()
                                    ->suffix('kWh')
                                    ->default(0),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('estimated_co2_emissions_kg')
                                    ->label('Emisiones CO2 Estimadas')
                                    ->numeric()
                                    ->suffix('kg'),
                                
                                Forms\Components\TextInput::make('efficiency_score')
                                    ->label('Puntuación de Eficiencia')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Calidad de Datos')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('data_quality')
                                    ->label('Calidad de Datos')
                                    ->options([
                                        'excellent' => 'Excelente',
                                        'good' => 'Buena',
                                        'fair' => 'Regular',
                                        'poor' => 'Pobre',
                                        'estimated' => 'Estimada'
                                    ])
                                    ->default('good'),
                                
                                Forms\Components\Toggle::make('is_estimated')
                                    ->label('Datos Estimados')
                                    ->default(false),
                            ]),
                        
                        Forms\Components\Toggle::make('consumption_alert_triggered')
                            ->label('Alerta de Consumo Activada')
                            ->default(false),
                        
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
                
                Tables\Columns\TextColumn::make('measurement_datetime')
                    ->label('Fecha y Hora')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('period_type')
                    ->label('Período')
                    ->colors([
                        'danger' => 'instant',
                        'warning' => 'hourly',
                        'success' => 'daily',
                        'primary' => 'monthly',
                        'secondary' => 'billing_period',
                    ]),
                
                Tables\Columns\TextColumn::make('consumption_kwh')
                    ->label('Consumo')
                    ->suffix(' kWh')
                    ->numeric(2)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('renewable_percentage')
                    ->label('% Renovable')
                    ->suffix('%')
                    ->numeric(1)
                    ->sortable()
                    ->color(fn ($state) => $state >= 50 ? 'success' : ($state >= 25 ? 'warning' : 'danger')),
                
                Tables\Columns\TextColumn::make('total_cost_eur')
                    ->label('Costo')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('efficiency_score')
                    ->label('Eficiencia')
                    ->numeric(1)
                    ->sortable()
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
                
                Tables\Columns\BadgeColumn::make('data_quality')
                    ->label('Calidad')
                    ->colors([
                        'success' => 'excellent',
                        'info' => 'good',
                        'warning' => 'fair',
                        'danger' => 'poor',
                        'secondary' => 'estimated',
                    ]),
                
                Tables\Columns\IconColumn::make('consumption_alert_triggered')
                    ->label('Alerta')
                    ->boolean()
                    ->color('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period_type')
                    ->label('Tipo de Período')
                    ->options([
                        'instant' => 'Instantáneo',
                        'hourly' => 'Por Hora',
                        'daily' => 'Diario',
                        'monthly' => 'Mensual',
                        'billing_period' => 'Período de Facturación'
                    ]),
                
                Tables\Filters\SelectFilter::make('data_quality')
                    ->label('Calidad de Datos')
                    ->options([
                        'excellent' => 'Excelente',
                        'good' => 'Buena',
                        'fair' => 'Regular',
                        'poor' => 'Pobre',
                        'estimated' => 'Estimada'
                    ]),
                
                Tables\Filters\Filter::make('high_consumption')
                    ->label('Alto Consumo (>1000 kWh)')
                    ->query(fn (Builder $query): Builder => $query->where('consumption_kwh', '>', 1000)),
                
                Tables\Filters\Filter::make('renewable_energy')
                    ->label('Con Energía Renovable (>50%)')
                    ->query(fn (Builder $query): Builder => $query->where('renewable_percentage', '>', 50)),
                
                Tables\Filters\Filter::make('alerts')
                    ->label('Con Alertas')
                    ->query(fn (Builder $query): Builder => $query->where('consumption_alert_triggered', true)),
                
                Tables\Filters\Filter::make('this_month')
                    ->label('Este Mes')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('measurement_datetime', now()->month)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
                ]),
            ])
            ->defaultSort('measurement_datetime', 'desc')
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
            'index' => Pages\ListEnergyConsumptions::route('/'),
            'create' => Pages\CreateEnergyConsumption::route('/create'),
            'view' => Pages\ViewEnergyConsumption::route('/{record}'),
            'edit' => Pages\EditEnergyConsumption::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'energyContract']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('consumption_alert_triggered', true)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('consumption_alert_triggered', true)->count() > 0 ? 'danger' : 'success';
    }
}