<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiClientResource\Pages;
use App\Models\ApiClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ApiClientResource extends Resource
{
    protected static ?string $model = ApiClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Sistema & Seguridad';

    protected static ?string $navigationLabel = 'Clientes API';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Cliente API';

    protected static ?string $pluralModelLabel = 'Clientes API';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej: App Móvil Android')
                                    ->helperText('Nombre descriptivo del cliente API'),

                                Select::make('organization_id')
                                    ->label('Organización')
                                    ->relationship('organization', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('token')
                                    ->label('Token API')
                                    ->required()
                                    ->maxLength(64)
                                    ->unique(ignoreRecord: true)
                                    ->default(fn () => Str::random(64))
                                    ->helperText('Token único para autenticación')
                                    ->suffixAction(
                                        \Filament\Forms\Components\Actions\Action::make('generate_token')
                                            ->icon('heroicon-o-arrow-path')
                                            ->action(function ($set) {
                                                $set('token', Str::random(64));
                                            })
                                            ->tooltip('Generar nuevo token')
                                    ),

                                Select::make('status')
                                    ->label('Estado')
                                    ->required()
                                    ->options([
                                        ApiClient::STATUS_ACTIVE => 'Activo',
                                        ApiClient::STATUS_SUSPENDED => 'Suspendido',
                                        ApiClient::STATUS_REVOKED => 'Revocado'
                                    ])
                                    ->default(ApiClient::STATUS_ACTIVE)
                                    ->searchable(),

                                TextInput::make('version')
                                    ->label('Versión de la API')
                                    ->default('1.0')
                                    ->maxLength(10)
                                    ->placeholder('1.0')
                                    ->helperText('Versión de la API que utiliza'),

                                Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->placeholder('Descripción del cliente y su propósito'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Permisos y Scopes')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                KeyValue::make('scopes')
                                    ->label('Scopes (Permisos)')
                                    ->keyLabel('Scope')
                                    ->valueLabel('Habilitado')
                                    ->helperText('Permisos específicos del cliente')
                                    ->default([
                                        'read' => true,
                                        'write' => false,
                                        'delete' => false,
                                        'admin' => false
                                    ]),

                                KeyValue::make('permissions')
                                    ->label('Permisos Específicos')
                                    ->keyLabel('Permiso')
                                    ->valueLabel('Habilitado')
                                    ->helperText('Permisos granulares adicionales'),

                                KeyValue::make('endpoints')
                                    ->label('Endpoints Permitidos')
                                    ->keyLabel('Endpoint')
                                    ->valueLabel('Acceso')
                                    ->helperText('Endpoints específicos a los que puede acceder'),

                                KeyValue::make('actions')
                                    ->label('Acciones Permitidas')
                                    ->keyLabel('Acción')
                                    ->valueLabel('Habilitada')
                                    ->helperText('Acciones específicas que puede realizar'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Configuración de Seguridad')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                KeyValue::make('allowed_ips')
                                    ->label('IPs Permitidas')
                                    ->keyLabel('IP')
                                    ->valueLabel('Descripción')
                                    ->helperText('Direcciones IP desde las que puede acceder (vacío = todas)'),

                                TextInput::make('callback_url')
                                    ->label('URL de Callback')
                                    ->url()
                                    ->placeholder('https://app.com/webhook')
                                    ->helperText('URL para notificaciones webhook'),

                                DateTimePicker::make('expires_at')
                                    ->label('Fecha de Expiración')
                                    ->helperText('Cuándo expira el token (vacío = sin expiración)'),

                                TextInput::make('max_requests_per_hour')
                                    ->label('Máx. Peticiones por Hora')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10000)
                                    ->default(1000)
                                    ->helperText('Límite de tasa por hora'),

                                TextInput::make('max_requests_per_day')
                                    ->label('Máx. Peticiones por Día')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100000)
                                    ->default(10000)
                                    ->helperText('Límite de tasa diario'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Configuración de Webhooks')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('webhooks_enabled')
                                    ->label('Webhooks Habilitados')
                                    ->default(false)
                                    ->helperText('Permite notificaciones webhook'),

                                KeyValue::make('webhook_config')
                                    ->label('Configuración de Webhooks')
                                    ->keyLabel('Clave')
                                    ->valueLabel('Valor')
                                    ->helperText('Configuración específica de webhooks')
                                    ->visible(fn (callable $get) => $get('webhooks_enabled')),

                                TextInput::make('webhook_secret')
                                    ->label('Secreto del Webhook')
                                    ->password()
                                    ->placeholder('Secreto para verificar webhooks')
                                    ->helperText('Secreto para verificar la autenticidad')
                                    ->visible(fn (callable $get) => $get('webhooks_enabled')),

                                TextInput::make('webhook_timeout')
                                    ->label('Timeout del Webhook (segundos)')
                                    ->numeric()
                                    ->minValue(5)
                                    ->maxValue(60)
                                    ->default(30)
                                    ->helperText('Tiempo máximo de espera')
                                    ->visible(fn (callable $get) => $get('webhooks_enabled')),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Configuración Avanzada')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('rate_limiting_enabled')
                                    ->label('Límites de Tasa Habilitados')
                                    ->default(true)
                                    ->helperText('Aplica límites de tasa'),

                                Toggle::make('ip_restriction_enabled')
                                    ->label('Restricción de IP Habilitada')
                                    ->default(false)
                                    ->helperText('Restringe acceso por IP'),

                                Toggle::make('logging_enabled')
                                    ->label('Logging Habilitado')
                                    ->default(true)
                                    ->helperText('Registra todas las peticiones'),

                                Toggle::make('analytics_enabled')
                                    ->label('Analíticas Habilitadas')
                                    ->default(true)
                                    ->helperText('Recopila datos de uso'),

                                Toggle::make('notifications_enabled')
                                    ->label('Notificaciones Habilitadas')
                                    ->default(true)
                                    ->helperText('Envía notificaciones de eventos'),

                                Toggle::make('auto_rotation')
                                    ->label('Rotación Automática de Tokens')
                                    ->default(false)
                                    ->helperText('Rota tokens automáticamente'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Metadatos')
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadatos Adicionales')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->helperText('Información adicional del cliente'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('token')
                    ->label('Token')
                    ->searchable()
                    ->limit(20)
                    ->copyable()
                    ->copyMessage('Token copiado al portapapeles')
                    ->tooltip('Haz clic para copiar'),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => ApiClient::STATUS_ACTIVE,
                        'warning' => ApiClient::STATUS_SUSPENDED,
                        'danger' => ApiClient::STATUS_REVOKED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        ApiClient::STATUS_ACTIVE => 'Activo',
                        ApiClient::STATUS_SUSPENDED => 'Suspendido',
                        ApiClient::STATUS_REVOKED => 'Revocado',
                        default => $state
                    }),

                TextColumn::make('version')
                    ->label('Versión')
                    ->badge()
                    ->formatStateUsing(fn ($state) => "v{$state}"),

                TextColumn::make('last_used_at')
                    ->label('Último Uso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->diffForHumans() : 'Nunca'),

                TextColumn::make('expires_at')
                    ->label('Expira')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y') : 'Sin expiración')
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 'success'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        ApiClient::STATUS_ACTIVE => 'Activo',
                        ApiClient::STATUS_SUSPENDED => 'Suspendido',
                        ApiClient::STATUS_REVOKED => 'Revocado'
                    ])
                    ->multiple(),

                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Filter::make('active_clients')
                    ->label('Solo Clientes Activos')
                    ->query(fn (Builder $query): Builder => $query->where('status', ApiClient::STATUS_ACTIVE))
                    ->toggle(),

                Filter::make('expired_clients')
                    ->label('Clientes Expirados')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('expires_at')->where('expires_at', '<', now()))
                    ->toggle(),

                Filter::make('recent_usage')
                    ->label('Uso Reciente (7 días)')
                    ->query(fn (Builder $query): Builder => $query->where('last_used_at', '>=', now()->subDays(7)))
                    ->toggle(),

                Filter::make('no_recent_usage')
                    ->label('Sin Uso Reciente (30 días)')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->whereNull('last_used_at')
                          ->orWhere('last_used_at', '<', now()->subDays(30));
                    }))
                    ->toggle(),

                SelectFilter::make('version')
                    ->label('Versión API')
                    ->options([
                        '1.0' => 'v1.0',
                        '2.0' => 'v2.0',
                        '3.0' => 'v3.0'
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),

                Action::make('regenerate_token')
                    ->label('Regenerar Token')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerar Token API')
                    ->modalDescription('¿Estás seguro de que quieres regenerar el token? El token actual dejará de funcionar inmediatamente.')
                    ->modalSubmitActionLabel('Sí, regenerar')
                    ->action(fn (ApiClient $record) => $record->regenerateToken())
                    ->visible(fn (ApiClient $record) => $record->status === ApiClient::STATUS_ACTIVE),

                Action::make('suspend')
                    ->label('Suspender')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Suspender Cliente API')
                    ->modalDescription('¿Estás seguro de que quieres suspender este cliente? No podrá hacer peticiones hasta que se reactive.')
                    ->modalSubmitActionLabel('Sí, suspender')
                    ->action(fn (ApiClient $record) => $record->suspend())
                    ->visible(fn (ApiClient $record) => $record->status === ApiClient::STATUS_ACTIVE),

                Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Activar Cliente API')
                    ->modalDescription('¿Estás seguro de que quieres activar este cliente? Podrá hacer peticiones nuevamente.')
                    ->modalSubmitActionLabel('Sí, activar')
                    ->action(fn (ApiClient $record) => $record->activate())
                    ->visible(fn (ApiClient $record) => $record->status !== ApiClient::STATUS_ACTIVE),

                Action::make('revoke')
                    ->label('Revocar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Revocar Cliente API')
                    ->modalDescription('¿Estás seguro de que quieres revocar este cliente? No podrá hacer peticiones y deberá crear uno nuevo.')
                    ->modalSubmitActionLabel('Sí, revocar')
                    ->action(fn (ApiClient $record) => $record->revoke())
                    ->visible(fn (ApiClient $record) => $record->status !== ApiClient::STATUS_REVOKED),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('activate_selected')
                        ->label('Activar Seleccionados')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->activate();
                        }),

                    BulkAction::make('suspend_selected')
                        ->label('Suspender Seleccionados')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->suspend();
                        }),

                    BulkAction::make('revoke_selected')
                        ->label('Revocar Seleccionados')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->revoke();
                        }),

                    BulkAction::make('regenerate_tokens')
                        ->label('Regenerar Tokens')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->regenerateToken();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListApiClients::route('/'),
            'create' => Pages\CreateApiClient::route('/create'),
            'edit' => Pages\EditApiClient::route('/{record}/edit'),
            'view' => Pages\ViewApiClient::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $activeCount = static::getModel()::where('status', ApiClient::STATUS_ACTIVE)->count();
        $totalCount = static::getModel()::count();
        
        if ($totalCount === 0) {
            return 'secondary';
        }
        
        $activePercentage = ($activeCount / $totalCount) * 100;
        
        if ($activePercentage >= 80) {
            return 'success';
        } elseif ($activePercentage >= 50) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}
