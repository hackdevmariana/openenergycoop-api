<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DashboardWidgetResource\Pages;
use App\Models\DashboardWidget;
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

class DashboardWidgetResource extends Resource
{
    protected static ?string $model = DashboardWidget::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Dashboard & Widgets';

    protected static ?string $navigationLabel = 'Widgets del Dashboard';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Widget del Dashboard';

    protected static ?string $pluralModelLabel = 'Widgets del Dashboard';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('dashboard_view_id')
                                    ->label('Vista del Dashboard')
                                    ->relationship('dashboardView', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Selecciona una vista (opcional)'),

                                TextInput::make('title')
                                    ->label('Título')
                                    ->maxLength(255)
                                    ->placeholder('Ej: Producción Solar')
                                    ->helperText('Título opcional del widget'),

                                Select::make('type')
                                    ->label('Tipo de Widget')
                                    ->required()
                                    ->options([
                                        DashboardWidget::TYPE_WALLET => 'Cartera',
                                        DashboardWidget::TYPE_ENERGY_PRODUCTION => 'Producción de Energía',
                                        DashboardWidget::TYPE_ENERGY_CONSUMPTION => 'Consumo de Energía',
                                        DashboardWidget::TYPE_DEVICES => 'Dispositivos',
                                        DashboardWidget::TYPE_METRICS => 'Métricas',
                                        DashboardWidget::TYPE_EVENTS => 'Eventos',
                                        DashboardWidget::TYPE_NOTIFICATIONS => 'Notificaciones',
                                        DashboardWidget::TYPE_CHARTS => 'Gráficos',
                                        DashboardWidget::TYPE_CALENDAR => 'Calendario',
                                        DashboardWidget::TYPE_WEATHER => 'Clima',
                                        DashboardWidget::TYPE_NEWS => 'Noticias',
                                        DashboardWidget::TYPE_SOCIAL => 'Social',
                                        DashboardWidget::TYPE_ANALYTICS => 'Analíticas',
                                        DashboardWidget::TYPE_MAINTENANCE => 'Mantenimiento',
                                        DashboardWidget::TYPE_BILLING => 'Facturación',
                                        DashboardWidget::TYPE_SUPPORT => 'Soporte'
                                    ])
                                    ->searchable()
                                    ->reactive(),

                                TextInput::make('position')
                                    ->label('Posición')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->helperText('Orden de aparición en la vista'),

                                Select::make('size')
                                    ->label('Tamaño')
                                    ->options([
                                        DashboardWidget::SIZE_SMALL => 'Pequeño (1x1)',
                                        DashboardWidget::SIZE_MEDIUM => 'Mediano (2x2)',
                                        DashboardWidget::SIZE_LARGE => 'Grande (3x3)',
                                        DashboardWidget::SIZE_XLARGE => 'Extra Grande (4x4)'
                                    ])
                                    ->default(DashboardWidget::SIZE_MEDIUM)
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración de Visualización')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('visible')
                                    ->label('Visible')
                                    ->default(true)
                                    ->helperText('Si el widget se muestra en el dashboard'),

                                Toggle::make('collapsible')
                                    ->label('Colapsable')
                                    ->default(false)
                                    ->helperText('Permite colapsar/expandir el widget'),

                                Toggle::make('collapsed')
                                    ->label('Colapsado')
                                    ->default(false)
                                    ->helperText('Estado inicial del widget')
                                    ->visible(fn (callable $get) => $get('collapsible')),

                                TextInput::make('refresh_interval')
                                    ->label('Intervalo de Actualización (segundos)')
                                    ->numeric()
                                    ->minValue(30)
                                    ->maxValue(3600)
                                    ->helperText('Cada cuánto se actualiza el widget'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Configuración del Widget')
                    ->schema([
                        KeyValue::make('settings_json')
                            ->label('Configuración Específica')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->helperText('Configuración específica según el tipo de widget'),

                        KeyValue::make('filters')
                            ->label('Filtros')
                            ->keyLabel('Filtro')
                            ->valueLabel('Valor')
                            ->helperText('Filtros aplicados a los datos del widget'),

                        KeyValue::make('permissions')
                            ->label('Permisos')
                            ->keyLabel('Permiso')
                            ->valueLabel('Habilitado')
                            ->helperText('Permisos específicos del widget'),

                        KeyValue::make('data_source')
                            ->label('Fuente de Datos')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->helperText('Configuración de la fuente de datos'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Posicionamiento en Grid')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('grid_position.x')
                                    ->label('Posición X')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Posición horizontal en el grid'),

                                TextInput::make('grid_position.y')
                                    ->label('Posición Y')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Posición vertical en el grid'),

                                TextInput::make('grid_position.width')
                                    ->label('Ancho')
                                    ->numeric()
                                    ->default(2)
                                    ->minValue(1)
                                    ->maxValue(12)
                                    ->helperText('Ancho del widget en columnas'),

                                TextInput::make('grid_position.height')
                                    ->label('Alto')
                                    ->numeric()
                                    ->default(2)
                                    ->minValue(1)
                                    ->maxValue(12)
                                    ->helperText('Alto del widget en filas'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Configuración Avanzada')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('auto_refresh')
                                    ->label('Actualización Automática')
                                    ->default(true)
                                    ->helperText('Actualiza automáticamente los datos'),

                                Toggle::make('show_loading')
                                    ->label('Mostrar Indicador de Carga')
                                    ->default(true)
                                    ->helperText('Muestra spinner durante la carga'),

                                Toggle::make('error_handling')
                                    ->label('Manejo de Errores')
                                    ->default(true)
                                    ->helperText('Maneja errores de datos graciosamente'),

                                Toggle::make('debug_mode')
                                    ->label('Modo Debug')
                                    ->default(false)
                                    ->helperText('Muestra información de debug'),
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
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('Sin título'),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dashboardView.name')
                    ->label('Vista')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin vista'),

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => DashboardWidget::TYPE_WALLET,
                        'success' => DashboardWidget::TYPE_ENERGY_PRODUCTION,
                        'warning' => DashboardWidget::TYPE_ENERGY_CONSUMPTION,
                        'info' => DashboardWidget::TYPE_DEVICES,
                        'danger' => DashboardWidget::TYPE_METRICS,
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        DashboardWidget::TYPE_WALLET => 'Cartera',
                        DashboardWidget::TYPE_ENERGY_PRODUCTION => 'Producción',
                        DashboardWidget::TYPE_ENERGY_CONSUMPTION => 'Consumo',
                        DashboardWidget::TYPE_DEVICES => 'Dispositivos',
                        DashboardWidget::TYPE_METRICS => 'Métricas',
                        DashboardWidget::TYPE_EVENTS => 'Eventos',
                        DashboardWidget::TYPE_NOTIFICATIONS => 'Notificaciones',
                        DashboardWidget::TYPE_CHARTS => 'Gráficos',
                        DashboardWidget::TYPE_CALENDAR => 'Calendario',
                        DashboardWidget::TYPE_WEATHER => 'Clima',
                        DashboardWidget::TYPE_NEWS => 'Noticias',
                        DashboardWidget::TYPE_SOCIAL => 'Social',
                        DashboardWidget::TYPE_ANALYTICS => 'Analíticas',
                        DashboardWidget::TYPE_MAINTENANCE => 'Mantenimiento',
                        DashboardWidget::TYPE_BILLING => 'Facturación',
                        DashboardWidget::TYPE_SUPPORT => 'Soporte',
                        default => $state
                    }),

                BadgeColumn::make('size')
                    ->label('Tamaño')
                    ->colors([
                        'primary' => DashboardWidget::SIZE_SMALL,
                        'success' => DashboardWidget::SIZE_MEDIUM,
                        'warning' => DashboardWidget::SIZE_LARGE,
                        'danger' => DashboardWidget::SIZE_XLARGE,
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        DashboardWidget::SIZE_SMALL => '1x1',
                        DashboardWidget::SIZE_MEDIUM => '2x2',
                        DashboardWidget::SIZE_LARGE => '3x3',
                        DashboardWidget::SIZE_XLARGE => '4x4',
                        default => $state
                    }),

                TextColumn::make('position')
                    ->label('Posición')
                    ->sortable()
                    ->badge(),

                ToggleColumn::make('visible')
                    ->label('Visible')
                    ->sortable(),

                ToggleColumn::make('collapsible')
                    ->label('Colapsable')
                    ->sortable(),

                TextColumn::make('last_refresh')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->diffForHumans() : 'Nunca')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        DashboardWidget::TYPE_WALLET => 'Cartera',
                        DashboardWidget::TYPE_ENERGY_PRODUCTION => 'Producción de Energía',
                        DashboardWidget::TYPE_ENERGY_CONSUMPTION => 'Consumo de Energía',
                        DashboardWidget::TYPE_DEVICES => 'Dispositivos',
                        DashboardWidget::TYPE_METRICS => 'Métricas',
                        DashboardWidget::TYPE_EVENTS => 'Eventos',
                        DashboardWidget::TYPE_NOTIFICATIONS => 'Notificaciones',
                        DashboardWidget::TYPE_CHARTS => 'Gráficos',
                        DashboardWidget::TYPE_CALENDAR => 'Calendario',
                        DashboardWidget::TYPE_WEATHER => 'Clima',
                        DashboardWidget::TYPE_NEWS => 'Noticias',
                        DashboardWidget::TYPE_SOCIAL => 'Social',
                        DashboardWidget::TYPE_ANALYTICS => 'Analíticas',
                        DashboardWidget::TYPE_MAINTENANCE => 'Mantenimiento',
                        DashboardWidget::TYPE_BILLING => 'Facturación',
                        DashboardWidget::TYPE_SUPPORT => 'Soporte'
                    ]),

                SelectFilter::make('size')
                    ->label('Tamaño')
                    ->options([
                        DashboardWidget::SIZE_SMALL => 'Pequeño (1x1)',
                        DashboardWidget::SIZE_MEDIUM => 'Mediano (2x2)',
                        DashboardWidget::SIZE_LARGE => 'Grande (3x3)',
                        DashboardWidget::SIZE_XLARGE => 'Extra Grande (4x4)'
                    ]),

                Filter::make('visible_widgets')
                    ->label('Solo Widgets Visibles')
                    ->query(fn (Builder $query): Builder => $query->where('visible', true))
                    ->toggle(),

                Filter::make('collapsible_widgets')
                    ->label('Solo Widgets Colapsables')
                    ->query(fn (Builder $query): Builder => $query->where('collapsible', true))
                    ->toggle(),

                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('dashboard_view_id')
                    ->label('Vista del Dashboard')
                    ->relationship('dashboardView', 'name')
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

                Action::make('show')
                    ->label('Mostrar')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(fn (DashboardWidget $record) => $record->show())
                    ->visible(fn (DashboardWidget $record) => !$record->visible),

                Action::make('hide')
                    ->label('Ocultar')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(fn (DashboardWidget $record) => $record->hide())
                    ->visible(fn (DashboardWidget $record) => $record->visible),

                Action::make('refresh')
                    ->label('Actualizar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(fn (DashboardWidget $record) => $record->refresh())
                    ->visible(fn (DashboardWidget $record) => $record->visible),

                Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(fn (DashboardWidget $record) => $record->duplicate())
                    ->modalHeading('Duplicar Widget')
                    ->modalDescription('¿Estás seguro de que quieres duplicar este widget? Se creará una copia con "(Copia)" en el título.')
                    ->modalSubmitActionLabel('Sí, duplicar'),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('show_selected')
                        ->label('Mostrar Seleccionados')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->show();
                        }),

                    BulkAction::make('hide_selected')
                        ->label('Ocultar Seleccionados')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->hide();
                        }),

                    BulkAction::make('refresh_selected')
                        ->label('Actualizar Seleccionados')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(function ($records) {
                            $records->each->refresh();
                        }),

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
            ->defaultSort('position', 'asc')
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
            'index' => Pages\ListDashboardWidgets::route('/'),
            'create' => Pages\CreateDashboardWidget::route('/create'),
            'edit' => Pages\EditDashboardWidget::route('/{record}/edit'),
            'view' => Pages\ViewDashboardWidget::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('visible', true)->count() > 0 ? 'success' : 'warning';
    }
}
