<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
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
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $navigationLabel = 'Banners';
    
    protected static ?string $modelLabel = 'Banner';
    
    protected static ?string $pluralModelLabel = 'Banners';
    
    protected static ?string $navigationGroup = 'Componentes';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Contenido')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Imágenes')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('image')
                                            ->label('Imagen Principal')
                                            ->collection('banner_images')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                                '3:2',
                                            ])
                                            ->helperText('Imagen principal del banner'),
                                            
                                        SpatieMediaLibraryFileUpload::make('mobile_image')
                                            ->label('Imagen para Móvil')
                                            ->collection('banner_mobile_images')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '1:1',
                                                '4:5',
                                                '9:16',
                                            ])
                                            ->helperText('Opcional. Imagen optimizada para móviles'),
                                    ])->columns(2),
                                    
                                Section::make('Información del Banner')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->maxLength(255)
                                            ->helperText('Título del banner (para identificación interna)'),
                                            
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText('Descripción del banner'),
                                            
                                        Forms\Components\TextInput::make('alt_text')
                                            ->label('Texto Alternativo')
                                            ->maxLength(255)
                                            ->helperText('Texto alternativo para accesibilidad'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Enlaces y Acción')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Section::make('Enlaces')
                                    ->schema([
                                        Forms\Components\TextInput::make('internal_link')
                                            ->label('Enlace Interno')
                                            ->placeholder('/pagina, /articulo/mi-articulo')
                                            ->helperText('Ruta interna del sitio'),
                                            
                                        Forms\Components\TextInput::make('url')
                                            ->label('URL Externa')
                                            ->url()
                                            ->placeholder('https://ejemplo.com')
                                            ->helperText('URL externa completa'),
                                    ])->columns(2),
                                    
                                Section::make('Estadísticas')
                                    ->schema([
                                        Forms\Components\TextInput::make('click_count')
                                            ->label('Clicks')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->helperText('Número de clicks registrados'),
                                            
                                        Forms\Components\TextInput::make('impression_count')
                                            ->label('Impresiones')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->helperText('Número de veces mostrado'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Posicionamiento')
                                    ->schema([
                                        Forms\Components\Select::make('banner_type')
                                            ->label('Tipo de Banner')
                                            ->options([
                                                'header' => 'Cabecera',
                                                'sidebar' => 'Barra Lateral',
                                                'footer' => 'Pie de Página',
                                                'content' => 'Contenido',
                                                'popup' => 'Popup',
                                                'floating' => 'Flotante',
                                            ])
                                            ->default('content')
                                            ->required(),
                                            
                                        Forms\Components\TextInput::make('position')
                                            ->label('Posición/Orden')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Orden de aparición (menor número = mayor prioridad)'),
                                    ])->columns(2),
                                    
                                Section::make('Reglas de Visualización')
                                    ->schema([
                                        Forms\Components\KeyValue::make('display_rules')
                                            ->label('Reglas de Visualización')
                                            ->keyLabel('Condición')
                                            ->valueLabel('Valor')
                                            ->helperText('Condiciones para mostrar el banner (ej: page=/inicio, user_role=member)'),
                                    ]),
                                    
                                Section::make('Programación')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('exhibition_beginning')
                                            ->label('Inicio de Exhibición')
                                            ->helperText('Fecha y hora de inicio para mostrar este banner'),
                                            
                                        Forms\Components\DateTimePicker::make('exhibition_end')
                                            ->label('Fin de Exhibición')
                                            ->helperText('Fecha y hora de finalización'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Estado')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Section::make('Estado de Publicación')
                                    ->schema([
                                        Forms\Components\Toggle::make('active')
                                            ->label('Activo')
                                            ->helperText('Solo los banners activos se muestran')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('is_draft')
                                            ->label('Borrador')
                                            ->helperText('Si está activado, el banner no será visible públicamente')
                                            ->default(true),
                                            
                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label('Fecha de Publicación')
                                            ->helperText('Fecha específica de publicación'),
                                    ])->columns(3),
                                    
                                Section::make('Organización')
                                    ->schema([
                                        Forms\Components\Select::make('organization_id')
                                            ->label('Organización')
                                            ->relationship('organization', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Global (todas las organizaciones)'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('Imagen')
                    ->collection('banner_images')
                    ->width(80)
                    ->height(60),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->limit(50),
                    
                Tables\Columns\BadgeColumn::make('banner_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'header',
                        'success' => 'sidebar',
                        'warning' => 'footer',
                        'danger' => 'content',
                        'secondary' => 'popup',
                        'gray' => 'floating',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'header' => 'Cabecera',
                        'sidebar' => 'Barra Lateral',
                        'footer' => 'Pie de Página',
                        'content' => 'Contenido',
                        'popup' => 'Popup',
                        'floating' => 'Flotante',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('position')
                    ->label('Posición')
                    ->sortable()
                    ->alignCenter(),
                    
                Tables\Columns\IconColumn::make('active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('click_count')
                    ->label('Clicks')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('impression_count')
                    ->label('Impresiones')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('exhibition_beginning')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Siempre')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('exhibition_end')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Siempre')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('banner_type')
                    ->label('Tipo')
                    ->options([
                        'header' => 'Cabecera',
                        'sidebar' => 'Barra Lateral',
                        'footer' => 'Pie de Página',
                        'content' => 'Contenido',
                        'popup' => 'Popup',
                        'floating' => 'Flotante',
                    ]),
                    
                TernaryFilter::make('active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('track_click')
                    ->label('Registrar Click')
                    ->icon('heroicon-o-cursor-arrow-rays')
                    ->action(fn (Banner $record) => $record->increment('click_count'))
                    ->color('success'),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->active ? 'Desactivar' : 'Activar')
                    ->icon(fn ($record) => $record->active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->active ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['active' => !$record->active])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar seleccionados')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('active', true)->count() ?: null;
    }
}