<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergySharingResource\Pages;
use App\Filament\Resources\EnergySharingResource\RelationManagers;
use App\Models\EnergySharing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnergySharingResource extends Resource
{
    protected static ?string $model = EnergySharing::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationLabel = 'Intercambios de Energía';

    protected static ?string $modelLabel = 'Intercambio de Energía';

    protected static ?string $pluralModelLabel = 'Intercambios de Energía';

    protected static ?string $navigationGroup = 'Gestión de Comunidad';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Datos del Intercambio')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Participantes')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Section::make('Usuarios del Intercambio')
                                    ->schema([
                                        Forms\Components\Select::make('provider_user_id')
                                            ->label('Usuario Proveedor')
                                            ->relationship('providerUser', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('consumer_user_id')
                                            ->label('Usuario Consumidor')
                                            ->relationship('consumerUser', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('energy_cooperative_id')
                                            ->label('Cooperativa Mediadora')
                                            ->relationship('energyCooperative', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ])->columns(3),

                                Forms\Components\Section::make('Información del Intercambio')
                                    ->schema([
                                        Forms\Components\TextInput::make('sharing_code')
                                            ->label('Código del Intercambio')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\Select::make('sharing_type')
                                            ->label('Tipo de Intercambio')
                                            ->options([
                                                'direct' => 'Directo',
                                                'community' => 'Comunitario',
                                                'marketplace' => 'Marketplace',
                                                'emergency' => 'Emergencia',
                                                'scheduled' => 'Programado',
                                                'real_time' => 'Tiempo Real',
                                            ])
                                            ->required(),

                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'proposed' => 'Propuesto',
                                                'accepted' => 'Aceptado',
                                                'active' => 'Activo',
                                                'completed' => 'Completado',
                                                'cancelled' => 'Cancelado',
                                                'failed' => 'Fallido',
                                                'disputed' => 'En Disputa',
                                                'expired' => 'Expirado',
                                            ])
                                            ->default('proposed')
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Descripción')
                                    ->schema([
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Energía')
                            ->icon('heroicon-o-bolt')
                            ->schema([
                                Forms\Components\Section::make('Configuración Energética')
                                    ->schema([
                                        Forms\Components\TextInput::make('energy_amount_kwh')
                                            ->label('Cantidad de Energía (kWh)')
                                            ->required()
                                            ->numeric()
                                            ->suffix('kWh'),

                                        Forms\Components\TextInput::make('energy_delivered_kwh')
                                            ->label('Energía Entregada (kWh)')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('kWh'),

                                        Forms\Components\TextInput::make('energy_source')
                                            ->label('Fuente de Energía')
                                            ->maxLength(255)
                                            ->placeholder('solar, eólica, hidráulica...'),

                                        Forms\Components\Toggle::make('is_renewable')
                                            ->label('Es Energía Renovable')
                                            ->live(),

                                        Forms\Components\TextInput::make('renewable_percentage')
                                            ->label('Porcentaje Renovable (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->maxValue(100)
                                            ->visible(fn (Forms\Get $get) => $get('is_renewable')),

                                        Forms\Components\Toggle::make('certified_green_energy')
                                            ->label('Energía Verde Certificada'),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Temporización')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('Fechas y Horarios')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('sharing_start_datetime')
                                            ->label('Inicio del Intercambio')
                                            ->required(),

                                        Forms\Components\DateTimePicker::make('sharing_end_datetime')
                                            ->label('Fin del Intercambio')
                                            ->required(),

                                        Forms\Components\DateTimePicker::make('proposal_expiry_datetime')
                                            ->label('Expiración de la Propuesta')
                                            ->required(),

                                        Forms\Components\TextInput::make('duration_hours')
                                            ->label('Duración (horas)')
                                            ->numeric()
                                            ->suffix('h'),

                                        Forms\Components\Toggle::make('flexible_timing')
                                            ->label('Horario Flexible'),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Financiero')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Precios y Pagos')
                                    ->schema([
                                        Forms\Components\TextInput::make('price_per_kwh')
                                            ->label('Precio por kWh')
                                            ->required()
                                            ->numeric()
                                            ->prefix('€'),

                                        Forms\Components\TextInput::make('total_amount')
                                            ->label('Monto Total')
                                            ->numeric()
                                            ->prefix('€'),

                                        Forms\Components\TextInput::make('platform_fee')
                                            ->label('Comisión de Plataforma')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('€'),

                                        Forms\Components\TextInput::make('cooperative_fee')
                                            ->label('Comisión de Cooperativa')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('€'),

                                        Forms\Components\Select::make('payment_method')
                                            ->label('Método de Pago')
                                            ->options([
                                                'credits' => 'Créditos',
                                                'bank_transfer' => 'Transferencia',
                                                'energy_tokens' => 'Tokens Energéticos',
                                                'barter' => 'Intercambio Directo',
                                                'loyalty_points' => 'Puntos de Lealtad',
                                            ])
                                            ->default('credits'),

                                        Forms\Components\Select::make('currency')
                                            ->label('Moneda')
                                            ->options([
                                                'EUR' => 'Euro (€)',
                                                'USD' => 'Dólar ($)',
                                                'GBP' => 'Libra (£)',
                                            ])
                                            ->default('EUR'),
                                    ])->columns(2),

                                Forms\Components\Section::make('Estado de Pago')
                                    ->schema([
                                        Forms\Components\Select::make('payment_status')
                                            ->label('Estado del Pago')
                                            ->options([
                                                'pending' => 'Pendiente',
                                                'processing' => 'Procesando',
                                                'completed' => 'Completado',
                                                'failed' => 'Fallido',
                                                'refunded' => 'Reembolsado',
                                                'disputed' => 'En Disputa',
                                            ])
                                            ->default('pending'),

                                        Forms\Components\DateTimePicker::make('payment_due_date')
                                            ->label('Fecha Límite de Pago'),

                                        Forms\Components\DateTimePicker::make('payment_completed_at')
                                            ->label('Pago Completado'),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Calidad')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Forms\Components\Section::make('Métricas de Calidad')
                                    ->schema([
                                        Forms\Components\TextInput::make('quality_score')
                                            ->label('Puntuación de Calidad (1-5)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(5)
                                            ->step(0.1),

                                        Forms\Components\TextInput::make('reliability_score')
                                            ->label('Puntuación de Confiabilidad (1-5)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(5)
                                            ->step(0.1),

                                        Forms\Components\TextInput::make('delivery_efficiency')
                                            ->label('Eficiencia de Entrega (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->maxValue(100),

                                        Forms\Components\TextInput::make('interruptions_count')
                                            ->label('Número de Interrupciones')
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(2),

                                Forms\Components\Section::make('Métricas Ambientales')
                                    ->schema([
                                        Forms\Components\TextInput::make('co2_reduction_kg')
                                            ->label('Reducción de CO2 (kg)')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('kg'),

                                        Forms\Components\TextInput::make('environmental_impact_score')
                                            ->label('Puntuación de Impacto Ambiental (1-5)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(5)
                                            ->step(0.1),

                                        Forms\Components\TextInput::make('certification_number')
                                            ->label('Número de Certificación')
                                            ->maxLength(255),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('Opciones')
                                    ->schema([
                                        Forms\Components\Toggle::make('allows_partial_delivery')
                                            ->label('Permite Entrega Parcial')
                                            ->default(true),

                                        Forms\Components\TextInput::make('min_delivery_kwh')
                                            ->label('Entrega Mínima (kWh)')
                                            ->numeric()
                                            ->suffix('kWh'),

                                        Forms\Components\Toggle::make('real_time_tracking')
                                            ->label('Seguimiento en Tiempo Real'),

                                        Forms\Components\TextInput::make('monitoring_frequency_minutes')
                                            ->label('Frecuencia de Monitoreo (min)')
                                            ->numeric()
                                            ->default(15)
                                            ->suffix('min'),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sharing_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('providerUser.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('consumerUser.name')
                    ->label('Consumidor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'proposed',
                        'info' => 'accepted',
                        'success' => 'active',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'danger' => 'failed',
                        'warning' => 'disputed',
                        'gray' => 'expired',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('sharing_type')
                    ->label('Tipo')
                    ->colors([
                        'blue' => 'direct',
                        'green' => 'community',
                        'purple' => 'marketplace',
                        'red' => 'emergency',
                        'orange' => 'scheduled',
                        'yellow' => 'real_time',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('energy_amount_kwh')
                    ->label('Energía (kWh)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' kWh'),

                Tables\Columns\TextColumn::make('price_per_kwh')
                    ->label('Precio/kWh')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_renewable')
                    ->label('Renovable')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sharing_start_datetime')
                    ->label('Inicio')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'proposed' => 'Propuesto',
                        'accepted' => 'Aceptado',
                        'active' => 'Activo',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        'failed' => 'Fallido',
                        'disputed' => 'En Disputa',
                        'expired' => 'Expirado',
                    ]),

                Tables\Filters\SelectFilter::make('sharing_type')
                    ->label('Tipo')
                    ->options([
                        'direct' => 'Directo',
                        'community' => 'Comunitario',
                        'marketplace' => 'Marketplace',
                        'emergency' => 'Emergencia',
                        'scheduled' => 'Programado',
                        'real_time' => 'Tiempo Real',
                    ]),

                Tables\Filters\TernaryFilter::make('is_renewable')
                    ->label('Energía Renovable'),

                Tables\Filters\TernaryFilter::make('certified_green_energy')
                    ->label('Certificación Verde'),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Estado de Pago')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                        'disputed' => 'En Disputa',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EnergySharing $record) => $record->status === 'proposed')
                    ->action(function (EnergySharing $record) {
                        $record->update(['status' => 'accepted', 'accepted_at' => now()]);
                    }),

                Tables\Actions\Action::make('start')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (EnergySharing $record) => $record->status === 'accepted')
                    ->action(function (EnergySharing $record) {
                        $record->update(['status' => 'active', 'started_at' => now()]);
                    }),

                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (EnergySharing $record) => $record->status === 'active')
                    ->action(function (EnergySharing $record) {
                        $record->update(['status' => 'completed', 'completed_at' => now()]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnergySharings::route('/'),
            'create' => Pages\CreateEnergySharing::route('/create'),
            // 'view' => Pages\ViewEnergySharing::route('/{record}'),
            'edit' => Pages\EditEnergySharing::route('/{record}/edit'),
        ];
    }
}