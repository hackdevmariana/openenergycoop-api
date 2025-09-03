<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSubscriptionResource\Pages;
use App\Filament\Resources\UserSubscriptionResource\RelationManagers;
use App\Models\UserSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Suscripciones';

    protected static ?string $modelLabel = 'Suscripción';

    protected static ?string $pluralModelLabel = 'Suscripciones';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Datos de la Suscripción')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información Básica')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\Section::make('Usuario y Relaciones')
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('Usuario')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('energy_cooperative_id')
                                            ->label('Cooperativa Energética')
                                            ->relationship('energyCooperative', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('provider_id')
                                            ->label('Proveedor')
                                            ->relationship('provider', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ])->columns(3),

                                Forms\Components\Section::make('Plan y Servicio')
                                    ->schema([
                                        Forms\Components\TextInput::make('subscription_type')
                                            ->label('Tipo de Suscripción')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('plan_name')
                                            ->label('Nombre del Plan')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('service_category')
                                            ->label('Categoría de Servicio')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'pending' => 'Pendiente',
                                                'active' => 'Activa',
                                                'paused' => 'Pausada',
                                                'cancelled' => 'Cancelada',
                                                'expired' => 'Expirada',
                                                'suspended' => 'Suspendida',
                                                'terminated' => 'Terminada'
                                            ])
                                            ->default('pending')
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Descripción')
                                    ->schema([
                                        Forms\Components\Textarea::make('plan_description')
                                            ->label('Descripción del Plan')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Fechas y Períodos')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\Section::make('Fechas del Contrato')
                                    ->schema([
                                        Forms\Components\DatePicker::make('start_date')
                                            ->label('Fecha de Inicio')
                                            ->required(),

                                        Forms\Components\DatePicker::make('end_date')
                                            ->label('Fecha de Fin'),

                                        Forms\Components\DatePicker::make('trial_end_date')
                                            ->label('Fin de Período de Prueba'),

                                        Forms\Components\DatePicker::make('next_billing_date')
                                            ->label('Próxima Facturación'),

                                        Forms\Components\DatePicker::make('cancellation_date')
                                            ->label('Fecha de Cancelación'),

                                        Forms\Components\DateTimePicker::make('last_renewed_at')
                                            ->label('Última Renovación'),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Facturación')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Configuración de Facturación')
                                    ->schema([
                                        Forms\Components\Select::make('billing_frequency')
                                            ->label('Frecuencia de Facturación')
                                            ->options([
                                                'weekly' => 'Semanal',
                                                'monthly' => 'Mensual',
                                                'quarterly' => 'Trimestral',
                                                'semi_annual' => 'Semestral',
                                                'annual' => 'Anual',
                                                'one_time' => 'Pago Único',
                                            ])
                                            ->default('monthly')
                                            ->required(),

                                        Forms\Components\TextInput::make('price')
                                            ->label('Precio')
                                            ->required()
                                            ->numeric()
                                            ->prefix('€'),

                                        Forms\Components\Select::make('currency')
                                            ->label('Moneda')
                                            ->options([
                                                'EUR' => 'Euro (€)',
                                                'USD' => 'Dólar ($)',
                                                'GBP' => 'Libra (£)',
                                            ])
                                            ->default('EUR')
                                            ->required(),
                                    ])->columns(3),

                                Forms\Components\Section::make('Descuentos y Promociones')
                                    ->schema([
                                        Forms\Components\TextInput::make('discount_percentage')
                                            ->label('Descuento (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->maxValue(100),

                                        Forms\Components\TextInput::make('discount_amount')
                                            ->label('Descuento Fijo')
                                            ->numeric()
                                            ->prefix('€'),

                                        Forms\Components\TextInput::make('promo_code')
                                            ->label('Código Promocional')
                                            ->maxLength(255),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Energía')
                            ->icon('heroicon-o-bolt')
                            ->schema([
                                Forms\Components\Section::make('Configuración Energética')
                                    ->schema([
                                        Forms\Components\TextInput::make('energy_allowance_kwh')
                                            ->label('Allowance Energético (kWh)')
                                            ->numeric()
                                            ->suffix('kWh'),

                                        Forms\Components\TextInput::make('overage_rate_per_kwh')
                                            ->label('Tarifa por Exceso (€/kWh)')
                                            ->numeric()
                                            ->prefix('€'),

                                        Forms\Components\Toggle::make('includes_renewable_energy')
                                            ->label('Incluye Energía Renovable')
                                            ->live(),

                                        Forms\Components\TextInput::make('renewable_percentage')
                                            ->label('Porcentaje Renovable (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->maxValue(100)
                                            ->visible(fn (Forms\Get $get) => $get('includes_renewable_energy')),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('Preferencias')
                                    ->schema([
                                        Forms\Components\Toggle::make('auto_renewal')
                                            ->label('Renovación Automática')
                                            ->default(true),

                                        Forms\Components\TextInput::make('renewal_reminder_days')
                                            ->label('Días de Recordatorio')
                                            ->numeric()
                                            ->default(7)
                                            ->minValue(1),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan_name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscription_type')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'secondary' => 'paused',
                        'danger' => 'cancelled',
                        'gray' => 'expired',
                        'warning' => 'suspended',
                        'dark' => 'terminated',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_frequency')
                    ->label('Facturación')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_billing_date')
                    ->label('Próxima Facturación')
                    ->date()
                    ->sortable(),

                Tables\Columns\IconColumn::make('auto_renewal')
                    ->label('Auto-renovación')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'active' => 'Activa',
                        'paused' => 'Pausada',
                        'cancelled' => 'Cancelada',
                        'expired' => 'Expirada',
                        'suspended' => 'Suspendida',
                        'terminated' => 'Terminada',
                    ]),

                Tables\Filters\SelectFilter::make('billing_frequency')
                    ->label('Facturación')
                    ->options([
                        'weekly' => 'Semanal',
                        'monthly' => 'Mensual',
                        'quarterly' => 'Trimestral',
                        'semi_annual' => 'Semestral',
                        'annual' => 'Anual',
                        'one_time' => 'Pago Único',
                    ]),

                Tables\Filters\TernaryFilter::make('auto_renewal')
                    ->label('Auto-renovación'),

                Tables\Filters\TernaryFilter::make('includes_renewable_energy')
                    ->label('Energía Renovable'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUserSubscriptions::route('/'),
            'create' => Pages\CreateUserSubscription::route('/create'),
            // 'view' => Pages\ViewUserSubscription::route('/{record}'),
            'edit' => Pages\EditUserSubscription::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $activeCount = static::getModel()::where('status', 'active')->count();
        $totalCount = static::getModel()::count();
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        $cancelledCount = static::getModel()::where('status', 'cancelled')->count();
        $expiredCount = static::getModel()::where('status', 'expired')->count();
        $suspendedCount = static::getModel()::where('status', 'suspended')->count();
        
        if ($activeCount === $totalCount && $totalCount > 0) {
            return 'success'; // Todas las suscripciones están activas
        } elseif ($expiredCount > 0) {
            return 'danger'; // Hay suscripciones expiradas
        } elseif ($cancelledCount > 0) {
            return 'danger'; // Hay suscripciones canceladas
        } elseif ($suspendedCount > 0) {
            return 'warning'; // Hay suscripciones suspendidas
        } elseif ($pendingCount > 0) {
            return 'warning'; // Hay suscripciones pendientes
        } elseif ($activeCount > 0) {
            return 'success'; // Hay suscripciones activas
        } else {
            return 'gray'; // No hay suscripciones activas
        }
    }
}