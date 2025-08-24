<?php

namespace App\Filament\Resources\EnergyChallengeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'userProgress';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Progreso de Usuarios';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\TextInput::make('progress_kwh')
                    ->label('Progreso (kWh)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->suffix('kWh'),
                
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Fecha de Completado')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('progress_kwh')
                    ->label('Progreso')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->suffix(' kWh')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('% Progreso')
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('remaining_kwh')
                    ->label('Restante')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->suffix(' kWh')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Días Restantes')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('En progreso'),
                
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Estado')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isCompleted())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('completed_at')
                    ->label('Completado'),
                
                Tables\Filters\Filter::make('progress_range')
                    ->label('Rango de Progreso')
                    ->form([
                        Forms\Components\TextInput::make('min_progress')
                            ->label('Progreso mínimo (kWh)')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_progress')
                            ->label('Progreso máximo (kWh)')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min_progress'], fn (Builder $query, $min) => $query->where('progress_kwh', '>=', $min))
                            ->when($data['max_progress'], fn (Builder $query, $max) => $query->where('progress_kwh', '<=', $max));
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('update_progress')
                    ->label('Actualizar Progreso')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Forms\Components\TextInput::make('additional_kwh')
                            ->label('kWh adicionales')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix('kWh'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->updateProgress($data['additional_kwh']);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('progress_kwh', 'desc');
    }
}
