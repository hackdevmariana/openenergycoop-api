<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyResponseResource\Pages;
use App\Filament\Resources\SurveyResponseResource\RelationManagers;
use App\Models\SurveyResponse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SurveyResponseResource extends Resource
{
    protected static ?string $model = SurveyResponse::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    
    protected static ?string $navigationGroup = 'Encuestas';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $modelLabel = 'Respuesta de Encuesta';
    
    protected static ?string $pluralModelLabel = 'Respuestas de Encuestas';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        
        return match (true) {
            $count >= 200 => 'success',
            $count >= 100 => 'warning',
            $count >= 50 => 'info',
            default => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Respuesta')
                    ->schema([
                        Forms\Components\Select::make('survey_id')
                            ->label('Encuesta')
                            ->relationship('survey', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $survey = \App\Models\Survey::find($state);
                                    if ($survey && !$survey->allowsAnonymous()) {
                                        $set('user_id', null);
                                    }
                                }
                            }),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Dejar vacío para respuesta anónima')
                            ->visible(function (callable $get) {
                                $surveyId = $get('survey_id');
                                if (!$surveyId) return true;
                                
                                $survey = \App\Models\Survey::find($surveyId);
                                return $survey ? $survey->allowsAnonymous() : true;
                            }),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Datos de la Respuesta')
                    ->schema([
                        Forms\Components\KeyValue::make('response_data')
                            ->label('Datos de Respuesta')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->addActionLabel('Agregar Campo')
                            ->deleteActionLabel('Eliminar Campo')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('survey.title')
                    ->label('Encuesta')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->survey->description ?? 'Sin descripción';
                    }),
                
                Tables\Columns\TextColumn::make('respondent_name')
                    ->label('Respondente')
                    ->getStateUsing(fn ($record) => $record->respondent_name)
                    ->searchable()
                    ->badge()
                    ->color(fn ($record) => $record->isAnonymous() ? 'warning' : 'success'),
                
                Tables\Columns\BadgeColumn::make('response_type')
                    ->label('Tipo')
                    ->getStateUsing(fn ($record) => $record->response_type)
                    ->color(fn ($record) => $record->isAnonymous() ? 'warning' : 'info'),
                
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('survey_id')
                    ->label('Encuesta')
                    ->relationship('survey', 'title')
                    ->searchable()
                    ->preload(),
                
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
                
                Tables\Filters\Filter::make('date_range')
                    ->label('Rango de Fechas')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('to')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\Action::make('view_survey')
                    ->label('Ver Encuesta')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn ($record) => route('filament.admin.resources.surveys.edit', $record->survey_id))
                    ->color('info'),
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
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyResponses::route('/'),
            'create' => Pages\CreateSurveyResponse::route('/create'),
            'edit' => Pages\EditSurveyResponse::route('/{record}/edit'),
        ];
    }
}
