<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlantResource\Pages;
use App\Filament\Resources\PlantResource\RelationManagers;
use App\Models\Plant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class PlantResource extends Resource
{
    protected static ?string $model = Plant::class;

    protected static ?string $navigationIcon = 'bi-leaf';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $modelLabel = 'Planta';
    
    protected static ?string $pluralModelLabel = 'Plantas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Planta')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej. Pino, Vid, Plátano'),
                        
                        Forms\Components\TextInput::make('unit_label')
                            ->label('Etiqueta de Unidad')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej. árbol, planta, viña'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Descripción detallada de la planta'),
                        
                        Forms\Components\TextInput::make('co2_equivalent_per_unit_kg')
                            ->label('CO2 Equivalente por Unidad (kg)')
                            ->required()
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0)
                            ->suffix('kg CO2'),
                        
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagen')
                            ->image()
                            ->directory('plants')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true)
                            ->helperText('Determina si la planta está disponible para uso'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('unit_label')
                    ->label('Unidad')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('co2_equivalent_per_unit_kg')
                    ->label('CO2 por Unidad')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->suffix(' kg')
                    ->sortable(),
                
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagen')
                    ->circular()
                    ->size(40),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
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
                Tables\Filters\SelectFilter::make('unit_label')
                    ->label('Tipo de Unidad')
                    ->options([
                        'árbol' => 'Árbol',
                        'planta' => 'Planta',
                        'viña' => 'Viña',
                        'arbusto' => 'Arbusto',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todas las plantas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
                
                Tables\Filters\Filter::make('co2_range')
                    ->label('Rango de CO2')
                    ->form([
                        Forms\Components\TextInput::make('min_co2')
                            ->label('CO2 Mínimo (kg)')
                            ->numeric()
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('max_co2')
                            ->label('CO2 Máximo (kg)')
                            ->numeric()
                            ->placeholder('100'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_co2'],
                                fn (Builder $query, $minCo2): Builder => $query->where('co2_equivalent_per_unit_kg', '>=', $minCo2),
                            )
                            ->when(
                                $data['max_co2'],
                                fn (Builder $query, $maxCo2): Builder => $query->where('co2_equivalent_per_unit_kg', '<=', $maxCo2),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label('Cambiar Estado')
                    ->icon('fluentui-toggle-right-16-o')
                    ->color('warning')
                    ->action(function (Plant $record): void {
                        $record->toggleActive();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cambiar Estado de la Planta')
                    ->modalDescription('¿Estás seguro de que quieres cambiar el estado de esta planta?')
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
                        ->modalHeading('Activar Plantas')
                        ->modalDescription('¿Estás seguro de que quieres activar las plantas seleccionadas?'),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar Seleccionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->deactivate();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Desactivar Plantas')
                        ->modalDescription('¿Estás seguro de que quieres desactivar las plantas seleccionadas?'),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PlantGroupsRelationManager::class,
            RelationManagers\CooperativeConfigsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlants::route('/'),
            'create' => Pages\CreatePlant::route('/create'),
            'edit' => Pages\EditPlant::route('/{record}/edit'),
        ];
    }
}
