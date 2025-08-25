<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
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
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Sistema & Seguridad';

    protected static ?string $navigationLabel = 'Registros de Auditoría';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Registro de Auditoría';

    protected static ?string $pluralModelLabel = 'Registros de Auditoría';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Acción')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('action')
                                    ->label('Acción')
                                    ->required()
                                    ->options([
                                        AuditLog::ACTION_CREATE => 'Crear',
                                        AuditLog::ACTION_UPDATE => 'Actualizar',
                                        AuditLog::ACTION_DELETE => 'Eliminar',
                                        AuditLog::ACTION_RESTORE => 'Restaurar',
                                        AuditLog::ACTION_LOGIN => 'Iniciar Sesión',
                                        AuditLog::ACTION_LOGOUT => 'Cerrar Sesión',
                                        AuditLog::ACTION_LOGIN_FAILED => 'Error de Inicio de Sesión',
                                        AuditLog::ACTION_PASSWORD_CHANGE => 'Cambio de Contraseña',
                                        AuditLog::ACTION_PERMISSION_CHANGE => 'Cambio de Permisos',
                                        AuditLog::ACTION_API_CALL => 'Llamada API',
                                        AuditLog::ACTION_FILE_UPLOAD => 'Subida de Archivo',
                                        AuditLog::ACTION_FILE_DELETE => 'Eliminación de Archivo',
                                        AuditLog::ACTION_EXPORT => 'Exportar',
                                        AuditLog::ACTION_IMPORT => 'Importar',
                                        AuditLog::ACTION_BULK_ACTION => 'Acción Masiva'
                                    ])
                                    ->searchable(),

                                Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->placeholder('Descripción detallada de la acción realizada')
                                    ->required(),

                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Usuario que realizó la acción'),

                                Select::make('actor_type')
                                    ->label('Tipo de Actor')
                                    ->options([
                                        AuditLog::ACTOR_TYPE_USER => 'Usuario',
                                        AuditLog::ACTOR_TYPE_SYSTEM => 'Sistema',
                                        AuditLog::ACTOR_TYPE_API => 'API',
                                        AuditLog::ACTOR_TYPE_CRON => 'Tarea Programada',
                                        AuditLog::ACTOR_TYPE_WEBHOOK => 'Webhook'
                                    ])
                                    ->searchable()
                                    ->default(AuditLog::ACTOR_TYPE_USER),

                                TextInput::make('actor_identifier')
                                    ->label('Identificador del Actor')
                                    ->placeholder('ID o identificador del actor')
                                    ->helperText('ID del usuario, nombre del sistema, etc.'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Objeto Auditado')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('auditable_type')
                                    ->label('Tipo de Objeto')
                                    ->placeholder('App\Models\User')
                                    ->helperText('Clase del modelo que se está auditando'),

                                TextInput::make('auditable_id')
                                    ->label('ID del Objeto')
                                    ->numeric()
                                    ->placeholder('123')
                                    ->helperText('ID del registro que se está auditando'),

                                KeyValue::make('old_values')
                                    ->label('Valores Anteriores')
                                    ->keyLabel('Campo')
                                    ->valueLabel('Valor Anterior')
                                    ->helperText('Valores antes de la modificación'),

                                KeyValue::make('new_values')
                                    ->label('Valores Nuevos')
                                    ->keyLabel('Campo')
                                    ->valueLabel('Valor Nuevo')
                                    ->helperText('Valores después de la modificación'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Información de Seguridad')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('ip_address')
                                    ->label('Dirección IP')
                                    ->placeholder('192.168.1.1')
                                    ->helperText('IP desde donde se realizó la acción'),

                                Textarea::make('user_agent')
                                    ->label('User Agent')
                                    ->rows(2)
                                    ->placeholder('Mozilla/5.0...')
                                    ->helperText('Navegador o cliente utilizado'),

                                TextInput::make('url')
                                    ->label('URL')
                                    ->url()
                                    ->placeholder('https://app.com/admin/users')
                                    ->helperText('URL donde se realizó la acción'),

                                TextInput::make('method')
                                    ->label('Método HTTP')
                                    ->placeholder('GET, POST, PUT, DELETE')
                                    ->helperText('Método HTTP utilizado'),

                                TextInput::make('session_id')
                                    ->label('ID de Sesión')
                                    ->placeholder('abc123def456')
                                    ->helperText('Identificador de la sesión'),

                                TextInput::make('request_id')
                                    ->label('ID de Petición')
                                    ->placeholder('req_123456')
                                    ->helperText('Identificador único de la petición'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Datos de la Petición')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                KeyValue::make('request_data')
                                    ->label('Datos de la Petición')
                                    ->keyLabel('Clave')
                                    ->valueLabel('Valor')
                                    ->helperText('Datos enviados en la petición'),

                                KeyValue::make('response_data')
                                    ->label('Datos de la Respuesta')
                                    ->keyLabel('Clave')
                                    ->valueLabel('Valor')
                                    ->helperText('Datos de la respuesta'),

                                TextInput::make('response_code')
                                    ->label('Código de Respuesta HTTP')
                                    ->numeric()
                                    ->placeholder('200, 404, 500')
                                    ->helperText('Código de estado HTTP'),

                                DateTimePicker::make('created_at')
                                    ->label('Fecha y Hora')
                                    ->default(now())
                                    ->required(),
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
                            ->helperText('Información adicional relevante'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('action')
                    ->label('Acción')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->colors([
                        'primary' => AuditLog::ACTION_CREATE,
                        'success' => AuditLog::ACTION_UPDATE,
                        'danger' => AuditLog::ACTION_DELETE,
                        'warning' => AuditLog::ACTION_RESTORE,
                        'info' => AuditLog::ACTION_LOGIN,
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        AuditLog::ACTION_CREATE => 'Crear',
                        AuditLog::ACTION_UPDATE => 'Actualizar',
                        AuditLog::ACTION_DELETE => 'Eliminar',
                        AuditLog::ACTION_RESTORE => 'Restaurar',
                        AuditLog::ACTION_LOGIN => 'Login',
                        AuditLog::ACTION_LOGOUT => 'Logout',
                        AuditLog::ACTION_LOGIN_FAILED => 'Login Fallido',
                        AuditLog::ACTION_PASSWORD_CHANGE => 'Cambio Contraseña',
                        AuditLog::ACTION_PERMISSION_CHANGE => 'Cambio Permisos',
                        AuditLog::ACTION_API_CALL => 'API Call',
                        AuditLog::ACTION_FILE_UPLOAD => 'Subida Archivo',
                        AuditLog::ACTION_FILE_DELETE => 'Eliminar Archivo',
                        AuditLog::ACTION_EXPORT => 'Exportar',
                        AuditLog::ACTION_IMPORT => 'Importar',
                        AuditLog::ACTION_BULK_ACTION => 'Acción Masiva',
                        default => ucfirst($state)
                    }),

                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sistema'),

                BadgeColumn::make('actor_type')
                    ->label('Actor')
                    ->colors([
                        'primary' => AuditLog::ACTOR_TYPE_USER,
                        'success' => AuditLog::ACTOR_TYPE_SYSTEM,
                        'warning' => AuditLog::ACTOR_TYPE_API,
                        'info' => AuditLog::ACTOR_TYPE_CRON,
                        'danger' => AuditLog::ACTOR_TYPE_WEBHOOK,
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        AuditLog::ACTOR_TYPE_USER => 'Usuario',
                        AuditLog::ACTOR_TYPE_SYSTEM => 'Sistema',
                        AuditLog::ACTOR_TYPE_API => 'API',
                        AuditLog::ACTOR_TYPE_CRON => 'Cron',
                        AuditLog::ACTOR_TYPE_WEBHOOK => 'Webhook',
                        default => $state
                    }),

                TextColumn::make('auditable_type')
                    ->label('Tipo de Objeto')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : 'N/A'),

                TextColumn::make('auditable_id')
                    ->label('ID Objeto')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),

                BadgeColumn::make('response_status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                        'info' => 'info',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'success' => 'Éxito',
                        'warning' => 'Advertencia',
                        'danger' => 'Error',
                        'info' => 'Info',
                        default => 'Desconocido'
                    }),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->sortable()
                    ->limit(15)
                    ->placeholder('N/A'),

                TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Acción')
                    ->options([
                        AuditLog::ACTION_CREATE => 'Crear',
                        AuditLog::ACTION_UPDATE => 'Actualizar',
                        AuditLog::ACTION_DELETE => 'Eliminar',
                        AuditLog::ACTION_RESTORE => 'Restaurar',
                        AuditLog::ACTION_LOGIN => 'Iniciar Sesión',
                        AuditLog::ACTION_LOGOUT => 'Cerrar Sesión',
                        AuditLog::ACTION_LOGIN_FAILED => 'Error de Inicio de Sesión',
                        AuditLog::ACTION_PASSWORD_CHANGE => 'Cambio de Contraseña',
                        AuditLog::ACTION_PERMISSION_CHANGE => 'Cambio de Permisos',
                        AuditLog::ACTION_API_CALL => 'Llamada API',
                        AuditLog::ACTION_FILE_UPLOAD => 'Subida de Archivo',
                        AuditLog::ACTION_FILE_DELETE => 'Eliminación de Archivo',
                        AuditLog::ACTION_EXPORT => 'Exportar',
                        AuditLog::ACTION_IMPORT => 'Importar',
                        AuditLog::ACTION_BULK_ACTION => 'Acción Masiva'
                    ])
                    ->multiple(),

                SelectFilter::make('actor_type')
                    ->label('Tipo de Actor')
                    ->options([
                        AuditLog::ACTOR_TYPE_USER => 'Usuario',
                        AuditLog::ACTOR_TYPE_SYSTEM => 'Sistema',
                        AuditLog::ACTOR_TYPE_API => 'API',
                        AuditLog::ACTOR_TYPE_CRON => 'Tarea Programada',
                        AuditLog::ACTOR_TYPE_WEBHOOK => 'Webhook'
                    ])
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Filter::make('has_changes')
                    ->label('Con Cambios')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('old_values')->orWhereNotNull('new_values'))
                    ->toggle(),

                Filter::make('recent_logs')
                    ->label('Registros Recientes (24h)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDay()))
                    ->toggle(),

                Filter::make('today_logs')
                    ->label('Registros de Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),

                Filter::make('this_week_logs')
                    ->label('Registros de Esta Semana')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]))
                    ->toggle(),

                SelectFilter::make('method')
                    ->label('Método HTTP')
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'PATCH' => 'PATCH',
                        'DELETE' => 'DELETE'
                    ])
                    ->multiple(),

                SelectFilter::make('response_code')
                    ->label('Código de Respuesta')
                    ->options([
                        '200' => '200 - OK',
                        '201' => '201 - Created',
                        '400' => '400 - Bad Request',
                        '401' => '401 - Unauthorized',
                        '403' => '403 - Forbidden',
                        '404' => '404 - Not Found',
                        '500' => '500 - Internal Server Error'
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye'),

                Action::make('view_changes')
                    ->label('Ver Cambios')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->modalHeading('Cambios Realizados')
                    ->modalContent(function (AuditLog $record) {
                        $changes = $record->getChangedFields();
                        if (empty($changes)) {
                            return 'No hay cambios registrados para esta acción.';
                        }

                        $content = '<div class="space-y-4">';
                        foreach ($changes as $field => $change) {
                            $content .= '<div class="border rounded p-3">';
                            $content .= '<strong class="text-gray-900">' . $field . '</strong><br>';
                            $content .= '<span class="text-red-600">Antes: ' . (is_array($change['old']) ? json_encode($change['old']) : $change['old']) . '</span><br>';
                            $content .= '<span class="text-green-600">Después: ' . (is_array($change['new']) ? json_encode($change['new']) : $change['new']) . '</span>';
                            $content .= '</div>';
                        }
                        $content .= '</div>';

                        return $content;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->visible(fn (AuditLog $record) => $record->has_changes),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Registro de Auditoría')
                    ->modalDescription('¿Estás seguro de que quieres eliminar este registro de auditoría? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('export_selected')
                        ->label('Exportar Seleccionados')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($records) {
                            // Lógica para exportar registros
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Registros de Auditoría')
                        ->modalDescription('¿Estás seguro de que quieres eliminar los registros seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100, 200])
            ->searchable();
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
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $recentCount = static::getModel()::where('created_at', '>=', now()->subHour())->count();
        
        if ($recentCount > 100) {
            return 'danger';
        } elseif ($recentCount > 50) {
            return 'warning';
        } elseif ($recentCount > 10) {
            return 'info';
        }
        
        return 'success';
    }
}
