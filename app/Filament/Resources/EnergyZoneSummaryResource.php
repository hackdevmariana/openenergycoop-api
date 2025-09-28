<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyZoneSummaryResource\Pages;
use App\Filament\Resources\EnergyZoneSummaryResource\RelationManagers;
use App\Models\EnergyZoneSummary;
use App\Models\Municipality;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class EnergyZoneSummaryResource extends Resource
{
    protected static ?string $model = EnergyZoneSummary::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    protected static ?string $navigationGroup = 'Energía';
    
    protected static ?string $modelLabel = 'Zona Energética';
    
    protected static ?string $pluralModelLabel = 'Zonas Energéticas';
    
    protected static ?int $navigationSort = 1;

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
                Section::make('Información de la Zona')
                    ->schema([
                        TextInput::make('zone_name')
                            ->label('Nombre de la Zona')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej. Centro Madrid'),
                        
                        TextInput::make('postal_code')
                            ->label('Código Postal')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('ej. 28001'),
                        
                        Select::make('municipality_id')
                            ->label('Municipio')
                            ->options(Municipality::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
                
                Section::make('Datos Energéticos')
                    ->schema([
                        TextInput::make('estimated_production_kwh_day')
                            ->label('Producción Estimada (kWh/día)')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->suffix('kWh/día')
                            ->required(),
                        
                        TextInput::make('reserved_kwh_day')
                            ->label('Energía Reservada (kWh/día)')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->suffix('kWh/día')
                            ->required(),
                        
                        TextInput::make('requested_kwh_day')
                            ->label('Energía Solicitada (kWh/día)')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->suffix('kWh/día')
                            ->required(),
                        
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'verde' => 'Verde',
                                'naranja' => 'Naranja',
                                'rojo' => 'Rojo',
                            ])
                            ->default('verde')
                            ->required(),
                    ])
                    ->columns(2),
                
                Section::make('Información Adicional')
                    ->schema([
                        DateTimePicker::make('last_updated_at')
                            ->label('Última Actualización')
                            ->default(now()),
                        
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->placeholder('Información adicional sobre la zona...'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('zone_name')
                    ->label('Zona')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('postal_code')
                    ->label('Código Postal')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('municipality.name')
                    ->label('Municipio')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                
                TextColumn::make('estimated_production_kwh_day')
                    ->label('Producción')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' kWh/día')
                    ->sortable()
                    ->color('success'),
                
                TextColumn::make('reserved_kwh_day')
                    ->label('Reservada')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' kWh/día')
                    ->sortable()
                    ->color('warning'),
                
                TextColumn::make('available_kwh_day')
                    ->label('Disponible')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' kWh/día')
                    ->sortable()
                    ->color('info'),
                
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'verde',
                        'warning' => 'naranja',
                        'danger' => 'rojo',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'verde',
                        'heroicon-o-exclamation-triangle' => 'naranja',
                        'heroicon-o-x-circle' => 'rojo',
                    ]),
                
                TextColumn::make('utilization_percentage')
                    ->label('Utilización')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'danger',
                        $state >= 70 => 'warning',
                        default => 'success',
                    }),
                
                TextColumn::make('last_updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'verde' => 'Verde',
                        'naranja' => 'Naranja',
                        'rojo' => 'Rojo',
                    ]),
                
                SelectFilter::make('municipality_id')
                    ->label('Municipio')
                    ->relationship('municipality', 'name')
                    ->searchable()
                    ->preload(),
                
                Filter::make('with_available_energy')
                    ->label('Con Energía Disponible')
                    ->query(fn (Builder $query): Builder => $query->where('available_kwh_day', '>', 0)),
                
                Filter::make('recently_updated')
                    ->label('Actualizado Recientemente')
                    ->query(fn (Builder $query): Builder => $query->where('last_updated_at', '>=', now()->subHours(24))),
            ])
            ->actions([
                Action::make('update_status')
                    ->label('Actualizar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (EnergyZoneSummary $record) {
                        $record->updateStatus();
                        Notification::make()
                            ->title('Estado actualizado')
                            ->body("El estado de {$record->zone_name} ha sido actualizado.")
                            ->success()
                            ->send();
                    }),
                
                Action::make('view_summary')
                    ->label('Ver Resumen')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalContent(fn (EnergyZoneSummary $record) => view('filament.modals.energy-zone-summary', [
                        'zone' => $record->getEnergySummary()
                    ])),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('update_all_statuses')
                    ->label('Actualizar Estados')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (Collection $records) {
                        $updated = 0;
                        foreach ($records as $record) {
                            $record->updateStatus();
                            $updated++;
                        }
                        Notification::make()
                            ->title('Estados actualizados')
                            ->body("Se han actualizado {$updated} zonas energéticas.")
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('zone_name')
            ->poll('30s'); // Actualizar cada 30 segundos
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
            'index' => Pages\ListEnergyZoneSummaries::route('/'),
            'create' => Pages\CreateEnergyZoneSummary::route('/create'),
            'edit' => Pages\EditEnergyZoneSummary::route('/{record}/edit'),
        ];
    }
}
