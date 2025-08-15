<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImageResource\Pages;
use App\Models\Image;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\ImageColumn;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    
    protected static ?string $navigationGroup = 'Gestión de Medios';
    
    protected static ?string $navigationLabel = 'Imágenes';
    
    protected static ?string $modelLabel = 'Imagen';
    
    protected static ?string $pluralModelLabel = 'Imágenes';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Image::class, 'slug', ignoreRecord: true),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(1000),
                            
                        Forms\Components\TextInput::make('alt_text')
                            ->label('Texto Alternativo')
                            ->maxLength(255)
                            ->helperText('Para accesibilidad'),
                    ])->columns(2),
                    
                Section::make('Archivo')
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->label('Imagen')
                            ->image()
                            ->directory('images')
                            ->visibility('public')
                            ->maxSize(10240)
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('Configuración')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable(),
                            
                        Forms\Components\TagsInput::make('tags')
                            ->label('Etiquetas'),
                            
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activo',
                                'archived' => 'Archivado',
                                'deleted' => 'Eliminado',
                            ])
                            ->default('active')
                            ->required(),
                            
                        Forms\Components\Toggle::make('is_public')
                            ->label('Público')
                            ->default(true),
                            
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Destacado')
                            ->default(false),
                            
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->placeholder('Global'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Vista Previa')
                    ->width(80)
                    ->height(60),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->limit(40),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Sin categoría'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'archived',
                        'danger' => 'deleted',
                    ]),
                    
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Público')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed'),
                    
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
                    
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'archived' => 'Archivado',
                        'deleted' => 'Eliminado',
                    ]),
                    
                TernaryFilter::make('is_public')
                    ->label('Visibilidad')
                    ->placeholder('Todas')
                    ->trueLabel('Solo públicas')
                    ->falseLabel('Solo privadas'),
                    
                TernaryFilter::make('is_featured')
                    ->label('Destacadas')
                    ->placeholder('Todas')
                    ->trueLabel('Solo destacadas')
                    ->falseLabel('Solo no destacadas'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => $record->getFullUrl())
                    ->openUrlInNewTab(),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggle_featured')
                    ->label(fn ($record) => $record->is_featured ? 'Quitar Destacado' : 'Destacar')
                    ->icon('heroicon-o-star')
                    ->action(fn ($record) => $record->update(['is_featured' => !$record->is_featured])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImages::route('/'),
            'create' => Pages\CreateImage::route('/create'),
            'edit' => Pages\EditImage::route('/{record}/edit'),
        ];
    }
}