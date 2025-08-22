<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChecklistTemplateResource\Pages;
use App\Filament\Resources\ChecklistTemplateResource\RelationManagers;
use App\Models\ChecklistTemplate;
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
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChecklistTemplateResource extends Resource
{
    protected static ?string $model = ChecklistTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Mantenimiento';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Plantillas de Listas de Verificación';

    protected static ?string $modelLabel = 'Plantilla de Lista de Verificación';

    protected static ?string $pluralModelLabel = 'Plantillas de Listas de Verificación';

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
                                    ->options([
                                        'maintenance' => 'Mantenimiento',
                                        'inspection' => 'Inspección',
                                        'safety' => 'Seguridad',
                                        'quality' => 'Calidad',
                                        'compliance' => 'Cumplimiento',
                                        'audit' => 'Auditoría',
                                        'startup' => 'Puesta en Marcha',
                                        'shutdown' => 'Parada',
                                        'other' => 'Otro',
                                    ])
                                    ->required()
                                    ->searchable(),
                                Select::make('category')
                                    ->label('Categoría')
                                    ->options([
                                        'equipment' => 'Equipos',
                                        'facility' => 'Instalaciones',
                                        'process' => 'Procesos',
                                        'safety' => 'Seguridad',
                                        'environmental' => 'Ambiental',
                                        'regulatory' => 'Regulatorio',
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
                                        'routine' => 'Rutinario',
                                        'special' => 'Especial',
                                        'other' => 'Otro',
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

                Section::make('Configuración de la Lista')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('pass_threshold')
                                    ->label('Umbral de Aprobación (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(1)
                                    ->default(80)
                                    ->suffix('%')
                                    ->helperText('Porcentaje mínimo para aprobar'),
                                TextInput::make('fail_threshold')
                                    ->label('Umbral de Fallo (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(1)
                                    ->default(60)
                                    ->suffix('%')
                                    ->helperText('Porcentaje para considerar fallo'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('scoring_system')
                                    ->label('Sistema de Puntuación')
                                    ->options([
                                        'pass_fail' => 'Aprobado/Fallido',
                                        'percentage' => 'Porcentaje',
                                        'points' => 'Puntos',
                                        'rating' => 'Calificación',
                                        'custom' => 'Personalizado',
                                    ])
                                    ->default('pass_fail')
                                    ->searchable(),
                                TextInput::make('estimated_completion_time')
                                    ->label('Tiempo Estimado de Completado (minutos)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Tiempo estimado para completar'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Elementos de la Lista')
                    ->schema([
                        Repeater::make('checklist_items')
                            ->label('Elementos de la Lista')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('item')
                                            ->label('Elemento')
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('Descripción del elemento a verificar'),
                                        Select::make('type')
                                            ->label('Tipo')
                                            ->options([
                                                'checkbox' => 'Casilla de Verificación',
                                                'radio' => 'Opción Única',
                                                'text' => 'Texto',
                                                'number' => 'Número',
                                                'date' => 'Fecha',
                                                'file' => 'Archivo',
                                                'signature' => 'Firma',
                                            ])
                                            ->default('checkbox')
                                            ->searchable(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('required')
                                            ->label('Requerido')
                                            ->default(true)
                                            ->helperText('¿Es obligatorio este elemento?'),
                                        TextInput::make('weight')
                                            ->label('Peso')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.1)
                                            ->default(1)
                                            ->helperText('Peso para el cálculo de puntuación'),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('order')
                                            ->label('Orden')
                                            ->numeric()
                                            ->minValue(1)
                                            ->step(1)
                                            ->helperText('Orden de aparición en la lista'),
                                        Select::make('conditional_logic')
                                            ->label('Lógica Condicional')
                                            ->options([
                                                'none' => 'Ninguna',
                                                'show_if' => 'Mostrar si',
                                                'hide_if' => 'Ocultar si',
                                                'required_if' => 'Requerido si',
                                            ])
                                            ->default('none')
                                            ->searchable(),
                                    ]),
                                Textarea::make('instructions')
                                    ->label('Instrucciones')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->helperText('Instrucciones específicas para este elemento'),
                                Textarea::make('notes')
                                    ->label('Notas')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->helperText('Notas adicionales o aclaraciones'),
                            ])
                            ->defaultItems(10)
                            ->minItems(1)
                            ->maxItems(100)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['item'] ?? null),
                    ])
                    ->collapsible(),

                Section::make('Elementos Condicionales')
                    ->schema([
                        Repeater::make('conditional_items')
                            ->label('Elementos Condicionales')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('trigger_item')
                                            ->label('Elemento Disparador')
                                            ->maxLength(255)
                                            ->helperText('Elemento que activa la condición'),
                                        Select::make('trigger_condition')
                                            ->label('Condición del Disparador')
                                            ->options([
                                                'checked' => 'Marcado',
                                                'unchecked' => 'Desmarcado',
                                                'equals' => 'Igual a',
                                                'not_equals' => 'Diferente de',
                                                'greater_than' => 'Mayor que',
                                                'less_than' => 'Menor que',
                                            ])
                                            ->searchable(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('trigger_value')
                                            ->label('Valor del Disparador')
                                            ->maxLength(255)
                                            ->helperText('Valor específico para la condición'),
                                        TextInput::make('target_item')
                                            ->label('Elemento Objetivo')
                                            ->maxLength(255)
                                            ->helperText('Elemento afectado por la condición'),
                                    ]),
                                Select::make('action')
                                    ->label('Acción')
                                    ->options([
                                        'show' => 'Mostrar',
                                        'hide' => 'Ocultar',
                                        'require' => 'Hacer Requerido',
                                        'optional' => 'Hacer Opcional',
                                        'enable' => 'Habilitar',
                                        'disable' => 'Deshabilitar',
                                    ])
                                    ->searchable(),
                            ])
                            ->defaultItems(0)
                            ->minItems(0)
                            ->maxItems(50)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['trigger_item'] ?? null),
                    ])
                    ->collapsible(),

                Section::make('Requisitos y Estándares')
                    ->schema([
                        RichEditor::make('quality_standards')
                            ->label('Estándares de Calidad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Estándares de calidad aplicables'),
                        RichEditor::make('safety_requirements')
                            ->label('Requisitos de Seguridad')
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
                        RichEditor::make('compliance_requirements')
                            ->label('Requisitos de Cumplimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
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
                            ->helperText('Instrucciones paso a paso para usar la lista'),
                        RichEditor::make('best_practices')
                            ->label('Mejores Prácticas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('lessons_learned')
                            ->label('Lecciones Aprendidas')
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
                        Grid::make(2)
                            ->schema([
                                TextInput::make('audit_frequency')
                                    ->label('Frecuencia de Auditoría (meses)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Cada cuántos meses se audita'),
                                DatePicker::make('last_review_date')
                                    ->label('Última Fecha de Revisión'),
                            ]),
                        DatePicker::make('next_review_date')
                            ->label('Próxima Fecha de Revisión'),
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
                        RichEditor::make('continuous_improvement')
                            ->label('Mejora Continua')
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
                            ->directory('checklist-templates')
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
                                Toggle::make('auto_generate_checklists')
                                    ->label('Generar Listas Automáticamente')
                                    ->helperText('¿Generar listas automáticamente?'),
                            ]),
                        RichEditor::make('approval_workflow')
                            ->label('Flujo de Aprobación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('escalation_procedures')
                            ->label('Procedimientos de Escalación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
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
                        Select::make('reviewed_by')
                            ->label('Revisado por')
                            ->relationship('reviewedBy', 'name')
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
                        'warning' => 'safety',
                        'danger' => 'quality',
                        'info' => 'compliance',
                        'secondary' => 'audit',
                        'gray' => 'startup',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'maintenance' => 'Mantenimiento',
                        'inspection' => 'Inspección',
                        'safety' => 'Seguridad',
                        'quality' => 'Calidad',
                        'compliance' => 'Cumplimiento',
                        'audit' => 'Auditoría',
                        'startup' => 'Puesta en Marcha',
                        'shutdown' => 'Parada',
                        'other' => 'Otro',
                        default => $state,
                    }),
                BadgeColumn::make('category')
                    ->label('Categoría')
                    ->colors([
                        'primary' => 'equipment',
                        'success' => 'facility',
                        'warning' => 'process',
                        'danger' => 'safety',
                        'info' => 'environmental',
                        'secondary' => 'regulatory',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'equipment' => 'Equipos',
                        'facility' => 'Instalaciones',
                        'process' => 'Procesos',
                        'safety' => 'Seguridad',
                        'environmental' => 'Ambiental',
                        'regulatory' => 'Regulatorio',
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
                TextColumn::make('pass_threshold')
                    ->label('Umbral Aprobación')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('fail_threshold')
                    ->label('Umbral Fallo')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estimated_completion_time')
                    ->label('Tiempo Est. (min)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' min'),
                BadgeColumn::make('scoring_system')
                    ->label('Sistema Puntuación')
                    ->colors([
                        'primary' => 'pass_fail',
                        'success' => 'percentage',
                        'warning' => 'points',
                        'info' => 'rating',
                        'secondary' => 'custom',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pass_fail' => 'Aprobado/Fallido',
                        'percentage' => 'Porcentaje',
                        'points' => 'Puntos',
                        'rating' => 'Calificación',
                        'custom' => 'Personalizado',
                        default => $state,
                    }),
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
                    ->options([
                        'maintenance' => 'Mantenimiento',
                        'inspection' => 'Inspección',
                        'safety' => 'Seguridad',
                        'quality' => 'Calidad',
                        'compliance' => 'Cumplimiento',
                        'audit' => 'Auditoría',
                        'startup' => 'Puesta en Marcha',
                        'shutdown' => 'Parada',
                        'other' => 'Otro',
                    ])
                    ->multiple(),
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'equipment' => 'Equipos',
                        'facility' => 'Instalaciones',
                        'process' => 'Procesos',
                        'safety' => 'Seguridad',
                        'environmental' => 'Ambiental',
                        'regulatory' => 'Regulatorio',
                        'other' => 'Otro',
                    ])
                    ->multiple(),
                SelectFilter::make('department')
                    ->label('Departamento')
                    ->options(function () {
                        return ChecklistTemplate::distinct()->pluck('department', 'department')->filter()->toArray();
                    })
                    ->multiple(),
                SelectFilter::make('scoring_system')
                    ->label('Sistema de Puntuación')
                    ->options([
                        'pass_fail' => 'Aprobado/Fallido',
                        'percentage' => 'Porcentaje',
                        'points' => 'Puntos',
                        'rating' => 'Calificación',
                        'custom' => 'Personalizado',
                    ])
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
                Filter::make('threshold_range')
                    ->form([
                        TextInput::make('min_threshold')
                            ->label('Umbral mínimo (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('max_threshold')
                            ->label('Umbral máximo (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_threshold'],
                                fn (Builder $query, $threshold): Builder => $query->where('pass_threshold', '>=', $threshold),
                            )
                            ->when(
                                $data['max_threshold'],
                                fn (Builder $query, $threshold): Builder => $query->where('pass_threshold', '<=', $threshold),
                            );
                    })
                    ->label('Rango de Umbral'),
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
                    ->action(function (ChecklistTemplate $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->version = '1.0';
                        $newRecord->is_standard = false;
                        $newRecord->save();
                    }),
                Tables\Actions\Action::make('create_checklist')
                    ->label('Crear Lista')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->url(fn (ChecklistTemplate $record): string => route('filament.admin.resources.checklists.create', ['template_id' => $record->id]))
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
            'index' => Pages\ListChecklistTemplates::route('/'),
            'create' => Pages\CreateChecklistTemplate::route('/create'),
            'edit' => Pages\EditChecklistTemplate::route('/{record}/edit'),
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
