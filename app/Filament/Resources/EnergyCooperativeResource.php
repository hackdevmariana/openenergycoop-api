<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyCooperativeResource\Pages;
use App\Filament\Resources\EnergyCooperativeResource\RelationManagers;
use App\Models\EnergyCooperative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnergyCooperativeResource extends Resource
{
    protected static ?string $model = EnergyCooperative::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Cooperativas Energéticas';

    protected static ?string $modelLabel = 'Cooperativa Energética';

    protected static ?string $pluralModelLabel = 'Cooperativas Energéticas';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Datos de la Cooperativa')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información Básica')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\Section::make('Datos Principales')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre de la Cooperativa')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                                                if ($context === 'create') {
                                                    $set('code', \Str::slug($state));
                                                }
                                            }),

                                        Forms\Components\TextInput::make('code')
                                            ->label('Código Identificador')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Código único para identificar la cooperativa'),

                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'pending' => 'Pendiente de Aprobación',
                                                'active' => 'Activa',
                                                'suspended' => 'Suspendida',
                                                'inactive' => 'Inactiva',
                                                'dissolved' => 'Disuelta'
                                            ])
                                            ->default('pending')
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Descripción y Misión')
                                    ->schema([
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('mission_statement')
                                            ->label('Declaración de Misión')
                                            ->rows(3),

                                        Forms\Components\Textarea::make('vision_statement')
                                            ->label('Declaración de Visión')
                                            ->rows(3),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Información Legal')
                            ->icon('heroicon-o-scale')
                            ->schema([
                                Forms\Components\Section::make('Datos Legales')
                                    ->schema([
                                        Forms\Components\TextInput::make('legal_name')
                                            ->label('Nombre Legal')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('tax_id')
                                            ->label('NIF/CIF')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('registration_number')
                                            ->label('Número de Registro')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('legal_form')
                                            ->label('Forma Jurídica')
                                            ->maxLength(255)
                                            ->placeholder('Cooperativa, Asociación, etc.'),
                                    ])->columns(2),

                                Forms\Components\Section::make('Fechas Importantes')
                                    ->schema([
                                        Forms\Components\DatePicker::make('founded_date')
                                            ->label('Fecha de Fundación'),

                                        Forms\Components\DatePicker::make('registration_date')
                                            ->label('Fecha de Registro'),

                                        Forms\Components\DatePicker::make('activation_date')
                                            ->label('Fecha de Activación'),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Ubicación')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Section::make('Dirección')
                                    ->schema([
                                        Forms\Components\Textarea::make('address')
                                            ->label('Dirección')
                                            ->rows(2)
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('city')
                                            ->label('Ciudad')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('state_province')
                                            ->label('Provincia/Estado')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('postal_code')
                                            ->label('Código Postal')
                                            ->maxLength(255),

                                        Forms\Components\Select::make('country')
                                            ->label('País')
                                            ->options([
                                                'España' => 'España',
                                                'Francia' => 'Francia',
                                                'Portugal' => 'Portugal',
                                                'Italia' => 'Italia',
                                                'Alemania' => 'Alemania',
                                            ])
                                            ->default('España')
                                            ->searchable(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Coordenadas GPS')
                                    ->schema([
                                        Forms\Components\TextInput::make('latitude')
                                            ->label('Latitud')
                                            ->numeric()
                                            ->step(0.00000001),

                                        Forms\Components\TextInput::make('longitude')
                                            ->label('Longitud')
                                            ->numeric()
                                            ->step(0.00000001),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Gestión')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Section::make('Administración')
                                    ->schema([
                                        Forms\Components\Select::make('founder_id')
                                            ->label('Fundador')
                                            ->relationship('founder', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('administrator_id')
                                            ->label('Administrador')
                                            ->relationship('administrator', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Membresía')
                                    ->schema([
                                        Forms\Components\TextInput::make('max_members')
                                            ->label('Máximo de Miembros')
                                            ->numeric()
                                            ->minValue(1),

                                        Forms\Components\TextInput::make('current_members')
                                            ->label('Miembros Actuales')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),

                                        Forms\Components\TextInput::make('membership_fee')
                                            ->label('Cuota de Membresía')
                                            ->numeric()
                                            ->prefix('€'),

                                        Forms\Components\Select::make('membership_fee_frequency')
                                            ->label('Frecuencia de Cuota')
                                            ->options([
                                                'monthly' => 'Mensual',
                                                'quarterly' => 'Trimestral',
                                                'semi_annual' => 'Semestral',
                                                'annual' => 'Anual',
                                                'one_time' => 'Pago Único',
                                            ]),

                                        Forms\Components\Toggle::make('open_enrollment')
                                            ->label('Inscripción Abierta')
                                            ->default(true),

                                        Forms\Components\Textarea::make('enrollment_requirements')
                                            ->label('Requisitos de Inscripción')
                                            ->rows(3),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Energía')
                            ->icon('heroicon-o-bolt')
                            ->schema([
                                Forms\Components\Section::make('Configuración Energética')
                                    ->schema([
                                        Forms\Components\TagsInput::make('energy_types')
                                            ->label('Tipos de Energía')
                                            ->placeholder('solar, eólica, hidráulica...')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('total_capacity_kw')
                                            ->label('Capacidad Total (kW)')
                                            ->numeric()
                                            ->suffix('kW'),

                                        Forms\Components\TextInput::make('available_capacity_kw')
                                            ->label('Capacidad Disponible (kW)')
                                            ->numeric()
                                            ->suffix('kW'),

                                        Forms\Components\Toggle::make('allows_energy_sharing')
                                            ->label('Permite Intercambio de Energía')
                                            ->default(true),

                                        Forms\Components\Toggle::make('allows_trading')
                                            ->label('Permite Trading Energético')
                                            ->default(true),

                                        Forms\Components\TextInput::make('sharing_fee_percentage')
                                            ->label('Comisión por Intercambio (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->maxValue(100),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('Configuración Financiera')
                                    ->schema([
                                        Forms\Components\Select::make('currency')
                                            ->label('Moneda')
                                            ->options([
                                                'EUR' => 'Euro (€)',
                                                'USD' => 'Dólar ($)',
                                                'GBP' => 'Libra (£)',
                                            ])
                                            ->default('EUR')
                                            ->required(),

                                        Forms\Components\Toggle::make('requires_deposit')
                                            ->label('Requiere Depósito')
                                            ->live(),

                                        Forms\Components\TextInput::make('deposit_amount')
                                            ->label('Monto del Depósito')
                                            ->numeric()
                                            ->prefix('€')
                                            ->visible(fn (Forms\Get $get) => $get('requires_deposit')),
                                    ])->columns(3),

                                Forms\Components\Section::make('Sistema')
                                    ->schema([
                                        Forms\Components\Select::make('timezone')
                                            ->label('Zona Horaria')
                                            ->options([
                                                'Europe/Madrid' => 'Madrid (Europe/Madrid)',
                                                'Europe/Paris' => 'París (Europe/Paris)',
                                                'Europe/London' => 'Londres (Europe/London)',
                                            ])
                                            ->default('Europe/Madrid')
                                            ->required(),

                                        Forms\Components\Select::make('language')
                                            ->label('Idioma')
                                            ->options([
                                                'es' => 'Español',
                                                'en' => 'English',
                                                'fr' => 'Français',
                                                'de' => 'Deutsch',
                                            ])
                                            ->default('es')
                                            ->required(),

                                        Forms\Components\TextInput::make('visibility_level')
                                            ->label('Nivel de Visibilidad (1-5)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(5)
                                            ->default(1)
                                            ->required(),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Destacada'),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'suspended',
                        'secondary' => 'inactive',
                        'dark' => 'dissolved',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('country')
                    ->label('País')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_members')
                    ->label('Miembros')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_capacity_kw')
                    ->label('Capacidad kW')
                    ->numeric()
                    ->sortable()
                    ->suffix(' kW'),

                Tables\Columns\IconColumn::make('allows_energy_sharing')
                    ->label('Intercambio')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacada')
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
                        'suspended' => 'Suspendida',
                        'inactive' => 'Inactiva',
                        'dissolved' => 'Disuelta',
                    ]),

                Tables\Filters\SelectFilter::make('country')
                    ->label('País')
                    ->options([
                        'España' => 'España',
                        'Francia' => 'Francia',
                        'Portugal' => 'Portugal',
                    ]),

                Tables\Filters\TernaryFilter::make('allows_energy_sharing')
                    ->label('Permite Intercambio'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Destacadas'),
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
            'index' => Pages\ListEnergyCooperatives::route('/'),
            'create' => Pages\CreateEnergyCooperative::route('/create'),
            // 'view' => Pages\ViewEnergyCooperative::route('/{record}'),
            'edit' => Pages\EditEnergyCooperative::route('/{record}/edit'),
        ];
    }
}