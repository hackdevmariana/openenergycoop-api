<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use App\Models\Category;
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

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    
    protected static ?string $navigationLabel = 'Documentos';
    
    protected static ?string $modelLabel = 'Documento';
    
    protected static ?string $pluralModelLabel = 'Documentos';
    
    protected static ?string $navigationGroup = 'Contenidos';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información del Documento')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Información Básica')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->rows(4)
                                            ->maxLength(1000)
                                            ->helperText('Descripción detallada del documento')
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Archivo')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('document')
                                            ->label('Archivo del Documento')
                                            ->collection('documents')
                                            ->acceptedFileTypes([
                                                'application/pdf',
                                                'application/msword',
                                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                'application/vnd.ms-excel',
                                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                'text/plain',
                                                'image/jpeg',
                                                'image/png',
                                            ])
                                            ->maxSize(10240) // 10MB
                                            ->helperText('Formatos admitidos: PDF, Word, Excel, texto, imágenes. Máximo 10MB')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Clasificación')
                            ->icon('heroicon-o-folder')
                            ->schema([
                                Section::make('Organización')
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Categoría')
                                            ->relationship('category', 'name', fn (Builder $query) => 
                                                $query->where('category_type', 'document')->orderBy('name'))
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nombre de la Categoría')
                                                    ->required(),
                                                Forms\Components\TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->required(),
                                                Forms\Components\Hidden::make('category_type')
                                                    ->default('document'),
                                            ]),
                                            
                                        Forms\Components\TagsInput::make('search_keywords')
                                            ->label('Palabras Clave')
                                            ->placeholder('Escriba palabras clave y presione Enter')
                                            ->helperText('Palabras clave para facilitar la búsqueda'),
                                            
                                        Forms\Components\TextInput::make('version')
                                            ->label('Versión')
                                            ->default('1.0')
                                            ->helperText('Versión del documento'),
                                    ])->columns(3),
                            ]),
                            
                        Tabs\Tab::make('Acceso y Seguridad')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make('Control de Acceso')
                                    ->schema([
                                        Forms\Components\Toggle::make('visible')
                                            ->label('Visible')
                                            ->helperText('Si está desactivado, el documento no aparecerá en listados públicos')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('requires_auth')
                                            ->label('Requiere Autenticación')
                                            ->helperText('Solo usuarios autenticados pueden descargar este documento'),
                                            
                                        Forms\Components\TagsInput::make('allowed_roles')
                                            ->label('Roles Permitidos')
                                            ->placeholder('Escriba los roles y presione Enter')
                                            ->helperText('Si se especifica, solo usuarios con estos roles pueden acceder'),
                                    ])->columns(3),
                                    
                                Section::make('Caducidad')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('expires_at')
                                            ->label('Fecha de Caducidad')
                                            ->helperText('Opcional. Después de esta fecha, el documento no estará disponible'),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Publicación')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Section::make('Estado de Publicación')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_draft')
                                            ->label('Borrador')
                                            ->helperText('Si está activado, el documento no será visible públicamente')
                                            ->default(true),
                                            
                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label('Fecha de Publicación')
                                            ->helperText('Opcional. Si se especifica, el documento se publicará automáticamente'),
                                    ])->columns(2),
                                    
                                Section::make('Organización e Idioma')
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
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->label('Vista Previa')
                    ->collection('thumbnails')
                    ->width(50)
                    ->height(50)
                    ->defaultImageUrl(fn ($record) => 
                        asset('images/file-icons/' . ($record->file_type ?? 'default') . '.svg')
                    ),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->placeholder('Sin categoría'),
                    
                Tables\Columns\TextColumn::make('file_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'doc', 'docx' => 'primary',
                        'xls', 'xlsx' => 'success',
                        'jpg', 'jpeg', 'png' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? 'N/A')),
                    
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) => $state ? self::formatBytes($state) : 'N/A')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('version')
                    ->label('Versión')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('download_count')
                    ->label('Descargas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('number_of_views')
                    ->label('Vistas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('visible')
                    ->label('Visible')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('requires_auth')
                    ->label('Auth')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-globe-alt')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn ($record) => $record->requires_auth ? 'Requiere autenticación' : 'Acceso público'),
                    
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Caduca')
                    ->dateTime('d/m/Y')
                    ->placeholder('Sin caducidad')
                    ->color(fn ($record) => $record->isExpiringSoon() ? 'warning' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Subido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->placeholder('Todas las categorías'),
                    
                SelectFilter::make('file_type')
                    ->label('Tipo de Archivo')
                    ->options([
                        'pdf' => 'PDF',
                        'doc' => 'Word (.doc)',
                        'docx' => 'Word (.docx)',
                        'xls' => 'Excel (.xls)',
                        'xlsx' => 'Excel (.xlsx)',
                        'txt' => 'Texto',
                        'jpg' => 'JPEG',
                        'png' => 'PNG',
                    ]),
                    
                TernaryFilter::make('visible')
                    ->label('Visibilidad')
                    ->placeholder('Todos')
                    ->trueLabel('Solo visibles')
                    ->falseLabel('Solo ocultos'),
                    
                TernaryFilter::make('requires_auth')
                    ->label('Acceso')
                    ->placeholder('Todos')
                    ->trueLabel('Requiere autenticación')
                    ->falseLabel('Acceso público'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record) => $record->getDownloadUrl())
                    ->openUrlInNewTab(),
                    
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                    
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Document $record) {
                        $duplicate = $record->duplicate();
                        return redirect()->route('filament.admin.resources.documents.edit', $duplicate);
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
                        ->action(fn ($records) => $records->each->update([
                            'is_draft' => false,
                            'visible' => true,
                            'published_at' => now()
                        ]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('hide')
                        ->label('Ocultar seleccionados')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['visible' => false]))
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
        return static::getModel()::where('visible', true)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
    
    /**
     * Format file size in human readable format
     */
    private static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}