<?php

namespace App\Filament\Resources\PlantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlantGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'plantGroups';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Grupo')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('custom_label')
                    ->label('Etiqueta Personalizada')
                    ->maxLength(255),
                
                Forms\Components\Select::make('user_id')
                    ->label('Usuario Propietario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Dejar vacío para grupo colectivo'),
                
                Forms\Components\TextInput::make('number_of_plants')
                    ->label('Número de Plantas')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                
                Forms\Components\TextInput::make('co2_avoided_total')
                    ->label('CO2 Evitado Total (kg)')
                    ->required()
                    ->numeric()
                    ->step(0.0001)
                    ->minValue(0)
                    ->default(0),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nombre del Grupo')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Propietario')
                    ->placeholder('Grupo Colectivo')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('number_of_plants')
                    ->label('Plantas')
                    ->numeric()
                    ->suffix(' plantas'),
                
                Tables\Columns\TextColumn::make('co2_avoided_total')
                    ->label('CO2 Evitado')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->suffix(' kg')
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
                    ->sortable(),
            ])
            ->filters([
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
