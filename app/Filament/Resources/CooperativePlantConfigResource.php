<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CooperativePlantConfigResource\Pages;
use App\Filament\Resources\CooperativePlantConfigResource\RelationManagers;
use App\Models\CooperativePlantConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class CooperativePlantConfigResource extends Resource
{
    protected static ?string $model = CooperativePlantConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $modelLabel = 'Configuración de Plantas';
    
    protected static ?string $pluralModelLabel = 'Configuraciones de Plantas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de Cooperativa')
                    ->schema([
                        Forms\Components\Select::make('cooperative_id')
                            ->label('Cooperativa Energética')
                            ->relationship('cooperative', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(3),
                            ]),
                        
                        Forms\Components\Select::make('plant_id')
                            ->label('Tipo de Planta')
                            ->relationship('plant', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required(),
                                Forms\Components\TextInput::make('unit_label')
                                    ->label('Etiqueta de Unidad')
                                    ->required(),
                                Forms\Components\TextInput::make('co2_equivalent_per_unit_kg')
                                    ->label('CO2 por Unidad (kg)')
                                    ->numeric()
                                    ->required(),
                            ]),
                        
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Sin organización específica')
                            ->helperText('Opcional: asociar a una organización específica'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Configuración de Estado')
                    ->schema([
                        Forms\Components\Toggle::make('default')
                            ->label('Planta por Defecto')
                            ->helperText('Marcar como planta por defecto para esta cooperativa')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('active', true);
                                }
                            }),
                        
                        Forms\Components\Toggle::make('active')
                            ->label('Activa')
                            ->default(true)
                            ->helperText('Determina si esta configuración está activa')
                            ->disabled(fn (callable $get) => $get('default')),
                        
                        Forms\Components\Placeholder::make('info')
                            ->label('Información')
                            ->content('Solo puede haber una planta por defecto por cooperativa. Si marcas esta como por defecto, se desactivará automáticamente la configuración anterior.')
                            ->columnSpan(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cooperative.name')
                    ->label('Cooperativa')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('plant.name')
                    ->label('Planta')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin organización')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('default')
                    ->label('Por Defecto')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cooperative_id')
                    ->label('Cooperativa')
                    ->relationship('cooperative', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('plant_id')
                    ->label('Planta')
                    ->relationship('plant', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todas las organizaciones'),
                
                Tables\Filters\TernaryFilter::make('default')
                    ->label('Por Defecto')
                    ->placeholder('Todas las configuraciones')
                    ->trueLabel('Solo por defecto')
                    ->falseLabel('No por defecto'),
                
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Estado')
                    ->placeholder('Todas las configuraciones')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('set_as_default')
                    ->label('Marcar como Por Defecto')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (CooperativePlantConfig $record): bool => !$record->default)
                    ->action(function (CooperativePlantConfig $record): void {
                        $record->setAsDefault();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Marcar como Planta por Defecto')
                    ->modalDescription('¿Estás seguro de que quieres marcar esta planta como por defecto para la cooperativa? Esto desactivará la configuración anterior.')
                    ->modalSubmitActionLabel('Marcar como Por Defecto'),
                
                Tables\Actions\Action::make('remove_default')
                    ->label('Quitar Por Defecto')
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn (CooperativePlantConfig $record): bool => $record->default)
                    ->action(function (CooperativePlantConfig $record): void {
                        $record->removeDefault();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Quitar Planta por Defecto')
                    ->modalDescription('¿Estás seguro de que quieres quitar esta planta como por defecto?')
                    ->modalSubmitActionLabel('Quitar Por Defecto'),
                
                Tables\Actions\Action::make('toggle_active')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-toggle-right')
                    ->color('warning')
                    ->visible(fn (CooperativePlantConfig $record): bool => !$record->default)
                    ->action(function (CooperativePlantConfig $record): void {
                        $record->toggleActive();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cambiar Estado de la Configuración')
                    ->modalDescription('¿Estás seguro de que quieres cambiar el estado de esta configuración?')
                    ->modalSubmitActionLabel('Cambiar Estado'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar Seleccionadas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->activate();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Activar Configuraciones')
                        ->modalDescription('¿Estás seguro de que quieres activar las configuraciones seleccionadas?'),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar Seleccionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): bool {
                            $records->each(function ($record) {
                                if (!$record->default) {
                                    $record->deactivate();
                                }
                            });
                            return true;
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Desactivar Configuraciones')
                        ->modalDescription('¿Estás seguro de que quieres desactivar las configuraciones seleccionadas? Solo se desactivarán las que no sean por defecto.'),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('cooperative_id')
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CooperativeRelationManager::class,
            RelationManagers\PlantRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCooperativePlantConfigs::route('/'),
            'create' => Pages\CreateCooperativePlantConfig::route('/create'),
            'edit' => Pages\EditCooperativePlantConfig::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $activeCount = static::getModel()::where('active', true)->count();
        $totalCount = static::getModel()::count();
        
        if ($totalCount === 0) {
            return 'gray';         // ⚫ Gris cuando no hay configuraciones
        }
        
        if ($activeCount === $totalCount) {
            return 'success';      // 🟢 Verde cuando todas las configuraciones están activas
        }
        
        $defaultCount = static::getModel()::where('default', true)->count();
        
        if ($defaultCount > 0) {
            return 'info';         // 🔵 Azul cuando hay configuraciones por defecto
        }
        
        $inactiveCount = $totalCount - $activeCount;
        
        if ($inactiveCount > $activeCount) {
            return 'danger';       // 🔴 Rojo cuando hay más configuraciones inactivas que activas
        }
        
        return 'warning';          // 🟡 Naranja cuando hay algunas configuraciones inactivas
    }
}
