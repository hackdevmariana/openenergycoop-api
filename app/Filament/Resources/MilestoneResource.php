<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MilestoneResource\Pages;
use App\Filament\Resources\MilestoneResource\RelationManagers;
use App\Models\Milestone;
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


class MilestoneResource extends Resource
{
    protected static ?string $model = Milestone::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Gestión de Proyectos';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Hitos';

    protected static ?string $modelLabel = 'Hito';

    protected static ?string $pluralModelLabel = 'Hitos';

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
                                    ->helperText('Título descriptivo del hito'),
                                Select::make('milestone_type')
                                    ->label('Tipo de Hito')
                                    ->options(Milestone::getMilestoneTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada del hito'),
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(Milestone::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(Milestone::getPriorities())
                                    ->required()
                                    ->searchable(),
                                Select::make('parent_milestone_id')
                                    ->label('Hito Padre')
                                    ->relationship('parentMilestone', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Hito padre si es un sub-hito'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('target_date')
                                    ->label('Fecha Objetivo')
                                    ->required()
                                    ->helperText('Fecha límite para completar'),
                                DatePicker::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->helperText('Cuándo se inició el hito'),
                                DatePicker::make('completion_date')
                                    ->label('Fecha de Completado')
                                    ->helperText('Cuándo se completó'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Valores y Progreso')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('target_value')
                                    ->label('Valor Objetivo')
                                    ->numeric()
                                    ->step(0.01)
                                    ->helperText('Valor objetivo a alcanzar'),
                                TextInput::make('current_value')
                                    ->label('Valor Actual')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Valor actual alcanzado'),
                                TextInput::make('progress_percentage')
                                    ->label('Porcentaje de Progreso (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de progreso'),
                            ]),
                        Placeholder::make('progress_bar')
                            ->content(function ($get) {
                                $target = $get('target_value') ?: 0;
                                $current = $get('current_value') ?: 0;
                                $percentage = $target > 0 ? min(100, ($current / $target) * 100) : 0;
                                
                                return view('components.progress-bar', [
                                    'percentage' => $percentage,
                                    'current' => $current,
                                    'target' => $target
                                ]);
                            })
                            ->visible(fn ($get) => $get('target_value') > 0),
                    ])
                    ->collapsible(),

                Section::make('Criterios de Éxito')
                    ->schema([
                        RichEditor::make('success_criteria')
                            ->label('Criterios de Éxito')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Criterios que definen el éxito del hito'),
                        RichEditor::make('dependencies')
                            ->label('Dependencias')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Otros hitos o tareas de las que depende'),
                        RichEditor::make('risks')
                            ->label('Riesgos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Riesgos identificados para este hito'),
                        RichEditor::make('mitigation_strategies')
                            ->label('Estrategias de Mitigación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Estrategias para mitigar los riesgos'),
                    ])
                    ->collapsible(),

