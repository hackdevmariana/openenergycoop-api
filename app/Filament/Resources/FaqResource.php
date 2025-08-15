<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
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

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    
    protected static ?string $navigationGroup = 'Soporte y Ayuda';
    
    protected static ?string $navigationLabel = 'Preguntas Frecuentes';
    
    protected static ?string $modelLabel = 'FAQ';
    
    protected static ?string $pluralModelLabel = 'FAQs';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Pregunta y Respuesta')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Section::make('Pregunta')
                                    ->schema([
                                        Forms\Components\TextInput::make('question')
                                            ->label('Pregunta')
                                            ->required()
                                            ->maxLength(500)
                                            ->columnSpanFull()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                                $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                            
                                        Forms\Components\TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(Faq::class, 'slug', ignoreRecord: true)
                                            ->helperText('URL amigable para esta FAQ')
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Respuesta')
                                    ->schema([
                                        Forms\Components\RichEditor::make('answer')
                                            ->label('Respuesta')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'link',
                                                'bulletList',
                                                'orderedList',
                                                'h2',
                                                'h3',
                                                'blockquote',
                                                'codeBlock',
                                            ])
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make('short_answer')
                                            ->label('Respuesta Corta')
                                            ->rows(3)
                                            ->maxLength(300)
                                            ->helperText('Versión resumida de la respuesta (opcional)')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Categorización')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Section::make('Organización')
                                    ->schema([
                                        Forms\Components\Select::make('topic_id')
                                            ->label('Tema')
                                            ->relationship('topic', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('slug')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('description')
                                                    ->maxLength(500),
                                            ]),
                                            
                                        Forms\Components\TagsInput::make('keywords')
                                            ->label('Palabras Clave')
                                            ->placeholder('Escriba palabras clave...')
                                            ->helperText('Palabras clave para búsqueda interna'),
                                    ])->columns(2),
                                    
                                Section::make('Prioridad y Visibilidad')
                                    ->schema([
                                        Forms\Components\Select::make('priority')
                                            ->label('Prioridad')
                                            ->options([
                                                'low' => 'Baja',
                                                'normal' => 'Normal',
                                                'high' => 'Alta',
                                                'urgent' => 'Urgente',
                                            ])
                                            ->default('normal')
                                            ->required(),
                                            
                                        Forms\Components\TextInput::make('position')
                                            ->label('Orden')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Orden dentro del tema'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Configuración Avanzada')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Estado')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_draft')
                                            ->label('Borrador')
                                            ->helperText('Si está activado, la FAQ no será visible públicamente')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Destacado')
                                            ->helperText('Las FAQs destacadas aparecen primero')
                                            ->default(false),
                                            
                                        Forms\Components\Toggle::make('show_in_search')
                                            ->label('Incluir en Búsqueda')
                                            ->helperText('Incluir en resultados de búsqueda del sitio')
                                            ->default(true),
                                    ])->columns(3),
                                    
                                Section::make('Estadísticas')
                                    ->schema([
                                        Forms\Components\TextInput::make('view_count')
                                            ->label('Visualizaciones')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->helperText('Número de veces vista'),
                                            
                                        Forms\Components\TextInput::make('helpful_count')
                                            ->label('Votos Útiles')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->helperText('Votos positivos de usuarios'),
                                            
                                        Forms\Components\TextInput::make('not_helpful_count')
                                            ->label('Votos No Útiles')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->helperText('Votos negativos de usuarios'),
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
                Tables\Columns\TextColumn::make('question')
                    ->label('Pregunta')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->limit(80),
                    
                Tables\Columns\TextColumn::make('topic.name')
                    ->label('Tema')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'primary' => 'normal',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'low' => 'Baja',
                        'normal' => 'Normal',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('position')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('secondary'),
                    
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Vistas')
                    ->numeric()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('helpful_rate')
                    ->label('% Útil')
                    ->getStateUsing(function ($record) {
                        $total = $record->helpful_count + $record->not_helpful_count;
                        if ($total == 0) return '—';
                        return round(($record->helpful_count / $total) * 100, 1) . '%';
                    })
                    ->alignCenter()
                    ->color('success')
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('is_draft')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn ($record) => $record->is_draft ? 'Borrador' : 'Publicado'),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->placeholder('Global')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('topic_id')
                    ->label('Tema')
                    ->relationship('topic', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'low' => 'Baja',
                        'normal' => 'Normal',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                    ]),
                    
                TernaryFilter::make('is_draft')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo borradores')
                    ->falseLabel('Solo publicados'),
                    
                TernaryFilter::make('is_featured')
                    ->label('Destacados')
                    ->placeholder('Todos')
                    ->trueLabel('Solo destacados')
                    ->falseLabel('Solo no destacados'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_public')
                    ->label('Ver Público')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('faq.show', $record->slug))
                    ->openUrlInNewTab()
                    ->color('primary'),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggle_featured')
                    ->label(fn ($record) => $record->is_featured ? 'Quitar Destacado' : 'Destacar')
                    ->icon(fn ($record) => $record->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn ($record) => $record->is_featured ? 'warning' : 'gray')
                    ->action(fn ($record) => $record->update(['is_featured' => !$record->is_featured])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publicar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_draft' => false, 'published_at' => now()]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('Destacar seleccionados')
                        ->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_draft', false)->count() ?: null;
    }
}