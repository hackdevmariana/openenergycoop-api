<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    
    protected static ?string $navigationLabel = 'Menús';
    
    protected static ?string $modelLabel = 'Elemento de Menú';
    
    protected static ?string $pluralModelLabel = 'Elementos de Menú';
    
    protected static ?string $navigationGroup = 'Contenidos';
    
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('text')
                            ->label('Texto del Menú')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('icon')
                            ->label('Icono')
                            ->placeholder('heroicon-o-home, fa-home, etc.')
                            ->helperText('Clase CSS del icono'),
                    ])->columns(2),
                    
                Section::make('Enlaces')
                    ->schema([
                        Forms\Components\TextInput::make('internal_link')
                            ->label('Enlace Interno')
                            ->placeholder('/pagina, /articulos/mi-articulo')
                            ->helperText('Ruta interna del sitio'),
                            
                        Forms\Components\TextInput::make('external_link')
                            ->label('Enlace Externo')
                            ->url()
                            ->placeholder('https://ejemplo.com')
                            ->helperText('URL externa completa'),
                            
                        Forms\Components\Toggle::make('target_blank')
                            ->label('Abrir en Nueva Pestaña')
                            ->default(false),
                    ])->columns(3),
                    
                Section::make('Jerarquía')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Elemento Padre')
                            ->relationship('parent', 'text')
                            ->searchable()
                            ->placeholder('Sin elemento padre (nivel superior)'),
                            
                        Forms\Components\TextInput::make('order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición'),
                            
                        Forms\Components\TextInput::make('menu_group')
                            ->label('Grupo de Menú')
                            ->placeholder('main, footer, sidebar')
                            ->helperText('Agrupa elementos de menú por ubicación'),
                    ])->columns(3),
                    
                Section::make('Configuración Avanzada')
                    ->schema([
                        Forms\Components\TextInput::make('permission')
                            ->label('Permiso Requerido')
                            ->placeholder('view_admin, edit_content')
                            ->helperText('Permiso necesario para ver este elemento'),
                            
                        Forms\Components\TextInput::make('css_classes')
                            ->label('Clases CSS')
                            ->placeholder('btn btn-primary, nav-item')
                            ->helperText('Clases CSS adicionales'),
                            
                        Forms\Components\KeyValue::make('visibility_rules')
                            ->label('Reglas de Visibilidad')
                            ->keyLabel('Condición')
                            ->valueLabel('Valor')
                            ->helperText('Condiciones para mostrar el elemento'),
                    ])->columns(3),
                    
                Section::make('Badge')
                    ->schema([
                        Forms\Components\TextInput::make('badge_text')
                            ->label('Texto del Badge')
                            ->maxLength(20)
                            ->placeholder('Nuevo, Beta'),
                            
                        Forms\Components\ColorPicker::make('badge_color')
                            ->label('Color del Badge')
                            ->default('#3B82F6'),
                    ])->columns(2),
                    
                Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                            
                        Forms\Components\Toggle::make('is_draft')
                            ->label('Borrador')
                            ->default(false),
                            
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Fecha de Publicación'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('text')
                    ->label('Texto')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->formatStateUsing(function ($record, $state) {
                        $depth = 0;
                        $parent = $record->parent;
                        while ($parent) {
                            $depth++;
                            $parent = $parent->parent;
                        }
                        $prefix = str_repeat('— ', $depth);
                        return $prefix . $state;
                    }),
                    
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icono')
                    ->formatStateUsing(fn ($state) => $state ? "🎨 {$state}" : '')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('internal_link')
                    ->label('Enlace Interno')
                    ->copyable()
                    ->placeholder('—')
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('external_link')
                    ->label('Enlace Externo')
                    ->copyable()
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('menu_group')
                    ->label('Grupo')
                    ->badge()
                    ->placeholder('default'),
                    
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('badge_text')
                    ->label('Badge')
                    ->badge()
                    ->color(fn ($record) => $record->badge_color ?? 'primary')
                    ->placeholder('—')
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('menu_group')
                    ->label('Grupo de Menú')
                    ->options([
                        'main' => 'Principal',
                        'footer' => 'Pie de Página',
                        'sidebar' => 'Barra Lateral',
                        'mobile' => 'Móvil',
                    ]),
                    
                SelectFilter::make('parent_id')
                    ->label('Elemento Padre')
                    ->relationship('parent', 'text')
                    ->placeholder('Todos los elementos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}