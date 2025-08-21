<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarbonCreditResource\Pages;
use App\Models\CarbonCredit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class CarbonCreditResource extends Resource
{
    protected static ?string $model = CarbonCredit::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-americas';
    
    protected static ?string $navigationLabel = 'Créditos de Carbono';
    
    protected static ?string $modelLabel = 'Crédito de Carbono';
    
    protected static ?string $pluralModelLabel = 'Créditos de Carbono';
    
    protected static ?string $navigationGroup = 'Energía y Comercio';
    
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Información Básica')
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
                                        ->preload(),
                                ]),
                            
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('credit_id')
                                        ->label('ID del Crédito')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->default(function () {
                                            return 'CC-' . now()->format('YmdHis') . '-' . random_int(10000, 99999);
                                        }),
                                    
                                    Forms\Components\TextInput::make('registry_id')
                                        ->label('ID del Registro')
                                        ->maxLength(255),
                                    
                                    Forms\Components\TextInput::make('serial_number')
                                        ->label('Número de Serie')
                                        ->maxLength(255),
                                ]),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('credit_type')
                                        ->label('Tipo de Crédito')
                                        ->options([
                                            'vcs' => 'VCS - Verified Carbon Standard',
                                            'gold_standard' => 'Gold Standard',
                                            'cdm' => 'CDM - Clean Development Mechanism',
                                            'vcu' => 'VCU - Verified Carbon Unit',
                                            'cer' => 'CER - Certified Emission Reduction',
                                            'rgu' => 'RGU - Renewable Generation Unit',
                                            'custom' => 'Personalizado'
                                        ])
                                        ->required(),
                                    
                                    Forms\Components\TextInput::make('methodology')
                                        ->label('Metodología')
                                        ->maxLength(255),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Información del Proyecto')
                        ->schema([
                            Forms\Components\TextInput::make('project_name')
                                ->label('Nombre del Proyecto')
                                ->required()
                                ->maxLength(255),
                            
                            Forms\Components\Textarea::make('project_description')
                                ->label('Descripción del Proyecto')
                                ->rows(3)
                                ->columnSpanFull(),
                            
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('project_type')
                                        ->label('Tipo de Proyecto')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('ej: renewable energy, forestry'),
                                    
                                    Forms\Components\TextInput::make('project_country')
                                        ->label('País del Proyecto')
                                        ->required()
                                        ->maxLength(255),
                                    
                                    Forms\Components\TextInput::make('project_location')
                                        ->label('Ubicación del Proyecto')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            
                            Forms\Components\TextInput::make('project_capacity_mw')
                                ->label('Capacidad del Proyecto')
                                ->numeric()
                                ->suffix('MW'),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Cantidades y Estado')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('total_credits')
                                        ->label('Total de Créditos')
                                        ->numeric()
                                        ->suffix('tCO2e')
                                        ->required(),
                                    
                                    Forms\Components\TextInput::make('available_credits')
                                        ->label('Créditos Disponibles')
                                        ->numeric()
                                        ->suffix('tCO2e')
                                        ->required(),
                                ]),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('retired_credits')
                                        ->label('Créditos Retirados')
                                        ->numeric()
                                        ->suffix('tCO2e')
                                        ->default(0),
                                    
                                    Forms\Components\TextInput::make('transferred_credits')
                                        ->label('Créditos Transferidos')
                                        ->numeric()
                                        ->suffix('tCO2e')
                                        ->default(0),
                                ]),
                            
                            Forms\Components\Select::make('status')
                                ->label('Estado')
                                ->options([
                                    'pending' => 'Pendiente',
                                    'verified' => 'Verificado',
                                    'issued' => 'Emitido',
                                    'available' => 'Disponible',
                                    'retired' => 'Retirado',
                                    'cancelled' => 'Cancelado',
                                    'expired' => 'Expirado'
                                ])
                                ->default('pending')
                                ->required(),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Períodos y Fechas')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('credit_period_start')
                                        ->label('Inicio Período del Crédito')
                                        ->required(),
                                    
                                    Forms\Components\DatePicker::make('credit_period_end')
                                        ->label('Fin Período del Crédito')
                                        ->required()
                                        ->after('credit_period_start'),
                                ]),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('vintage_year')
                                        ->label('Año Vintage')
                                        ->required(),
                                    
                                    Forms\Components\DatePicker::make('issuance_date')
                                        ->label('Fecha de Emisión'),
                                ]),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('verification_date')
                                        ->label('Fecha de Verificación'),
                                    
                                    Forms\Components\DatePicker::make('expiry_date')
                                        ->label('Fecha de Expiración'),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Información Económica')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('purchase_price_per_credit')
                                        ->label('Precio de Compra por Crédito')
                                        ->numeric()
                                        ->prefix('€'),
                                    
                                    Forms\Components\TextInput::make('current_market_price')
                                        ->label('Precio Actual de Mercado')
                                        ->numeric()
                                        ->prefix('€'),
                                    
                                    Forms\Components\TextInput::make('total_investment')
                                        ->label('Inversión Total')
                                        ->numeric()
                                        ->prefix('€'),
                                ]),
                            
                            Forms\Components\Select::make('currency')
                                ->label('Moneda')
                                ->options([
                                    'EUR' => 'Euro (€)',
                                    'USD' => 'Dólar ($)',
                                    'GBP' => 'Libra (£)'
                                ])
                                ->default('EUR'),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Verificación')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('verifier_name')
                                        ->label('Entidad Verificadora')
                                        ->maxLength(255),
                                    
                                    Forms\Components\TextInput::make('verifier_accreditation')
                                        ->label('Acreditación del Verificador')
                                        ->maxLength(255),
                                ]),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('additionality_demonstrated')
                                        ->label('Adicionalidad Demostrada')
                                        ->default(false),
                                    
                                    Forms\Components\Toggle::make('meets_article_6_requirements')
                                        ->label('Cumple Artículo 6 Acuerdo París')
                                        ->default(false),
                                ]),
                            
                            Forms\Components\Textarea::make('additionality_justification')
                                ->label('Justificación de Adicionalidad')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Sostenibilidad e Impacto')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('actual_co2_reduced')
                                        ->label('CO2 Realmente Reducido')
                                        ->numeric()
                                        ->suffix('tCO2e'),
                                    
                                    Forms\Components\TextInput::make('annual_emission_reductions')
                                        ->label('Reducciones Anuales de Emisiones')
                                        ->numeric()
                                        ->suffix('tCO2e/año'),
                                ]),
                            
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\Toggle::make('gender_inclusive')
                                        ->label('Inclusivo de Género')
                                        ->default(false),
                                    
                                    Forms\Components\Toggle::make('community_engagement')
                                        ->label('Participación Comunitaria')
                                        ->default(false),
                                    
                                    Forms\Components\Toggle::make('public_registry_listed')
                                        ->label('Listado en Registro Público')
                                        ->default(false),
                                ]),
                            
                            Forms\Components\Textarea::make('social_impact_description')
                                ->label('Descripción del Impacto Social')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()
                ->persistStepInQueryString()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('credit_id')
                    ->label('ID Crédito')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('project_name')
                    ->label('Proyecto')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\BadgeColumn::make('credit_type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'vcs',
                        'warning' => 'gold_standard',
                        'info' => 'cdm',
                        'primary' => 'vcu',
                        'secondary' => 'cer',
                        'danger' => 'rgu',
                        'gray' => 'custom',
                    ]),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'pending',
                        'info' => 'verified',
                        'warning' => 'issued',
                        'success' => 'available',
                        'gray' => 'retired',
                        'danger' => 'cancelled',
                        'dark' => 'expired',
                    ]),
                
                Tables\Columns\TextColumn::make('total_credits')
                    ->label('Total')
                    ->suffix(' tCO2e')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('available_credits')
                    ->label('Disponibles')
                    ->suffix(' tCO2e')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('project_country')
                    ->label('País')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('vintage_year')
                    ->label('Año Vintage')
                    ->date('Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_market_price')
                    ->label('Precio Mercado')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('additionality_demonstrated')
                    ->label('Adicionalidad')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('credit_type')
                    ->label('Tipo de Crédito')
                    ->options([
                        'vcs' => 'VCS',
                        'gold_standard' => 'Gold Standard',
                        'cdm' => 'CDM',
                        'vcu' => 'VCU',
                        'cer' => 'CER',
                        'rgu' => 'RGU',
                        'custom' => 'Personalizado'
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'verified' => 'Verificado',
                        'issued' => 'Emitido',
                        'available' => 'Disponible',
                        'retired' => 'Retirado',
                        'cancelled' => 'Cancelado',
                        'expired' => 'Expirado'
                    ]),
                
                Tables\Filters\Filter::make('available_credits')
                    ->label('Con Créditos Disponibles')
                    ->query(fn (Builder $query): Builder => $query->where('available_credits', '>', 0)),
                
                Tables\Filters\Filter::make('additionality')
                    ->label('Adicionalidad Demostrada')
                    ->query(fn (Builder $query): Builder => $query->where('additionality_demonstrated', true)),
                
                Tables\Filters\SelectFilter::make('project_country')
                    ->label('País')
                    ->options(function () {
                        return CarbonCredit::distinct('project_country')
                            ->pluck('project_country', 'project_country')
                            ->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('retire')
                    ->label('Retirar Créditos')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('credits_to_retire')
                            ->label('Cantidad a Retirar')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                        Forms\Components\Textarea::make('retirement_reason')
                            ->label('Razón del Retiro')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data, CarbonCredit $record) {
                        $creditsToRetire = $data['credits_to_retire'];
                        if ($creditsToRetire <= $record->available_credits) {
                            $record->update([
                                'available_credits' => $record->available_credits - $creditsToRetire,
                                'retired_credits' => $record->retired_credits + $creditsToRetire,
                                'retirement_reason' => $data['retirement_reason'],
                                'retirement_date' => now(),
                                'retired_by' => auth()->id(),
                            ]);
                        }
                    })
                    ->visible(fn (CarbonCredit $record): bool => $record->available_credits > 0),
                
                Tables\Actions\Action::make('verify')
                    ->label('Verificar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (CarbonCredit $record) {
                        $record->update([
                            'status' => 'verified',
                            'verification_date' => now(),
                        ]);
                    })
                    ->visible(fn (CarbonCredit $record): bool => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('verify_selected')
                        ->label('Verificar Seleccionados')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'verified',
                                        'verification_date' => now(),
                                    ]);
                                }
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
                Infolists\Components\Section::make('Información del Crédito')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('credit_id')
                                    ->label('ID del Crédito')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('registry_id')
                                    ->label('ID del Registro'),
                                Infolists\Components\TextEntry::make('serial_number')
                                    ->label('Número de Serie'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('credit_type')
                                    ->label('Tipo de Crédito')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge(),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Proyecto')
                    ->schema([
                        Infolists\Components\TextEntry::make('project_name')
                            ->label('Nombre del Proyecto'),
                        Infolists\Components\TextEntry::make('project_description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('project_type')
                                    ->label('Tipo'),
                                Infolists\Components\TextEntry::make('project_country')
                                    ->label('País'),
                                Infolists\Components\TextEntry::make('project_location')
                                    ->label('Ubicación'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Cantidades')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_credits')
                                    ->label('Total de Créditos')
                                    ->suffix(' tCO2e'),
                                Infolists\Components\TextEntry::make('available_credits')
                                    ->label('Disponibles')
                                    ->suffix(' tCO2e'),
                                Infolists\Components\TextEntry::make('retired_credits')
                                    ->label('Retirados')
                                    ->suffix(' tCO2e'),
                                Infolists\Components\TextEntry::make('transferred_credits')
                                    ->label('Transferidos')
                                    ->suffix(' tCO2e'),
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
            'index' => Pages\ListCarbonCredits::route('/'),
            'create' => Pages\CreateCarbonCredit::route('/create'),
            'view' => Pages\ViewCarbonCredit::route('/{record}'),
            'edit' => Pages\EditCarbonCredit::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'provider']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : 'success';
    }
}