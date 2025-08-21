<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeatherSnapshotResource\Pages;
use App\Filament\Resources\WeatherSnapshotResource\RelationManagers;
use App\Models\WeatherSnapshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WeatherSnapshotResource extends Resource
{
    protected static ?string $model = WeatherSnapshot::class;

    protected static ?string $navigationIcon = 'heroicon-o-cloud';
    
    protected static ?string $navigationGroup = 'Infraestructura';
    
    protected static ?string $modelLabel = 'Dato Meteorológico';
    
    protected static ?string $pluralModelLabel = 'Datos Meteorológicos';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('municipality_id')
                    ->relationship('municipality', 'name')
                    ->required(),
                Forms\Components\TextInput::make('temperature')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('cloud_coverage')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('solar_radiation')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('timestamp')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('municipality.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('temperature')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cloud_coverage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('solar_radiation')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timestamp')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListWeatherSnapshots::route('/'),
            'create' => Pages\CreateWeatherSnapshot::route('/create'),
            'edit' => Pages\EditWeatherSnapshot::route('/{record}/edit'),
        ];
    }
}
