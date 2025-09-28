<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionProjectResource\Pages;
use App\Filament\Resources\ProductionProjectResource\RelationManagers;
use App\Models\ProductionProject;
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

class ProductionProjectResource extends Resource
{
    protected static ?string $model = ProductionProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Proyectos de Producción';

    protected static ?string $modelLabel = 'Proyecto de Producción';

    protected static ?string $pluralModelLabel = 'Proyectos de Producción';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('project_number')
                                    ->label('Número de Proyecto')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Número único de identificación'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre del proyecto'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada del proyecto'),
                        Grid::make(3)
                            ->schema([
                                Select::make('project_type')
                                    ->label('Tipo de Proyecto')
                                    ->options(ProductionProject::getProjectTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(ProductionProject::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(ProductionProject::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas del Proyecto')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->required()
                                    ->helperText('Cuándo comenzó el proyecto'),
                                DatePicker::make('expected_completion_date')
                                    ->label('Fecha de Finalización Esperada')
                                    ->helperText('Cuándo se espera que termine'),
                                DatePicker::make('actual_completion_date')
                                    ->label('Fecha de Finalización Real')
                                    ->helperText('Cuándo terminó realmente'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Presupuesto y Financiamiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('budget')
                                    ->label('Presupuesto Total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Presupuesto total del proyecto'),
                                TextInput::make('spent_amount')
                                    ->label('Monto Gastado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Monto ya gastado'),
                                TextInput::make('remaining_budget')
                                    ->label('Presupuesto Restante')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Presupuesto disponible'),
                            ]),
                        Placeholder::make('budget_status')
                            ->content(function ($get) {
                                $budget = $get('budget') ?: 0;
                                $spent = $get('spent_amount') ?: 0;
                                $remaining = $get('remaining_budget') ?: 0;
                                $percentage = $budget > 0 ? ($spent / $budget) * 100 : 0;
                                
                                return "**Gastado: " . number_format($percentage, 1) . "% del presupuesto**";
                            })
                            ->visible(fn ($get) => $get('budget') > 0),
                    ])
                    ->collapsible(),

                Section::make('Capacidad y Rendimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('planned_capacity_mw')
                                    ->label('Capacidad Planificada (MW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText('Capacidad planificada en megavatios'),
                                TextInput::make('actual_capacity_mw')
                                    ->label('Capacidad Real (MW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Capacidad real instalada'),
                                TextInput::make('efficiency_rating')
                                    ->label('Eficiencia (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Factor de eficiencia'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Ubicación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location_address')
                                    ->label('Dirección')
                                    ->maxLength(255)
                                    ->helperText('Dirección física del proyecto'),
                                TextInput::make('latitude')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->helperText('Coordenada de latitud'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('longitude')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->helperText('Coordenada de longitud'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Especificaciones Técnicas')
                    ->schema([
                        RichEditor::make('technical_specifications')
                            ->label('Especificaciones Técnicas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Especificaciones técnicas detalladas'),
                        RichEditor::make('environmental_impact')
                            ->label('Impacto Ambiental')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Evaluación del impacto ambiental'),
                        RichEditor::make('regulatory_compliance')
                            ->label('Cumplimiento Regulatorio')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Requisitos regulatorios y cumplimiento'),
                        RichEditor::make('safety_measures')
                            ->label('Medidas de Seguridad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Medidas de seguridad implementadas'),
                    ])
                    ->collapsible(),

                Section::make('Equipo y Stakeholders')
                    ->schema([
                        RichEditor::make('project_team')
                            ->label('Equipo del Proyecto')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Miembros del equipo del proyecto'),
                        RichEditor::make('stakeholders')
                            ->label('Stakeholders')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Partes interesadas en el proyecto'),
                        RichEditor::make('contractors')
                            ->label('Contratistas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Contratistas involucrados'),
                        RichEditor::make('suppliers')
                            ->label('Proveedores')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Proveedores de materiales y servicios'),
                    ])
                    ->collapsible(),

                Section::make('Gestión del Proyecto')
                    ->schema([
                        RichEditor::make('milestones')
                            ->label('Hitos del Proyecto')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Hitos importantes del proyecto'),
                        RichEditor::make('risks')
                            ->label('Riesgos Identificados')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Riesgos identificados en el proyecto'),
                        RichEditor::make('mitigation_strategies')
                            ->label('Estrategias de Mitigación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Estrategias para mitigar riesgos'),
                        RichEditor::make('quality_standards')
                            ->label('Estándares de Calidad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Estándares de calidad aplicados'),
                    ])
                    ->collapsible(),

                Section::make('Documentación')
                    ->schema([
                        RichEditor::make('documentation')
                            ->label('Documentación del Proyecto')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Documentación técnica y administrativa'),
                    ])
                    ->collapsible(),

                Section::make('Gestión y Aprobación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('project_manager')
                                    ->label('Gerente del Proyecto')
                                    ->maxLength(255)
                                    ->helperText('Persona responsable del proyecto'),
                                TextInput::make('created_by')
                                    ->label('Creado por')
                                    ->numeric()
                                    ->required()
                                    ->helperText('ID del usuario que creó'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('approved_by')
                                    ->label('Aprobado por')
                                    ->numeric()
                                    ->helperText('ID del usuario que aprobó'),
                                DatePicker::make('approved_at')
                                    ->label('Fecha de Aprobación')
                                    ->helperText('Cuándo fue aprobado'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('project_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'solar_farm',
                        'success' => 'wind_farm',
                        'warning' => 'hydroelectric',
                        'danger' => 'biomass',
                        'info' => 'geothermal',
                        'secondary' => 'hybrid',
                        'purple' => 'storage',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => ProductionProject::getProjectTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'planning',
                        'success' => 'approved',
                        'warning' => 'in_progress',
                        'info' => 'on_hold',
                        'primary' => 'completed',
                        'danger' => 'cancelled',
                        'secondary' => 'maintenance',
                    ])
                    ->formatStateUsing(fn (string $state): string => ProductionProject::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'gray' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ])
                    ->formatStateUsing(fn (string $state): string => ProductionProject::getPriorities()[$state] ?? $state),
                TextColumn::make('planned_capacity_mw')
                    ->label('Capacidad Planificada (MW)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' MW')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' MW'),
                    ]),
                TextColumn::make('actual_capacity_mw')
                    ->label('Capacidad Real (MW)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' MW')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('efficiency_rating')
                    ->label('Eficiencia (%)')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('budget')
                    ->label('Presupuesto Total')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('spent_amount')
                    ->label('Gastado')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('start_date')
                    ->label('Fecha de Inicio')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('expected_completion_date')
                    ->label('Finalización Esperada')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('location_address')
                    ->label('Ubicación')
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
                SelectFilter::make('project_type')
                    ->label('Tipo de Proyecto')
                    ->options(ProductionProject::getProjectTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ProductionProject::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(ProductionProject::getPriorities())
                    ->multiple(),
                Filter::make('high_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('planned_capacity_mw', '>=', 100))
                    ->label('Alta Capacidad (≥100 MW)'),
                Filter::make('low_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('planned_capacity_mw', '<', 10))
                    ->label('Baja Capacidad (<10 MW)'),
                Filter::make('high_budget')
                    ->query(fn (Builder $query): Builder => $query->where('budget', '>=', 1000000))
                    ->label('Alto Presupuesto (≥$1M)'),
                Filter::make('low_budget')
                    ->query(fn (Builder $query): Builder => $query->where('budget', '<', 100000))
                    ->label('Bajo Presupuesto (<$100K)'),
                Filter::make('over_budget')
                    ->query(fn (Builder $query): Builder => $query->where('spent_amount', '>', 'budget'))
                    ->label('Sobre Presupuesto'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_from')
                            ->label('Inicio desde'),
                        DatePicker::make('start_until')
                            ->label('Inicio hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn (Builder $query, $date): Builder => $query->where('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn (Builder $query, $date): Builder => $query->where('start_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Inicio'),
                Filter::make('capacity_range')
                    ->form([
                        TextInput::make('min_capacity')
                            ->label('Capacidad mínima (MW)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_capacity')
                            ->label('Capacidad máxima (MW)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('planned_capacity_mw', '>=', $capacity),
                            )
                            ->when(
                                $data['max_capacity'],
                                fn (Builder $query, $capacity): Builder => $query->where('planned_capacity_mw', '<=', $capacity),
                            );
                    })
                    ->label('Rango de Capacidad'),
                Filter::make('budget_range')
                    ->form([
                        TextInput::make('min_budget')
                            ->label('Presupuesto mínimo')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_budget')
                            ->label('Presupuesto máximo')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_budget'],
                                fn (Builder $query, $budget): Builder => $query->where('budget', '>=', $budget),
                            )
                            ->when(
                                $data['max_budget'],
                                fn (Builder $query, $budget): Builder => $query->where('budget', '<=', $budget),
                            );
                    })
                    ->label('Rango de Presupuesto'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('mark_approved')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ProductionProject $record) => $record->status === 'planning')
                    ->action(function (ProductionProject $record) {
                        $record->update(['status' => 'approved']);
                    }),
                Tables\Actions\Action::make('start_project')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (ProductionProject $record) => $record->status === 'approved')
                    ->action(function (ProductionProject $record) {
                        $record->update(['status' => 'in_progress']);
                    }),
                Tables\Actions\Action::make('complete_project')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ProductionProject $record) => $record->status === 'in_progress')
                    ->action(function (ProductionProject $record) {
                        $record->update(['status' => 'completed']);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (ProductionProject $record) {
                        $newRecord = $record->replicate();
                        $newRecord->project_number = $newRecord->project_number . '-COPY';
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->status = 'planning';
                        $newRecord->spent_amount = 0;
                        $newRecord->actual_capacity_mw = 0;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_all')
                        ->label('Aprobar Todos')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'planning') {
                                    $record->update(['status' => 'approved']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('start_all')
                        ->label('Iniciar Todos')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'approved') {
                                    $record->update(['status' => 'in_progress']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(ProductionProject::getStatuses())
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
                                ->options(ProductionProject::getPriorities())
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
            'index' => Pages\ListProductionProjects::route('/'),
            'create' => Pages\CreateProductionProject::route('/create'),
            'edit' => Pages\EditProductionProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $planningCount = static::getModel()::where('status', 'planning')->count();
        
        if ($planningCount > 0) {
            return 'warning';
        }
        
        $inProgressCount = static::getModel()::where('status', 'in_progress')->count();
        
        if ($inProgressCount > 0) {
            return 'info';
        }
        
        return 'success';
    }
}
