<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource\Pages;
use App\Filament\Resources\DiscountCodeResource\RelationManagers;
use App\Models\DiscountCode;
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


class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Marketing y Ventas';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Códigos de Descuento';

    protected static ?string $modelLabel = 'Código de Descuento';

    protected static ?string $pluralModelLabel = 'Códigos de Descuento';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Código')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Código único del descuento')
                                    ->copyable()
                                    ->tooltip('Haz clic para copiar'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre descriptivo del descuento'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada del descuento'),
                        Grid::make(3)
                            ->schema([
                                Select::make('discount_type')
                                    ->label('Tipo de Descuento')
                                    ->options(DiscountCode::getDiscountTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(DiscountCode::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(DiscountCode::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Descuento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('discount_value')
                                    ->label('Valor del Descuento')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Valor del descuento'),
                                TextInput::make('minimum_order_amount')
                                    ->label('Monto Mínimo de Orden')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Monto mínimo para aplicar'),
                                TextInput::make('maximum_discount_amount')
                                    ->label('Descuento Máximo')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Límite máximo del descuento'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('discount_percentage')
                                    ->label('Porcentaje de Descuento (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de descuento'),
                                TextInput::make('fixed_amount')
                                    ->label('Monto Fijo')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Monto fijo de descuento'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_stackable')
                                    ->label('Acumulable')
                                    ->helperText('¿Se puede combinar con otros descuentos?'),
                                Toggle::make('is_first_time_only')
                                    ->label('Solo Primera Compra')
                                    ->helperText('¿Solo para clientes nuevos?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Vigencia y Límites')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('valid_from')
                                    ->label('Válido Desde')
                                    ->required()
                                    ->helperText('Cuándo comienza a ser válido'),
                                DateTimePicker::make('valid_to')
                                    ->label('Válido Hasta')
                                    ->required()
                                    ->helperText('Cuándo expira'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('usage_limit')
                                    ->label('Límite de Uso')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Número máximo de usos'),
                                TextInput::make('usage_count')
                                    ->label('Usos Actuales')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Veces que se ha usado'),
                                TextInput::make('per_user_limit')
                                    ->label('Límite por Usuario')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Máximo usos por usuario'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('daily_limit')
                                    ->label('Límite Diario')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Máximo usos por día'),
                                TextInput::make('monthly_limit')
                                    ->label('Límite Mensual')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Máximo usos por mes'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Aplicación y Restricciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('applies_to_type')
                                    ->label('Se Aplica a')
                                    ->options([
                                        'all' => 'Todos los Productos',
                                        'products' => 'Productos Específicos',
                                        'categories' => 'Categorías',
                                        'brands' => 'Marcas',
                                        'collections' => 'Colecciones',
                                        'custom' => 'Personalizado',
                                    ])
                                    ->searchable(),
                                TextInput::make('applies_to_id')
                                    ->label('ID de Aplicación')
                                    ->numeric()
                                    ->helperText('ID específico para aplicar'),
                            ]),
                        RichEditor::make('product_restrictions')
                            ->label('Restricciones de Productos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Productos o categorías excluidas'),
                        RichEditor::make('user_restrictions')
                            ->label('Restricciones de Usuarios')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Tipos de usuarios excluidos'),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('exclude_sale_items')
                                    ->label('Excluir Productos en Oferta')
                                    ->helperText('¿No aplicar a productos ya en oferta?'),
                                Toggle::make('exclude_clearance')
                                    ->label('Excluir Liquidación')
                                    ->helperText('¿No aplicar a productos de liquidación?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('affiliate_id')
                                    ->label('Afiliado')
                                    ->relationship('affiliate', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Afiliado asociado'),
                                Select::make('campaign_id')
                                    ->label('Campaña')
                                    ->relationship('campaign', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Campaña de marketing'),
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

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->helperText('¿El código está activo?'),
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para usar?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label('Destacado')
                                    ->helperText('¿Mostrar como destacado?'),
                                Toggle::make('is_public')
                                    ->label('Público')
                                    ->helperText('¿Visible para todos los usuarios?'),
                            ]),
                        RichEditor::make('terms_conditions')
                            ->label('Términos y Condiciones')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Términos específicos del descuento'),
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

                Section::make('Estadísticas y Seguimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_revenue_generated')
                                    ->label('Ingresos Generados')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Total de ingresos generados'),
                                TextInput::make('total_orders')
                                    ->label('Total de Órdenes')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Órdenes que usaron el código'),
                                TextInput::make('average_order_value')
                                    ->label('Valor Promedio de Orden')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Valor promedio de las órdenes'),
                            ]),
                        RichEditor::make('performance_notes')
                            ->label('Notas de Rendimiento')
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
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Haz clic para copiar')
                    ->limit(15),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                BadgeColumn::make('discount_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'percentage',
                        'success' => 'fixed_amount',
                        'warning' => 'free_shipping',
                        'danger' => 'buy_one_get_one',
                        'info' => 'bogo',
                        'secondary' => 'custom',
                    ])
                    ->formatStateUsing(fn (string $state): string => DiscountCode::getDiscountTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'expired',
                        'info' => 'scheduled',
                        'secondary' => 'disabled',
                    ])
                    ->formatStateUsing(fn (string $state): string => DiscountCode::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => DiscountCode::getPriorities()[$state] ?? $state),
                TextColumn::make('discount_value')
                    ->label('Valor')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->discount_type === 'percentage') {
                            return $state . '%';
                        }
                        return '$' . number_format($state, 2);
                    }),
                TextColumn::make('minimum_order_amount')
                    ->label('Mínimo')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('usage_count')
                    ->label('Usos')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'primary',
                        $state >= 25 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('usage_limit')
                    ->label('Límite')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('valid_from')
                    ->label('Válido Desde')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('valid_to')
                    ->label('Válido Hasta')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('total_revenue_generated')
                    ->label('Ingresos Generados')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                ToggleColumn::make('is_featured')
                    ->label('Destacado'),
                ToggleColumn::make('is_public')
                    ->label('Público'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('discount_type')
                    ->label('Tipo de Descuento')
                    ->options(DiscountCode::getDiscountTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(DiscountCode::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(DiscountCode::getPriorities())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Solo Destacados'),
                Filter::make('public')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true))
                    ->label('Solo Públicos'),
                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('valid_to', '<', now()))
                    ->label('Expirados'),
                Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder => $query->where('valid_to', '<=', now()->addDays(7)))
                    ->label('Expiran Pronto'),
                Filter::make('high_usage')
                    ->query(fn (Builder $query): Builder => $query->where('usage_count', '>=', 50))
                    ->label('Alto Uso (≥50)'),
                Filter::make('low_usage')
                    ->query(fn (Builder $query): Builder => $query->where('usage_count', '<', 10))
                    ->label('Bajo Uso (<10)'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('valid_from')
                            ->label('Válido desde'),
                        DateTimePicker::make('valid_until')
                            ->label('Válido hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['valid_from'],
                                fn (Builder $query, $date): Builder => $query->where('valid_from', '>=', $date),
                            )
                            ->when(
                                $data['valid_until'],
                                fn (Builder $query, $date): Builder => $query->where('valid_to', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas'),
                Filter::make('value_range')
                    ->form([
                        TextInput::make('min_value')
                            ->label('Valor mínimo')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_value')
                            ->label('Valor máximo')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_value'],
                                fn (Builder $query, $value): Builder => $query->where('discount_value', '>=', $value),
                            )
                            ->when(
                                $data['max_value'],
                                fn (Builder $query, $value): Builder => $query->where('discount_value', '<=', $value),
                            );
                    })
                    ->label('Rango de Valores'),
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
                    ->visible(fn (DiscountCode $record) => $record->status !== 'active')
                    ->action(function (DiscountCode $record) {
                        $record->update(['status' => 'active']);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (DiscountCode $record) => $record->status === 'active')
                    ->action(function (DiscountCode $record) {
                        $record->update(['status' => 'disabled']);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (DiscountCode $record) {
                        $newRecord = $record->replicate();
                        $newRecord->code = $newRecord->code . '_copy';
                        $newRecord->status = 'pending';
                        $newRecord->usage_count = 0;
                        $newRecord->total_revenue_generated = 0;
                        $newRecord->total_orders = 0;
                        $newRecord->average_order_value = 0;
                        $newRecord->save();
                    }),
                Tables\Actions\Action::make('mark_featured')
                    ->label('Destacar')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (DiscountCode $record) => !$record->is_featured)
                    ->action(function (DiscountCode $record) {
                        $record->update(['is_featured' => true]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate_all')
                        ->label('Activar Todos')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'active']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate_all')
                        ->label('Desactivar Todos')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'disabled']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_featured_all')
                        ->label('Destacar Todos')
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
                                ->options(DiscountCode::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_priority')
                        ->label('Actualizar Prioridad')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority')
                                ->label('Prioridad')
                                ->options(DiscountCode::getPriorities())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['priority' => $data['priority']]);
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
            'index' => Pages\ListDiscountCodes::route('/'),
            'create' => Pages\CreateDiscountCode::route('/create'),
            'edit' => Pages\EditDiscountCode::route('/{record}/edit'),
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
