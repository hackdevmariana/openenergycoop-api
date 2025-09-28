<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SustainabilityMetricResource\Pages;
use App\Filament\Resources\SustainabilityMetricResource\RelationManagers;
use App\Models\SustainabilityMetric;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SustainabilityMetricResource extends Resource
{
    protected static ?string $model = SustainabilityMetric::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Analytics y Métricas';

    protected static ?string $navigationLabel = 'Métricas de Sostenibilidad';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('metric_name')
                    ->label('Nombre de la Métrica')
                    ->required(),

                Forms\Components\TextInput::make('metric_code')
                    ->label('Código')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('metric_type')
                    ->label('Tipo')
                    ->required()
                    ->options([
                        'carbon_footprint' => 'Huella de Carbono',
                        'renewable_percentage' => 'Porcentaje Renovable',
                        'energy_efficiency' => 'Eficiencia Energética',
                    ]),

                Forms\Components\TextInput::make('value')
                    ->label('Valor')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('unit')
                    ->label('Unidad')
                    ->required(),

                Forms\Components\DatePicker::make('measurement_date')
                    ->label('Fecha de Medición')
                    ->required()
                    ->native(false),

                Forms\Components\Toggle::make('is_certified')
                    ->label('Certificado'),

                Forms\Components\Select::make('energy_cooperative_id')
                    ->label('Cooperativa')
                    ->relationship('energyCooperative', 'name')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('metric_name')
                    ->label('Métrica')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(fn ($record) => $record->formatValueWithUnit()),
                    
                Tables\Columns\BadgeColumn::make('metric_type')
                    ->label('Tipo'),
                    
                Tables\Columns\IconColumn::make('is_certified')
                    ->label('Certificado')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('measurement_date')
                    ->label('Fecha')
                    ->date('d/m/Y'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListSustainabilityMetrics::route('/'),
            'create' => Pages\CreateSustainabilityMetric::route('/create'),
            'edit' => Pages\EditSustainabilityMetric::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $totalCount = static::getModel()::count();
        $criticalAlertsCount = static::getModel()::where('alert_status', 'critical')->count();
        $certifiedCount = static::getModel()::where('is_certified', true)->count();
        $improvingTrendCount = static::getModel()::where('trend', 'improving')->count();
        
        if ($criticalAlertsCount > 0) {
            return 'danger';
        } elseif ($certifiedCount > 20) {
            return 'success';
        } elseif ($improvingTrendCount > 30) {
            return 'info';
        } elseif ($totalCount > 50) {
            return 'warning';
        } else {
            return 'gray';
        }
    }
}
