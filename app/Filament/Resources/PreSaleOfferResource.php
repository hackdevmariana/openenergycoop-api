<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PreSaleOfferResource\Pages;
use App\Filament\Resources\PreSaleOfferResource\RelationManagers;
use App\Models\PreSaleOffer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PreSaleOfferResource extends Resource
{
    protected static ?string $model = PreSaleOffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                Forms\Components\Select::make('production_project_id')
                    ->relationship('productionProject', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('total_units_available')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('units_reserved')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('price_per_unit')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('reservation_amount')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\TextInput::make('visibility')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organization.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('productionProject.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_units_available')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('units_reserved')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_unit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservation_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('visibility'),
                Tables\Columns\TextColumn::make('status'),
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
            'index' => Pages\ListPreSaleOffers::route('/'),
            'create' => Pages\CreatePreSaleOffer::route('/create'),
            'edit' => Pages\EditPreSaleOffer::route('/{record}/edit'),
        ];
    }
}
