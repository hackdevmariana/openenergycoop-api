<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    
    protected static ?string $navigationLabel = 'Artículos';
    
    protected static ?string $modelLabel = 'Artículo';
    
    protected static ?string $pluralModelLabel = 'Artículos';
    
    protected static ?string $navigationGroup = 'Contenidos';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Contenido Principal')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Section::make('Información Básica')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                                $context === 'create' ? $set('slug', \Str::slug($state)) : null),
                                        
                                        Forms\Components\TextInput::make('subtitle')
                                            ->label('Subtítulo')
                                            ->maxLength(255)
                                            ->placeholder('Subtítulo opcional para el artículo'),
                                            
                                        Forms\Components\TextInput::make('slug')
                                            ->label('URL Amigable')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(Article::class, 'slug', ignoreRecord: true)
                                            ->rules(['regex:/^[a-z0-9\-]+$/']),
                                    ])->columns(2),
                                    
                                Section::make('Contenido')
                                    ->schema([
                                        Forms\Components\RichEditor::make('text')
                                            ->label('Contenido del Artículo')
                                            ->required()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('articles/attachments')
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make('excerpt')
                                            ->label('Extracto')
                                            ->rows(4)
                                            ->maxLength(500)
                                            ->helperText('Resumen del artículo que aparecerá en listados y redes sociales')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Media y Clasificación')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Imagen Destacada')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('featured_image')
                                            ->label('Imagen Destacada')
                                            ->collection('featured_images')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            ->helperText('Imagen principal del artículo. Recomendado: 1200x630px'),
                                    ]),
                                    
                                Section::make('Clasificación')
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Categoría')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nombre de la Categoría')
                                                    ->required(),
                                                Forms\Components\TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Descripción'),
                                            ]),
                                            
                                        Forms\Components\Select::make('author_id')
                                            ->label('Autor')
                                            ->relationship('author', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(auth()->id()),
                                            
                                        Forms\Components\TagsInput::make('search_keywords')
                                            ->label('Palabras Clave')
                                            ->placeholder('Escriba palabras clave y presione Enter'),
                                    ])->columns(3),
                            ]),
                            
                        Tabs\Tab::make('Publicación')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Section::make('Estado del Artículo')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options(Article::STATUSES)
                                            ->default('draft')
                                            ->required(),
                                            
                                        Forms\Components\Toggle::make('is_draft')
                                            ->label('Borrador')
                                            ->helperText('Si está activado, el artículo no será visible públicamente')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('featured')
                                            ->label('Artículo Destacado')
                                            ->helperText('Los artículos destacados aparecen en posiciones prominentes'),
                                    ])->columns(3),
                                    
                                Section::make('Programación')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label('Fecha de Publicación')
                                            ->helperText('Opcional. Si se especifica, el artículo se publicará automáticamente en esta fecha'),
                                            
                                        Forms\Components\DateTimePicker::make('scheduled_at')
                                            ->label('Programado Para')
                                            ->helperText('Fecha y hora específica para la publicación automática'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Interacción')
                                    ->schema([
                                        Forms\Components\Toggle::make('comment_enabled')
                                            ->label('Permitir Comentarios')
                                            ->helperText('Los usuarios podrán comentar en este artículo')
                                            ->default(true),
                                            
                                        Forms\Components\TextInput::make('reading_time')
                                            ->label('Tiempo de Lectura (minutos)')
                                            ->numeric()
                                            ->helperText('Se calcula automáticamente si se deja vacío'),
                                    ])->columns(2),
                                    
                                Section::make('SEO')
                                    ->schema([
                                        Forms\Components\TextInput::make('seo_focus_keyword')
                                            ->label('Palabra Clave Principal')
                                            ->helperText('Palabra clave principal para SEO'),
                                            
                                        Forms\Components\Select::make('reading_level')
                                            ->label('Nivel de Lectura')
                                            ->options([
                                                'basic' => 'Básico',
                                                'intermediate' => 'Intermedio',
                                                'advanced' => 'Avanzado',
                                                'expert' => 'Experto',
                                            ])
                                            ->placeholder('No especificado'),
                                    ])->columns(2),
                                    
                                Section::make('Organización')
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
                                            
                                        Forms\Components\Textarea::make('accessibility_notes')
                                            ->label('Notas de Accesibilidad')
                                            ->rows(3)
                                            ->helperText('Información sobre características de accesibilidad'),
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
                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->label('Imagen')
                    ->collection('featured_images')
                    ->width(60)
                    ->height(60)
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Autor')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->placeholder('Sin categoría'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'review',
                        'success' => 'published',
                        'danger' => 'archived',
                    ])
                    ->formatStateUsing(fn ($state) => Article::STATUSES[$state] ?? $state),
                    
                Tables\Columns\IconColumn::make('featured')
                    ->label('Destacado')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('comment_enabled')
                    ->label('Comentarios')
                    ->boolean()
                    ->trueIcon('heroicon-o-chat-bubble-left-right')
                    ->falseIcon('heroicon-o-no-symbol')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('number_of_views')
                    ->label('Vistas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('reading_time')
                    ->label('Lectura')
                    ->suffix(' min')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No publicado'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('author_id')
                    ->label('Autor')
                    ->relationship('author', 'name')
                    ->placeholder('Todos los autores'),
                    
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->placeholder('Todas las categorías'),
                    
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Article::STATUSES),
                    
                TernaryFilter::make('featured')
                    ->label('Destacado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo destacados')
                    ->falseLabel('No destacados'),
                    
                TernaryFilter::make('comment_enabled')
                    ->label('Comentarios')
                    ->placeholder('Todos')
                    ->trueLabel('Comentarios habilitados')
                    ->falseLabel('Comentarios deshabilitados'),
                    
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
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Article $record) {
                        $duplicate = $record->duplicate();
                        return redirect()->route('filament.admin.resources.articles.edit', $duplicate);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('publish')
                    ->label('Publicar')
                    ->icon('heroicon-o-eye')
                    ->action(fn (Article $record) => $record->update([
                        'status' => 'published', 
                        'is_draft' => false,
                        'published_at' => now()
                    ]))
                    ->visible(fn (Article $record) => $record->status !== 'published')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publicar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'published', 
                            'is_draft' => false,
                            'published_at' => now()
                        ]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('Marcar como destacados')
                        ->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->update(['featured' => true]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}