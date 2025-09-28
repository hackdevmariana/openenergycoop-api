<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyParticipationResource\Pages;
use App\Filament\Resources\EnergyParticipationResource\RelationManagers;
use App\Models\EnergyParticipation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;

class EnergyParticipationResource extends Resource
{
    protected static ?string $model = EnergyParticipation::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Energía';

    protected static ?string $modelLabel = 'Participación Energética';

    protected static ?string $pluralModelLabel = 'Participaciones Energéticas';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::count();
        if ($count > 50) return 'success';
        if ($count > 20) return 'warning';
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('plan_code')
                            ->label('Plan')
                            ->options(EnergyParticipation::getPlanCodes())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(EnergyParticipation::getStatuses())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('fidelity_years')
                            ->label('Años de Fidelidad')
                            ->numeric()
                            ->default(0)
                            ->step(0.1)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Información Financiera')
                    ->schema([
                        Forms\Components\TextInput::make('monthly_amount')
                            ->label('Cuota Mensual (€)')
                            ->numeric()
                            ->step(0.01)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('one_time_amount')
                            ->label('Aportación Única (€)')
                            ->numeric()
                            ->step(0.01)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Fecha de Finalización')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Derechos Energéticos')
                    ->schema([
                        Forms\Components\TextInput::make('energy_rights_daily')
                            ->label('Derechos Diarios (kWh/día)')
                            ->numeric()
                            ->step(0.001)
                            ->default(0)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('energy_rights_total_kwh')
                            ->label('Derechos Totales (kWh)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('plan_code')
                    ->label('Plan')
                    ->formatStateUsing(fn (string $state): string => EnergyParticipation::getPlanCodes()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'raiz' => 'success',
                        'hogar' => 'info',
                        'independencia_22' => 'warning',
                        'independencia_25' => 'primary',
                        'independencia_30' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('monthly_amount')
                    ->label('Cuota Mensual')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('one_time_amount')
                    ->label('Aportación Única')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => EnergyParticipation::getStatuses()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('fidelity_years')
                    ->label('Fidelidad')
                    ->formatStateUsing(fn (float $state): string => number_format($state, 1) . ' años')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('energy_rights_daily')
                    ->label('Derechos Diarios')
                    ->formatStateUsing(fn (float $state): string => number_format($state, 3) . ' kWh/día')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('energy_rights_total_kwh')
                    ->label('Derechos Totales')
                    ->formatStateUsing(fn (float $state): string => number_format($state, 2) . ' kWh')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Completado')
                    ->formatStateUsing(fn (float $state): string => number_format($state, 1) . '%')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('is_expired')
                    ->label('Expirado')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('is_expiring_soon')
                    ->label('Expira Pronto')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('days_until_expiration')
                    ->label('Días hasta Expiración')
                    ->formatStateUsing(fn (?int $state): string => $state ? $state . ' días' : 'Sin fecha')
                    ->sortable()
                    ->toggleable(),

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
                    ->options(EnergyParticipation::getStatuses()),

                Tables\Filters\SelectFilter::make('plan_code')
                    ->label('Plan')
                    ->options(EnergyParticipation::getPlanCodes()),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('monthly')
                    ->label('Solo Mensuales')
                    ->query(fn (Builder $query): Builder => $query->monthly()),

                Tables\Filters\Filter::make('one_time')
                    ->label('Solo Aportaciones Únicas')
                    ->query(fn (Builder $query): Builder => $query->oneTime()),

                Tables\Filters\Filter::make('with_end_date')
                    ->label('Con Fecha de Fin')
                    ->query(fn (Builder $query): Builder => $query->withEndDate()),

                Tables\Filters\Filter::make('without_end_date')
                    ->label('Sin Fecha de Fin')
                    ->query(fn (Builder $query): Builder => $query->withoutEndDate()),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Expiran Pronto')
                    ->query(fn (Builder $query): Builder => $query->expiringSoon()),

                Tables\Filters\Filter::make('expired')
                    ->label('Expiradas')
                    ->query(fn (Builder $query): Builder => $query->expired()),

                Tables\Filters\Filter::make('recently_created')
                    ->label('Creadas Recientemente')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Tables\Actions\Action::make('suspend')
                    ->label('Suspender')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Suspender Participación')
                    ->modalDescription('¿Estás seguro de que quieres suspender esta participación?')
                    ->action(function (EnergyParticipation $record) {
                        if ($record->canBeSuspended()) {
                            $record->suspend();
                            Notification::make()
                                ->title('Participación suspendida')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No se puede suspender')
                                ->body('La participación no puede ser suspendida en su estado actual')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (EnergyParticipation $record): bool => $record->canBeSuspended()),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Participación')
                    ->modalDescription('¿Estás seguro de que quieres cancelar esta participación?')
                    ->action(function (EnergyParticipation $record) {
                        if ($record->canBeCancelled()) {
                            $record->cancel();
                            Notification::make()
                                ->title('Participación cancelada')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No se puede cancelar')
                                ->body('La participación no puede ser cancelada en su estado actual')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (EnergyParticipation $record): bool => $record->canBeCancelled()),

                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Completar Participación')
                    ->modalDescription('¿Estás seguro de que quieres completar esta participación?')
                    ->action(function (EnergyParticipation $record) {
                        if ($record->canBeCompleted()) {
                            $record->complete();
                            Notification::make()
                                ->title('Participación completada')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No se puede completar')
                                ->body('La participación no puede ser completada en su estado actual')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (EnergyParticipation $record): bool => $record->canBeCompleted()),

                Tables\Actions\Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Activar Participación')
                    ->modalDescription('¿Estás seguro de que quieres activar esta participación?')
                    ->action(function (EnergyParticipation $record) {
                        if ($record->isSuspended()) {
                            $record->activate();
                            Notification::make()
                                ->title('Participación activada')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No se puede activar')
                                ->body('Solo se pueden activar participaciones suspendidas')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (EnergyParticipation $record): bool => $record->isSuspended()),

                Tables\Actions\Action::make('view_summary')
                    ->label('Ver Resumen')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Resumen de Participación')
                    ->modalContent(function (EnergyParticipation $record) {
                        $summary = $record->getParticipationSummary();
                        return view('filament.modals.energy-participation-summary', compact('summary'));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('suspend_selected')
                        ->label('Suspender Seleccionadas')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->canBeSuspended()) {
                                    $record->suspend();
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title("{$count} participaciones suspendidas")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('cancel_selected')
                        ->label('Cancelar Seleccionadas')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->canBeCancelled()) {
                                    $record->cancel();
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title("{$count} participaciones canceladas")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
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
            'index' => Pages\ListEnergyParticipations::route('/'),
            'create' => Pages\CreateEnergyParticipation::route('/create'),
            'edit' => Pages\EditEnergyParticipation::route('/{record}/edit'),
        ];
    }
}