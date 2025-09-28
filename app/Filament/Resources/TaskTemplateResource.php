<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskTemplateResource\Pages;
use App\Filament\Resources\TaskTemplateResource\RelationManagers;
use App\Models\TaskTemplate;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskTemplateResource extends Resource
{
    protected static ?string $model = TaskTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Sistema y Mantenimiento';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Plantillas de Tareas';

    protected static ?string $modelLabel = 'Plantilla de Tarea';

    protected static ?string $pluralModelLabel = 'Plantillas de Tareas';

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
                                    ->helperText('Nombre descriptivo de la plantilla'),
                                TextInput::make('version')
                                    ->label('Versión')
                                    ->maxLength(50)
                                    ->default('1.0')
                                    ->helperText('Versión de la plantilla'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada de la plantilla'),
                        Grid::make(3)
                            ->schema([
                                Select::make('template_type')
                                    ->label('Tipo de Plantilla')
                                    ->options(TaskTemplate::getTemplateTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('category')
                                    ->label('Categoría')
                                    ->options([
                                        'maintenance' => 'Mantenimiento',
                                        'inspection' => 'Inspección',
                                        'repair' => 'Reparación',
                                        'installation' => 'Instalación',
                                        'testing' => 'Pruebas',
                                        'calibration' => 'Calibración',
                                        'cleaning' => 'Limpieza',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                Select::make('subcategory')
                                    ->label('Subcategoría')
                                    ->options([
                                        'preventive' => 'Preventivo',
                                        'corrective' => 'Correctivo',
                                        'predictive' => 'Predictivo',
                                        'emergency' => 'Emergencia',
                                        'scheduled' => 'Programado',
                                        'unscheduled' => 'No Programado',
                                    ])
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('department')
                                    ->label('Departamento')
                                    ->maxLength(255)
                                    ->helperText('Departamento responsable'),
                                TextInput::make('priority')
                                    ->label('Prioridad')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->step(1)
                                    ->default(5)
                                    ->helperText('Prioridad de 1 a 10'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Especificaciones Técnicas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('estimated_duration_hours')
                                    ->label('Duración Estimada (horas)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.5)
                                    ->helperText('Tiempo estimado para completar'),
                                TextInput::make('estimated_cost')
                                    ->label('Costo Estimado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Costo estimado de la tarea'),
                            ]),
                        RichEditor::make('technical_requirements')
                            ->label('Requisitos Técnicos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Requisitos técnicos específicos'),
                        RichEditor::make('quality_standards')
                            ->label('Estándares de Calidad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Recursos Requeridos')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                RichEditor::make('required_skills')
                                    ->label('Habilidades Requeridas')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                    ])
                                    ->helperText('Habilidades necesarias para ejecutar la tarea'),
                                RichEditor::make('required_tools')
                                    ->label('Herramientas Requeridas')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                    ]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                RichEditor::make('required_parts')
                                    ->label('Partes Requeridas')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                    ]),
                                RichEditor::make('required_materials')
                                    ->label('Materiales Requeridos')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                    ]),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Seguridad y Cumplimiento')
                    ->schema([
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
                        RichEditor::make('compliance_requirements')
                            ->label('Requisitos de Cumplimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('training_required')
                                    ->label('Requiere Entrenamiento')
                                    ->helperText('¿Se necesita entrenamiento específico?'),
                                Toggle::make('certification_required')
                                    ->label('Requiere Certificación')
                                    ->helperText('¿Se necesita certificación específica?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Instrucciones de Trabajo')
                    ->schema([
                        RichEditor::make('work_instructions')
                            ->label('Instrucciones de Trabajo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Instrucciones paso a paso para ejecutar la tarea'),
                        Repeater::make('checklist_items')
                            ->label('Elementos de Lista de Verificación')
                            ->schema([
                                TextInput::make('item')
                                    ->label('Elemento')
                                    ->required()
                                    ->maxLength(255),
                                Toggle::make('required')
                                    ->label('Requerido')
                                    ->default(true),
                                TextInput::make('notes')
                                    ->label('Notas')
                                    ->maxLength(255),
                            ])
                            ->defaultItems(5)
                            ->minItems(1)
                            ->maxItems(50)
                            ->reorderable()
                            ->collapsible(),
                    ])
                    ->collapsible(),

                Section::make('Consideraciones Ambientales')
                    ->schema([
                        RichEditor::make('environmental_considerations')
                            ->label('Consideraciones Ambientales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Impacto ambiental y consideraciones'),
                        RichEditor::make('waste_disposal')
                            ->label('Disposición de Residuos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('budget_code')
                                    ->label('Código de Presupuesto')
                                    ->maxLength(255),
                                TextInput::make('cost_center')
                                    ->label('Centro de Costos')
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('project_code')
                                    ->label('Código de Proyecto')
                                    ->maxLength(255),
                                Select::make('risk_level')
                                    ->label('Nivel de Riesgo')
                                    ->options([
                                        'low' => 'Bajo',
                                        'medium' => 'Medio',
                                        'high' => 'Alto',
                                        'critical' => 'Crítico',
                                    ])
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Documentación de Referencia')
                    ->schema([
                        RichEditor::make('reference_documents')
                            ->label('Documentos de Referencia')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('best_practices')
                            ->label('Mejores Prácticas')
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
                            ->directory('task-templates')
                            ->maxFiles(20)
                            ->maxSize(10240)
                            ->helperText('Máximo 20 archivos de 10MB cada uno'),
                    ])
                    ->collapsible(),

                Section::make('Configuración de la Plantilla')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true)
                                    ->helperText('¿La plantilla está activa?'),
                                Toggle::make('is_standard')
                                    ->label('Estándar')
                                    ->helperText('¿Es una plantilla estándar del sistema?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para usar?'),
                                Toggle::make('auto_generate_tasks')
                                    ->label('Generar Tareas Automáticamente')
                                    ->helperText('¿Generar tareas automáticamente?'),
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
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('template_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'maintenance',
                        'success' => 'inspection',
                        'warning' => 'repair',
                        'danger' => 'installation',
                        'info' => 'testing',
                        'secondary' => 'calibration',
                        'gray' => 'cleaning',
                    ])
                    ->formatStateUsing(fn (string $state): string => TaskTemplate::getTemplateTypes()[$state] ?? $state),
                BadgeColumn::make('category')
                    ->label('Categoría')
                    ->colors([
                        'primary' => 'maintenance',
                        'success' => 'inspection',
                        'warning' => 'repair',
                        'danger' => 'installation',
                        'info' => 'testing',
                        'secondary' => 'calibration',
                        'gray' => 'cleaning',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'maintenance' => 'Mantenimiento',
                        'inspection' => 'Inspección',
                        'repair' => 'Reparación',
                        'installation' => 'Instalación',
                        'testing' => 'Pruebas',
                        'calibration' => 'Calibración',
                        'cleaning' => 'Limpieza',
                        'other' => 'Otro',
                        default => $state,
                    }),
                TextColumn::make('department')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('Prioridad')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 8 => 'danger',
                        $state >= 6 => 'warning',
                        $state >= 4 => 'info',
                        default => 'success',
                    }),
                TextColumn::make('estimated_duration_hours')
                    ->label('Duración Est. (h)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' h'),
                TextColumn::make('estimated_cost')
                    ->label('Costo Est.')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('risk_level')
                    ->label('Riesgo')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Bajo',
                        'medium' => 'Medio',
                        'high' => 'Alto',
                        'critical' => 'Crítico',
                        default => 'N/A',
                    }),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                ToggleColumn::make('is_standard')
                    ->label('Estándar'),
                ToggleColumn::make('training_required')
                    ->label('Entrenamiento'),
                ToggleColumn::make('certification_required')
                    ->label('Certificación'),
                TextColumn::make('version')
                    ->label('Versión')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('template_type')
                    ->label('Tipo de Plantilla')
                    ->options(TaskTemplate::getTemplateTypes())
                    ->multiple(),
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'maintenance' => 'Mantenimiento',
                        'inspection' => 'Inspección',
                        'repair' => 'Reparación',
                        'installation' => 'Instalación',
                        'testing' => 'Pruebas',
                        'calibration' => 'Calibración',
                        'cleaning' => 'Limpieza',
                        'other' => 'Otro',
                    ])
                    ->multiple(),
                SelectFilter::make('department')
                    ->label('Departamento')
                    ->options(function () {
                        return TaskTemplate::distinct()->pluck('department', 'department')->filter()->toArray();
                    })
                    ->multiple(),
                SelectFilter::make('risk_level')
                    ->label('Nivel de Riesgo')
                    ->options([
                        'low' => 'Bajo',
                        'medium' => 'Medio',
                        'high' => 'Alto',
                        'critical' => 'Crítico',
                    ])
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activas'),
                Filter::make('standard')
                    ->query(fn (Builder $query): Builder => $query->where('is_standard', true))
                    ->label('Solo Estándar'),
                Filter::make('training_required')
                    ->query(fn (Builder $query): Builder => $query->where('training_required', true))
                    ->label('Requieren Entrenamiento'),
                Filter::make('certification_required')
                    ->query(fn (Builder $query): Builder => $query->where('certification_required', true))
                    ->label('Requieren Certificación'),
                Filter::make('priority_range')
                    ->form([
                        TextInput::make('min_priority')
                            ->label('Prioridad mínima')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),
                        TextInput::make('max_priority')
                            ->label('Prioridad máxima')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_priority'],
                                fn (Builder $query, $priority): Builder => $query->where('priority', '>=', $priority),
                            )
                            ->when(
                                $data['max_priority'],
                                fn (Builder $query, $priority): Builder => $query->where('priority', '<=', $priority),
                            );
                    })
                    ->label('Rango de Prioridad'),
                Filter::make('duration_range')
                    ->form([
                        TextInput::make('min_duration')
                            ->label('Duración mínima (horas)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_duration')
                            ->label('Duración máxima (horas)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_duration'],
                                fn (Builder $query, $duration): Builder => $query->where('estimated_duration_hours', '>=', $duration),
                            )
                            ->when(
                                $data['max_duration'],
                                fn (Builder $query, $duration): Builder => $query->where('estimated_duration_hours', '<=', $duration),
                            );
                    })
                    ->label('Rango de Duración'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (TaskTemplate $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->version = '1.0';
                        $newRecord->is_standard = false;
                        $newRecord->save();
                    }),
                Tables\Actions\Action::make('create_task')
                    ->label('Crear Tarea')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->url(fn (TaskTemplate $record): string => route('filament.admin.resources.maintenance-tasks.create', ['template_id' => $record->id]))
                    ->openUrlInNewTab(),
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
                    Tables\Actions\BulkAction::make('mark_standard')
                        ->label('Marcar como Estándar')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_standard' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('assign_department')
                        ->label('Asignar Departamento')
                        ->icon('heroicon-o-building-office')
                        ->form([
                            TextInput::make('department')
                                ->label('Departamento')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['department' => $data['department']]);
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
            'index' => Pages\ListTaskTemplates::route('/'),
            'create' => Pages\CreateTaskTemplate::route('/create'),
            'edit' => Pages\EditTaskTemplate::route('/{record}/edit'),
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
        
        return 'success';
    }
}
