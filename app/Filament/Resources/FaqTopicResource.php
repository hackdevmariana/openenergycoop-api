<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqTopicResource\Pages;
use App\Models\FaqTopic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class FaqTopicResource extends Resource
{
    protected static ?string $model = FaqTopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    protected static ?string $navigationGroup = 'Soporte y Encuestas';
    
    protected static ?string $navigationLabel = 'Temas de FAQ';
    
    protected static ?string $modelLabel = 'Tema';
    
    protected static ?string $pluralModelLabel = 'Temas';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Tema')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Tema')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                            
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(FaqTopic::class, 'slug', ignoreRecord: true)
                            ->helperText('URL amigable para este tema'),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Descripción del tema de FAQ'),
                    ])->columns(2),
                    
                Section::make('Configuración')
                    ->schema([
                        Forms\Components\ColorPicker::make('color')
                            ->label('Color')
                            ->helperText('Color distintivo para este tema'),
                            
                        Forms\Components\TextInput::make('icon')
                            ->label('Icono')
                            ->placeholder('heroicon-o-question-mark-circle')
                            ->helperText('Clase CSS del icono (Heroicons)'),
                            
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición (menor número = mayor prioridad)'),
                    ])->columns(3),
                    
                Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->helperText('Solo los temas activos se muestran')
                            ->default(true),
                    ]),
                    
                Section::make('Organización')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Global (todas las organizaciones)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('icon_display')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->icon ?: 'heroicon-o-folder')
                    ->color(fn ($record) => $record->color ? 'primary' : 'gray'),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->color('primary')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('faqs_count')
                    ->label('FAQs')
                    ->counts('faqs')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('secondary'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->placeholder('Global')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
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
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('view_faqs')
                    ->label('Ver FAQs')
                    ->icon('heroicon-o-question-mark-circle')
                    ->url(fn ($record) => FaqResource::getUrl('index', ['tableFilters[topic_id][value]' => $record->id]))
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqTopics::route('/'),
            'create' => Pages\CreateFaqTopic::route('/create'),
            'edit' => Pages\EditFaqTopic::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count() ?: null;
    }
}