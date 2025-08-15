<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollaboratorResource\Pages;
use App\Models\Collaborator;
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

class CollaboratorResource extends Resource
{
    protected static ?string $model = Collaborator::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    
    protected static ?string $navigationGroup = 'Información Corporativa';
    
    protected static ?string $navigationLabel = 'Colaboradores';
    
    protected static ?string $modelLabel = 'Colaborador';
    
    protected static ?string $pluralModelLabel = 'Colaboradores';
    
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
                                Section::make('Datos del Colaborador')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),
                                            
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->rows(3)
                                            ->maxLength(500),
                                            
                                        Forms\Components\TextInput::make('website_url')
                                            ->label('Sitio Web')
                                            ->url()
                                            ->placeholder('https://ejemplo.com'),
                                    ])->columns(2),
                                    
                                Section::make('Logo del Colaborador')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('logo')
                                            ->label('Logo')
                                            ->collection('collaborator_logos')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '1:1',
                                                '16:9',
                                                '4:3',
                                            ])
                                            ->helperText('Logo del colaborador (formato recomendado: PNG con fondo transparente)')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Posicionamiento')
                                    ->schema([
                                        Forms\Components\TextInput::make('position')
                                            ->label('Orden de Aparición')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Orden en que aparece (menor número = mayor prioridad)'),
                                            
                                        Forms\Components\Select::make('category')
                                            ->label('Categoría')
                                            ->options([
                                                'partner' => 'Socio',
                                                'sponsor' => 'Patrocinador',
                                                'supplier' => 'Proveedor',
                                                'client' => 'Cliente',
                                                'institutional' => 'Institucional',
                                                'media' => 'Medios',
                                            ])
                                            ->default('partner'),
                                    ])->columns(2),
                                    
                                Section::make('Estado')
                                    ->schema([
                                        Forms\Components\Toggle::make('active')
                                            ->label('Activo')
                                            ->helperText('Solo los colaboradores activos se muestran públicamente')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('featured')
                                            ->label('Destacado')
                                            ->helperText('Los colaboradores destacados aparecen primero')
                                            ->default(false),
                                    ])->columns(2),
                                    
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
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label('Logo')
                    ->collection('collaborator_logos')
                    ->width(80)
                    ->height(60),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Categoría')
                    ->colors([
                        'primary' => 'partner',
                        'success' => 'sponsor',
                        'warning' => 'supplier',
                        'danger' => 'client',
                        'secondary' => 'institutional',
                        'gray' => 'media',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'partner' => 'Socio',
                        'sponsor' => 'Patrocinador',
                        'supplier' => 'Proveedor',
                        'client' => 'Cliente',
                        'institutional' => 'Institucional',
                        'media' => 'Medios',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('position')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('secondary'),
                    
                Tables\Columns\IconColumn::make('featured')
                    ->label('Destacado')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('website_url')
                    ->label('Web')
                    ->placeholder('Sin web')
                    ->url(fn ($record) => $record->website_url)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
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
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'partner' => 'Socio',
                        'sponsor' => 'Patrocinador',
                        'supplier' => 'Proveedor',
                        'client' => 'Cliente',
                        'institutional' => 'Institucional',
                        'media' => 'Medios',
                    ]),
                    
                TernaryFilter::make('active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                    
                TernaryFilter::make('featured')
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
                Tables\Actions\Action::make('visit_website')
                    ->label('Visitar Web')
                    ->icon('heroicon-o-globe-alt')
                    ->url(fn ($record) => $record->website_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->website_url)),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggle_featured')
                    ->label(fn ($record) => $record->featured ? 'Quitar Destacado' : 'Destacar')
                    ->icon(fn ($record) => $record->featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn ($record) => $record->featured ? 'warning' : 'gray')
                    ->action(fn ($record) => $record->update(['featured' => !$record->featured])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('Destacar seleccionados')
                        ->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->update(['featured' => true]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollaborators::route('/'),
            'create' => Pages\CreateCollaborator::route('/create'),
            'edit' => Pages\EditCollaborator::route('/{record}/edit'),
        ];
    }
}