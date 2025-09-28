<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyRightPreSaleResource\Pages;
use App\Filament\Resources\EnergyRightPreSaleResource\RelationManagers;
use App\Models\EnergyRightPreSale;
use App\Models\User;
use App\Models\EnergyInstallation;
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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class EnergyRightPreSaleResource extends Resource
{
    protected static ?string $model = EnergyRightPreSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Energía';

    protected static ?string $modelLabel = 'Preventa de Derechos';

    protected static ?string $pluralModelLabel = 'Preventas de Derechos';

    protected static ?int $navigationSort = 3;

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
                Section::make('Información de la Preventa')
                    ->schema([
                        Select::make('user_id')
                            ->label('Usuario')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Seleccionar usuario'),

                        Select::make('energy_installation_id')
                            ->label('Instalación')
                            ->options(EnergyInstallation::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Seleccionar instalación (opcional)')
                            ->helperText('Si no se selecciona instalación, se requieren datos de zona'),

                        TextInput::make('zone_name')
                            ->label('Nombre de la Zona')
                            ->maxLength(255)
                            ->placeholder('ej. Centro Madrid')
                            ->visible(fn (Forms\Get $get) => !$get('energy_installation_id'))
                            ->helperText('Solo si no hay instalación seleccionada'),

                        TextInput::make('postal_code')
                            ->label('Código Postal')
                            ->maxLength(10)
                            ->placeholder('ej. 28001')
                            ->visible(fn (Forms\Get $get) => !$get('energy_installation_id'))
                            ->helperText('Solo si no hay instalación seleccionada'),
                    ])
                    ->columns(2),

                Section::make('Datos de la Preventa')
                    ->schema([
                        TextInput::make('kwh_per_month_reserved')
                            ->label('kWh Reservados por Mes')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->suffix('kWh/mes')
                            ->helperText('Cantidad de energía reservada mensualmente'),

                        TextInput::make('price_per_kwh')
                            ->label('Precio por kWh')
                            ->required()
                            ->numeric()
                            ->step(0.0001)
                            ->suffix('€/kWh')
                            ->helperText('Precio pactado por kilovatio-hora'),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'confirmed' => 'Confirmado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->default('pending')
                            ->required()
                            ->helperText('Estado de la preventa'),
                    ])
                    ->columns(3),

                Section::make('Fechas y Notas')
                    ->schema([
                        DateTimePicker::make('signed_at')
                            ->label('Fecha de Firma')
                            ->nullable()
                            ->helperText('Fecha cuando se firmó el contrato'),

                        DateTimePicker::make('expires_at')
                            ->label('Fecha de Expiración')
                            ->nullable()
                            ->after('now')
                            ->helperText('Fecha de expiración de la preventa'),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->placeholder('Información adicional sobre la preventa...')
                            ->helperText('Notas adicionales sobre la preventa'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('installation.name')
                    ->label('Instalación')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Por zona')
                    ->color('gray'),

                TextColumn::make('zone_name')
                    ->label('Zona')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Instalación específica')
                    ->color('gray'),

                TextColumn::make('postal_code')
                    ->label('Código Postal')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('N/A'),

                TextColumn::make('kwh_per_month_reserved')
                    ->label('kWh/mes')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' kWh')
                    ->sortable()
                    ->color('success'),

                TextColumn::make('price_per_kwh')
                    ->label('Precio/kWh')
                    ->numeric(decimalPlaces: 4)
                    ->suffix(' €')
                    ->sortable()
                    ->color('warning'),

                TextColumn::make('total_value')
                    ->label('Valor Total')
                    ->formatStateUsing(fn ($record) => $record->total_value_formatted)
                    ->sortable()
                    ->color('info'),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'confirmed',
                        'heroicon-o-x-circle' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),

                TextColumn::make('signed_at')
                    ->label('Firmado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No firmado')
                    ->color('gray'),

                TextColumn::make('expires_at')
                    ->label('Expira')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin expiración')
                    ->color(fn ($record) => match (true) {
                        $record->is_expired => 'danger',
                        $record->is_expiring_soon => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('days_until_expiration')
                    ->label('Días Restantes')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} días" : 'N/A')
                    ->color(fn ($state) => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 30 => 'warning',
                        default => 'success',
                    })
                    ->placeholder('N/A'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('energy_installation_id')
                    ->label('Instalación')
                    ->relationship('installation', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('with_installation')
                    ->label('Con Instalación')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('energy_installation_id')),

                Filter::make('without_installation')
                    ->label('Por Zona')
                    ->query(fn (Builder $query): Builder => $query->whereNull('energy_installation_id')),

                Filter::make('active')
                    ->label('Activas')
                    ->query(fn (Builder $query): Builder => $query->active()),

                Filter::make('expired')
                    ->label('Expiradas')
                    ->query(fn (Builder $query): Builder => $query->expired()),

                Filter::make('expiring_soon')
                    ->label('Expiran Pronto')
                    ->query(fn (Builder $query): Builder => $query->expiringSoon()),

                Filter::make('recent')
                    ->label('Recientes')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EnergyRightPreSale $record) => $record->canBeConfirmed())
                    ->action(function (EnergyRightPreSale $record) {
                        $record->confirm();
                        Notification::make()
                            ->title('Preventa confirmada')
                            ->body("La preventa de {$record->user->name} ha sido confirmada.")
                            ->success()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (EnergyRightPreSale $record) => $record->canBeCancelled())
                    ->action(function (EnergyRightPreSale $record) {
                        $record->cancel();
                        Notification::make()
                            ->title('Preventa cancelada')
                            ->body("La preventa de {$record->user->name} ha sido cancelada.")
                            ->warning()
                            ->send();
                    }),

                Action::make('view_summary')
                    ->label('Ver Resumen')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalContent(fn (EnergyRightPreSale $record) => view('filament.modals.energy-right-presale-summary', [
                        'presale' => $record->getPreSaleSummary()
                    ])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('confirm_all')
                    ->label('Confirmar Seleccionadas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $confirmed = 0;
                        foreach ($records as $record) {
                            if ($record->canBeConfirmed()) {
                                $record->confirm();
                                $confirmed++;
                            }
                        }
                        Notification::make()
                            ->title('Preventas confirmadas')
                            ->body("Se han confirmado {$confirmed} preventas de derechos energéticos.")
                            ->success()
                            ->send();
                    }),

                BulkAction::make('cancel_all')
                    ->label('Cancelar Seleccionadas')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (Collection $records) {
                        $cancelled = 0;
                        foreach ($records as $record) {
                            if ($record->canBeCancelled()) {
                                $record->cancel();
                                $cancelled++;
                            }
                        }
                        Notification::make()
                            ->title('Preventas canceladas')
                            ->body("Se han cancelado {$cancelled} preventas de derechos energéticos.")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListEnergyRightPreSales::route('/'),
            'create' => Pages\CreateEnergyRightPreSale::route('/create'),
            'edit' => Pages\EditEnergyRightPreSale::route('/{record}/edit'),
        ];
    }
}