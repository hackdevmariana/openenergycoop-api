<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DashboardViewResource\Pages;
use App\Models\DashboardView;
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
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;

class DashboardViewResource extends Resource
{
    protected static ?string $model = DashboardView::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Dashboard & Widgets';

    protected static ?string $navigationLabel = 'Vistas del Dashboard';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Vista del Dashboard';

    protected static ?string $pluralModelLabel = 'Vistas del Dashboard';

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
                                    ->maxLength(255)
                                    ->placeholder('Ej: Mi Vista Solar')
                                    ->helperText('Nombre opcional para identificar la vista'),

                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Toggle::make('is_default')
                                    ->label('Vista por Defecto')
                                    ->helperText('Esta será la vista principal del usuario'),

                                Toggle::make('is_public')
                                    ->label('Vista Pública')
                                    ->helperText('Permite que otros usuarios vean esta vista'),

                                Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->placeholder('Descripción de la vista y su propósito'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Apariencia')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('theme')
                                    ->label('Tema')
                                    ->options([
                                        DashboardView::THEME_DEFAULT => 'Predeterminado',
                                        DashboardView::THEME_DARK => 'Oscuro',
                                        DashboardView::THEME_LIGHT => 'Claro',
                                        DashboardView::THEME_SOLAR => 'Solar',
                                        DashboardView::THEME_WIND => 'Eólico'
                                    ])
                                    ->default(DashboardView::THEME_DEFAULT)
                                    ->searchable(),

                                Select::make('color_scheme')
                                    ->label('Esquema de Colores')
                                    ->options([
                                        DashboardView::COLOR_SCHEME_LIGHT => 'Claro',
                                        DashboardView::COLOR_SCHEME_DARK => 'Oscuro',
                                        DashboardView::COLOR_SCHEME_AUTO => 'Automático'
                                    ])
                                    ->default(DashboardView::COLOR_SCHEME_LIGHT)
                                    ->searchable(),

                                ColorPicker::make('primary_color')
                                    ->label('Color Primario')
                                    ->helperText('Color principal de la vista'),

                                ColorPicker::make('secondary_color')
                                    ->label('Color Secundario')
                                    ->helperText('Color secundario de la vista'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Layout del Dashboard')
                    ->schema([
                        Repeater::make('layout_json')
                            ->label('Módulos del Layout')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('module')
                                            ->label('Módulo')
                                            ->options([
                                                'wallet' => 'Cartera',
                                                'energy_production' => 'Producción de Energía',
                                                'energy_consumption' => 'Consumo de Energía',
                                                'devices' => 'Dispositivos',
                                                'metrics' => 'Métricas',
                                                'events' => 'Eventos',
                                                'notifications' => 'Notificaciones',
                                                'charts' => 'Gráficos',
                                                'calendar' => 'Calendario',
                                                'weather' => 'Clima',
                                                'news' => 'Noticias',
                                                'social' => 'Social',
                                                'analytics' => 'Analíticas',
                                                'maintenance' => 'Mantenimiento',
                                                'billing' => 'Facturación',
                                                'support' => 'Soporte'
                                            ])
                                            ->required()
                                            ->searchable(),

                                        TextInput::make('position')
                                            ->label('Posición')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1)
                                            ->helperText('Orden de aparición'),

                                        Toggle::make('visible')
                                            ->label('Visible')
                                            ->default(true)
                                            ->helperText('Si el módulo se muestra'),
                                    ]),

                                KeyValue::make('settings')
                                    ->label('Configuración')
                                    ->keyLabel('Clave')
                                    ->valueLabel('Valor')
                                    ->helperText('Configuración específica del módulo'),
                            ])
                            ->defaultItems(5)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['module'] ?? null)
                            ->helperText('Configura los módulos y su orden en el dashboard'),

                        KeyValue::make('widget_settings')
                            ->label('Configuración Global de Widgets')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->helperText('Configuración aplicada a todos los widgets'),

                        KeyValue::make('access_permissions')
                            ->label('Permisos de Acceso')
                            ->keyLabel('Permiso')
                            ->valueLabel('Habilitado')
                            ->helperText('Permisos específicos para esta vista'),
                    ])
                    ->collapsible(),

                Section::make('Configuración Avanzada')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('refresh_interval')
                                    ->label('Intervalo de Actualización (segundos)')
                                    ->numeric()
                                    ->minValue(30)
                                    ->maxValue(3600)
                                    ->helperText('Cada cuánto se actualiza la vista'),

                                Toggle::make('auto_save')
                                    ->label('Guardado Automático')
                                    ->default(true)
                                    ->helperText('Guarda cambios automáticamente'),

                                Toggle::make('notifications_enabled')
                                    ->label('Notificaciones Habilitadas')
                                    ->default(true)
                                    ->helperText('Permite notificaciones en esta vista'),

                                Toggle::make('analytics_tracking')
                                    ->label('Seguimiento de Analíticas')
                                    ->default(false)
                                    ->helperText('Registra el uso de la vista'),
                            ]),
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
                    ->limit(30)
                    ->placeholder('Sin nombre'),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('theme')
                    ->label('Tema')
                    ->colors([
                        'primary' => DashboardView::THEME_DEFAULT,
                        'success' => DashboardView::THEME_SOLAR,
                        'warning' => DashboardView::THEME_WIND,
                        'info' => DashboardView::THEME_LIGHT,
                        'danger' => DashboardView::THEME_DARK,
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        DashboardView::THEME_DEFAULT => 'Predeterminado',
                        DashboardView::THEME_DARK => 'Oscuro',
                        DashboardView::THEME_LIGHT => 'Claro',
                        DashboardView::THEME_SOLAR => 'Solar',
                        DashboardView::THEME_WIND => 'Eólico',
                        default => $state
                    }),

                BadgeColumn::make('color_scheme')
                    ->label('Colores')
                    ->colors([
                        'primary' => DashboardView::COLOR_SCHEME_LIGHT,
                        'success' => DashboardView::COLOR_SCHEME_DARK,
                        'info' => DashboardView::COLOR_SCHEME_AUTO,
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        DashboardView::COLOR_SCHEME_LIGHT => 'Claro',
                        DashboardView::COLOR_SCHEME_DARK => 'Oscuro',
                        DashboardView::COLOR_SCHEME_AUTO => 'Automático',
                        default => $state
                    }),

                TextColumn::make('widget_count')
                    ->label('Widgets')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 0),

                ToggleColumn::make('is_default')
                    ->label('Por Defecto')
                    ->sortable(),

                ToggleColumn::make('is_public')
                    ->label('Pública')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('theme')
                    ->label('Tema')
                    ->options([
                        DashboardView::THEME_DEFAULT => 'Predeterminado',
                        DashboardView::THEME_DARK => 'Oscuro',
                        DashboardView::THEME_LIGHT => 'Claro',
                        DashboardView::THEME_SOLAR => 'Solar',
                        DashboardView::THEME_WIND => 'Eólico'
                    ]),

                SelectFilter::make('color_scheme')
                    ->label('Esquema de Colores')
                    ->options([
                        DashboardView::COLOR_SCHEME_LIGHT => 'Claro',
                        DashboardView::COLOR_SCHEME_DARK => 'Oscuro',
                        DashboardView::COLOR_SCHEME_AUTO => 'Automático'
                    ]),

                Filter::make('default_views')
                    ->label('Solo Vistas por Defecto')
                    ->query(fn (Builder $query): Builder => $query->where('is_default', true))
                    ->toggle(),

                Filter::make('public_views')
                    ->label('Solo Vistas Públicas')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true))
                    ->toggle(),

                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),

                Action::make('set_as_default')
                    ->label('Establecer por Defecto')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (DashboardView $record) => $record->setAsDefault())
                    ->visible(fn (DashboardView $record) => !$record->is_default),

                Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(fn (DashboardView $record) => $record->duplicate())
                    ->modalHeading('Duplicar Vista')
                    ->modalDescription('¿Estás seguro de que quieres duplicar esta vista? Se creará una copia con "(Copia)" en el nombre.')
                    ->modalSubmitActionLabel('Sí, duplicar'),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('set_as_default')
                        ->label('Establecer por Defecto')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            // Solo el primero será por defecto
                            $records->first()->setAsDefault();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('duplicate_selected')
                        ->label('Duplicar Seleccionados')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->duplicate();
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
            'index' => Pages\ListDashboardViews::route('/'),
            'create' => Pages\CreateDashboardView::route('/create'),
            'edit' => Pages\EditDashboardView::route('/{record}/edit'),
            'view' => Pages\ViewDashboardView::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_default', true)->count() > 0 ? 'success' : 'warning';
    }
}
