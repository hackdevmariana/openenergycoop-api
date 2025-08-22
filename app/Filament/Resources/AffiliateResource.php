<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Filament\Resources\AffiliateResource\RelationManagers;
use App\Models\Affiliate;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Marketing y Ventas';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Afiliados';

    protected static ?string $modelLabel = 'Afiliado';

    protected static ?string $pluralModelLabel = 'Afiliados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('affiliate_code')
                                    ->label('Código de Afiliado')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Código único del afiliado'),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(Affiliate::getStatuses())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('tier')
                                    ->label('Nivel')
                                    ->options(Affiliate::getTiers())
                                    ->required()
                                    ->searchable(),
                                DatePicker::make('joined_date')
                                    ->label('Fecha de Ingreso')
                                    ->required()
                                    ->helperText('Cuándo se unió al programa'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción del afiliado'),
                    ])
                    ->collapsible(),

                Section::make('Información de Contacto')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('contact_person')
                                    ->label('Persona de Contacto')
                                    ->maxLength(255)
                                    ->helperText('Nombre de la persona principal de contacto'),
                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Correo electrónico principal'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('website')
                                    ->label('Sitio Web')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('address')
                                    ->label('Dirección')
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->label('Ciudad')
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('state')
                                    ->label('Estado/Provincia')
                                    ->maxLength(255),
                                TextInput::make('postal_code')
                                    ->label('Código Postal')
                                    ->maxLength(20),
                            ]),
                        TextInput::make('country')
                            ->label('País')
                            ->maxLength(255)
                            ->required(),
                    ])
                    ->collapsible(),

                Section::make('Configuración de Comisiones')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('commission_rate')
                                    ->label('Tasa de Comisión (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->required()
                                    ->helperText('Porcentaje de comisión por venta'),
                                TextInput::make('bonus_rate')
                                    ->label('Tasa de Bono (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de bono adicional'),
                                TextInput::make('performance_bonus')
                                    ->label('Bono de Rendimiento')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Bono fijo por buen rendimiento'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payment_threshold')
                                    ->label('Umbral de Pago')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Monto mínimo para realizar pago'),
                                TextInput::make('max_monthly_earnings')
                                    ->label('Ganancias Máximas Mensuales')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Límite de ganancias por mes'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Métricas de Rendimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_referrals')
                                    ->label('Total de Referencias')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Número total de referencias'),
                                TextInput::make('active_referrals')
                                    ->label('Referencias Activas')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Referencias que generan ingresos'),
                                TextInput::make('conversion_rate')
                                    ->label('Tasa de Conversión (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de conversión'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_earnings')
                                    ->label('Ganancias Totales')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Total de ganancias acumuladas'),
                                TextInput::make('monthly_earnings')
                                    ->label('Ganancias del Mes')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Ganancias del mes actual'),
                                TextInput::make('pending_payout')
                                    ->label('Pago Pendiente')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto pendiente de pago'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración de Pagos')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('payment_method')
                                    ->label('Método de Pago')
                                    ->options([
                                        'bank_transfer' => 'Transferencia Bancaria',
                                        'paypal' => 'PayPal',
                                        'stripe' => 'Stripe',
                                        'check' => 'Cheque',
                                        'crypto' => 'Criptomonedas',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('payment_schedule')
                                    ->label('Programa de Pagos')
                                    ->maxLength(255)
                                    ->helperText('Ej: Mensual, Quincenal, etc.'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Nombre del Banco')
                                    ->maxLength(255),
                                TextInput::make('account_number')
                                    ->label('Número de Cuenta')
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('routing_number')
                                    ->label('Número de Ruta')
                                    ->maxLength(255),
                                TextInput::make('swift_code')
                                    ->label('Código SWIFT')
                                    ->maxLength(255),
                            ]),
                        RichEditor::make('payment_notes')
                            ->label('Notas de Pago')
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
                        RichEditor::make('promotional_codes')
                            ->label('Códigos Promocionales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('social_media_links')
                                    ->label('Enlaces de Redes Sociales')
                                    ->maxLength(255),
                                TextInput::make('marketing_budget')
                                    ->label('Presupuesto de Marketing')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Referencias')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('referred_by')
                                    ->label('Referido por')
                                    ->relationship('referredBy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Afiliado que lo refirió'),
                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario asociado'),
                            ]),
                        RichEditor::make('referral_network')
                            ->label('Red de Referencias')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Descripción de la red de referencias'),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->helperText('¿El afiliado está activo?'),
                                Toggle::make('is_verified')
                                    ->label('Verificado')
                                    ->helperText('¿El afiliado ha sido verificado?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('auto_approve_referrals')
                                    ->label('Aprobar Referencias Automáticamente')
                                    ->helperText('¿Aprobar referencias sin revisión manual?'),
                                Toggle::make('send_notifications')
                                    ->label('Enviar Notificaciones')
                                    ->default(true)
                                    ->helperText('¿Enviar notificaciones por email?'),
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

                Section::make('Asignaciones del Sistema')
                    ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('affiliate_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'suspended',
                        'info' => 'inactive',
                        'secondary' => 'terminated',
                    ])
                    ->formatStateUsing(fn (string $state): string => Affiliate::getStatuses()[$state] ?? $state),
                BadgeColumn::make('tier')
                    ->label('Nivel')
                    ->colors([
                        'primary' => 'bronze',
                        'warning' => 'silver',
                        'success' => 'gold',
                        'danger' => 'platinum',
                        'info' => 'diamond',
                    ])
                    ->formatStateUsing(fn (string $state): string => Affiliate::getTiers()[$state] ?? $state),
                TextColumn::make('commission_rate')
                    ->label('Comisión (%)')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('total_referrals')
                    ->label('Referencias')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                TextColumn::make('active_referrals')
                    ->label('Activas')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                TextColumn::make('conversion_rate')
                    ->label('Conversión (%)')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('total_earnings')
                    ->label('Ganancias Totales')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('monthly_earnings')
                    ->label('Ganancias del Mes')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('pending_payout')
                    ->label('Pago Pendiente')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('joined_date')
                    ->label('Ingreso')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                ToggleColumn::make('is_verified')
                    ->label('Verificado'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Affiliate::getStatuses())
                    ->multiple(),
                SelectFilter::make('tier')
                    ->label('Nivel')
                    ->options(Affiliate::getTiers())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->where('is_verified', true))
                    ->label('Solo Verificados'),
                Filter::make('high_performers')
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '>=', 10))
                    ->label('Alto Rendimiento (≥10%)'),
                Filter::make('low_performers')
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '<', 5))
                    ->label('Bajo Rendimiento (<5%)'),
                Filter::make('earnings_range')
                    ->form([
                        TextInput::make('min_earnings')
                            ->label('Ganancias mínimas')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_earnings')
                            ->label('Ganancias máximas')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_earnings'],
                                fn (Builder $query, $earnings): Builder => $query->where('total_earnings', '>=', $earnings),
                            )
                            ->when(
                                $data['max_earnings'],
                                fn (Builder $query, $earnings): Builder => $query->where('total_earnings', '<=', $earnings),
                            );
                    })
                    ->label('Rango de Ganancias'),
                Filter::make('referrals_range')
                    ->form([
                        TextInput::make('min_referrals')
                            ->label('Referencias mínimas')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_referrals')
                            ->label('Referencias máximas')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_referrals'],
                                fn (Builder $query, $referrals): Builder => $query->where('total_referrals', '>=', $referrals),
                            )
                            ->when(
                                $data['max_referrals'],
                                fn (Builder $query, $referrals): Builder => $query->where('total_referrals', '<=', $referrals),
                            );
                    })
                    ->label('Rango de Referencias'),
                Filter::make('joined_date_range')
                    ->form([
                        DatePicker::make('joined_from')
                            ->label('Ingreso desde'),
                        DatePicker::make('joined_until')
                            ->label('Ingreso hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['joined_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('joined_date', '>=', $date),
                            )
                            ->when(
                                $data['joined_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('joined_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Ingreso'),
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
                    ->visible(fn (Affiliate $record) => $record->status !== 'active')
                    ->action(function (Affiliate $record) {
                        $record->update(['status' => 'active']);
                    }),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspender')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Affiliate $record) => $record->status === 'active')
                    ->action(function (Affiliate $record) {
                        $record->update(['status' => 'suspended']);
                    }),
                Tables\Actions\Action::make('verify')
                    ->label('Verificar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Affiliate $record) => !$record->is_verified)
                    ->action(function (Affiliate $record) {
                        $record->update(['is_verified' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (Affiliate $record) {
                        $newRecord = $record->replicate();
                        $newRecord->affiliate_code = $newRecord->affiliate_code . '_copy';
                        $newRecord->status = 'pending';
                        $newRecord->is_verified = false;
                        $newRecord->total_referrals = 0;
                        $newRecord->active_referrals = 0;
                        $newRecord->total_earnings = 0;
                        $newRecord->monthly_earnings = 0;
                        $newRecord->pending_payout = 0;
                        $newRecord->save();
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
                    Tables\Actions\BulkAction::make('suspend_all')
                        ->label('Suspender Todos')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'suspended']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('verify_all')
                        ->label('Verificar Todos')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_verified' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_tier')
                        ->label('Actualizar Nivel')
                        ->icon('heroicon-o-star')
                        ->form([
                            Select::make('tier')
                                ->label('Nivel')
                                ->options(Affiliate::getTiers())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['tier' => $data['tier']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_commission')
                        ->label('Actualizar Comisión')
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            TextInput::make('commission_rate')
                                ->label('Tasa de Comisión (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['commission_rate' => $data['commission_rate']]);
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
            'index' => Pages\ListAffiliates::route('/'),
            'create' => Pages\CreateAffiliate::route('/create'),
            'edit' => Pages\EditAffiliate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $suspendedCount = static::getModel()::where('status', 'suspended')->count();
        
        if ($suspendedCount > 0) {
            return 'warning';
        }
        
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        
        if ($pendingCount > 0) {
            return 'info';
        }
        
        return 'success';
    }
}
