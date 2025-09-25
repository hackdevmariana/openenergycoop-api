<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyReadingResource\Pages;
use App\Models\EnergyReading;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class EnergyReadingResource extends Resource
{
    protected static ?string $model = EnergyReading::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Consumo de Energía';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Lecturas de Energía';

    protected static ?string $modelLabel = 'Lectura de Energía';

    protected static ?string $pluralModelLabel = 'Lecturas de Energía';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $model = static::getModel();
        $total = $model::count();
        
        if ($total >= 100) {
            return 'success';
        } elseif ($total >= 50) {
            return 'warning';
        } elseif ($total >= 10) {
            return 'info';
        } else {
            return 'gray';
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        TextInput::make('reading_number')
                            ->label('Número de Lectura')
                            ->required(),
                        TextInput::make('reading_value')
                            ->label('Valor de Lectura')
                            ->numeric()
                            ->required(),
                        Select::make('reading_type')
                            ->label('Tipo de Lectura')
                            ->options([
                                'instantaneous' => 'Instantáneo',
                                'interval' => 'Intervalo',
                                'cumulative' => 'Acumulativo',
                                'demand' => 'Demanda',
                                'energy' => 'Energía',
                            ])
                            ->required(),
                        DateTimePicker::make('reading_timestamp')
                            ->label('Timestamp de Lectura')
                            ->required()
                            ->default(now()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reading_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reading_value')
                    ->label('Valor')
                    ->numeric()
                    ->sortable(),
                BadgeColumn::make('reading_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'instantaneous',
                        'success' => 'interval',
                        'warning' => 'cumulative',
                        'danger' => 'demand',
                        'info' => 'energy',
                    ]),
                TextColumn::make('reading_timestamp')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
            'index' => Pages\ListEnergyReadings::route('/'),
            'create' => Pages\CreateEnergyReading::route('/create'),
            'edit' => Pages\EditEnergyReading::route('/{record}/edit'),
        ];
    }
}