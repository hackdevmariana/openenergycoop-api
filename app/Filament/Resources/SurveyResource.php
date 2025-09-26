<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyResource\Pages;
use App\Filament\Resources\SurveyResource\RelationManagers;
use App\Models\Survey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = 'Encuestas';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $modelLabel = 'Encuesta';
    
    protected static ?string $pluralModelLabel = 'Encuestas';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        
        return match (true) {
            $count >= 50 => 'success',
            $count >= 20 => 'warning',
            $count >= 10 => 'info',
            default => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Encuesta de Satisfacción del Cliente'),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(4)
                            ->placeholder('Describe el propósito y contenido de la encuesta...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Configuración de Fechas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(now())
                            ->helperText('Cuándo comenzará a estar disponible la encuesta'),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fecha de Fin')
                            ->required()
                            ->default(now()->addDays(30))
                            ->helperText('Cuándo dejará de estar disponible la encuesta')
                            ->after('starts_at'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Configuración de Respuestas')
                    ->schema([
                        Forms\Components\Toggle::make('anonymous_allowed')
                            ->label('Permitir Respuestas Anónimas')
                            ->helperText('Los usuarios pueden responder sin identificarse')
                            ->default(true),
                        Forms\Components\Toggle::make('visible_results')
                            ->label('Resultados Visibles')
                            ->helperText('Los usuarios pueden ver los resultados de la encuesta')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->description;
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->getStateUsing(fn ($record) => $record->status)
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'draft' => 'info',
                        'completed' => 'primary',
                        'expired' => 'warning',
                        'cancelled' => 'danger',
                        default => 'secondary'
                    }),
                
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('danger'),
                
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duración')
                    ->getStateUsing(fn ($record) => $record->duration)
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('anonymous_allowed')
                    ->label('Anónimas')
                    ->boolean()
                    ->trueIcon('heroicon-o-user-group')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('success')
                    ->falseColor('warning'),
                
                Tables\Columns\IconColumn::make('visible_results')
                    ->label('Resultados')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('responses_count')
                    ->label('Respuestas')
                    ->counts('responses')
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'active' => 'Activa',
                        'completed' => 'Completada',
                        'expired' => 'Expirada',
                        'cancelled' => 'Cancelada'
                    ]),
                
                Tables\Filters\TernaryFilter::make('anonymous_allowed')
                    ->label('Respuestas Anónimas')
                    ->placeholder('Todas')
                    ->trueLabel('Solo permitidas')
                    ->falseLabel('Solo identificadas'),
                
                Tables\Filters\TernaryFilter::make('visible_results')
                    ->label('Resultados Visibles')
                    ->placeholder('Todas')
                    ->trueLabel('Solo visibles')
                    ->falseLabel('Solo ocultos'),
                
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
                                fn (Builder $query, $date): Builder => $query->whereDate('starts_at', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('ends_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\Action::make('responses')
                    ->label('Ver Respuestas')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn ($record) => route('filament.admin.resources.survey-responses.index', ['tableFilters[survey_id][value]' => $record->id]))
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
            RelationManagers\ResponsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveys::route('/'),
            'create' => Pages\CreateSurvey::route('/create'),
            'edit' => Pages\EditSurvey::route('/{record}/edit'),
        ];
    }
}
