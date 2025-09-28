<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TextContentResource\Pages;
use App\Filament\Resources\TextContentResource\RelationManagers;
use App\Models\TextContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class TextContentResource extends Resource
{
    protected static ?string $model = TextContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Contenidos de Texto';
    
    protected static ?string $modelLabel = 'Contenido de Texto';
    
    protected static ?string $pluralModelLabel = 'Contenidos de Texto';
    
    protected static ?string $navigationGroup = 'Comunicaciones y Contenido';
    
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
                                        Forms\Components\TextInput::make('slug')
                                            ->label('Identificador (Slug)')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(TextContent::class, 'slug', ignoreRecord: true)
                                            ->helperText('Identificador único para este contenido')
                                            ->rules(['regex:/^[a-z0-9\-]+$/']),
                                            
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                                $context === 'create' && empty($set('slug')) ? $set('slug', \Str::slug($state)) : null),
                                            
                                        Forms\Components\TextInput::make('subtitle')
                                            ->label('Subtítulo')
                                            ->maxLength(255),
                                    ])->columns(3),
                                    
                                Section::make('Contenido')
                                    ->schema([
                                        Forms\Components\RichEditor::make('text')
                                            ->label('Contenido')
                                            ->required()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('text-content/attachments')
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make('excerpt')
                                            ->label('Extracto')
                                            ->rows(3)
                                            ->maxLength(300)
                                            ->helperText('Resumen breve del contenido')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Jerarquía')
                                    ->schema([
                                        Forms\Components\Select::make('parent_id')
                                            ->label('Contenido Padre')
                                            ->relationship('parent', 'title')
                                            ->searchable()
                                            ->placeholder('Sin contenido padre'),
                                            
                                        Forms\Components\Select::make('author_id')
                                            ->label('Autor')
                                            ->relationship('author', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(auth()->id()),
                                    ])->columns(2),
                                    
                                Section::make('SEO y Búsqueda')
                                    ->schema([
                                        Forms\Components\TextInput::make('seo_focus_keyword')
                                            ->label('Palabra Clave Principal')
                                            ->helperText('Palabra clave principal para SEO'),
                                            
                                        Forms\Components\TagsInput::make('search_keywords')
                                            ->label('Palabras Clave de Búsqueda')
                                            ->placeholder('Escriba palabras clave y presione Enter'),
                                            
                                        Forms\Components\TextInput::make('reading_time')
                                            ->label('Tiempo de Lectura (minutos)')
                                            ->numeric()
                                            ->helperText('Se calcula automáticamente si se deja vacío'),
                                    ])->columns(3),
                            ]),
                            
                        Tabs\Tab::make('Publicación')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Section::make('Estado')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_draft')
                                            ->label('Borrador')
                                            ->helperText('Si está activado, el contenido no será visible públicamente')
                                            ->default(true),
                                            
                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label('Fecha de Publicación')
                                            ->helperText('Opcional. Fecha específica de publicación'),
                                            
                                        Forms\Components\TextInput::make('version')
                                            ->label('Versión')
                                            ->default('1.0')
                                            ->helperText('Versión del contenido'),
                                    ])->columns(3),
                                    
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
                            ]),
                            
                        Tabs\Tab::make('Notas y Accesibilidad')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
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
                Tables\Columns\TextColumn::make('slug')
                    ->label('Identificador')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('Sin título'),
                    
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Autor')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin autor'),
                    
                Tables\Columns\TextColumn::make('version')
                    ->label('Versión')
                    ->badge()
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('reading_time')
                    ->label('Lectura')
                    ->suffix(' min')
                    ->alignCenter()
                    ->placeholder('—')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('number_of_views')
                    ->label('Vistas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_draft')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseColor('success'),
                    
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => 'ca',
                        'danger' => 'eu',
                        'secondary' => 'gl',
                    ]),
                    
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No publicado')
                    ->toggleable(),
                    
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
                    ->trueLabel('Solo borradores')
                    ->falseLabel('Solo publicados'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (TextContent $record) {
                        $duplicate = $record->replicate();
                        $duplicate->slug = $record->slug . '-copia-' . time();
                        $duplicate->title = ($record->title ?? 'Contenido') . ' (Copia)';
                        $duplicate->is_draft = true;
                        $duplicate->published_at = null;
                        $duplicate->number_of_views = 0;
                        $duplicate->save();
                        
                        return redirect()->route('filament.admin.resources.text-contents.edit', $duplicate);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publicar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update([
                            'is_draft' => false,
                            'published_at' => now()
                        ]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTextContents::route('/'),
            'create' => Pages\CreateTextContent::route('/create'),
            'edit' => Pages\EditTextContent::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_draft', false)->count() ?: null;
    }
}