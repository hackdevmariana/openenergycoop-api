<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlantGroupResource\Pages;
use App\Filament\Resources\PlantGroupResource\RelationManagers;
use App\Models\PlantGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class PlantGroupResource extends Resource
{
    protected static ?string $model = PlantGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $modelLabel = 'Grupo de Plantas';
    
    protected static ?string $pluralModelLabel = 'Grupos de Plantas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Grupo')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Grupo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej. Mi viña solar, Pinar cooperativo'),
                        
                        Forms\Components\TextInput::make('custom_label')
                            ->label('Etiqueta Personalizada')
                            ->maxLength(255)
                            ->placeholder('ej. Mi huerta solar, Pinar cooperativo')
                            ->helperText('Opcional: sobrescribe el nombre para mostrar'),
                        
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
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario Propietario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Dejar vacío para grupo colectivo')
                            ->helperText('Si se deja vacío, será un grupo colectivo'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Plantas y CO2')
                    ->schema([
                        Forms\Components\TextInput::make('number_of_plants')
                            ->label('Número de Plantas')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('plantas'),
                        
                        Forms\Components\TextInput::make('co2_avoided_total')
                            ->label('CO2 Evitado Total (kg)')
                            ->required()
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0)
                            ->default(0)
                            ->suffix('kg CO2')
                            ->helperText('Total de CO2 evitado por este grupo'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Determina si el grupo está activo'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nombre del Grupo')
                    ->searchable()
                    ->sortable()
                    ->description(fn (PlantGroup $record): string => $record->custom_label ? 'Etiqueta personalizada' : ''),
                
                Tables\Columns\TextColumn::make('plant.name')
                    ->label('Tipo de Planta')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Propietario')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Grupo Colectivo')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('number_of_plants')
                    ->label('Plantas')
                    ->numeric()
                    ->sortable()
                    ->suffix(' plantas'),
                
                Tables\Columns\TextColumn::make('co2_avoided_total')
                    ->label('CO2 Evitado')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->suffix(' kg')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plant_id')
                    ->label('Tipo de Planta')
                    ->relationship('plant', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Propietario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos los grupos'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos los grupos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                
                Tables\Filters\TernaryFilter::make('user_id')
                    ->label('Tipo de Grupo')
                    ->placeholder('Todos los grupos')
                    ->trueLabel('Solo individuales')
                    ->falseLabel('Solo colectivos'),
                
                Tables\Filters\Filter::make('co2_range')
                    ->label('Rango de CO2 Evitado')
                    ->form([
                        Forms\Components\TextInput::make('min_co2')
                            ->label('CO2 Mínimo (kg)')
                            ->numeric()
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('max_co2')
                            ->label('CO2 Máximo (kg)')
                            ->numeric()
                            ->placeholder('1000'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_co2'],
                                fn (Builder $query, $minCo2): Builder => $query->where('co2_avoided_total', '>=', $minCo2),
                            )
                            ->when(
                                $data['max_co2'],
                                fn (Builder $query, $maxCo2): Builder => $query->where('co2_avoided_total', '<=', $maxCo2),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add_plants')
                    ->label('Añadir Plantas')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('count')
                            ->label('Cantidad de Plantas')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('co2_avoided')
                            ->label('CO2 Evitado (kg)')
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0)
                            ->helperText('Dejar vacío para cálculo automático'),
                    ])
                    ->action(function (PlantGroup $record, array $data): void {
                        $record->addPlants($data['count'], $data['co2_avoided'] ?? null);
                    })
                    ->modalHeading('Añadir Plantas al Grupo')
                    ->modalSubmitActionLabel('Añadir Plantas'),
                
                Tables\Actions\Action::make('remove_plants')
                    ->label('Quitar Plantas')
                    ->icon('heroicon-o-minus')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('count')
                            ->label('Cantidad de Plantas')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('co2_avoided')
                            ->label('CO2 Evitado (kg)')
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0)
                            ->helperText('Dejar vacío para cálculo automático'),
                    ])
                    ->action(function (PlantGroup $record, array $data): void {
                        $record->removePlants($data['count'], $data['co2_avoided'] ?? null);
                    })
                    ->modalHeading('Quitar Plantas del Grupo')
                    ->modalSubmitActionLabel('Quitar Plantas'),
                
                Tables\Actions\Action::make('toggle_active')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-toggle-right')
                    ->color('warning')
                    ->action(function (PlantGroup $record): void {
                        $record->toggleActive();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cambiar Estado del Grupo')
                    ->modalDescription('¿Estás seguro de que quieres cambiar el estado de este grupo?')
                    ->modalSubmitActionLabel('Cambiar Estado'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar Seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->activate();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Activar Grupos')
                        ->modalDescription('¿Estás seguro de que quieres activar los grupos seleccionados?'),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar Seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->deactivate();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Desactivar Grupos')
                        ->modalDescription('¿Estás seguro de que quieres desactivar los grupos seleccionados?'),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UserRelationManager::class,
            RelationManagers\PlantRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlantGroups::route('/'),
            'create' => Pages\CreatePlantGroup::route('/create'),
            'edit' => Pages\EditPlantGroup::route('/{record}/edit'),
        ];
    }
}
