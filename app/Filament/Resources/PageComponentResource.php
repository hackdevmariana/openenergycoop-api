<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageComponentResource\Pages;
use App\Models\PageComponent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class PageComponentResource extends Resource
{
    protected static ?string $model = PageComponent::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    
    protected static ?string $navigationLabel = 'Componentes de Página';
    
    protected static ?string $modelLabel = 'Componente';
    
    protected static ?string $pluralModelLabel = 'Componentes';
    
    protected static ?string $navigationGroup = 'Componentes';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Componente')
                    ->schema([
                        Forms\Components\Select::make('page_id')
                            ->label('Página')
                            ->relationship('page', 'title')
                            ->searchable()
                            ->required()
                            ->preload(),
                            
                        Forms\Components\TextInput::make('componentable_type')
                            ->label('Tipo de Componente')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => class_basename($state)),
                            
                        Forms\Components\TextInput::make('componentable_id')
                            ->label('ID del Componente')
                            ->disabled(),
                    ])->columns(3),
                    
                Section::make('Posicionamiento')
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->label('Posición')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición en la página'),
                            
                        Forms\Components\Select::make('parent_id')
                            ->label('Componente Padre')
                            ->relationship('parent', 'componentable_type')
                            ->searchable()
                            ->placeholder('Sin componente padre')
                            ->formatStateUsing(function ($record) {
                                if (!$record) return null;
                                return class_basename($record->componentable_type) . " #{$record->componentable_id}";
                            }),
                    ])->columns(2),
                    
                Section::make('Configuración')
                    ->schema([
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
                            
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Global (todas las organizaciones)'),
                    ])->columns(2),
                    
                Section::make('Estado y Publicación')
                    ->schema([
                        Forms\Components\Toggle::make('is_draft')
                            ->label('Borrador')
                            ->helperText('Si está activado, el componente no será visible públicamente')
                            ->default(true),
                            
                        Forms\Components\TextInput::make('version')
                            ->label('Versión')
                            ->default('1.0')
                            ->helperText('Versión del componente'),
                            
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Fecha de Publicación')
                            ->helperText('Fecha específica de publicación'),
                    ])->columns(3),
                    
                Section::make('Configuraciones Avanzadas')
                    ->schema([
                        Forms\Components\Toggle::make('cache_enabled')
                            ->label('Cache Habilitado')
                            ->helperText('Permite cachear este componente para mejor rendimiento')
                            ->default(true),
                            
                        Forms\Components\TextInput::make('preview_token')
                            ->label('Token de Vista Previa')
                            ->disabled()
                            ->copyable()
                            ->helperText('Token para compartir vista previa sin publicar'),
                            
                        Forms\Components\TextInput::make('ab_test_group')
                            ->label('Grupo de Test A/B')
                            ->placeholder('control, variant_a, variant_b')
                            ->helperText('Grupo para pruebas A/B'),
                    ])->columns(3),
                    
                Section::make('Configuraciones del Componente')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->label('Configuraciones')
                            ->keyLabel('Configuración')
                            ->valueLabel('Valor')
                            ->helperText('Configuraciones específicas del componente')
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('Reglas de Visibilidad')
                    ->schema([
                        Forms\Components\KeyValue::make('visibility_rules')
                            ->label('Reglas de Visibilidad')
                            ->keyLabel('Condición')
                            ->valueLabel('Valor')
                            ->helperText('Condiciones para mostrar el componente')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page.title')
                    ->label('Página')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('componentable_type')
                    ->label('Tipo de Componente')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'App\\Models\\Hero' => 'Hero',
                        'App\\Models\\TextContent' => 'Contenido de Texto',
                        'App\\Models\\Banner' => 'Banner',
                        'App\\Models\\Menu' => 'Menú',
                        default => class_basename($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'App\\Models\\Hero' => 'danger',
                        'App\\Models\\TextContent' => 'primary',
                        'App\\Models\\Banner' => 'warning',
                        'App\\Models\\Menu' => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('componentable_id')
                    ->label('ID')
                    ->alignCenter()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('position')
                    ->label('Posición')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('secondary'),
                    
                Tables\Columns\TextColumn::make('version')
                    ->label('Versión')
                    ->badge()
                    ->alignCenter()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_draft')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseColor('success'),
                    
                Tables\Columns\IconColumn::make('cache_enabled')
                    ->label('Cache')
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-bolt-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->cache_enabled ? 'Cache habilitado' : 'Cache deshabilitado'),
                    
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => 'ca',
                        'danger' => 'eu',
                        'secondary' => 'gl',
                    ]),
                    
                Tables\Columns\TextColumn::make('ab_test_group')
                    ->label('Test A/B')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No publicado')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('page_id')
                    ->label('Página')
                    ->relationship('page', 'title')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('componentable_type')
                    ->label('Tipo de Componente')
                    ->options([
                        'App\\Models\\Hero' => 'Hero',
                        'App\\Models\\TextContent' => 'Contenido de Texto',
                        'App\\Models\\Banner' => 'Banner',
                        'App\\Models\\Menu' => 'Menú',
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
                    
                TernaryFilter::make('is_draft')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo borradores')
                    ->falseLabel('Solo publicados'),
                    
                TernaryFilter::make('cache_enabled')
                    ->label('Cache')
                    ->placeholder('Todos')
                    ->trueLabel('Cache habilitado')
                    ->falseLabel('Cache deshabilitado'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Vista Previa')
                    ->icon('heroicon-o-eye')
                    ->url(function ($record) {
                        if ($record->preview_token) {
                            return route('preview.component', [
                                'token' => $record->preview_token,
                                'component' => $record->id
                            ]);
                        }
                        return '#';
                    })
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->preview_token)),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (PageComponent $record) {
                        $duplicate = $record->replicate();
                        $duplicate->position = $record->position + 1;
                        $duplicate->version = '1.0';
                        $duplicate->is_draft = true;
                        $duplicate->published_at = null;
                        $duplicate->preview_token = null;
                        $duplicate->save();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Componente duplicado')
                            ->success()
                            ->send();
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
                    Tables\Actions\BulkAction::make('enable_cache')
                        ->label('Habilitar cache')
                        ->icon('heroicon-o-bolt')
                        ->action(fn ($records) => $records->each->update(['cache_enabled' => true]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position')
            ->groupedBulkActions();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageComponents::route('/'),
            'create' => Pages\CreatePageComponent::route('/create'),
            'edit' => Pages\EditPageComponent::route('/{record}/edit'),
        ];
    }
}