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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;


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
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Título atractivo de la oferta'),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('URL amigable para la oferta'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->required()
                            ->helperText('Descripción detallada de la oferta'),
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(PreSaleOffer::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('visibility')
                                    ->label('Visibilidad')
                                    ->options(PreSaleOffer::getVisibilities())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(PreSaleOffer::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('organization_id')
                                    ->label('Organización')
                                    ->relationship('organization', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Organización que ofrece la preventa'),
                                Select::make('production_project_id')
                                    ->label('Proyecto de Producción')
                                    ->relationship('productionProject', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Proyecto asociado'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('approved_by')
                                    ->label('Aprobado por')
                                    ->relationship('approvedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración de Unidades')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_units_available')
                                    ->label('Total de Unidades Disponibles')
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

                Section::make('Precios y Pagos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('price_per_unit')
                                    ->label('Precio por Unidad')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Precio por unidad'),
                                TextInput::make('reservation_amount')
                                    ->label('Monto de Reserva')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Monto para reservar'),
                                TextInput::make('early_bird_discount')
                                    ->label('Descuento Early Bird (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Descuento por compra anticipada'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('minimum_order_quantity')
                                    ->label('Cantidad Mínima de Orden')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->default(1)
                                    ->helperText('Cantidad mínima por orden'),
                                TextInput::make('maximum_order_quantity')
                                    ->label('Cantidad Máxima de Orden')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Cantidad máxima por orden'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('allows_partial_payment')
                                    ->label('Permite Pago Parcial')
                                    ->helperText('¿Se puede pagar en cuotas?'),
                                Toggle::make('requires_full_payment')
                                    ->label('Requiere Pago Completo')
                                    ->default(true)
                                    ->helperText('¿Se requiere pago completo?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Inicia el')
                                    ->required()
                                    ->helperText('Cuándo comienza la oferta'),
                                DateTimePicker::make('ends_at')
                                    ->label('Termina el')
                                    ->required()
                                    ->helperText('Cuándo termina la oferta'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('early_bird_ends')
                                    ->label('Early Bird Termina el')
                                    ->helperText('Cuándo termina el descuento early bird'),
                                DateTimePicker::make('delivery_expected')
                                    ->label('Entrega Esperada')
                                    ->helperText('Cuándo se espera la entrega'),
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
                        RichEditor::make('refund_policy')
                            ->label('Política de Reembolso')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('delivery_terms')
                            ->label('Términos de Entrega')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Marketing y Promoción')
                    ->schema([
                        RichEditor::make('marketing_copy')
                            ->label('Texto de Marketing')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Texto promocional para marketing'),
                        RichEditor::make('features_benefits')
                            ->label('Características y Beneficios')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('social_media_hashtags')
                                    ->label('Hashtags de Redes Sociales')
                                    ->maxLength(255)
                                    ->helperText('Hashtags para promoción'),
                                TextInput::make('marketing_budget')
                                    ->label('Presupuesto de Marketing')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true)
                                    ->helperText('¿La oferta está activa?'),
                                Toggle::make('is_featured')
                                    ->label('Destacada')
                                    ->helperText('¿Mostrar como destacada?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación?'),
                                Toggle::make('auto_activate')
                                    ->label('Activación Automática')
                                    ->helperText('¿Se activa automáticamente?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('send_notifications')
                                    ->label('Enviar Notificaciones')
                                    ->default(true)
                                    ->helperText('¿Enviar notificaciones?'),
                                Toggle::make('track_analytics')
                                    ->label('Seguir Analíticas')
                                    ->default(true)
                                    ->helperText('¿Seguir métricas de rendimiento?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos y Etiquetas')
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

                Section::make('Documentos y Archivos')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Imágenes')
                            ->multiple()
                            ->image()
                            ->directory('pre-sale-offers')
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->helperText('Máximo 10 imágenes de 5MB cada una'),
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('pre-sale-offers/documents')
                            ->maxFiles(20)
                            ->maxSize(10240)
                            ->helperText('Máximo 20 documentos de 10MB cada uno'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'cancelled',
                        'info' => 'draft',
                        'secondary' => 'expired',
                        'gray' => 'suspended',
                    ])
                    ->formatStateUsing(fn (string $state): string => PreSaleOffer::getStatuses()[$state] ?? $state),
                BadgeColumn::make('visibility')
                    ->label('Visibilidad')
                    ->colors([
                        'primary' => 'public',
                        'success' => 'members_only',
                        'warning' => 'invite_only',
                        'danger' => 'private',
                        'info' => 'restricted',
                    ])
                    ->formatStateUsing(fn (string $state): string => PreSaleOffer::getVisibilities()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => PreSaleOffer::getPriorities()[$state] ?? $state),
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
                TextColumn::make('price_per_unit')
                    ->label('Precio/Unidad')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('reservation_amount')
                    ->label('Monto Reserva')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('starts_at')
                    ->label('Inicia')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('ends_at')
                    ->label('Termina')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                ToggleColumn::make('is_featured')
                    ->label('Destacada'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PreSaleOffer::getStatuses())
                    ->multiple(),
                SelectFilter::make('visibility')
                    ->label('Visibilidad')
                    ->options(PreSaleOffer::getVisibilities())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(PreSaleOffer::getPriorities())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activas'),
                Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Solo Destacadas'),
                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('ends_at', '<', now()))
                    ->label('Expiradas'),
                Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder => $query->where('ends_at', '<=', now()->addDays(7)))
                    ->label('Expiran Pronto'),
                Filter::make('high_demand')
                    ->query(fn (Builder $query): Builder => $query->where('units_reserved', '>=', 50))
                    ->label('Alta Demanda (≥50)'),
                Filter::make('low_demand')
                    ->query(fn (Builder $query): Builder => $query->where('units_reserved', '<', 10))
                    ->label('Baja Demanda (<10)'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('starts_from')
                            ->label('Inicia desde'),
                        DateTimePicker::make('starts_until')
                            ->label('Inicia hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['starts_from'],
                                fn (Builder $query, $date): Builder => $query->where('starts_at', '>=', $date),
                            )
                            ->when(
                                $data['starts_until'],
                                fn (Builder $query, $date): Builder => $query->where('starts_at', '<=', $date),
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
                                fn (Builder $query, $price): Builder => $query->where('price_per_unit', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_unit', '<=', $price),
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
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (PreSaleOffer $record) => $record->status === 'active')
                    ->action(function (PreSaleOffer $record) {
                        $record->update(['status' => 'suspended']);
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
                        $newRecord->title = $newRecord->title . ' (Copia)';
                        $newRecord->slug = $newRecord->slug . '-copy';
                        $newRecord->status = 'draft';
                        $newRecord->is_active = false;
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
                    Tables\Actions\BulkAction::make('deactivate_all')
                        ->label('Desactivar Todas')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'suspended']);
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
                    Tables\Actions\BulkAction::make('update_visibility')
                        ->label('Actualizar Visibilidad')
                        ->icon('heroicon-o-eye')
                        ->form([
                            Select::make('visibility')
                                ->label('Visibilidad')
                                ->options(PreSaleOffer::getVisibilities())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['visibility' => $data['visibility']]);
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
        return parent::getEloquentQuery();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $expiredCount = static::getModel()::where('ends_at', '<', now())->count();
        
        if ($expiredCount > 0) {
            return 'danger';
        }
        
        $expiringSoonCount = static::getModel()::where('ends_at', '<=', now()->addDays(7))->count();
        
        if ($expiringSoonCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
