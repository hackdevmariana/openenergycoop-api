<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketPriceResource\Pages;
use App\Models\MarketPrice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketPriceResource extends Resource
{
    protected static ?string $model = MarketPrice::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    
    protected static ?string $navigationLabel = 'Precios de Mercado';
    
    protected static ?string $modelLabel = 'Precio de Mercado';
    
    protected static ?string $pluralModelLabel = 'Precios de Mercado';
    
    protected static ?string $navigationGroup = 'Energía y Comercio';
    
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Mercado')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('market_name')
                                    ->label('Nombre del Mercado')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ej: MIBEL, EPEX'),
                                
                                Forms\Components\TextInput::make('market_code')
                                    ->label('Código del Mercado')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('country')
                                    ->label('País')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('region')
                                    ->label('Región')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('zone')
                                    ->label('Zona')
                                    ->maxLength(255),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Producto')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('commodity_type')
                                    ->label('Tipo de Producto')
                                    ->options([
                                        'electricity' => 'Electricidad',
                                        'natural_gas' => 'Gas Natural',
                                        'carbon_credits' => 'Créditos de Carbono',
                                        'renewable_certificates' => 'Certificados Renovables',
                                        'capacity' => 'Capacidad',
                                        'balancing' => 'Servicios de Balance'
                                    ])
                                    ->required(),
                                
                                Forms\Components\TextInput::make('product_name')
                                    ->label('Nombre del Producto')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        
                        Forms\Components\Textarea::make('product_description')
                            ->label('Descripción del Producto')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Información Temporal')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('price_datetime')
                                    ->label('Fecha y Hora del Precio')
                                    ->required(),
                                
                                Forms\Components\Select::make('period_type')
                                    ->label('Tipo de Período')
                                    ->options([
                                        'real_time' => 'Tiempo Real',
                                        'hourly' => 'Por Hora',
                                        'daily' => 'Diario',
                                        'weekly' => 'Semanal',
                                        'monthly' => 'Mensual',
                                        'quarterly' => 'Trimestral',
                                        'annual' => 'Anual'
                                    ])
                                    ->required(),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('delivery_start_date')
                                    ->label('Inicio de Entrega')
                                    ->required(),
                                
                                Forms\Components\DatePicker::make('delivery_end_date')
                                    ->label('Fin de Entrega')
                                    ->required()
                                    ->after('delivery_start_date'),
                            ]),
                        
                        Forms\Components\Select::make('delivery_period')
                            ->label('Período de Entrega')
                            ->options([
                                'spot' => 'Mercado Spot',
                                'next_day' => 'Día Siguiente',
                                'current_week' => 'Semana Actual',
                                'next_week' => 'Semana Siguiente',
                                'current_month' => 'Mes Actual',
                                'next_month' => 'Mes Siguiente',
                                'current_quarter' => 'Trimestre Actual',
                                'next_quarter' => 'Trimestre Siguiente',
                                'current_year' => 'Año Actual',
                                'next_year' => 'Año Siguiente'
                            ])
                            ->required(),
                    ]),
                
                Forms\Components\Section::make('Precios')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Precio Principal')
                                    ->numeric()
                                    ->required(),
                                
                                Forms\Components\Select::make('currency')
                                    ->label('Moneda')
                                    ->options([
                                        'EUR' => 'Euro (€)',
                                        'USD' => 'Dólar ($)',
                                        'GBP' => 'Libra (£)'
                                    ])
                                    ->default('EUR')
                                    ->required(),
                                
                                Forms\Components\TextInput::make('unit')
                                    ->label('Unidad')
                                    ->required()
                                    ->placeholder('ej: EUR/MWh, EUR/tCO2'),
                            ]),
                        
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('opening_price')
                                    ->label('Precio de Apertura')
                                    ->numeric(),
                                
                                Forms\Components\TextInput::make('closing_price')
                                    ->label('Precio de Cierre')
                                    ->numeric(),
                                
                                Forms\Components\TextInput::make('high_price')
                                    ->label('Precio Máximo')
                                    ->numeric(),
                                
                                Forms\Components\TextInput::make('low_price')
                                    ->label('Precio Mínimo')
                                    ->numeric(),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Volumen y Liquidez')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('volume')
                                    ->label('Volumen Negociado')
                                    ->numeric(),
                                
                                Forms\Components\TextInput::make('volume_unit')
                                    ->label('Unidad del Volumen')
                                    ->placeholder('ej: MWh, tCO2'),
                                
                                Forms\Components\TextInput::make('number_of_transactions')
                                    ->label('Número de Transacciones')
                                    ->numeric(),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('bid_price')
                                    ->label('Precio de Compra (Bid)')
                                    ->numeric(),
                                
                                Forms\Components\TextInput::make('ask_price')
                                    ->label('Precio de Venta (Ask)')
                                    ->numeric(),
                                
                                Forms\Components\TextInput::make('spread')
                                    ->label('Diferencial (Spread)')
                                    ->numeric(),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Factores del Sistema')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('demand_mw')
                                    ->label('Demanda')
                                    ->numeric()
                                    ->suffix('MW'),
                                
                                Forms\Components\TextInput::make('supply_mw')
                                    ->label('Oferta')
                                    ->numeric()
                                    ->suffix('MW'),
                                
                                Forms\Components\TextInput::make('renewable_generation_mw')
                                    ->label('Generación Renovable')
                                    ->numeric()
                                    ->suffix('MW'),
                                
                                Forms\Components\TextInput::make('conventional_generation_mw')
                                    ->label('Generación Convencional')
                                    ->numeric()
                                    ->suffix('MW'),
                            ]),
                        
                        Forms\Components\Select::make('system_condition')
                            ->label('Condición del Sistema')
                            ->options([
                                'normal' => 'Normal',
                                'tight' => 'Ajustado',
                                'emergency' => 'Emergencia',
                                'surplus' => 'Excedente'
                            ]),
                    ]),
                
                Forms\Components\Section::make('Fuente de Datos')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('data_source')
                                    ->label('Fuente de Datos')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\Select::make('data_quality')
                                    ->label('Calidad de Datos')
                                    ->options([
                                        'official' => 'Oficial',
                                        'preliminary' => 'Preliminar',
                                        'estimated' => 'Estimado',
                                        'forecasted' => 'Pronosticado'
                                    ])
                                    ->default('official'),
                            ]),
                        
                        Forms\Components\DateTimePicker::make('data_retrieved_at')
                            ->label('Datos Obtenidos'),
                    ]),
                
                Forms\Components\Section::make('Estado del Mercado')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('market_status')
                                    ->label('Estado del Mercado')
                                    ->options([
                                        'open' => 'Abierto',
                                        'closed' => 'Cerrado',
                                        'pre_opening' => 'Pre-apertura',
                                        'auction' => 'En Subasta',
                                        'suspended' => 'Suspendido',
                                        'maintenance' => 'Mantenimiento'
                                    ])
                                    ->default('open'),
                                
                                Forms\Components\Toggle::make('is_holiday')
                                    ->label('Día Festivo')
                                    ->default(false),
                                
                                Forms\Components\TextInput::make('day_type')
                                    ->label('Tipo de Día')
                                    ->maxLength(255)
                                    ->placeholder('ej: weekend, holiday, working_day'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('market_name')
                    ->label('Mercado')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('commodity_type')
                    ->label('Producto')
                    ->colors([
                        'primary' => 'electricity',
                        'warning' => 'natural_gas',
                        'success' => 'carbon_credits',
                        'info' => 'renewable_certificates',
                        'secondary' => 'capacity',
                        'danger' => 'balancing',
                    ]),
                
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Nombre')
                    ->searchable()
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('price_datetime')
                    ->label('Fecha y Hora')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('period_type')
                    ->label('Período')
                    ->colors([
                        'danger' => 'real_time',
                        'warning' => 'hourly',
                        'success' => 'daily',
                        'primary' => 'weekly',
                        'info' => 'monthly',
                        'secondary' => 'quarterly',
                        'dark' => 'annual',
                    ]),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->numeric(5)
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->price_change_percentage > 5) return 'success';
                        if ($record->price_change_percentage < -5) return 'danger';
                        return 'primary';
                    }),
                
                Tables\Columns\TextColumn::make('unit')
                    ->label('Unidad')
                    ->limit(15),
                
                Tables\Columns\TextColumn::make('price_change_percentage')
                    ->label('Cambio %')
                    ->suffix('%')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),
                
                Tables\Columns\TextColumn::make('volume')
                    ->label('Volumen')
                    ->numeric(0)
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('market_status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'open',
                        'secondary' => 'closed',
                        'warning' => 'pre_opening',
                        'info' => 'auction',
                        'danger' => 'suspended',
                        'gray' => 'maintenance',
                    ]),
                
                Tables\Columns\BadgeColumn::make('data_quality')
                    ->label('Calidad')
                    ->colors([
                        'success' => 'official',
                        'warning' => 'preliminary',
                        'info' => 'estimated',
                        'secondary' => 'forecasted',
                    ]),
                
                Tables\Columns\IconColumn::make('is_holiday')
                    ->label('Festivo')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('commodity_type')
                    ->label('Tipo de Producto')
                    ->options([
                        'electricity' => 'Electricidad',
                        'natural_gas' => 'Gas Natural',
                        'carbon_credits' => 'Créditos de Carbono',
                        'renewable_certificates' => 'Certificados Renovables',
                        'capacity' => 'Capacidad',
                        'balancing' => 'Servicios de Balance'
                    ]),
                
                Tables\Filters\SelectFilter::make('period_type')
                    ->label('Tipo de Período')
                    ->options([
                        'real_time' => 'Tiempo Real',
                        'hourly' => 'Por Hora',
                        'daily' => 'Diario',
                        'weekly' => 'Semanal',
                        'monthly' => 'Mensual',
                        'quarterly' => 'Trimestral',
                        'annual' => 'Anual'
                    ]),
                
                Tables\Filters\SelectFilter::make('market_status')
                    ->label('Estado del Mercado')
                    ->options([
                        'open' => 'Abierto',
                        'closed' => 'Cerrado',
                        'pre_opening' => 'Pre-apertura',
                        'auction' => 'En Subasta',
                        'suspended' => 'Suspendido',
                        'maintenance' => 'Mantenimiento'
                    ]),
                
                Tables\Filters\SelectFilter::make('market_name')
                    ->label('Mercado')
                    ->options(function () {
                        return MarketPrice::distinct('market_name')
                            ->pluck('market_name', 'market_name')
                            ->toArray();
                    }),
                
                Tables\Filters\Filter::make('price_spike')
                    ->label('Picos de Precio (+20%)')
                    ->query(fn (Builder $query): Builder => $query->where('price_change_percentage', '>', 20)),
                
                Tables\Filters\Filter::make('high_volume')
                    ->label('Alto Volumen (>10,000)')
                    ->query(fn (Builder $query): Builder => $query->where('volume', '>', 10000)),
                
                Tables\Filters\Filter::make('today')
                    ->label('Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('price_datetime', today())),
                
                Tables\Filters\Filter::make('holidays')
                    ->label('Días Festivos')
                    ->query(fn (Builder $query): Builder => $query->where('is_holiday', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('price_datetime', 'desc')
            ->poll('60s'); // Auto-refresh every minute for real-time market data
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
            'index' => Pages\ListMarketPrices::route('/'),
            'create' => Pages\CreateMarketPrice::route('/create'),
            'view' => Pages\ViewMarketPrice::route('/{record}'),
            'edit' => Pages\EditMarketPrice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->latest('price_datetime');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('price_spike_detected', true)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('price_spike_detected', true)->count() > 0 ? 'danger' : 'success';
    }
}