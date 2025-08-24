<?php

namespace App\Filament\Resources\PlantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CooperativeConfigsRelationManager extends RelationManager
{
    protected static string $relationship = 'cooperativeConfigs';

    public function form(Form $form): Form
    {
        return $form
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
                
                Forms\Components\Select::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin organización específica'),
                
                Forms\Components\Toggle::make('default')
                    ->label('Planta por Defecto')
                    ->helperText('Marcar como planta por defecto para esta cooperativa'),
                
                Forms\Components\Toggle::make('active')
                    ->label('Activa')
                    ->default(true)
                    ->helperText('Determina si esta configuración está activa'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cooperative_id')
            ->columns([
                Tables\Columns\TextColumn::make('cooperative.name')
                    ->label('Cooperativa')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
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
                    ->sortable(),
            ])
            ->filters([
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
            ->defaultSort('cooperative_id');
    }
}
