<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerformanceIndicatorResource\Pages;
use App\Filament\Resources\PerformanceIndicatorResource\RelationManagers;
use App\Models\PerformanceIndicator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerformanceIndicatorResource extends Resource
{
    protected static ?string $model = PerformanceIndicator::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'Analytics y Reportes';

    protected static ?string $navigationLabel = 'Indicadores de Rendimiento';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('indicator_name')
                    ->label('Nombre del Indicador')
                    ->required(),

                Forms\Components\TextInput::make('indicator_code')
                    ->label('Código')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('indicator_type')
                    ->label('Tipo')
                    ->required()
                    ->options([
                        'kpi' => 'KPI',
                        'metric' => 'Métrica',
                        'efficiency' => 'Eficiencia',
                        'quality' => 'Calidad',
                    ]),

                Forms\Components\TextInput::make('current_value')
                    ->label('Valor Actual')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('target_value')
                    ->label('Valor Objetivo')
                    ->numeric(),

                Forms\Components\Select::make('criticality')
                    ->label('Criticidad')
                    ->options([
                        'low' => 'Baja',
                        'medium' => 'Media',
                        'high' => 'Alta',
                        'critical' => 'Crítica',
                    ])
                    ->default('medium'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),

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
                Tables\Columns\TextColumn::make('indicator_name')
                    ->label('Indicador')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('current_value')
                    ->label('Valor Actual')
                    ->formatStateUsing(fn ($record) => $record->formatValueWithUnit()),
                    
                Tables\Columns\BadgeColumn::make('criticality')
                    ->label('Criticidad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'gray' => 'critical',
                    ]),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
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
            'index' => Pages\ListPerformanceIndicators::route('/'),
            'create' => Pages\CreatePerformanceIndicator::route('/create'),
            'edit' => Pages\EditPerformanceIndicator::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $totalCount = static::getModel()::count();
        $criticalCount = static::getModel()::where('criticality', 'critical')->count();
        $activeCount = static::getModel()::where('is_active', true)->count();
        $alertCount = static::getModel()::where('current_alert_level', 'critical')->count();

        if ($alertCount > 0) {
            return 'danger';
        } elseif ($criticalCount > 10) {
            return 'warning';
        } elseif ($activeCount > 70) {
            return 'success';
        } elseif ($totalCount > 50) {
            return 'info';
        } else {
            return 'gray';
        }
    }
}
