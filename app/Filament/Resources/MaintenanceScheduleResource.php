<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceScheduleResource\Pages;
use App\Filament\Resources\MaintenanceScheduleResource\RelationManagers;
use App\Models\MaintenanceSchedule;
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

class MaintenanceScheduleResource extends Resource
{
    protected static ?string $model = MaintenanceSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Administración del Sistema';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Programas de Mantenimiento';

    protected static ?string $modelLabel = 'Programa de Mantenimiento';

    protected static ?string $pluralModelLabel = 'Programas de Mantenimiento';

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
                                    ->helperText('Nombre del programa de mantenimiento'),
                                Select::make('maintenance_type')
                                    ->label('Tipo de Mantenimiento')
                                    ->options(MaintenanceSchedule::getMaintenanceTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(MaintenanceSchedule::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(MaintenanceSchedule::getPriorities())
                                    ->required()
                                    ->searchable(),
                                Select::make('frequency')
                                    ->label('Frecuencia')
                                    ->options(MaintenanceSchedule::getFrequencies())
                                    ->required()
                                    ->searchable(),
                            ]),
                        RichEditor::make('description')
                            ->label('Descripción')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Descripción detallada del programa de mantenimiento'),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('installation_id')
                                    ->label('Instalación')
                                    ->relationship('installation', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Instalación que requiere mantenimiento'),
                                Select::make('assigned_technician_id')
                                    ->label('Técnico Asignado')
                                    ->relationship('assignedTechnician', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Técnico responsable del mantenimiento'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('organization_id')
                                    ->label('Organización')
                                    ->relationship('organization', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Organización propietaria de la instalación'),
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Programación y Fechas')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('scheduled_start')
                                    ->label('Inicio Programado')
                                    ->required()
                                    ->helperText('Cuándo debe comenzar el mantenimiento'),
                                DateTimePicker::make('scheduled_end')
                                    ->label('Fin Programado')
                                    ->required()
                                    ->helperText('Cuándo debe terminar el mantenimiento'),
                                DateTimePicker::make('estimated_duration_hours')
                                    ->label('Duración Estimada (horas)')
                                    ->numeric()
                                    ->minValue(0.5)
                                    ->step(0.5)
                                    ->suffix(' horas')
                                    ->helperText('Duración estimada del mantenimiento'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('last_maintenance_date')
                                    ->label('Último Mantenimiento')
                                    ->helperText('Cuándo se realizó el último mantenimiento'),
                                DateTimePicker::make('next_maintenance_date')
                                    ->label('Próximo Mantenimiento')
                                    ->helperText('Cuándo se debe realizar el próximo'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('maintenance_window_start')
                                    ->label('Ventana de Mantenimiento - Inicio')
                                    ->options([
                                        '00:00' => '00:00',
                                        '02:00' => '02:00',
                                        '04:00' => '04:00',
                                        '06:00' => '06:00',
                                        '08:00' => '08:00',
                                        '10:00' => '10:00',
                                        '12:00' => '12:00',
                                        '14:00' => '14:00',
                                        '16:00' => '16:00',
                                        '18:00' => '18:00',
                                        '20:00' => '20:00',
                                        '22:00' => '22:00',
                                    ])
                                    ->searchable(),
                                TextInput::make('maintenance_window_end')
                                    ->label('Ventana de Mantenimiento - Fin')
                                    ->options([
                                        '00:00' => '00:00',
                                        '02:00' => '02:00',
                                        '04:00' => '04:00',
                                        '06:00' => '06:00',
                                        '08:00' => '08:00',
                                        '10:00' => '10:00',
                                        '12:00' => '12:00',
                                        '14:00' => '14:00',
                                        '16:00' => '16:00',
                                        '18:00' => '18:00',
                                        '20:00' => '20:00',
                                        '22:00' => '22:00',
                                    ])
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalles del Mantenimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('maintenance_category')
                                    ->label('Categoría')
                                    ->options([
                                        'preventive' => 'Preventivo',
                                        'corrective' => 'Correctivo',
                                        'predictive' => 'Predictivo',
                                        'emergency' => 'Emergencia',
                                        'scheduled' => 'Programado',
                                        'unscheduled' => 'No Programado',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('equipment_type')
                                    ->label('Tipo de Equipo')
                                    ->options([
                                        'solar_panel' => 'Panel Solar',
                                        'inverter' => 'Inversor',
                                        'battery' => 'Batería',
                                        'transformer' => 'Transformador',
                                        'switchgear' => 'Interruptor',
                                        'meter' => 'Medidor',
                                        'cable' => 'Cable',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('maintenance_level')
                                    ->label('Nivel de Mantenimiento')
                                    ->options([
                                        'basic' => 'Básico',
                                        'intermediate' => 'Intermedio',
                                        'advanced' => 'Avanzado',
                                        'expert' => 'Experto',
                                        'specialized' => 'Especializado',
                                    ])
                                    ->searchable(),
                            ]),
                        RichEditor::make('maintenance_procedures')
                            ->label('Procedimientos de Mantenimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Procedimientos detallados a seguir'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('required_tools')
                                    ->label('Herramientas Requeridas')
                                    ->maxLength(255)
                                    ->helperText('Herramientas necesarias para el mantenimiento'),
                                TextInput::make('required_parts')
                                    ->label('Partes Requeridas')
                                    ->maxLength(255)
                                    ->helperText('Partes que se necesitan reemplazar'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('safety_requirements')
                                    ->label('Requisitos de Seguridad')
                                    ->maxLength(255)
                                    ->helperText('Medidas de seguridad a seguir'),
                                TextInput::make('quality_standards')
                                    ->label('Estándares de Calidad')
                                    ->maxLength(255)
                                    ->helperText('Estándares de calidad a cumplir'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Costos y Recursos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('estimated_cost')
                                    ->label('Costo Estimado (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Costo estimado del mantenimiento'),
                                TextInput::make('actual_cost')
                                    ->label('Costo Real (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Costo real del mantenimiento'),
                                TextInput::make('labor_hours')
                                    ->label('Horas de Trabajo')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.5)
                                    ->suffix(' horas')
                                    ->helperText('Horas de trabajo requeridas'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('material_cost')
                                    ->label('Costo de Materiales (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Costo de materiales utilizados'),
                                TextInput::make('external_contractor_cost')
                                    ->label('Costo de Contratista (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Costo si se contrata externamente'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Monitoreo y Estado')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('completion_percentage')
                                    ->label('Porcentaje de Completado')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Progreso del mantenimiento'),
                                TextInput::make('quality_score')
                                    ->label('Puntuación de Calidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Calidad del trabajo realizado'),
                                TextInput::make('efficiency_score')
                                    ->label('Puntuación de Eficiencia')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia del mantenimiento'),
                            ]),
                        RichEditor::make('progress_notes')
                            ->label('Notas de Progreso')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas sobre el progreso del mantenimiento'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('issues_encountered')
                                    ->label('Problemas Encontrados')
                                    ->maxLength(255)
                                    ->helperText('Problemas encontrados durante el mantenimiento'),
                                TextInput::make('solutions_applied')
                                    ->label('Soluciones Aplicadas')
                                    ->maxLength(255)
                                    ->helperText('Soluciones aplicadas a los problemas'),
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
                                    ->helperText('¿El programa está activo?'),
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para cambios?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('auto_schedule')
                                    ->label('Programación Automática')
                                    ->helperText('¿Se programa automáticamente?'),
                                Toggle::make('notifications_enabled')
                                    ->label('Notificaciones Habilitadas')
                                    ->default(true)
                                    ->helperText('¿Enviar notificaciones?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Orden de Clasificación')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Orden para mostrar en listas'),
                                TextInput::make('visibility_level')
                                    ->label('Nivel de Visibilidad')
                                    ->options([
                                        'public' => 'Público',
                                        'members_only' => 'Solo Miembros',
                                        'organization_only' => 'Solo Organización',
                                        'private' => 'Privado',
                                    ])
                                    ->searchable(),
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
                            ->directory('maintenance-schedules')
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
                    ->limit(30),
                TextColumn::make('installation.name')
                    ->label('Instalación')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                BadgeColumn::make('maintenance_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'preventive',
                        'success' => 'corrective',
                        'warning' => 'predictive',
                        'danger' => 'emergency',
                        'info' => 'scheduled',
                        'secondary' => 'unscheduled',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => MaintenanceSchedule::getMaintenanceTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'in_progress',
                        'danger' => 'overdue',
                        'info' => 'scheduled',
                        'secondary' => 'cancelled',
                        'gray' => 'pending',
                    ])
                    ->formatStateUsing(fn (string $state): string => MaintenanceSchedule::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => MaintenanceSchedule::getPriorities()[$state] ?? $state),
                TextColumn::make('scheduled_start')
                    ->label('Inicio Programado')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('estimated_duration_hours')
                    ->label('Duración (h)')
                    ->suffix(' h')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 2 => 'success',
                        $state <= 8 => 'primary',
                        $state <= 24 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('completion_percentage')
                    ->label('Completado (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 50 => 'warning',
                        $state >= 25 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('estimated_cost')
                    ->label('Costo Estimado (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('assigned_technician.name')
                    ->label('Técnico')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                ToggleColumn::make('auto_schedule')
                    ->label('Auto Programado'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('maintenance_type')
                    ->label('Tipo de Mantenimiento')
                    ->options(MaintenanceSchedule::getMaintenanceTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(MaintenanceSchedule::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(MaintenanceSchedule::getPriorities())
                    ->multiple(),
                SelectFilter::make('frequency')
                    ->label('Frecuencia')
                    ->options(MaintenanceSchedule::getFrequencies())
                    ->multiple(),
                SelectFilter::make('maintenance_category')
                    ->label('Categoría')
                    ->options([
                        'preventive' => 'Preventivo',
                        'corrective' => 'Correctivo',
                        'predictive' => 'Predictivo',
                        'emergency' => 'Emergencia',
                        'scheduled' => 'Programado',
                        'unscheduled' => 'No Programado',
                        'other' => 'Otro',
                    ])
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('scheduled_end', '<', now())->where('status', '!=', 'completed'))
                    ->label('Atrasados'),
                Filter::make('scheduled_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('scheduled_start', today()))
                    ->label('Programados Hoy'),
                Filter::make('scheduled_this_week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('scheduled_start', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label('Programados Esta Semana'),
                Filter::make('high_priority')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', ['high', 'urgent', 'critical']))
                    ->label('Alta Prioridad'),
                Filter::make('low_cost')
                    ->query(fn (Builder $query): Builder => $query->where('estimated_cost', '<=', 100))
                    ->label('Bajo Costo (≤100€)'),
                Filter::make('high_cost')
                    ->query(fn (Builder $query): Builder => $query->where('estimated_cost', '>=', 1000))
                    ->label('Alto Costo (≥1000€)'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('scheduled_from')
                            ->label('Programado desde'),
                        DateTimePicker::make('scheduled_until')
                            ->label('Programado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn (Builder $query, $date): Builder => $query->where('scheduled_start', '>=', $date),
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn (Builder $query, $date): Builder => $query->where('scheduled_start', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas Programadas'),
                Filter::make('cost_range')
                    ->form([
                        TextInput::make('min_cost')
                            ->label('Costo mínimo (€)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_cost')
                            ->label('Costo máximo (€)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_cost'],
                                fn (Builder $query, $cost): Builder => $query->where('estimated_cost', '>=', $cost),
                            )
                            ->when(
                                $data['max_cost'],
                                fn (Builder $query, $cost): Builder => $query->where('estimated_cost', '<=', $cost),
                            );
                    })
                    ->label('Rango de Costos'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('start_maintenance')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (MaintenanceSchedule $record) => $record->status === 'scheduled')
                    ->action(function (MaintenanceSchedule $record) {
                        $record->update(['status' => 'in_progress']);
                    }),
                Tables\Actions\Action::make('complete_maintenance')
                    ->label('Completar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (MaintenanceSchedule $record) => $record->status === 'in_progress')
                    ->action(function (MaintenanceSchedule $record) {
                        $record->update([
                            'status' => 'completed',
                            'completion_percentage' => 100,
                        ]);
                    }),
                Tables\Actions\Action::make('reschedule_maintenance')
                    ->label('Reprogramar')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->visible(fn (MaintenanceSchedule $record) => in_array($record->status, ['scheduled', 'pending']))
                    ->form([
                        DateTimePicker::make('new_scheduled_start')
                            ->label('Nuevo Inicio')
                            ->required(),
                        DateTimePicker::make('new_scheduled_end')
                            ->label('Nuevo Fin')
                            ->required(),
                    ])
                    ->action(function (MaintenanceSchedule $record, array $data) {
                        $record->update([
                            'scheduled_start' => $data['new_scheduled_start'],
                            'scheduled_end' => $data['new_scheduled_end'],
                        ]);
                    }),
                Tables\Actions\Action::make('cancel_maintenance')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (MaintenanceSchedule $record) => in_array($record->status, ['scheduled', 'pending']))
                    ->action(function (MaintenanceSchedule $record) {
                        $record->update(['status' => 'cancelled']);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (MaintenanceSchedule $record) {
                        $newRecord = $record->replicate();
                        $newRecord->status = 'pending';
                        $newRecord->scheduled_start = now()->addDays(7);
                        $newRecord->scheduled_end = now()->addDays(7)->addHours(2);
                        $newRecord->completion_percentage = 0;
                        $newRecord->is_active = true;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('start_all')
                        ->label('Iniciar Todos')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'scheduled') {
                                    $record->update(['status' => 'in_progress']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('complete_all')
                        ->label('Completar Todos')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'in_progress') {
                                    $record->update([
                                        'status' => 'completed',
                                        'completion_percentage' => 100,
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('reschedule_all')
                        ->label('Reprogramar Todos')
                        ->icon('heroicon-o-calendar')
                        ->form([
                            DateTimePicker::make('days_offset')
                                ->label('Días a adelantar/retrasar')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $offset = $data['days_offset'];
                                $record->update([
                                    'scheduled_start' => $record->scheduled_start->addDays($offset),
                                    'scheduled_end' => $record->scheduled_end->addDays($offset),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(MaintenanceSchedule::getStatuses())
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
                                ->options(MaintenanceSchedule::getPriorities())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['priority' => $data['priority']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('scheduled_start', 'asc')
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
            'index' => Pages\ListMaintenanceSchedules::route('/'),
            'create' => Pages\CreateMaintenanceSchedule::route('/create'),
            'edit' => Pages\EditMaintenanceSchedule::route('/{record}/edit'),
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
        $overdueCount = static::getModel()::where('scheduled_end', '<', now())->where('status', '!=', 'completed')->count();
        
        if ($overdueCount > 0) {
            return 'danger';
        }
        
        $todayCount = static::getModel()::whereDate('scheduled_start', today())->count();
        
        if ($todayCount > 0) {
            return 'warning';
        }
        
        $urgentCount = static::getModel()::whereIn('priority', ['high', 'urgent', 'critical'])->count();
        
        if ($urgentCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
