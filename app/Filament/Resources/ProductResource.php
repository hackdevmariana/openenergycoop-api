<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Tienda Energética';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('provider_id')
                            ->label('Proveedor')
                            ->relationship('provider', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->options(Provider::TYPES)
                                    ->required(),
                            ]),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->helperText('Se genera automáticamente del nombre'),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Producto')
                            ->required()
                            ->options(Product::TYPES)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Auto-configurar unidades según el tipo
                                match ($state) {
                                    'energy_kwh' => $set('unit', 'kWh'),
                                    'mining_ths' => $set('unit', 'TH/s'),
                                    'production_right' => $set('unit', 'percentage'),
                                    'storage_capacity' => $set('unit', 'kWh'),
                                    default => $set('unit', 'unit'),
                                };
                            }),
                        
                        Forms\Components\Select::make('unit')
                            ->label('Unidad')
                            ->required()
                            ->options(Product::UNITS)
                            ->native(false),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Precios y Comisiones')
                    ->schema([
                        Forms\Components\TextInput::make('base_purchase_price')
                            ->label('Precio de Compra')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        
                        Forms\Components\TextInput::make('base_sale_price')
                            ->label('Precio de Venta')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        
                        Forms\Components\Select::make('commission_type')
                            ->label('Tipo de Comisión')
                            ->options(Product::COMMISSION_TYPES)
                            ->native(false)
                            ->live(),
                        
                        Forms\Components\TextInput::make('commission_value')
                            ->label('Valor de Comisión')
                            ->numeric()
                            ->step(0.01)
                            ->suffix(fn (Forms\Get $get): string => 
                                $get('commission_type') === 'percentage' ? '%' : '€'
                            )
                            ->visible(fn (Forms\Get $get): bool => 
                                in_array($get('commission_type'), ['percentage', 'fixed'])
                            ),
                        
                        Forms\Components\Select::make('surcharge_type')
                            ->label('Tipo de Recargo')
                            ->options(Product::SURCHARGE_TYPES)
                            ->native(false)
                            ->live(),
                        
                        Forms\Components\TextInput::make('surcharge_value')
                            ->label('Valor de Recargo')
                            ->numeric()
                            ->step(0.01)
                            ->suffix(fn (Forms\Get $get): string => 
                                $get('surcharge_type') === 'percentage' ? '%' : '€'
                            )
                            ->visible(fn (Forms\Get $get): bool => 
                                in_array($get('surcharge_type'), ['percentage', 'fixed'])
                            ),
                    ])->columns(3),

                Forms\Components\Section::make('Sostenibilidad y Ubicación')
                    ->schema([
                        Forms\Components\TextInput::make('renewable_percentage')
                            ->label('% Renovable')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.1),
                        
                        Forms\Components\TextInput::make('carbon_footprint')
                            ->label('Huella de Carbono')
                            ->numeric()
                            ->suffix('kg CO₂/kWh')
                            ->step(0.001)
                            ->minValue(0),
                        
                        Forms\Components\TextInput::make('geographical_zone')
                            ->label('Zona Geográfica')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Fechas e Inventario')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha de Inicio'),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Fecha de Fin')
                            ->after('start_date'),
                        
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Cantidad en Stock')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Dejar vacío para stock ilimitado'),
                        
                        Forms\Components\TextInput::make('estimated_lifespan_years')
                            ->label('Vida Útil (años)')
                            ->numeric()
                            ->minValue(1),
                    ])->columns(4),

                Forms\Components\Section::make('Características Adicionales')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Imagen del Producto')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->imageEditor(),
                        
                        Forms\Components\TagsInput::make('features')
                            ->label('Características')
                            ->placeholder('Agregar característica...'),
                        
                        Forms\Components\TagsInput::make('keywords')
                            ->label('Palabras Clave')
                            ->placeholder('Agregar palabra clave...'),
                        
                        Forms\Components\Textarea::make('warranty_info')
                            ->label('Información de Garantía')
                            ->rows(2),
                        
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadatos')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->square()
                    ->size(40),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('provider.name')
                    ->label('Proveedor')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'energy_kwh' => 'success',
                        'production_right' => 'warning',
                        'mining_ths' => 'info',
                        'energy_bond' => 'purple',
                        'storage_capacity' => 'orange',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Product::TYPES[$state] ?? $state),
                
                Tables\Columns\TextColumn::make('base_purchase_price')
                    ->label('Precio')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('renewable_percentage')
                    ->label('% Renovable')
                    ->suffix('%')
                    ->color(fn (?float $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sustainability_score')
                    ->label('Score Sostenible')
                    ->getStateUsing(fn (Product $record): string => 
                        number_format($record->getSustainabilityScore(), 1) . '/100'
                    )
                    ->badge()
                    ->color(fn (Product $record): string => match (true) {
                        $record->getSustainabilityScore() >= 80 => 'success',
                        $record->getSustainabilityScore() >= 60 => 'warning',
                        default => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->formatStateUsing(fn (?int $state): string => 
                        $state === null ? 'Ilimitado' : number_format($state)
                    )
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'success',
                        $state > 100 => 'success',
                        $state > 10 => 'warning',
                        default => 'danger',
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->relationship('provider', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(Product::TYPES),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
                
                Tables\Filters\Filter::make('sustainable')
                    ->label('Productos Sostenibles (80%+ renovable)')
                    ->query(fn (Builder $query): Builder => $query->where('renewable_percentage', '>=', 80)),
                
                Tables\Filters\Filter::make('in_stock')
                    ->label('En Stock')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->whereNull('stock_quantity')->orWhere('stock_quantity', '>', 0);
                    })),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('calculate_price')
                        ->label('Calcular Precio Final')
                        ->icon('heroicon-o-calculator')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->label('Cantidad')
                                ->numeric()
                                ->default(1)
                                ->required(),
                        ])
                        ->action(function (Product $record, array $data) {
                            $calculation = $record->calculateFinalPrice($data['quantity']);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Cálculo de Precio')
                                ->body(sprintf(
                                    'Precio base: €%.2f | Comisión: €%.2f | Recargo: €%.2f | **Total: €%.2f**',
                                    $calculation['base_price'],
                                    $calculation['commission'],
                                    $calculation['surcharge'],
                                    $calculation['final_price']
                                ))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
}

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'provider.name'];
    }
}