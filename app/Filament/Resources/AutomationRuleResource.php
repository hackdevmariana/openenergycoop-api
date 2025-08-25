<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutomationRuleResource\Pages;
use App\Filament\Resources\AutomationRuleResource\RelationManagers;
use App\Models\AutomationRule;
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

class AutomationRuleResource extends Resource
{
    protected static ?string $model = AutomationRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Administración del Sistema';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Reglas de Automatización';

    protected static ?string $modelLabel = 'Regla de Automatización';

    protected static ?string $pluralModelLabel = 'Reglas de Automatización';

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
                                    ->helperText('Nombre descriptivo de la regla'),
                                Select::make('trigger_type')
                                    ->label('Tipo de Disparador')
                                    ->options(AutomationRule::getTriggerTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada de la regla'),
                        Grid::make(3)
                            ->schema([
                                Select::make('action_type')
                                    ->label('Tipo de Acción')
                                    ->options(AutomationRule::getActionTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(AutomationRule::getPriorities())
                                    ->required()
                                    ->searchable(),
                                Select::make('execution_frequency')
                                    ->label('Frecuencia de Ejecución')
                                    ->options(AutomationRule::getExecutionFrequencies())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario propietario de la regla'),
                                Select::make('organization_id')
                                    ->label('Organización')
                                    ->relationship('organization', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Organización asociada'),
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

                Section::make('Condiciones y Lógica')
                    ->schema([
                        RichEditor::make('conditions')
                            ->label('Condiciones')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->required()
                            ->helperText('Condiciones que deben cumplirse para ejecutar la regla'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('condition_logic')
                                    ->label('Lógica de Condiciones')
                                    ->options([
                                        'AND' => 'TODAS deben cumplirse (AND)',
                                        'OR' => 'AL MENOS UNA debe cumplirse (OR)',
                                        'XOR' => 'EXACTAMENTE UNA debe cumplirse (XOR)',
                                        'NAND' => 'NO todas deben cumplirse (NAND)',
                                        'NOR' => 'NINGUNA debe cumplirse (NOR)',
                                    ])
                                    ->searchable(),
                                TextInput::make('condition_count')
                                    ->label('Número de Condiciones')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->step(1)
                                    ->helperText('Cantidad total de condiciones'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('evaluation_order')
                                    ->label('Orden de Evaluación')
                                    ->options([
                                        'sequential' => 'Secuencial',
                                        'parallel' => 'Paralelo',
                                        'priority_based' => 'Basado en Prioridad',
                                        'dependency_based' => 'Basado en Dependencias',
                                    ])
                                    ->searchable(),
                                Toggle::make('stop_on_first_match')
                                    ->label('Detener en Primera Coincidencia')
                                    ->helperText('¿Detener la evaluación al encontrar la primera coincidencia?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Acciones y Parámetros')
                    ->schema([
                        RichEditor::make('action_parameters')
                            ->label('Parámetros de Acción')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->required()
                            ->helperText('Parámetros específicos para la acción a ejecutar'),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('action_timeout_seconds')
                                    ->label('Timeout de Acción (seg)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(3600)
                                    ->step(1)
                                    ->suffix(' seg')
                                    ->helperText('Tiempo máximo para ejecutar la acción'),
                                TextInput::make('retry_attempts')
                                    ->label('Intentos de Reintento')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->step(1)
                                    ->default(3)
                                    ->helperText('Número de reintentos si falla'),
                                TextInput::make('retry_delay_seconds')
                                    ->label('Retraso entre Reintentos (seg)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(3600)
                                    ->step(1)
                                    ->suffix(' seg')
                                    ->helperText('Tiempo de espera entre reintentos'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('rollback_on_failure')
                                    ->label('Reversión en Fallo')
                                    ->helperText('¿Revertir cambios si la acción falla?'),
                                Toggle::make('notify_on_failure')
                                    ->label('Notificar en Fallo')
                                    ->default(true)
                                    ->helperText('¿Enviar notificación si falla?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Programación y Ejecución')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('last_executed_at')
                                    ->label('Última Ejecución')
                                    ->helperText('Cuándo se ejecutó por última vez'),
                                DateTimePicker::make('next_execution_at')
                                    ->label('Próxima Ejecución')
                                    ->helperText('Cuándo se ejecutará próximamente'),
                                TextInput::make('execution_count')
                                    ->label('Contador de Ejecuciones')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->required()
                                    ->helperText('Número total de ejecuciones'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('max_executions')
                                    ->label('Máximo de Ejecuciones')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Límite máximo de ejecuciones'),
                                TextInput::make('success_rate')
                                    ->label('Tasa de Éxito (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de ejecuciones exitosas'),
                                TextInput::make('error_count')
                                    ->label('Contador de Errores')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->required()
                                    ->helperText('Número total de errores'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('cooldown_period_minutes')
                                    ->label('Período de Enfriamiento (min)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(1440)
                                    ->step(1)
                                    ->suffix(' min')
                                    ->helperText('Tiempo mínimo entre ejecuciones'),
                                TextInput::make('execution_window_start')
                                    ->label('Inicio de Ventana de Ejecución')
                                    ->options([
                                        '00:00' => '00:00',
                                        '06:00' => '06:00',
                                        '09:00' => '09:00',
                                        '12:00' => '12:00',
                                        '18:00' => '18:00',
                                        '22:00' => '22:00',
                                    ])
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('execution_window_end')
                                    ->label('Fin de Ventana de Ejecución')
                                    ->options([
                                        '06:00' => '06:00',
                                        '09:00' => '09:00',
                                        '12:00' => '12:00',
                                        '18:00' => '18:00',
                                        '22:00' => '22:00',
                                        '23:59' => '23:59',
                                    ])
                                    ->searchable(),
                                Toggle::make('respect_business_hours')
                                    ->label('Respetar Horario Comercial')
                                    ->helperText('¿Solo ejecutar en horario comercial?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Monitoreo y Errores')
                    ->schema([
                        RichEditor::make('last_error')
                            ->label('Último Error')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Detalle del último error ocurrido'),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('error_threshold')
                                    ->label('Umbral de Errores')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->step(1)
                                    ->default(5)
                                    ->helperText('Número de errores antes de desactivar'),
                                TextInput::make('consecutive_error_count')
                                    ->label('Errores Consecutivos')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Errores consecutivos actuales'),
                                TextInput::make('last_error_at')
                                    ->label('Último Error el')
                                    ->dateTime()
                                    ->helperText('Cuándo ocurrió el último error'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('auto_disable_on_errors')
                                    ->label('Desactivar Automáticamente por Errores')
                                    ->default(true)
                                    ->helperText('¿Desactivar automáticamente si hay muchos errores?'),
                                Toggle::make('log_all_executions')
                                    ->label('Registrar Todas las Ejecuciones')
                                    ->default(true)
                                    ->helperText('¿Registrar cada ejecución en el log?'),
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
                                    ->required()
                                    ->helperText('¿La regla está activa?'),
                                Toggle::make('is_public')
                                    ->label('Pública')
                                    ->helperText('¿La regla es visible públicamente?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para cambios?'),
                                Toggle::make('is_template')
                                    ->label('Es Plantilla')
                                    ->helperText('¿Es una plantilla reutilizable?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('version')
                                    ->label('Versión')
                                    ->maxLength(50)
                                    ->default('1.0.0')
                                    ->helperText('Versión de la regla'),
                                TextInput::make('sort_order')
                                    ->label('Orden de Clasificación')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Orden para mostrar en listas'),
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
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('automation-rules')
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
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                BadgeColumn::make('trigger_type')
                    ->label('Disparador')
                    ->colors([
                        'primary' => 'event_based',
                        'success' => 'time_based',
                        'warning' => 'condition_based',
                        'danger' => 'manual',
                        'info' => 'webhook',
                        'secondary' => 'api_call',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => AutomationRule::getTriggerTypes()[$state] ?? $state),
                BadgeColumn::make('action_type')
                    ->label('Acción')
                    ->colors([
                        'primary' => 'notification',
                        'success' => 'data_update',
                        'warning' => 'workflow_trigger',
                        'danger' => 'system_command',
                        'info' => 'email_send',
                        'secondary' => 'api_call',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => AutomationRule::getActionTypes()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => AutomationRule::getPriorities()[$state] ?? $state),
                BadgeColumn::make('execution_frequency')
                    ->label('Frecuencia')
                    ->colors([
                        'primary' => 'immediate',
                        'success' => 'hourly',
                        'warning' => 'daily',
                        'danger' => 'weekly',
                        'info' => 'monthly',
                        'secondary' => 'custom',
                        'gray' => 'manual',
                    ])
                    ->formatStateUsing(fn (string $state): string => AutomationRule::getExecutionFrequencies()[$state] ?? $state),
                TextColumn::make('execution_count')
                    ->label('Ejecuciones')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('success_rate')
                    ->label('Éxito (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 95 => 'success',
                        $state >= 80 => 'primary',
                        $state >= 60 => 'warning',
                        $state >= 40 => 'info',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('error_count')
                    ->label('Errores')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state === 0 => 'success',
                        $state <= 5 => 'warning',
                        $state <= 20 => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('last_executed_at')
                    ->label('Última Ejecución')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('next_execution_at')
                    ->label('Próxima Ejecución')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cooldown_period_minutes')
                    ->label('Enfriamiento (min)')
                    ->suffix(' min')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('trigger_type')
                    ->label('Tipo de Disparador')
                    ->options(AutomationRule::getTriggerTypes())
                    ->multiple(),
                SelectFilter::make('action_type')
                    ->label('Tipo de Acción')
                    ->options(AutomationRule::getActionTypes())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(AutomationRule::getPriorities())
                    ->multiple(),
                SelectFilter::make('execution_frequency')
                    ->label('Frecuencia de Ejecución')
                    ->options(AutomationRule::getExecutionFrequencies())
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activas'),
                Filter::make('inactive')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false))
                    ->label('Solo Inactivas'),
                Filter::make('high_priority')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', ['high', 'urgent', 'critical']))
                    ->label('Alta Prioridad'),
                Filter::make('low_priority')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', ['low', 'medium']))
                    ->label('Baja Prioridad'),
                Filter::make('frequent_execution')
                    ->query(fn (Builder $query): Builder => $query->whereIn('execution_frequency', ['immediate', 'hourly']))
                    ->label('Ejecución Frecuente'),
                Filter::make('infrequent_execution')
                    ->query(fn (Builder $query): Builder => $query->whereIn('execution_frequency', ['weekly', 'monthly', 'manual']))
                    ->label('Ejecución Poco Frecuente'),
                Filter::make('high_success_rate')
                    ->query(fn (Builder $query): Builder => $query->where('success_rate', '>=', 90))
                    ->label('Alta Tasa de Éxito (≥90%)'),
                Filter::make('low_success_rate')
                    ->query(fn (Builder $query): Builder => $query->where('success_rate', '<', 70))
                    ->label('Baja Tasa de Éxito (<70%)'),
                Filter::make('error_prone')
                    ->query(fn (Builder $query): Builder => $query->where('error_count', '>', 10))
                    ->label('Propensas a Errores (>10)'),
                Filter::make('recent_execution')
                    ->query(fn (Builder $query): Builder => $query->where('last_executed_at', '>=', now()->subDays(7)))
                    ->label('Ejecutadas Recientemente (≤7 días)'),
                Filter::make('never_executed')
                    ->query(fn (Builder $query): Builder => $query->whereNull('last_executed_at'))
                    ->label('Nunca Ejecutadas'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('created_from')
                            ->label('Creado desde'),
                        DateTimePicker::make('created_until')
                            ->label('Creado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Creación'),
                Filter::make('execution_count_range')
                    ->form([
                        TextInput::make('min_executions')
                            ->label('Ejecuciones mínimas')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_executions')
                            ->label('Ejecuciones máximas')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_executions'],
                                fn (Builder $query, $count): Builder => $query->where('execution_count', '>=', $count),
                            )
                            ->when(
                                $data['max_executions'],
                                fn (Builder $query, $count): Builder => $query->where('execution_count', '<=', $count),
                            );
                    })
                    ->label('Rango de Ejecuciones'),
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
                    ->visible(fn (AutomationRule $record) => !$record->is_active)
                    ->action(function (AutomationRule $record) {
                        $record->update(['is_active' => true]);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (AutomationRule $record) => $record->is_active)
                    ->action(function (AutomationRule $record) {
                        $record->update(['is_active' => false]);
                    }),
                Tables\Actions\Action::make('execute_now')
                    ->label('Ejecutar Ahora')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn (AutomationRule $record) => $record->is_active)
                    ->action(function (AutomationRule $record) {
                        $record->update([
                            'last_executed_at' => now(),
                            'execution_count' => $record->execution_count + 1,
                        ]);
                    }),
                Tables\Actions\Action::make('reset_errors')
                    ->label('Resetear Errores')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (AutomationRule $record) {
                        $record->update([
                            'error_count' => 0,
                            'consecutive_error_count' => 0,
                            'last_error' => null,
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (AutomationRule $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->is_active = false;
                        $newRecord->execution_count = 0;
                        $newRecord->error_count = 0;
                        $newRecord->last_executed_at = null;
                        $newRecord->next_execution_at = null;
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
                                $record->update(['is_active' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate_all')
                        ->label('Desactivar Todas')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('reset_errors_all')
                        ->label('Resetear Errores')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'failure_count' => 0,
                                    'last_error_message' => null,
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_priority')
                        ->label('Actualizar Prioridad')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority')
                                ->label('Prioridad')
                                ->options(AutomationRule::getPriorities())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['priority' => $data['priority']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_execution_frequency')
                        ->label('Actualizar Frecuencia')
                        ->icon('heroicon-o-clock')
                        ->form([
                            Select::make('execution_frequency')
                                ->label('Frecuencia de Ejecución')
                                ->options(AutomationRule::getExecutionFrequencies())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['execution_frequency' => $data['execution_frequency']]);
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
            'index' => Pages\ListAutomationRules::route('/'),
            'create' => Pages\CreateAutomationRule::route('/create'),
            'edit' => Pages\EditAutomationRule::route('/{record}/edit'),
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
        $inactiveCount = static::getModel()::where('is_active', false)->count();
        
        if ($inactiveCount > 0) {
            return 'warning';
        }
        
        $errorProneCount = static::getModel()::where('failure_count', '>', 10)->count();
        
        if ($errorProneCount > 0) {
            return 'danger';
        }
        
        return 'success';
    }
}
