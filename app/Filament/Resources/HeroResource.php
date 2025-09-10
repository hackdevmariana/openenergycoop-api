<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroResource\Pages;
use App\Filament\Resources\HeroResource\RelationManagers;
use App\Models\Hero;
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
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class HeroResource extends Resource
{
    protected static ?string $model = Hero::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    
    protected static ?string $navigationLabel = 'Heroes/Banners';
    
    protected static ?string $modelLabel = 'Hero';
    
    protected static ?string $pluralModelLabel = 'Heroes';
    
    protected static ?string $navigationGroup = 'Componentes';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Contenido')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Textos')
                                    ->schema([
                                        Forms\Components\TextInput::make('text')
                                            ->label('Título Principal')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make('subtext')
                                            ->label('Subtítulo/Descripción')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\TextInput::make('text_button')
                                            ->label('Texto del Botón')
                                            ->maxLength(100)
                                            ->placeholder('Más información, Contactar, etc.'),
                                    ]),
                                    
                                Section::make('Enlaces')
                                    ->schema([
                                        Forms\Components\TextInput::make('internal_link')
                                            ->label('Enlace Interno')
                                            ->placeholder('/pagina, /contacto')
                                            ->helperText('Ruta interna del sitio'),
                                            
                                        Forms\Components\TextInput::make('cta_link_external')
                                            ->label('Enlace Externo')
                                            ->url()
                                            ->placeholder('https://ejemplo.com')
                                            ->helperText('URL externa completa'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Medios')
                            ->icon('heroicon-o-camera')
                            ->schema([
                                Section::make('Imágenes')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('image')
                                            ->label('Imagen Principal')
                                            ->collection('hero_images')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '21:9',
                                                '3:2',
                                            ])
                                            ->helperText('Recomendado: 1920x1080px para mejor calidad'),
                                            
                                        SpatieMediaLibraryFileUpload::make('mobile_image')
                                            ->label('Imagen para Móvil')
                                            ->collection('hero_mobile_images')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '9:16',
                                                '4:5',
                                                '1:1',
                                            ])
                                            ->helperText('Opcional. Imagen optimizada para dispositivos móviles'),
                                    ])->columns(2),
                                    
                                Section::make('Video de Fondo')
                                    ->schema([
                                        Forms\Components\TextInput::make('video_url')
                                            ->label('URL del Video')
                                            ->url()
                                            ->placeholder('https://youtube.com/watch?v=...')
                                            ->helperText('YouTube, Vimeo o URL directa'),
                                            
                                        Forms\Components\Toggle::make('video_background')
                                            ->label('Video como Fondo')
                                            ->helperText('El video se reproducirá automáticamente como fondo')
                                            ->default(false),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Diseño')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Section::make('Posicionamiento y Estilo')
                                    ->schema([
                                        Forms\Components\Select::make('text_align')
                                            ->label('Alineación del Texto')
                                            ->options([
                                                'left' => 'Izquierda',
                                                'center' => 'Centro',
                                                'right' => 'Derecha',
                                            ])
                                            ->default('center'),
                                            
                                        Forms\Components\TextInput::make('overlay_opacity')
                                            ->label('Opacidad del Overlay')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(50)
                                            ->suffix('%')
                                            ->helperText('Oscurece la imagen para mejorar legibilidad del texto'),
                                            
                                        Forms\Components\Select::make('animation_type')
                                            ->label('Tipo de Animación')
                                            ->options([
                                                'none' => 'Sin animación',
                                                'fade' => 'Desvanecimiento',
                                                'slide' => 'Deslizamiento',
                                                'zoom' => 'Zoom',
                                                'bounce' => 'Rebote',
                                            ])
                                            ->default('fade'),
                                    ])->columns(3),
                                    
                                Section::make('Botón de Acción')
                                    ->schema([
                                        Forms\Components\Select::make('cta_style')
                                            ->label('Estilo del Botón')
                                            ->options([
                                                'primary' => 'Primario',
                                                'secondary' => 'Secundario',
                                                'outline' => 'Contorno',
                                                'ghost' => 'Fantasma',
                                                'link' => 'Enlace',
                                            ])
                                            ->default('primary'),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Programación')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Section::make('Período de Exhibición')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('exhibition_beginning')
                                            ->label('Inicio de Exhibición')
                                            ->helperText('Fecha y hora de inicio para mostrar este hero'),
                                            
                                        Forms\Components\DateTimePicker::make('exhibition_end')
                                            ->label('Fin de Exhibición')
                                            ->helperText('Fecha y hora de finalización'),
                                    ])->columns(2),
                                    
                                Section::make('Configuración')
                                    ->schema([
                                        Forms\Components\TextInput::make('position')
                                            ->label('Posición/Orden')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Orden de aparición (menor número = mayor prioridad)'),
                                            
                                        Forms\Components\TextInput::make('priority')
                                            ->label('Prioridad')
                                            ->numeric()
                                            ->default(1)
                                            ->helperText('Prioridad para rotación automática'),
                                            
                                        Forms\Components\Toggle::make('active')
                                            ->label('Activo')
                                            ->helperText('Solo los heroes activos se muestran')
                                            ->default(true),
                                    ])->columns(3),
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
                    ->collection('hero_images')
                    ->width(100)
                    ->height(60),
                    
                Tables\Columns\TextColumn::make('text')
                    ->label('Título')
                    ->searchable()
                    ->limit(50)
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('text_button')
                    ->label('Botón')
                    ->badge()
                    ->placeholder('Sin botón')
                    ->limit(20),
                    
                Tables\Columns\TextColumn::make('text_align')
                    ->label('Alineación')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'left' => 'Izquierda',
                        'center' => 'Centro',
                        'right' => 'Derecha',
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
                    
                Tables\Columns\IconColumn::make('video_background')
                    ->label('Video')
                    ->boolean()
                    ->trueIcon('heroicon-o-play-circle')
                    ->falseIcon('heroicon-o-photo')
                    ->trueColor('primary')
                    ->falseColor('gray')
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
                TernaryFilter::make('active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                    
                SelectFilter::make('text_align')
                    ->label('Alineación')
                    ->options([
                        'left' => 'Izquierda',
                        'center' => 'Centro',
                        'right' => 'Derecha',
                    ]),
                    
                TernaryFilter::make('video_background')
                    ->label('Tipo')
                    ->placeholder('Todos')
                    ->trueLabel('Con video')
                    ->falseLabel('Solo imagen'),
            ])
            ->actions([
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
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroes::route('/'),
            'create' => Pages\CreateHero::route('/create'),
            'edit' => Pages\EditHero::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('active', true)->count() ?: null;
    }
}