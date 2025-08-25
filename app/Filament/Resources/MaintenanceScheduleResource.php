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
                                Select::make('schedule_type')
                                    ->label('Tipo de Programación')
                                    ->options(MaintenanceSchedule::getScheduleTypes())
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

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->required()
                                    ->helperText('Cuándo comienza el programa'),
                                DatePicker::make('end_date')
                                    ->label('Fecha de Fin')
                                    ->helperText('Cuándo termina el programa'),
                            ]),
                        TextInput::make('cron_expression')
                            ->label('Expresión Cron')
                            ->helperText('Expresión cron para programación automática (ej: 0 0 * * *)')
                            ->placeholder('0 0 * * *'),
                    ])
                    ->collapsible(),

                Section::make('Equipamiento y Ubicación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('equipment_id')
                                    ->label('ID del Equipamiento')
                                    ->numeric()
                                    ->helperText('ID del equipamiento a mantener'),
                                Select::make('equipment_type')
                                    ->label('Tipo de Equipamiento')
                                    ->options([
                                        'device' => 'Dispositivo',
                                        'installation' => 'Instalación',
                                        'meter' => 'Medidor',
                                        'panel' => 'Panel',
                                        'inverter' => 'Inversor',
                                        'battery' => 'Batería',
                                        'transformer' => 'Transformador',
                                        'cable' => 'Cable',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location_id')
                                    ->label('ID de la Ubicación')
                                    ->numeric()
                                    ->helperText('ID de la ubicación del equipamiento'),
                                Select::make('location_type')
                                    ->label('Tipo de Ubicación')
                                    ->options([
                                        'site' => 'Sitio',
                                        'building' => 'Edificio',
                                        'room' => 'Habitación',
                                        'area' => 'Área',
                                        'zone' => 'Zona',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Asignaciones y Equipos')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('assigned_team_id')
                                    ->label('ID del Equipo Asignado')
                                    ->numeric()
                                    ->helperText('ID del equipo responsable'),
                                TextInput::make('vendor_id')
                                    ->label('ID del Proveedor')
                                    ->numeric()
                                    ->helperText('ID del proveedor de servicios'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('task_template_id')
                                    ->label('ID de la Plantilla de Tareas')
                                    ->numeric()
                                    ->helperText('ID de la plantilla de tareas'),
                                TextInput::make('checklist_template_id')
                                    ->label('ID de la Plantilla de Checklist')
                                    ->numeric()
                                    ->helperText('ID de la plantilla de checklist'),
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
                            ]),
                        RichEditor::make('schedule_config')
                            ->label('Configuración de Programación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Configuración específica de la programación'),
                    ])
                    ->collapsible(),

                Section::make('Gestión y Aprobación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('created_by')
                                    ->label('Creado por')
                                    ->numeric()
                                    ->required()
                                    ->helperText('ID del usuario que creó'),
                                TextInput::make('approved_by')
                                    ->label('Aprobado por')
                                    ->numeric()
                                    ->helperText('ID del usuario que aprobó'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('approved_at')
                                    ->label('Fecha de Aprobación')
                                    ->helperText('Cuándo fue aprobado'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos')
                    ->schema([
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
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('schedule_type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'preventive',
                        'primary' => 'predictive',
                        'warning' => 'condition_based',
                        'info' => 'time_based',
                        'purple' => 'usage_based',
                        'cyan' => 'calendar_based',
                        'pink' => 'event_based',
                        'gray' => 'manual',
                    ])
                    ->formatStateUsing(fn (string $state): string => MaintenanceSchedule::getScheduleTypes()[$state] ?? $state),
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
                TextColumn::make('end_date')
                    ->label('Fecha de Fin')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('equipment_type')
                    ->label('Tipo Equipamiento')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location_type')
                    ->label('Tipo Ubicación')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('schedule_type')
                    ->label('Tipo de Programación')
                    ->options(MaintenanceSchedule::getScheduleTypes())
                    ->multiple(),
                SelectFilter::make('equipment_type')
                    ->label('Tipo de Equipamiento')
                    ->options([
                        'device' => 'Dispositivo',
                        'installation' => 'Instalación',
                        'meter' => 'Medidor',
                        'panel' => 'Panel',
                        'inverter' => 'Inversor',
                        'battery' => 'Batería',
                        'transformer' => 'Transformador',
                        'cable' => 'Cable',
                        'other' => 'Otro',
                    ])
                    ->multiple(),
                SelectFilter::make('location_type')
                    ->label('Tipo de Ubicación')
                    ->options([
                        'site' => 'Sitio',
                        'building' => 'Edificio',
                        'room' => 'Habitación',
                        'area' => 'Área',
                        'zone' => 'Zona',
                        'other' => 'Otro',
                    ])
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('inactive')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false))
                    ->label('Solo Inactivos'),
                Filter::make('approved')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('approved_at'))
                    ->label('Solo Aprobados'),
                Filter::make('pending_approval')
                    ->query(fn (Builder $query): Builder => $query->whereNull('approved_at'))
                    ->label('Pendientes de Aprobación'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_from')
                            ->label('Fecha de inicio desde'),
                        DatePicker::make('start_until')
                            ->label('Fecha de inicio hasta'),
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
                    ->visible(fn (MaintenanceSchedule $record) => !$record->is_active)
                    ->action(function (MaintenanceSchedule $record) {
                        $record->update(['is_active' => true]);
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (MaintenanceSchedule $record) => $record->is_active)
                    ->action(function (MaintenanceSchedule $record) {
                        $record->update(['is_active' => false]);
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (MaintenanceSchedule $record) => !$record->approved_at)
                    ->action(function (MaintenanceSchedule $record) {
                        $record->update([
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (MaintenanceSchedule $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->is_active = false;
                        $newRecord->approved_at = null;
                        $newRecord->approved_by = null;
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
                                $record->update(['is_active' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate_all')
                        ->label('Desactivar Todos')
                        ->icon('heroicon-o-pause')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_schedule_type')
                        ->label('Actualizar Tipo de Programación')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('schedule_type')
                                ->label('Tipo de Programación')
                                ->options(MaintenanceSchedule::getScheduleTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['schedule_type' => $data['schedule_type']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('start_date', 'asc')
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
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
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
        
        $pendingApprovalCount = static::getModel()::whereNull('approved_at')->count();
        
        if ($pendingApprovalCount > 0) {
            return 'danger';
        }
        
        return 'success';
    }
}

