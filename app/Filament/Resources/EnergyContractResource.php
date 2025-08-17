<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyContractResource\Pages;
use App\Models\EnergyContract;
use App\Models\User;
use App\Models\Provider;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class EnergyContractResource extends Resource
{
    protected static ?string $model = EnergyContract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Contratos Energéticos';
    
    protected static ?string $modelLabel = 'Contrato Energético';
    
    protected static ?string $pluralModelLabel = 'Contratos Energéticos';
    
    protected static ?string $navigationGroup = 'Tienda Energética';
    
    protected static ?int $navigationSort = 1;

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
                                        ->label('Cliente')
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
                            
                            Forms\Components\Select::make('product_id')
                                ->label('Producto Energético')
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('contract_number')
                                        ->label('Número de Contrato')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->default(function () {
                                            return 'CTR-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
                                        }),
                                    
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nombre del Contrato')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            
                            Forms\Components\Textarea::make('description')
                                ->label('Descripción')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Tipo y Estado')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('type')
                                        ->label('Tipo de Contrato')
                                        ->options([
                                            'supply' => 'Suministro de Energía',
                                            'generation' => 'Generación de Energía',
                                            'storage' => 'Almacenamiento',
                                            'hybrid' => 'Combinado'
                                        ])
                                        ->required(),
                                    
                                    Forms\Components\Select::make('status')
                                        ->label('Estado')
                                        ->options([
                                            'draft' => 'Borrador',
                                            'pending' => 'Pendiente',
                                            'active' => 'Activo',
                                            'suspended' => 'Suspendido',
                                            'terminated' => 'Terminado',
                                            'expired' => 'Expirado'
                                        ])
                                        ->default('draft')
                                        ->required(),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Términos Económicos')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('total_value')
                                        ->label('Valor Total')
                                        ->numeric()
                                        ->prefix('€')
                                        ->required(),
                                    
                                    Forms\Components\TextInput::make('monthly_payment')
                                        ->label('Pago Mensual')
                                        ->numeric()
                                        ->prefix('€'),
                                    
                                    Forms\Components\Select::make('currency')
                                        ->label('Moneda')
                                        ->options([
                                            'EUR' => 'Euro (€)',
                                            'USD' => 'Dólar ($)',
                                            'GBP' => 'Libra (£)'
                                        ])
                                        ->default('EUR'),
                                ]),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('deposit_amount')
                                        ->label('Monto del Depósito')
                                        ->numeric()
                                        ->prefix('€'),
                                    
                                    Forms\Components\Toggle::make('deposit_paid')
                                        ->label('Depósito Pagado')
                                        ->default(false),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Términos Energéticos')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('contracted_power')
                                        ->label('Potencia Contratada')
                                        ->numeric()
                                        ->suffix('kW')
                                        ->required(),
                                    
                                    Forms\Components\TextInput::make('estimated_annual_consumption')
                                        ->label('Consumo Anual Estimado')
                                        ->numeric()
                                        ->suffix('kWh'),
                                ]),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('guaranteed_supply_percentage')
                                        ->label('% Suministro Garantizado')
                                        ->numeric()
                                        ->suffix('%')
                                        ->default(100),
                                    
                                    Forms\Components\TextInput::make('green_energy_percentage')
                                        ->label('% Energía Verde')
                                        ->numeric()
                                        ->suffix('%')
                                        ->default(0),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Fechas y Períodos')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('start_date')
                                        ->label('Fecha de Inicio')
                                        ->required(),
                                    
                                    Forms\Components\DatePicker::make('end_date')
                                        ->label('Fecha de Fin')
                                        ->required()
                                        ->after('start_date'),
                                ]),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('signed_date')
                                        ->label('Fecha de Firma'),
                                    
                                    Forms\Components\DatePicker::make('activation_date')
                                        ->label('Fecha de Activación'),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Facturación')
                        ->schema([
                            Forms\Components\Select::make('billing_frequency')
                                ->label('Frecuencia de Facturación')
                                ->options([
                                    'monthly' => 'Mensual',
                                    'quarterly' => 'Trimestral', 
                                    'semi_annual' => 'Semestral',
                                    'annual' => 'Anual'
                                ])
                                ->default('monthly'),
                            
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('next_billing_date')
                                        ->label('Próxima Facturación'),
                                    
                                    Forms\Components\DatePicker::make('last_billing_date')
                                        ->label('Última Facturación'),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Sostenibilidad')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('estimated_co2_reduction')
                                        ->label('Reducción CO2 Estimada')
                                        ->numeric()
                                        ->suffix('kg/año'),
                                    
                                    Forms\Components\Toggle::make('carbon_neutral')
                                        ->label('Carbono Neutral')
                                        ->default(false),
                                ]),
                            
                            Forms\Components\Repeater::make('sustainability_certifications')
                                ->label('Certificaciones de Sostenibilidad')
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Certificación')
                                        ->required(),
                                    Forms\Components\TextInput::make('issuer')
                                        ->label('Emisor'),
                                    Forms\Components\DatePicker::make('date')
                                        ->label('Fecha'),
                                ])
                                ->collapsible()
                                ->columnSpanFull(),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Información Adicional')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('auto_renewal')
                                        ->label('Renovación Automática')
                                        ->default(false),
                                    
                                    Forms\Components\TextInput::make('renewal_period_months')
                                        ->label('Período de Renovación (meses)')
                                        ->numeric(),
                                ]),
                            
                            Forms\Components\TextInput::make('early_termination_fee')
                                ->label('Penalización por Terminación Temprana')
                                ->numeric()
                                ->prefix('€'),
                            
                            Forms\Components\RichEditor::make('terms_conditions')
                                ->label('Términos y Condiciones')
                                ->columnSpanFull(),
                            
                            Forms\Components\Textarea::make('notes')
                                ->label('Notas')
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
                Tables\Columns\TextColumn::make('contract_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('provider.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'supply',
                        'success' => 'generation',
                        'warning' => 'storage',
                        'info' => 'hybrid',
                    ])
                    ->icons([
                        'heroicon-o-bolt' => 'supply',
                        'heroicon-o-sun' => 'generation',
                        'heroicon-o-battery-100' => 'storage',
                        'heroicon-o-puzzle-piece' => 'hybrid',
                    ]),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'suspended',
                        'gray' => 'terminated',
                        'dark' => 'expired',
                    ]),
                
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor Total')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('carbon_neutral')
                    ->label('Carbono Neutral')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'supply' => 'Suministro',
                        'generation' => 'Generación',
                        'storage' => 'Almacenamiento',
                        'hybrid' => 'Combinado'
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'pending' => 'Pendiente',
                        'active' => 'Activo',
                        'suspended' => 'Suspendido',
                        'terminated' => 'Terminado',
                        'expired' => 'Expirado'
                    ]),
                
                Tables\Filters\Filter::make('carbon_neutral')
                    ->label('Solo Carbono Neutral')
                    ->query(fn (Builder $query): Builder => $query->where('carbon_neutral', true)),
                
                Tables\Filters\Filter::make('active_contracts')
                    ->label('Contratos Activos')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (EnergyContract $record) {
                        $record->update([
                            'status' => 'active',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                    })
                    ->visible(fn (EnergyContract $record): bool => $record->status === 'pending'),
                
                Tables\Actions\Action::make('suspend')
                    ->label('Suspender')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (EnergyContract $record) {
                        $record->update(['status' => 'suspended']);
                    })
                    ->visible(fn (EnergyContract $record): bool => $record->status === 'active'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Aprobar Seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'active',
                                        'approved_at' => now(),
                                        'approved_by' => auth()->id(),
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
                Infolists\Components\Section::make('Información del Contrato')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('contract_number')
                                    ->label('Número de Contrato')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nombre'),
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Cliente'),
                                Infolists\Components\TextEntry::make('provider.name')
                                    ->label('Proveedor'),
                            ]),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ]),
                
                Infolists\Components\Section::make('Estado y Tipo')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Tipo')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'supply' => 'primary',
                                        'generation' => 'success',
                                        'storage' => 'warning',
                                        'hybrid' => 'info',
                                    }),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'active' => 'success',
                                        'suspended' => 'danger',
                                        'terminated' => 'gray',
                                        'expired' => 'dark',
                                    }),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Información Económica')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_value')
                                    ->label('Valor Total')
                                    ->money('EUR'),
                                Infolists\Components\TextEntry::make('monthly_payment')
                                    ->label('Pago Mensual')
                                    ->money('EUR'),
                                Infolists\Components\TextEntry::make('deposit_amount')
                                    ->label('Depósito')
                                    ->money('EUR'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Términos Energéticos')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('contracted_power')
                                    ->label('Potencia Contratada')
                                    ->suffix(' kW'),
                                Infolists\Components\TextEntry::make('estimated_annual_consumption')
                                    ->label('Consumo Anual Estimado')
                                    ->suffix(' kWh'),
                                Infolists\Components\TextEntry::make('guaranteed_supply_percentage')
                                    ->label('Suministro Garantizado')
                                    ->suffix('%'),
                                Infolists\Components\TextEntry::make('green_energy_percentage')
                                    ->label('Energía Verde')
                                    ->suffix('%'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Fechas Importantes')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->date(),
                                Infolists\Components\TextEntry::make('end_date')
                                    ->label('Fecha de Fin')
                                    ->date(),
                                Infolists\Components\TextEntry::make('signed_date')
                                    ->label('Fecha de Firma')
                                    ->date(),
                                Infolists\Components\TextEntry::make('activation_date')
                                    ->label('Fecha de Activación')
                                    ->date(),
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
            'index' => Pages\ListEnergyContracts::route('/'),
            'create' => Pages\CreateEnergyContract::route('/create'),
            'view' => Pages\ViewEnergyContract::route('/{record}'),
            'edit' => Pages\EditEnergyContract::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'provider', 'product']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : 'primary';
    }
}