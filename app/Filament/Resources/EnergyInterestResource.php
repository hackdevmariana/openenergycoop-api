<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyInterestResource\Pages;
use App\Filament\Resources\EnergyInterestResource\RelationManagers;
use App\Models\EnergyInterest;
use App\Models\User;
use App\Models\EnergyZoneSummary;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class EnergyInterestResource extends Resource
{
    protected static ?string $model = EnergyInterest::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?string $modelLabel = 'Interés Energético';

    protected static ?string $pluralModelLabel = 'Intereses Energéticos';

    protected static ?int $navigationSort = 2;

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
                Section::make('Información del Interés')
                    ->schema([
                        Select::make('user_id')
                            ->label('Usuario')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Seleccionar usuario (opcional)')
                            ->helperText('Si no se selecciona usuario, se requieren datos de contacto'),

                        TextInput::make('zone_name')
                            ->label('Nombre de la Zona')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej. Centro Madrid')
                            ->helperText('Nombre de la zona energética'),

                        TextInput::make('postal_code')
                            ->label('Código Postal')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('ej. 28001')
                            ->helperText('Código postal de la zona'),

                        Select::make('type')
                            ->label('Tipo de Interés')
                            ->options([
                                'consumer' => 'Consumidor',
                                'producer' => 'Productor',
                                'mixed' => 'Mixto',
                            ])
                            ->required()
                            ->reactive()
                            ->helperText('Tipo de interés energético'),
                    ])
                    ->columns(2),

                Section::make('Datos Energéticos')
                    ->schema([
                        TextInput::make('estimated_production_kwh_day')
                            ->label('Producción Estimada (kWh/día)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('kWh/día')
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['producer', 'mixed']))
                            ->helperText('Solo para productores y mixtos'),

                        TextInput::make('requested_kwh_day')
                            ->label('Demanda Solicitada (kWh/día)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('kWh/día')
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['consumer', 'mixed']))
                            ->helperText('Solo para consumidores y mixtos'),
                    ])
                    ->columns(2),

                Section::make('Datos de Contacto')
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('Nombre de Contacto')
                            ->maxLength(255)
                            ->placeholder('ej. Juan Pérez')
                            ->visible(fn (Forms\Get $get) => !$get('user_id'))
                            ->helperText('Solo si no hay usuario seleccionado'),

                        TextInput::make('contact_email')
                            ->label('Email de Contacto')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('ej. juan@email.com')
                            ->visible(fn (Forms\Get $get) => !$get('user_id'))
                            ->helperText('Solo si no hay usuario seleccionado'),

                        TextInput::make('contact_phone')
                            ->label('Teléfono de Contacto')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('ej. +34 976 123 456')
                            ->visible(fn (Forms\Get $get) => !$get('user_id'))
                            ->helperText('Solo si no hay usuario seleccionado'),
                    ])
                    ->columns(2),

                Section::make('Estado y Notas')
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'approved' => 'Aprobado',
                                'rejected' => 'Rechazado',
                                'active' => 'Activo',
                            ])
                            ->default('pending')
                            ->required()
                            ->helperText('Estado del interés energético'),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->placeholder('Información adicional sobre el interés...')
                            ->helperText('Notas adicionales sobre el interés'),
                    ])
                    ->columns(1),
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
                    ->placeholder('Sin usuario')
                    ->color('gray'),

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

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'consumer',
                        'warning' => 'producer',
                        'info' => 'mixed',
                    ])
                    ->icons([
                        'heroicon-o-home' => 'consumer',
                        'heroicon-o-sun' => 'producer',
                        'heroicon-o-arrows-right-left' => 'mixed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'consumer' => 'Consumidor',
                        'producer' => 'Productor',
                        'mixed' => 'Mixto',
                        default => $state,
                    }),

                TextColumn::make('estimated_production_kwh_day')
                    ->label('Producción')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' kWh/día')
                    ->sortable()
                    ->color('success')
                    ->placeholder('N/A'),

                TextColumn::make('requested_kwh_day')
                    ->label('Demanda')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' kWh/día')
                    ->sortable()
                    ->color('warning')
                    ->placeholder('N/A'),

                TextColumn::make('contact_name')
                    ->label('Contacto')
                    ->searchable()
                    ->placeholder('Usuario registrado')
                    ->color('gray'),

                TextColumn::make('contact_email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('Usuario registrado')
                    ->color('gray'),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'active',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-play' => 'active',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'active' => 'Activo',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'consumer' => 'Consumidor',
                        'producer' => 'Productor',
                        'mixed' => 'Mixto',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'active' => 'Activo',
                    ]),

                SelectFilter::make('zone_name')
                    ->label('Zona')
                    ->options(EnergyInterest::distinct()->pluck('zone_name', 'zone_name'))
                    ->searchable(),

                Filter::make('with_user')
                    ->label('Con Usuario')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('user_id')),

                Filter::make('without_user')
                    ->label('Sin Usuario')
                    ->query(fn (Builder $query): Builder => $query->whereNull('user_id')),

                Filter::make('recent')
                    ->label('Recientes')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EnergyInterest $record) => $record->status === 'pending')
                    ->action(function (EnergyInterest $record) {
                        $record->approve();
                        Notification::make()
                            ->title('Interés aprobado')
                            ->body("El interés de {$record->zone_name} ha sido aprobado.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (EnergyInterest $record) => $record->status === 'pending')
                    ->action(function (EnergyInterest $record) {
                        $record->reject();
                        Notification::make()
                            ->title('Interés rechazado')
                            ->body("El interés de {$record->zone_name} ha sido rechazado.")
                            ->warning()
                            ->send();
                    }),

                Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (EnergyInterest $record) => $record->status === 'approved')
                    ->action(function (EnergyInterest $record) {
                        $record->activate();
                        Notification::make()
                            ->title('Interés activado')
                            ->body("El interés de {$record->zone_name} ha sido activado.")
                            ->success()
                            ->send();
                    }),

                Action::make('view_summary')
                    ->label('Ver Resumen')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalContent(fn (EnergyInterest $record) => view('filament.modals.energy-interest-summary', [
                        'interest' => $record->getEnergySummary()
                    ])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('approve_all')
                    ->label('Aprobar Seleccionados')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $approved = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'pending') {
                                $record->approve();
                                $approved++;
                            }
                        }
                        Notification::make()
                            ->title('Intereses aprobados')
                            ->body("Se han aprobado {$approved} intereses energéticos.")
                            ->success()
                            ->send();
                    }),

                BulkAction::make('reject_all')
                    ->label('Rechazar Seleccionados')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (Collection $records) {
                        $rejected = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'pending') {
                                $record->reject();
                                $rejected++;
                            }
                        }
                        Notification::make()
                            ->title('Intereses rechazados')
                            ->body("Se han rechazado {$rejected} intereses energéticos.")
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
            'index' => Pages\ListEnergyInterests::route('/'),
            'create' => Pages\CreateEnergyInterest::route('/create'),
            'edit' => Pages\EditEnergyInterest::route('/{record}/edit'),
        ];
    }
}