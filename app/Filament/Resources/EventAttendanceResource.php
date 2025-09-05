<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventAttendanceResource\Pages;
use App\Filament\Resources\EventAttendanceResource\RelationManagers;
use App\Models\EventAttendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class EventAttendanceResource extends Resource
{
    protected static ?string $model = EventAttendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Asistencias a Eventos';
    protected static ?string $modelLabel = 'Asistencia a Evento';
    protected static ?string $pluralModelLabel = 'Asistencias a Eventos';
    protected static ?string $navigationGroup = 'Gestión de Eventos';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Asistencia')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn () => null),
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'registered' => 'Registrado',
                                'attended' => 'Asistió',
                                'cancelled' => 'Cancelado',
                                'no_show' => 'No Asistió',
                            ])
                            ->required()
                            ->default('registered')
                            ->live(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Fechas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('registered_at')
                            ->label('Fecha de Registro')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y H:i')
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\DateTimePicker::make('checked_in_at')
                            ->label('Fecha de Check-in')
                            ->displayFormat('d/m/Y H:i')
                            ->native(false)
                            ->seconds(false)
                            ->visible(fn (string $context): bool => $context === 'edit')
                            ->helperText('Solo visible al editar'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Razón de Cancelación')
                            ->rows(3)
                            ->placeholder('Motivo de la cancelación...')
                            ->visible(fn (string $context, $get): bool => $get('status') === 'cancelled')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->placeholder('Notas adicionales para administradores...')
                            ->helperText('Información adicional visible solo para administradores')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('checkin_token')
                            ->label('Token de Check-in')
                            ->disabled()
                            ->helperText('Token único generado automáticamente')
                            ->visible(fn (string $context): bool => $context === 'edit'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->url(fn (?EventAttendance $record): string => $record ? route('filament.admin.resources.events.edit', $record->event_id) : '#')
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'info' => 'registered',
                        'success' => 'attended',
                        'danger' => 'cancelled',
                        'warning' => 'no_show',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'registered' => 'Registrado',
                        'attended' => 'Asistió',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'No Asistió',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('registered_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No realizado')
                    ->badge()
                    ->color(fn (?EventAttendance $record): string => $record && $record->checked_in_at ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('cancellation_reason')
                    ->label('Razón de Cancelación')
                    ->limit(30)
                    ->visible(fn (?EventAttendance $record): bool => $record && $record->status === 'cancelled')
                    ->placeholder('No especificada'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(30)
                    ->placeholder('Sin notas'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'registered' => 'Registrado',
                        'attended' => 'Asistió',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'No Asistió',
                    ]),
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date_range')
                    ->label('Rango de Fechas de Registro')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('registered_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('registered_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('checkin_status')
                    ->label('Estado de Check-in')
                    ->form([
                        Forms\Components\Select::make('checkin_status')
                            ->label('Check-in')
                            ->options([
                                'checked_in' => 'Realizado',
                                'not_checked_in' => 'No Realizado',
                            ])
                            ->default('not_checked_in'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['checkin_status'],
                            fn (Builder $query, $status): Builder => match ($status) {
                                'checked_in' => $query->whereNotNull('checked_in_at'),
                                'not_checked_in' => $query->whereNull('checked_in_at'),
                                default => $query,
                            },
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in')
                    ->label('Hacer Check-in')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (?EventAttendance $record): bool => $record && $record->canCheckIn())
                    ->action(function (EventAttendance $record): void {
                        $record->checkIn();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Check-in')
                    ->modalDescription('¿Estás seguro de que quieres marcar a este usuario como asistido?')
                    ->modalSubmitActionLabel('Confirmar Check-in'),
                Tables\Actions\Action::make('cancel_attendance')
                    ->label('Cancelar Asistencia')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (?EventAttendance $record): bool => $record && $record->canCancel())
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Razón de Cancelación')
                            ->required()
                            ->placeholder('Motivo de la cancelación...'),
                    ])
                    ->action(function (EventAttendance $record, array $data): void {
                        $record->cancel($data['cancellation_reason']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Asistencia')
                    ->modalDescription('¿Estás seguro de que quieres cancelar esta asistencia?'),
                Tables\Actions\Action::make('mark_no_show')
                    ->label('Marcar No Asistió')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn (?EventAttendance $record): bool => $record && $record->isRegistered())
                    ->action(function (EventAttendance $record): void {
                        $record->markAsNoShow();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Marcar No Asistió')
                    ->modalDescription('¿Estás seguro de que quieres marcar a este usuario como no asistió?'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('check_in_bulk')
                        ->label('Hacer Check-in Masivo')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                if ($record->canCheckIn()) {
                                    $record->checkIn();
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Check-in Masivo')
                        ->modalDescription('¿Estás seguro de que quieres hacer check-in a todos los usuarios registrados seleccionados?'),
                    Tables\Actions\BulkAction::make('mark_no_show_bulk')
                        ->label('Marcar No Asistieron')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                if ($record->isRegistered()) {
                                    $record->markAsNoShow();
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Marcar No Asistieron')
                        ->modalDescription('¿Estás seguro de que quieres marcar como no asistieron a todos los usuarios registrados seleccionados?'),
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['event', 'user'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventAttendances::route('/'),
            'create' => Pages\CreateEventAttendance::route('/create'),
            'edit' => Pages\EditEventAttendance::route('/{record}/edit'),
        ];
    }
}
