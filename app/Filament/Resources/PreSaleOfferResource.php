<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PreSaleOfferResource\Pages;
use App\Filament\Resources\PreSaleOfferResource\RelationManagers;
use App\Models\PreSaleOffer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PreSaleOfferResource extends Resource
{
    protected static ?string $model = PreSaleOffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Marketing y Ventas';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Ofertas de Preventa';

    protected static ?string $modelLabel = 'Oferta de Preventa';

    protected static ?string $pluralModelLabel = 'Ofertas de Preventa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('offer_number')
                                    ->label('Número de Oferta')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Número único de identificación'),
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Título atractivo de la oferta'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->required()
                            ->helperText('Descripción detallada de la oferta'),
                        Grid::make(2)
                            ->schema([
                                Select::make('offer_type')
                                    ->label('Tipo de Oferta')
                                    ->options(PreSaleOffer::getOfferTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(PreSaleOffer::getStatuses())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->required()
                                    ->helperText('Cuándo comienza la oferta'),
                                DatePicker::make('end_date')
                                    ->label('Fecha de Fin')
                                    ->required()
                                    ->helperText('Cuándo termina la oferta'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('early_bird_end_date')
                                    ->label('Fin Early Bird')
                                    ->helperText('Cuándo termina el descuento early bird'),
                                DatePicker::make('founder_end_date')
                                    ->label('Fin Fundador')
                                    ->helperText('Cuándo termina el descuento fundador'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Unidades y Disponibilidad')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_units_available')
                                    ->label('Total de Unidades')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->required()
                                    ->helperText('Número total de unidades'),
                                TextInput::make('units_reserved')
                                    ->label('Unidades Reservadas')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Unidades ya reservadas'),
                                TextInput::make('units_sold')
                                    ->label('Unidades Vendidas')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Unidades ya vendidas'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_units_per_customer')
                                    ->label('Máximo por Cliente')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Máximo de unidades por cliente'),
                            ]),
                        Placeholder::make('units_remaining')
                            ->content(function ($get) {
                                $total = $get('total_units_available') ?: 0;
                                $reserved = $get('units_reserved') ?: 0;
                                $sold = $get('units_sold') ?: 0;
                                $remaining = $total - $reserved - $sold;
                                
                                return "**Unidades Restantes: {$remaining}**";
                            })
                            ->visible(fn ($get) => $get('total_units_available') > 0),
                    ])
                    ->collapsible(),

                Section::make('Precios y Descuentos')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('early_bird_price')
                                    ->label('Precio Early Bird')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Precio durante el período early bird'),
                                TextInput::make('founder_price')
                                    ->label('Precio Fundador')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Precio durante el período fundador'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('regular_price')
                                    ->label('Precio Regular')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Precio regular de la oferta'),
                                TextInput::make('final_price')
                                    ->label('Precio Final')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Precio final después de descuentos'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('savings_percentage')
                                    ->label('Porcentaje de Ahorro')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de descuento'),
                                TextInput::make('savings_amount')
                                    ->label('Monto de Ahorro')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Monto de descuento en dólares'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label('Destacada')
                                    ->helperText('¿Mostrar como destacada?'),
                                Toggle::make('is_public')
                                    ->label('Pública')
                                    ->default(true)
                                    ->helperText('¿La oferta es pública?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Términos y Condiciones')
                    ->schema([
                        RichEditor::make('terms_conditions')
                            ->label('Términos y Condiciones')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Términos legales de la oferta'),
                        RichEditor::make('delivery_timeline')
                            ->label('Cronograma de Entrega')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Cuándo se entregará el producto'),
                        RichEditor::make('risk_disclosure')
                            ->label('Divulgación de Riesgos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Riesgos asociados con la inversión'),
                    ])
                    ->collapsible(),

                Section::make('Características y Beneficios')
                    ->schema([
                        RichEditor::make('included_features')
                            ->label('Características Incluidas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Características incluidas en la oferta'),
                        RichEditor::make('excluded_features')
                            ->label('Características Excluidas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Características NO incluidas'),
                        RichEditor::make('bonus_items')
                            ->label('Elementos Bonus')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Elementos adicionales incluidos'),
                    ])
                    ->collapsible(),

                Section::make('Beneficios Especiales')
                    ->schema([
                        RichEditor::make('early_access_benefits')
                            ->label('Beneficios de Acceso Anticipado')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Beneficios para compradores early bird'),
                        RichEditor::make('founder_benefits')
                            ->label('Beneficios de Fundador')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Beneficios para compradores fundadores'),
                        RichEditor::make('marketing_materials')
                            ->label('Materiales de Marketing')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Materiales promocionales disponibles'),
                    ])
                    ->collapsible(),

                Section::make('Gestión y Aprobación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('created_by')
                                    ->label('Creado por')
                                    ->numeric()
                                    ->required()
                                    ->helperText('ID del usuario que creó'),
                                TextInput::make('approved_by')
                                    ->label('Aprobado por')
                                    ->numeric()
                                    ->helperText('ID del usuario que aprobó'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('approved_at')
                                    ->label('Fecha de Aprobación')
                                    ->helperText('Cuándo fue aprobado'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos')
                    ->schema([
                        TagsInput::make('tags')
                            ->label('Etiquetas')
                            ->separator(','),
                        RichEditor::make('notes')
                            ->label('Notas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('offer_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('offer_type')
                    ->label('Tipo')
                    ->colors([
                        'warning' => 'early_bird',
                        'purple' => 'founder',
                        'danger' => 'limited_time',
                        'indigo' => 'exclusive',
                        'primary' => 'beta',
                        'success' => 'pilot',
                    ])
                    ->formatStateUsing(fn (string $state): string => PreSaleOffer::getOfferTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'active',
                        'warning' => 'paused',
                        'danger' => 'expired',
                        'danger' => 'cancelled',
                        'primary' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => PreSaleOffer::getStatuses()[$state] ?? $state),
                TextColumn::make('total_units_available')
                    ->label('Total Unidades')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                TextColumn::make('units_reserved')
                    ->label('Reservadas')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                TextColumn::make('units_sold')
                    ->label('Vendidas')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                TextColumn::make('regular_price')
                    ->label('Precio Regular')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('early_bird_price')
                    ->label('Precio Early Bird')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('founder_price')
                    ->label('Precio Fundador')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('start_date')
                    ->label('Inicia')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('end_date')
                    ->label('Termina')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_featured')
                    ->label('Destacada'),
                ToggleColumn::make('is_public')
                    ->label('Pública'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('offer_type')
                    ->label('Tipo de Oferta')
                    ->options(PreSaleOffer::getOfferTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PreSaleOffer::getStatuses())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active'))
                    ->label('Solo Activas'),
                Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Solo Destacadas'),
                Filter::make('public')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true))
                    ->label('Solo Públicas'),
                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<', now()))
                    ->label('Expiradas'),
                Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<=', now()->addDays(7)))
                    ->label('Expiran Pronto'),
                Filter::make('early_bird_active')
                    ->query(fn (Builder $query): Builder => $query->where('early_bird_end_date', '>=', now()))
                    ->label('Early Bird Activo'),
                Filter::make('founder_active')
                    ->query(fn (Builder $query): Builder => $query->where('founder_end_date', '>=', now()))
                    ->label('Fundador Activo'),
                Filter::make('high_demand')
                    ->query(fn (Builder $query): Builder => $query->where('units_reserved', '>=', 50))
                    ->label('Alta Demanda (≥50)'),
                Filter::make('low_demand')
                    ->query(fn (Builder $query): Builder => $query->where('units_reserved', '<', 10))
                    ->label('Baja Demanda (<10)'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_from')
                            ->label('Inicia desde'),
                        DatePicker::make('start_until')
                            ->label('Inicia hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn (Builder $query, $date): Builder => $query->where('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn (Builder $query, $date): Builder => $query->where('start_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Inicio'),
                Filter::make('price_range')
                    ->form([
                        TextInput::make('min_price')
                            ->label('Precio mínimo')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_price')
                            ->label('Precio máximo')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('regular_price', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('regular_price', '<=', $price),
                            );
                    })
                    ->label('Rango de Precios'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (PreSaleOffer $record) => $record->status !== 'active')
                    ->action(function (PreSaleOffer $record) {
                        $record->update(['status' => 'active']);
                    }),
                Tables\Actions\Action::make('pause')
                    ->label('Pausar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (PreSaleOffer $record) => $record->status === 'active')
                    ->action(function (PreSaleOffer $record) {
                        $record->update(['status' => 'paused']);
                    }),
                Tables\Actions\Action::make('mark_featured')
                    ->label('Destacar')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (PreSaleOffer $record) => !$record->is_featured)
                    ->action(function (PreSaleOffer $record) {
                        $record->update(['is_featured' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (PreSaleOffer $record) {
                        $newRecord = $record->replicate();
                        $newRecord->offer_number = $newRecord->offer_number . '-COPY';
                        $newRecord->title = $newRecord->title . ' (Copia)';
                        $newRecord->status = 'draft';
                        $newRecord->units_reserved = 0;
                        $newRecord->units_sold = 0;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate_all')
                        ->label('Activar Todas')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'active']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('pause_all')
                        ->label('Pausar Todas')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'paused']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_featured_all')
                        ->label('Destacar Todas')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_featured' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(PreSaleOffer::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_offer_type')
                        ->label('Actualizar Tipo')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('offer_type')
                                ->label('Tipo de Oferta')
                                ->options(PreSaleOffer::getOfferTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['offer_type' => $data['offer_type']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListPreSaleOffers::route('/'),
            'create' => Pages\CreatePreSaleOffer::route('/create'),
            'edit' => Pages\EditPreSaleOffer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $expiredCount = static::getModel()::where('end_date', '<', now())->count();
        
        if ($expiredCount > 0) {
            return 'danger';
        }
        
        $expiringSoonCount = static::getModel()::where('end_date', '<=', now()->addDays(7))->count();
        
        if ($expiringSoonCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}

