<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoMetaDataResource\Pages;
use App\Models\SeoMetaData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;

class SeoMetaDataResource extends Resource
{
    protected static ?string $model = SeoMetaData::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    
    protected static ?string $navigationLabel = 'SEO Metadatos';
    
    protected static ?string $modelLabel = 'Metadato SEO';
    
    protected static ?string $pluralModelLabel = 'Metadatos SEO';
    
    protected static ?string $navigationGroup = 'SEO y Marketing';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información Básica')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Contenido Asociado')
                                    ->schema([
                                        Forms\Components\TextInput::make('seoable_type')
                                            ->label('Tipo de Contenido')
                                            ->disabled()
                                            ->formatStateUsing(fn ($state) => class_basename($state)),
                                            
                                        Forms\Components\TextInput::make('seoable_id')
                                            ->label('ID del Contenido')
                                            ->disabled(),
                                            
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
                            ]),
                            
                        Tabs\Tab::make('Meta Tags Básicos')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Section::make('Título y Descripción')
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title')
                                            ->label('Título Meta')
                                            ->maxLength(60)
                                            ->helperText('Recomendado: 50-60 caracteres. Actual: 0')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($component, $state) {
                                                $length = strlen($state ?? '');
                                                $component->helperText("Recomendado: 50-60 caracteres. Actual: {$length}");
                                            })
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('Descripción Meta')
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText('Recomendado: 150-160 caracteres. Actual: 0')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($component, $state) {
                                                $length = strlen($state ?? '');
                                                $component->helperText("Recomendado: 150-160 caracteres. Actual: {$length}");
                                            })
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Configuración Avanzada')
                                    ->schema([
                                        Forms\Components\TextInput::make('canonical_url')
                                            ->label('URL Canónica')
                                            ->url()
                                            ->placeholder('https://ejemplo.com/pagina')
                                            ->helperText('URL canónica para evitar contenido duplicado'),
                                            
                                        Forms\Components\Select::make('robots')
                                            ->label('Directivas para Robots')
                                            ->options([
                                                'index, follow' => 'Index, Follow',
                                                'noindex, follow' => 'NoIndex, Follow',
                                                'index, nofollow' => 'Index, NoFollow',
                                                'noindex, nofollow' => 'NoIndex, NoFollow',
                                            ])
                                            ->default('index, follow')
                                            ->helperText('Instrucciones para motores de búsqueda'),
                                            
                                        Forms\Components\TextInput::make('focus_keyword')
                                            ->label('Palabra Clave Principal')
                                            ->helperText('Palabra clave principal para esta página'),
                                    ])->columns(3),
                            ]),
                            
                        Tabs\Tab::make('Open Graph (Facebook)')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Section::make('Metadatos de Open Graph')
                                    ->schema([
                                        Forms\Components\TextInput::make('og_title')
                                            ->label('Título OG')
                                            ->maxLength(95)
                                            ->helperText('Título para redes sociales (Facebook, LinkedIn)')
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make('og_description')
                                            ->label('Descripción OG')
                                            ->maxLength(200)
                                            ->rows(3)
                                            ->helperText('Descripción para redes sociales')
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\TextInput::make('og_image_path')
                                            ->label('Imagen OG')
                                            ->placeholder('/images/og-image.jpg')
                                            ->helperText('Imagen para redes sociales (1200x630px recomendado)'),
                                            
                                        Forms\Components\Select::make('og_type')
                                            ->label('Tipo OG')
                                            ->options([
                                                'website' => 'Website',
                                                'article' => 'Article',
                                                'product' => 'Product',
                                                'profile' => 'Profile',
                                            ])
                                            ->default('website'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Twitter Cards')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Section::make('Metadatos de Twitter')
                                    ->schema([
                                        Forms\Components\TextInput::make('twitter_title')
                                            ->label('Título Twitter')
                                            ->maxLength(70)
                                            ->helperText('Título específico para Twitter')
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make('twitter_description')
                                            ->label('Descripción Twitter')
                                            ->maxLength(200)
                                            ->rows(3)
                                            ->helperText('Descripción específica para Twitter')
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\TextInput::make('twitter_image_path')
                                            ->label('Imagen Twitter')
                                            ->placeholder('/images/twitter-image.jpg')
                                            ->helperText('Imagen para Twitter (1200x600px recomendado)'),
                                            
                                        Forms\Components\Select::make('twitter_card')
                                            ->label('Tipo de Twitter Card')
                                            ->options([
                                                'summary' => 'Summary',
                                                'summary_large_image' => 'Summary Large Image',
                                                'app' => 'App',
                                                'player' => 'Player',
                                            ])
                                            ->default('summary_large_image'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Datos Estructurados')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Section::make('Schema.org JSON-LD')
                                    ->schema([
                                        Forms\Components\Textarea::make('structured_data')
                                            ->label('Datos Estructurados (JSON-LD)')
                                            ->rows(10)
                                            ->helperText('Código JSON-LD para datos estructurados de Schema.org')
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Metadatos Adicionales')
                                    ->schema([
                                        Forms\Components\KeyValue::make('additional_meta')
                                            ->label('Meta Tags Adicionales')
                                            ->keyLabel('Nombre')
                                            ->valueLabel('Contenido')
                                            ->helperText('Meta tags personalizados adicionales')
                                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('seoable_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'App\\Models\\Page' => 'Página',
                        'App\\Models\\Article' => 'Artículo',
                        'App\\Models\\Document' => 'Documento',
                        'App\\Models\\Hero' => 'Hero',
                        default => class_basename($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'App\\Models\\Page' => 'primary',
                        'App\\Models\\Article' => 'success',
                        'App\\Models\\Document' => 'warning',
                        'App\\Models\\Hero' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('meta_title')
                    ->label('Título Meta')
                    ->searchable()
                    ->limit(50)
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('meta_title_length')
                    ->label('Long. Título')
                    ->getStateUsing(fn ($record) => strlen($record->meta_title ?? ''))
                    ->alignCenter()
                    ->color(fn ($state) => match(true) {
                        $state == 0 => 'gray',
                        $state < 30 => 'danger',
                        $state <= 60 => 'success',
                        default => 'warning',
                    })
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('meta_description')
                    ->label('Descripción Meta')
                    ->limit(80)
                    ->placeholder('Sin descripción'),
                    
                Tables\Columns\TextColumn::make('meta_description_length')
                    ->label('Long. Desc.')
                    ->getStateUsing(fn ($record) => strlen($record->meta_description ?? ''))
                    ->alignCenter()
                    ->color(fn ($state) => match(true) {
                        $state == 0 => 'gray',
                        $state < 120 => 'danger',
                        $state <= 160 => 'success',
                        default => 'warning',
                    })
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('focus_keyword')
                    ->label('Palabra Clave')
                    ->badge()
                    ->placeholder('Sin definir')
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('has_og_data')
                    ->label('OG')
                    ->getStateUsing(fn ($record) => !empty($record->og_title) && !empty($record->og_description))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip('Datos de Open Graph completos'),
                    
                Tables\Columns\IconColumn::make('has_twitter_data')
                    ->label('Twitter')
                    ->getStateUsing(fn ($record) => !empty($record->twitter_title) && !empty($record->twitter_description))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip('Datos de Twitter completos'),
                    
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => 'ca',
                        'danger' => 'eu',
                        'secondary' => 'gl',
                    ]),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('seoable_type')
                    ->label('Tipo de Contenido')
                    ->options([
                        'App\\Models\\Page' => 'Páginas',
                        'App\\Models\\Article' => 'Artículos',
                        'App\\Models\\Document' => 'Documentos',
                        'App\\Models\\Hero' => 'Heroes',
                    ]),
                    
                SelectFilter::make('language')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Español',
                        'en' => 'English',
                        'ca' => 'Català',
                        'eu' => 'Euskera',
                        'gl' => 'Galego',
                    ]),
                    
                SelectFilter::make('completeness')
                    ->label('Completitud')
                    ->query(function ($query, $data) {
                        return match($data['value'] ?? null) {
                            'complete' => $query->whereNotNull('meta_title')
                                               ->whereNotNull('meta_description')
                                               ->whereNotNull('focus_keyword'),
                            'incomplete' => $query->where(function ($q) {
                                $q->whereNull('meta_title')
                                  ->orWhereNull('meta_description')
                                  ->orWhereNull('focus_keyword');
                            }),
                            'no_meta' => $query->whereNull('meta_title')->whereNull('meta_description'),
                            default => $query,
                        };
                    })
                    ->options([
                        'complete' => 'Completo',
                        'incomplete' => 'Incompleto',
                        'no_meta' => 'Sin metadatos',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('preview_meta')
                    ->label('Vista Previa')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading('Vista Previa SEO')
                    ->modalContent(function ($record) {
                        return view('filament.seo-preview', compact('record'));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoMetaData::route('/'),
            'create' => Pages\CreateSeoMetaData::route('/create'),
            'edit' => Pages\EditSeoMetaData::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('meta_title')->count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getNavigationBadge();
        
        if ($count > 10) {
            return 'danger';
        } elseif ($count > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}