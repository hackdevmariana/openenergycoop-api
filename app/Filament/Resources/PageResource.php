<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers;
use App\Models\Page;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Páginas';
    
    protected static ?string $modelLabel = 'Página';
    
    protected static ?string $pluralModelLabel = 'Páginas';
    
    protected static ?string $navigationGroup = 'Comunicaciones y Contenido';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información Básica')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Contenido Principal')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                                $context === 'create' ? $set('slug', \Str::slug($state)) : null),
                                        
                                        Forms\Components\TextInput::make('slug')
                                            ->label('URL Amigable')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(Page::class, 'slug', ignoreRecord: true)
                                            ->rules(['regex:/^[a-z0-9\-]+$/']),
                                            
                                        Forms\Components\TextInput::make('route')
                                            ->label('Ruta Personalizada')
                                            ->maxLength(255)
                                            ->placeholder('Ej: /mi-pagina-especial')
                                            ->helperText('Opcional. Si no se especifica, se usa el slug.'),
                                    ])->columns(2),
                                    
                                Section::make('Configuración')
                                    ->schema([
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
                                            
                                        Forms\Components\Select::make('template')
                                            ->label('Plantilla')
                                            ->options([
                                                'default' => 'Predeterminada',
                                                'landing' => 'Página de Aterrizaje',
                                                'full-width' => 'Ancho Completo',
                                                'sidebar-left' => 'Barra Lateral Izquierda',
                                                'sidebar-right' => 'Barra Lateral Derecha',
                                                'minimal' => 'Minimalista',
                                            ])
                                            ->default('default')
                                            ->required(),
                                    ])->columns(3),
                            ]),
                            
                        Tabs\Tab::make('Jerarquía')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Section::make('Estructura de Páginas')
                                    ->schema([
                                        Forms\Components\Select::make('parent_id')
                                            ->label('Página Padre')
                                            ->relationship('parent', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Sin página padre (nivel superior)'),
                                            
                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Orden')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Orden de aparición (menor número = mayor prioridad)'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Publicación')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Section::make('Estado de Publicación')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_draft')
                                            ->label('Borrador')
                                            ->helperText('Si está activado, la página no será visible públicamente')
                                            ->default(true),
                                            
                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label('Fecha de Publicación')
                                            ->helperText('Opcional. Si se especifica, la página se publicará automáticamente en esta fecha'),
                                    ])->columns(2),
                                    
                                Section::make('Control de Acceso')
                                    ->schema([
                                        Forms\Components\Toggle::make('requires_auth')
                                            ->label('Requiere Autenticación')
                                            ->helperText('Si está activado, solo usuarios autenticados pueden ver esta página'),
                                            
                                        Forms\Components\TagsInput::make('allowed_roles')
                                            ->label('Roles Permitidos')
                                            ->placeholder('Escriba los roles y presione Enter')
                                            ->helperText('Opcional. Si se especifica, solo usuarios con estos roles pueden ver la página'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('SEO y Metadatos')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('Optimización para Buscadores')
                                    ->schema([
                                        Forms\Components\KeyValue::make('meta_data')
                                            ->label('Metadatos Personalizados')
                                            ->keyLabel('Clave')
                                            ->valueLabel('Valor')
                                            ->reorderable()
                                            ->addActionLabel('Añadir metadato'),
                                            
                                        Forms\Components\TagsInput::make('search_keywords')
                                            ->label('Palabras Clave')
                                            ->placeholder('Escriba palabras clave y presione Enter')
                                            ->helperText('Palabras clave para mejorar la búsqueda interna'),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Configuración Avanzada')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Performance y Cache')
                                    ->schema([
                                        Forms\Components\TextInput::make('cache_duration')
                                            ->label('Duración del Cache (minutos)')
                                            ->numeric()
                                            ->default(60)
                                            ->minValue(0)
                                            ->helperText('Tiempo en minutos que se mantendrá en cache. 0 = sin cache'),
                                    ]),
                                    
                                Section::make('Accesibilidad')
                                    ->schema([
                                        Forms\Components\Select::make('reading_level')
                                            ->label('Nivel de Lectura')
                                            ->options([
                                                'basic' => 'Básico',
                                                'intermediate' => 'Intermedio',
                                                'advanced' => 'Avanzado',
                                                'expert' => 'Experto',
                                            ])
                                            ->placeholder('No especificado'),
                                            
                                        Forms\Components\Textarea::make('accessibility_notes')
                                            ->label('Notas de Accesibilidad')
                                            ->rows(3)
                                            ->helperText('Información sobre características de accesibilidad de esta página'),
                                    ])->columns(2),
                                    
                                Section::make('Notas Internas')
                                    ->schema([
                                        Forms\Components\Textarea::make('internal_notes')
                                            ->label('Notas Internas')
                                            ->rows(3)
                                            ->helperText('Notas visibles solo para administradores'),
                                            
                                        Forms\Components\DateTimePicker::make('last_reviewed_at')
                                            ->label('Última Revisión')
                                            ->helperText('Fecha de la última revisión de contenido'),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->searchable()
                    ->copyable()
                    ->prefix('/')
                    ->color('gray'),
                    
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => 'ca',
                        'danger' => 'eu',
                        'secondary' => 'gl',
                    ]),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Global')
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_draft')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseColor('success'),
                    
                Tables\Columns\TextColumn::make('template')
                    ->label('Plantilla')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('parent.title')
                    ->label('Página Padre')
                    ->searchable()
                    ->placeholder('Raíz')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('requires_auth')
                    ->label('Auth')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-globe-alt')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn ($record) => $record->requires_auth ? 'Requiere autenticación' : 'Acceso público'),
                    
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No programado')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                    
                TernaryFilter::make('is_draft')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Borradores')
                    ->falseLabel('Publicados'),
                    
                SelectFilter::make('template')
                    ->label('Plantilla')
                    ->options([
                        'default' => 'Predeterminada',
                        'landing' => 'Página de Aterrizaje',
                        'full-width' => 'Ancho Completo',
                        'sidebar-left' => 'Barra Lateral Izquierda',
                        'sidebar-right' => 'Barra Lateral Derecha',
                        'minimal' => 'Minimalista',
                    ]),
                    
                TernaryFilter::make('requires_auth')
                    ->label('Acceso')
                    ->placeholder('Todos')
                    ->trueLabel('Requiere autenticación')
                    ->falseLabel('Acceso público'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Page $record) {
                        $duplicate = $record->replicate();
                        $duplicate->title = $record->title . ' (Copia)';
                        $duplicate->slug = $record->slug . '-copia-' . time();
                        $duplicate->is_draft = true;
                        $duplicate->published_at = null;
                        $duplicate->save();
                        
                        return redirect()->route('filament.admin.resources.pages.edit', $duplicate);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publicar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_draft' => false]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('draft')
                        ->label('Convertir en borradores')
                        ->icon('heroicon-o-pencil')
                        ->action(fn ($records) => $records->each->update(['is_draft' => true]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::published()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
