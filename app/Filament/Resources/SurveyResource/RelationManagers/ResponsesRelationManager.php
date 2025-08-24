<?php

namespace App\Filament\Resources\SurveyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Respuestas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Dejar vacío para respuesta anónima'),
                
                Forms\Components\KeyValue::make('response_data')
                    ->label('Datos de Respuesta')
                    ->keyLabel('Campo')
                    ->valueLabel('Valor')
                    ->addActionLabel('Agregar Campo')
                    ->deleteActionLabel('Eliminar Campo')
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('respondent_name')
                    ->label('Respondente')
                    ->getStateUsing(fn ($record) => $record->respondent_name)
                    ->searchable()
                    ->badge()
                    ->color(fn ($record) => $record->isAnonymous() ? 'warning' : 'success'),
                
                Tables\Columns\BadgeColumn::make('response_type')
                    ->label('Tipo')
                    ->getStateUsing(fn ($record) => $record->response_type)
                    ->getColorUsing(fn ($record) => $record->isAnonymous() ? 'warning' : 'info'),
                
                Tables\Columns\TextColumn::make('response_field_count')
                    ->label('Campos')
                    ->getStateUsing(fn ($record) => $record->response_field_count)
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('time_since_response')
                    ->label('Respondida')
                    ->getStateUsing(fn ($record) => $record->time_since_response)
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('response_type')
                    ->label('Tipo de Respuesta')
                    ->options([
                        'anonymous' => 'Anónima',
                        'identified' => 'Identificada'
                    ]),
                
                Tables\Filters\TernaryFilter::make('user_id')
                    ->label('Usuario')
                    ->placeholder('Todas')
                    ->trueLabel('Solo identificadas')
                    ->falseLabel('Solo anónimas'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar Respuesta'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
