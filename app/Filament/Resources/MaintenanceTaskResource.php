<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceTaskResource\Pages;
use App\Filament\Resources\MaintenanceTaskResource\RelationManagers;
use App\Models\MaintenanceTask;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;


class MaintenanceTaskResource extends Resource
{
    protected static ?string $model = MaintenanceTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Mantenimiento';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Tareas de Mantenimiento';

    protected static ?string $modelLabel = 'Tarea de Mantenimiento';

    protected static ?string $pluralModelLabel = 'Tareas de Mantenimiento';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // TextInput::make('task_number')
                                //     ->label('Número de Tarea')
                                //     ->required()
                                //     ->unique(ignoreRecord: true)
                                //     ->maxLength(255)
                                //     ->helperText('Identificador único de la tarea'),
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Título descriptivo de la tarea'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada de la tarea'),
                        Grid::make(3)
                            ->schema([
                                Select::make('task_type')
                                    ->label('Tipo de Tarea')
                                    ->options(MaintenanceTask::getTaskTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(MaintenanceTask::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(MaintenanceTask::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('category')
                                    ->label('Categoría')
                                    ->options([
                                        'preventive' => 'Preventivo',
                                        'corrective' => 'Correctivo',
                                        'predictive' => 'Predictivo',
                                        'emergency' => 'Emergencia',
                                        'scheduled' => 'Programado',
                                        'unscheduled' => 'No Programado',
                                    ])
                                    ->searchable(),
                                Select::make('subcategory')
                                    ->label('Subcategoría')
                                    ->options([
                                        'inspection' => 'Inspección',
                                        'cleaning' => 'Limpieza',
                                        'lubrication' => 'Lubricación',
                                        'calibration' => 'Calibración',
                                        'replacement' => 'Reemplazo',
                                        'repair' => 'Reparación',
                                        'testing' => 'Pruebas',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Asignaciones y Responsabilidades')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('assigned_to')
                                    ->label('Usuario Asignado')
                                    ->relationship('assignedTo', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario responsable de ejecutar la tarea'),
                                Select::make('assigned_by')
                                    ->label('Usuario Responsable')
                                    ->relationship('assignedBy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario responsable de supervisar la tarea'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('vendor_id')
                                    ->label('Proveedor')
                                    ->relationship('vendor', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('maintenance_schedule_id')
                                    ->label('Programa de Mantenimiento')
                                    ->relationship('maintenanceSchedule', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('planned_start_date')
                                    ->label('Fecha de Inicio Planificada')
                                    ->required()
                                    ->helperText('Cuándo se planea iniciar la tarea'),
                                DateTimePicker::make('planned_end_date')
                                    ->label('Fecha de Fin Planificada')
                                    ->required()
                                    ->helperText('Cuándo se planea terminar la tarea'),
                                DateTimePicker::make('due_date')
                                    ->label('Fecha de Vencimiento')
                                    ->required()
                                    ->helperText('Fecha límite para completar la tarea'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('actual_start_date')
                                    ->label('Fecha de Inicio Real'),
                                DateTimePicker::make('actual_end_date')
                                    ->label('Fecha de Fin Real'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('next_maintenance_date')
                                    ->label('Próxima Fecha de Mantenimiento'),
                                TextInput::make('maintenance_interval_days')
                                    ->label('Intervalo de Mantenimiento (días)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Duración y Costos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('estimated_hours')
                                    ->label('Duración Estimada (horas)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.5)
                                    ->helperText('Duración estimada en horas'),
                                TextInput::make('actual_hours')
                                    ->label('Duración Real (horas)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.5),
                                // TextInput::make('duration_variance_hours')
                                //     ->label('Variación de Duración (horas)')
                                //     ->numeric()
                                //     ->step(0.5)
                                //     ->disabled()
                                //     ->helperText('Diferencia entre estimado y real'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('cost_estimate')
                                    ->label('Costo Estimado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01),
                                TextInput::make('actual_cost')
                                    ->label('Costo Real')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01),
                                TextInput::make('cost_variance')
                                    ->label('Variación de Costo')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->disabled()
                                    ->helperText('Diferencia entre estimado y real'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Materiales y Requisitos')
                    ->schema([
                        RichEditor::make('required_materials')
                            ->label('Materiales Requeridos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Lista de materiales necesarios'),
                        RichEditor::make('required_tools')
                            ->label('Herramientas Requeridas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('safety_requirements')
                            ->label('Requisitos de Seguridad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Procedimientos de seguridad obligatorios'),
                        RichEditor::make('technical_requirements')
                            ->label('Requisitos Técnicos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Calidad y Evaluación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('quality_score')
                                    ->label('Puntuación de Calidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(1)
                                    ->suffix('%')
                                    ->helperText('Puntuación de 0 a 100'),
                                Select::make('quality_rating')
                                    ->label('Calificación de Calidad')
                                    ->options([
                                        'excellent' => 'Excelente (90-100)',
                                        'good' => 'Buena (80-89)',
                                        'satisfactory' => 'Satisfactoria (70-79)',
                                        'needs_improvement' => 'Necesita Mejora (60-69)',
                                        'unsatisfactory' => 'Insatisfactoria (<60)',
                                    ])
                                    ->searchable(),
                            ]),
                        RichEditor::make('quality_notes')
                            ->label('Notas de Calidad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('completion_notes')
                            ->label('Notas de Completado')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas sobre la ejecución de la tarea'),
                    ])
                    ->collapsible(),

                Section::make('Recurrencia y Programación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_recurring')
                                    ->label('Es Recurrente')
                                    ->helperText('¿Esta tarea se repite periódicamente?'),
                                Select::make('recurrence_type')
                                    ->label('Tipo de Recurrencia')
                                    ->options([
                                        'daily' => 'Diaria',
                                        'weekly' => 'Semanal',
                                        'monthly' => 'Mensual',
                                        'quarterly' => 'Trimestral',
                                        'semi_annually' => 'Semestral',
                                        'annually' => 'Anual',
                                        'custom' => 'Personalizada',
                                    ])
                                    ->searchable()
                                    ->visible(fn ($get) => $get('is_recurring')),
                            ]),
                        TextInput::make('recurrence_pattern')
                            ->label('Patrón de Recurrencia')
                            ->helperText('Patrón personalizado de recurrencia (ej: cada 15 días)')
                            ->visible(fn ($get) => $get('is_recurring')),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('recurrence_interval')
                                    ->label('Intervalo de Recurrencia')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Cada cuántas unidades de tiempo')
                                    ->visible(fn ($get) => $get('is_recurring')),
                                TextInput::make('max_recurrences')
                                    ->label('Máximo de Recurrencias')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('0 = sin límite')
                                    ->visible(fn ($get) => $get('is_recurring')),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos y Documentación')
                    ->schema([
                        TagsInput::make('tags')
                            ->label('Etiquetas')
                            ->separator(','),
                        RichEditor::make('metadata')
                            ->label('Metadatos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        FileUpload::make('attachments')
                            ->label('Archivos Adjuntos')
                            ->multiple()
                            ->directory('maintenance-tasks')
                            ->maxFiles(10)
                            ->maxSize(10240)
                            ->helperText('Máximo 10 archivos de 10MB cada uno'),
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
                        Select::make('managed_by')
                            ->label('Gestionado por')
                            // ->relationship('managedBy', 'name') // Relación no existe
                            ->searchable()
                            ->preload(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('task_number')
                //     ->label('Número')
                //     ->searchable()
                //     ->sortable()
                //     ->copyable()
                //     ->tooltip('Haz clic para copiar'),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('task_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'preventive',
                        'success' => 'corrective',
                        'warning' => 'predictive',
                        'danger' => 'emergency',
                        'info' => 'scheduled',
                        'secondary' => 'unscheduled',
                    ])
                    ->formatStateUsing(fn (string $state): string => MaintenanceTask::getTaskTypes()[$state] ?? $state),
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
                    ->formatStateUsing(fn (string $state): string => MaintenanceTask::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => MaintenanceTask::getPriorities()[$state] ?? $state),
                TextColumn::make('assignedUser.name')
                    ->label('Asignado a')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('estimated_hours')
                    ->label('Duración Est. (h)')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                TextColumn::make('actual_hours')
                    ->label('Duración Real (h)')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                TextColumn::make('cost_estimate')
                    ->label('Costo Est.')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('actual_cost')
                    ->label('Costo Real')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('quality_score')
                    ->label('Calidad')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                ToggleColumn::make('is_recurring')
                    ->label('Recurrente'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('task_type')
                    ->label('Tipo de Tarea')
                    ->options(MaintenanceTask::getTaskTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(MaintenanceTask::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(MaintenanceTask::getPriorities())
                    ->multiple(),
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'preventive' => 'Preventivo',
                        'corrective' => 'Correctivo',
                        'predictive' => 'Predictivo',
                        'emergency' => 'Emergencia',
                        'scheduled' => 'Programado',
                        'unscheduled' => 'No Programado',
                    ])
                    ->multiple(),
                Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())
                        ->whereNotIn('status', ['completed', 'cancelled']))
                    ->label('Vencidas'),
                Filter::make('due_soon')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<=', now()->addDays(7))
                        ->whereNotIn('status', ['completed', 'cancelled']))
                    ->label('Vencen Pronto'),
                Filter::make('recurring')
                    ->query(fn (Builder $query): Builder => $query->where('is_recurring', true))
                    ->label('Solo Recurrentes'),
                Filter::make('high_priority')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', ['high', 'urgent', 'critical']))
                    ->label('Alta Prioridad'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('due_from')
                            ->label('Vencimiento desde'),
                        DatePicker::make('due_until')
                            ->label('Vencimiento hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('start_task')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (MaintenanceTask $record) => $record->status === 'pending')
                    ->action(function (MaintenanceTask $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'actual_start_date' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('complete_task')
                    ->label('Completar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (MaintenanceTask $record) => $record->status === 'in_progress')
                    ->action(function (MaintenanceTask $record) {
                        $record->update([
                            'status' => 'completed',
                            'actual_end_date' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (MaintenanceTask $record) {
                        $newRecord = $record->replicate();
                        // $newRecord->task_number = $newRecord->task_number . '_copy';
                        $newRecord->title = $newRecord->title . ' (Copia)';
                        $newRecord->status = 'pending';
                        $newRecord->actual_start_date = null;
                        $newRecord->actual_end_date = null;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Marcar como Completadas')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'completed',
                                    'actual_end_date' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_in_progress')
                        ->label('Marcar como En Progreso')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'in_progress',
                                    'actual_start_date' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('assign_user')
                        ->label('Asignar Usuario')
                        ->icon('heroicon-o-user')
                        ->form([
                            Select::make('user_id')
                                ->label('Usuario')
                                ->options(\App\Models\User::pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['assigned_user_id' => $data['user_id']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('due_date', 'asc')
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
            'index' => Pages\ListMaintenanceTasks::route('/'),
            'create' => Pages\CreateMaintenanceTask::route('/create'),
            'edit' => Pages\EditMaintenanceTask::route('/{record}/edit'),
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
        $overdueCount = static::getModel()::where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        
        if ($overdueCount > 0) {
            return 'danger';
        }
        
        $dueSoonCount = static::getModel()::where('due_date', '<=', now()->addDays(7))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        
        if ($dueSoonCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
