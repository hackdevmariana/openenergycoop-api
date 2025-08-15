<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    protected static ?string $navigationLabel = 'Categorías';
    
    protected static ?string $modelLabel = 'Categoría';
    
    protected static ?string $pluralModelLabel = 'Categorías';
    
    protected static ?string $navigationGroup = 'Contenidos';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                $context === 'create' ? $set('slug', \Str::slug($state)) : null),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('URL Amigable')
                            ->required()
                            ->maxLength(255)
                            ->unique(Category::class, 'slug', ignoreRecord: true)
                            ->rules(['regex:/^[a-z0-9\-]+$/']),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Section::make('Configuración Visual')
                    ->schema([
                        Forms\Components\ColorPicker::make('color')
                            ->label('Color')
                            ->helperText('Color representativo de la categoría'),
                            
                        Forms\Components\TextInput::make('icon')
                            ->label('Icono')
                            ->placeholder('heroicon-o-folder, fa-folder, etc.')
                            ->helperText('Clase CSS del icono (Heroicons, FontAwesome, etc.)'),
                    ])->columns(2),
                    
                Section::make('Jerarquía y Organización')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Categoría Padre')
                            ->relationship('parent', 'name', fn (Builder $query) => $query->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Sin categoría padre (nivel superior)')
                            ->helperText('Selecciona una categoría padre para crear una jerarquía'),
                            
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición (menor número = mayor prioridad)'),
                            
                        Forms\Components\Select::make('category_type')
                            ->label('Tipo de Categoría')
                            ->options(Category::CATEGORY_TYPES)
                            ->default('article')
                            ->required()
                            ->helperText('Define para qué tipo de contenido se usará esta categoría'),
                    ])->columns(3),
                    
                Section::make('Estado y Configuración')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->helperText('Solo las categorías activas aparecen en el frontend')
                            ->default(true),
                            
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Global (todas las organizaciones)'),
                            
                        Forms\Components\Select::make('language')
                            ->label('Idioma')
                            ->options([
                                'es' => 'Español',
                                'en' => 'English',
                                'ca' => 'Català',
                                'eu' => 'Euskera',
                                'gl' => 'Galego',
                            ])
                            ->default('es')
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('Color')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->formatStateUsing(function ($record, $state) {
                        $depth = $record->getDepth();
                        $prefix = str_repeat('— ', $depth);
                        return $prefix . $state;
                    }),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->searchable()
                    ->copyable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\BadgeColumn::make('category_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'article',
                        'success' => 'document',
                        'warning' => 'media',
                        'danger' => 'faq',
                        'secondary' => 'event',
                    ])
                    ->formatStateUsing(fn ($state) => Category::CATEGORY_TYPES[$state] ?? $state),
                    
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoría Padre')
                    ->searchable()
                    ->placeholder('Raíz')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('articles_count')
                    ->label('Artículos')
                    ->counts('articles')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('children_count')
                    ->label('Subcategorías')
                    ->counts('children')
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->placeholder('Global')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => 'ca',
                        'danger' => 'eu',
                        'secondary' => 'gl',
                    ]),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_type')
                    ->label('Tipo')
                    ->options(Category::CATEGORY_TYPES),
                    
                SelectFilter::make('parent_id')
                    ->label('Categoría Padre')
                    ->relationship('parent', 'name')
                    ->placeholder('Todas las categorías'),
                    
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
                    
                SelectFilter::make('language')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Español',
                        'en' => 'English',
                        'ca' => 'Català',
                        'eu' => 'Euskera',
                        'gl' => 'Galego',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Desactivar' : 'Activar')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionadas'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionadas')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar seleccionadas')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}