                Section::make('Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('assigned_to')
                                    ->label('Asignado a')
                                    ->relationship('assignedTo', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Persona responsable del hito'),
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
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
                        FileUpload::make('attachments')
                            ->label('Archivos Adjuntos')
                            ->multiple()
                            ->directory('milestones')
                            ->maxFiles(20)
                            ->maxSize(10240)
                            ->helperText('Máximo 20 archivos de 10MB cada uno'),
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
                BadgeColumn::make('milestone_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'project',
                        'success' => 'financial',
                        'warning' => 'operational',
                        'danger' => 'regulatory',
                        'info' => 'community',
                        'secondary' => 'environmental',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => Milestone::getMilestoneTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'in_progress',
                        'danger' => 'overdue',
                        'info' => 'not_started',
                        'secondary' => 'on_hold',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => Milestone::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => Milestone::getPriorities()[$state] ?? $state),
                TextColumn::make('assignedTo.name')
                    ->label('Asignado a')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_date')
                    ->label('Fecha Objetivo')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completion_date')
                    ->label('Completado')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target_value')
                    ->label('Objetivo')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                TextColumn::make('current_value')
                    ->label('Actual')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                TextColumn::make('progress_percentage')
                    ->label('Progreso')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 50 => 'warning',
                        $state >= 25 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('parentMilestone.title')
                    ->label('Hito Padre')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('milestone_type')
                    ->label('Tipo de Hito')
                    ->options(Milestone::getMilestoneTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Milestone::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(Milestone::getPriorities())
                    ->multiple(),
                Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('target_date', '<', now())
                        ->whereNotIn('status', ['completed', 'cancelled']))
                    ->label('Vencidos'),
                Filter::make('due_soon')
                    ->query(fn (Builder $query): Builder => $query->query->where('target_date', '<=', now()->addDays(7))
                        ->whereNotIn('status', ['completed', 'cancelled']))
                    ->label('Vencen Pronto'),
                Filter::make('high_priority')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', ['high', 'urgent', 'critical']))
                    ->label('Alta Prioridad'),
                Filter::make('completed')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'completed'))
                    ->label('Solo Completados'),
                Filter::make('in_progress')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'in_progress'))
                    ->label('En Progreso'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('target_from')
                            ->label('Fecha objetivo desde'),
                        DatePicker::make('target_until')
                            ->label('Fecha objetivo hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['target_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('target_date', '>=', $date),
                            )
                            ->when(
                                $data['target_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('target_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas'),
                Filter::make('progress_range')
                    ->form([
                        TextInput::make('min_progress')
                            ->label('Progreso mínimo (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('max_progress')
                            ->label('Progreso máximo (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_progress'],
                                fn (Builder $query, $progress): Builder => $query->where('progress_percentage', '>=', $progress),
                            )
                            ->when(
                                $data['max_progress'],
                                fn (Builder $query, $progress): Builder => $query->where('progress_percentage', '<=', $progress),
                            );
                    })
                    ->label('Rango de Progreso'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('start_milestone')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Milestone $record) => $record->status === 'not_started')
                    ->action(function (Milestone $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'start_date' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('complete_milestone')
                    ->label('Completar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Milestone $record) => in_array($record->status, ['not_started', 'in_progress', 'on_hold']))
                    ->action(function (Milestone $record) {
                        $record->update([
                            'status' => 'completed',
                            'completion_date' => now(),
                            'progress_percentage' => 100,
                        ]);
                    }),
                Tables\Actions\Action::make('put_on_hold')
                    ->label('Poner en Espera')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Milestone $record) => in_array($record->record->status, ['not_started', 'in_progress']))
                    ->action(function (Milestone $record) {
                        $record->update(['status' => 'on_hold']);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (Milestone $record) {
                        $newRecord = $record->replicate();
                        $newRecord->title = $newRecord->title . ' (Copia)';
                        $newRecord->status = 'not_started';
                        $newRecord->start_date = null;
                        $newRecord->completion_date = null;
                        $newRecord->current_value = 0;
                        $newRecord->progress_percentage = 0;
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
                                $record->update([
                                    'status' => 'in_progress',
                                    'start_date' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('complete_all')
                        ->label('Completar Todos')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'completed',
                                    'completion_date' => now(),
                                    'progress_percentage' => 100,
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('put_on_hold_all')
                        ->label('Poner en Espera')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'on_hold']);
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
                                $record->update(['assigned_to' => $data['user_id']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_priority')
                        ->label('Actualizar Prioridad')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority')
                                ->label('Prioridad')
                                ->options(Milestone::getPriorities())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['priority' => $data['priority']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('target_date', 'asc')
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
            'index' => Pages\ListMilestones::route('/'),
            'create' => Pages\CreateMilestone::route('/create'),
            'edit' => Pages\EditMilestone::route('/{record}/edit'),
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
        $overdueCount = static::getModel()::where('target_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        
        if ($overdueCount > 0) {
            return 'danger';
        }
        
        $dueSoonCount = static::getModel()::where('target_date', '<=', now()->addDays(7))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        
        if ($dueSoonCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
