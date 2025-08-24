<?php

namespace App\Filament\Resources\UserChallengeProgressResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChallengeRelationManager extends RelationManager
{
    protected static string $relationship = 'challenge';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Información del Desafío';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->required()
                    ->rows(3),
                
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->required()
                    ->options([
                        'individual' => 'Individual',
                        'colectivo' => 'Colectivo',
                    ]),
                
                Forms\Components\TextInput::make('goal_kwh')
                    ->label('Meta (kWh)')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->suffix('kWh'),
                
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Fecha de Inicio')
                    ->required(),
                
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('Fecha de Fin')
                    ->required(),
                
                Forms\Components\Select::make('reward_type')
                    ->label('Tipo de Recompensa')
                    ->required()
                    ->options([
                        'symbolic' => 'Simbólica',
                        'energy_donation' => 'Donación Energética',
                        'badge' => 'Insignia',
                    ]),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'individual' => 'info',
                        'colectivo' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => 'Individual',
                        'colectivo' => 'Colectivo',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('goal_kwh')
                    ->label('Meta')
                    ->numeric()
                    ->suffix(' kWh')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reward_type')
                    ->label('Recompensa')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'symbolic' => 'gray',
                        'energy_donation' => 'warning',
                        'badge' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'symbolic' => 'Simbólica',
                        'energy_donation' => 'Donación',
                        'badge' => 'Insignia',
                        default => $state,
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'info',
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'upcoming' => 'Próximo',
                        'draft' => 'Borrador',
                        'active' => 'Activo',
                        'completed' => 'Completado',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'individual' => 'Individual',
                        'colectivo' => 'Colectivo',
                    ]),
                
                Tables\Filters\SelectFilter::make('reward_type')
                    ->label('Tipo de Recompensa')
                    ->options([
                        'symbolic' => 'Simbólica',
                        'energy_donation' => 'Donación Energética',
                        'badge' => 'Insignia',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
