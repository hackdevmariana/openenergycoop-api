<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyReportResource\Pages;
use App\Filament\Resources\EnergyReportResource\RelationManagers;
use App\Models\EnergyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnergyReportResource extends Resource
{
    protected static ?string $model = EnergyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Analytics y Reportes';

    protected static ?string $navigationLabel = 'Reportes de Energía';

    protected static ?string $modelLabel = 'Reporte de Energía';

    protected static ?string $pluralModelLabel = 'Reportes de Energía';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('report_code')
                    ->label('Código del Reporte')
                    ->required()
                    ->unique(EnergyReport::class, 'report_code', ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3),

                Forms\Components\Select::make('report_type')
                    ->label('Tipo de Reporte')
                    ->required()
                    ->options([
                        'consumption' => 'Consumo',
                        'production' => 'Producción',
                        'trading' => 'Comercio',
                        'savings' => 'Ahorros',
                        'cooperative' => 'Cooperativa',
                        'user' => 'Usuario',
                        'system' => 'Sistema',
                        'custom' => 'Personalizado',
                    ]),

                Forms\Components\Select::make('report_category')
                    ->label('Categoría')
                    ->required()
                    ->options([
                        'energy' => 'Energía',
                        'financial' => 'Financiero',
                        'environmental' => 'Ambiental',
                        'operational' => 'Operacional',
                        'performance' => 'Rendimiento',
                    ]),

                Forms\Components\Select::make('scope')
                    ->label('Alcance')
                    ->required()
                    ->options([
                        'user' => 'Usuario',
                        'cooperative' => 'Cooperativa',
                        'provider' => 'Proveedor',
                        'system' => 'Sistema',
                        'custom' => 'Personalizado',
                    ]),

                Forms\Components\DatePicker::make('period_start')
                    ->label('Fecha Inicio')
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('period_end')
                    ->label('Fecha Fin')
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('period_type')
                    ->label('Tipo de Período')
                    ->required()
                    ->options([
                        'daily' => 'Diario',
                        'weekly' => 'Semanal',
                        'monthly' => 'Mensual',
                        'quarterly' => 'Trimestral',
                        'yearly' => 'Anual',
                        'custom' => 'Personalizado',
                    ]),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->required()
                    ->options([
                        'draft' => 'Borrador',
                        'generating' => 'Generando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'scheduled' => 'Programado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->default('draft'),

                Forms\Components\Toggle::make('auto_generate')
                    ->label('Generación Automática')
                    ->default(false),

                Forms\Components\Toggle::make('is_public')
                    ->label('Público')
                    ->default(false),

                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable(),

                Forms\Components\Select::make('energy_cooperative_id')
                    ->label('Cooperativa Energética')
                    ->relationship('energyCooperative', 'name')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('report_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'consumption' => 'Consumo',
                        'production' => 'Producción',
                        'trading' => 'Comercio',
                        'savings' => 'Ahorros',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'generating',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('period_start')
                    ->label('Período')
                    ->date('d/m/Y'),

                Tables\Columns\IconColumn::make('auto_generate')
                    ->label('Auto')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('report_type')
                    ->label('Tipo de Reporte')
                    ->options([
                        'consumption' => 'Consumo',
                        'production' => 'Producción',
                        'trading' => 'Comercio',
                        'savings' => 'Ahorros',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'generating' => 'Generando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                    ]),
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
            'index' => Pages\ListEnergyReports::route('/'),
            'create' => Pages\CreateEnergyReport::route('/create'),
            // 'view' => Pages\ViewEnergyReport::route('/{record}'),
            'edit' => Pages\EditEnergyReport::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $totalCount = static::getModel()::count();
        $failedCount = static::getModel()::where('status', 'failed')->count();
        $generatingCount = static::getModel()::where('status', 'generating')->count();
        $completedCount = static::getModel()::where('status', 'completed')->count();
        
        if ($failedCount > 0) {
            return 'danger';
        } elseif ($generatingCount > 0) {
            return 'warning';
        } elseif ($completedCount > 50) {
            return 'success';
        } elseif ($totalCount > 20) {
            return 'info';
        } else {
            return 'gray';
        }
    }
}
