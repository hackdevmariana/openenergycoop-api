<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionRequestResource\Pages;
use App\Models\SubscriptionRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionRequestResource extends Resource
{
    protected static ?string $model = SubscriptionRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestión de Solicitudes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Solicitud')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('cooperative_id')
                            ->label('Cooperativa')
                            ->relationship('cooperative', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de Solicitud')
                            ->options([
                                SubscriptionRequest::TYPE_NEW_SUBSCRIPTION => 'Nueva Suscripción',
                                SubscriptionRequest::TYPE_OWNERSHIP_CHANGE => 'Cambio de Titularidad',
                                SubscriptionRequest::TYPE_TENANT_REQUEST => 'Solicitud de Inquilino',
                            ])
                            ->required()
                            ->default(SubscriptionRequest::TYPE_NEW_SUBSCRIPTION),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                SubscriptionRequest::STATUS_PENDING => 'Pendiente',
                                SubscriptionRequest::STATUS_IN_REVIEW => 'En Revisión',
                                SubscriptionRequest::STATUS_APPROVED => 'Aprobada',
                                SubscriptionRequest::STATUS_REJECTED => 'Rechazada',
                            ])
                            ->required()
                            ->default(SubscriptionRequest::STATUS_PENDING),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->placeholder('Notas adicionales sobre la solicitud'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Fechas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->label('Fecha de Envío')
                            ->placeholder('Se establece automáticamente'),

                        Forms\Components\DateTimePicker::make('processed_at')
                            ->label('Fecha de Procesamiento')
                            ->placeholder('Se establece automáticamente'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cooperative.name')
                    ->label('Cooperativa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => SubscriptionRequest::TYPE_NEW_SUBSCRIPTION,
                        'warning' => SubscriptionRequest::TYPE_OWNERSHIP_CHANGE,
                        'info' => SubscriptionRequest::TYPE_TENANT_REQUEST,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        SubscriptionRequest::TYPE_NEW_SUBSCRIPTION => 'Nueva Suscripción',
                        SubscriptionRequest::TYPE_OWNERSHIP_CHANGE => 'Cambio de Titularidad',
                        SubscriptionRequest::TYPE_TENANT_REQUEST => 'Solicitud de Inquilino',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => SubscriptionRequest::STATUS_PENDING,
                        'info' => SubscriptionRequest::STATUS_IN_REVIEW,
                        'success' => SubscriptionRequest::STATUS_APPROVED,
                        'danger' => SubscriptionRequest::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        SubscriptionRequest::STATUS_PENDING => 'Pendiente',
                        SubscriptionRequest::STATUS_IN_REVIEW => 'En Revisión',
                        SubscriptionRequest::STATUS_APPROVED => 'Aprobada',
                        SubscriptionRequest::STATUS_REJECTED => 'Rechazada',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Enviada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Procesada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        SubscriptionRequest::STATUS_PENDING => 'Pendiente',
                        SubscriptionRequest::STATUS_IN_REVIEW => 'En Revisión',
                        SubscriptionRequest::STATUS_APPROVED => 'Aprobada',
                        SubscriptionRequest::STATUS_REJECTED => 'Rechazada',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        SubscriptionRequest::TYPE_NEW_SUBSCRIPTION => 'Nueva Suscripción',
                        SubscriptionRequest::TYPE_OWNERSHIP_CHANGE => 'Cambio de Titularidad',
                        SubscriptionRequest::TYPE_TENANT_REQUEST => 'Solicitud de Inquilino',
                    ]),

                Tables\Filters\Filter::make('submitted_at')
                    ->form([
                        Forms\Components\DatePicker::make('submitted_from'),
                        Forms\Components\DatePicker::make('submitted_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['submitted_from'],
                                fn (Builder $query, $date): Builder => $query->dateFilter('submitted_at', '>=', $date),
                            )
                            ->when(
                                $data['submitted_until'],
                                fn (Builder $query, $date): Builder => $query->dateFilter('submitted_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (SubscriptionRequest $record) => $record->approve())
                    ->visible(fn (SubscriptionRequest $record) => $record->status === SubscriptionRequest::STATUS_PENDING || $record->status === SubscriptionRequest::STATUS_IN_REVIEW),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (SubscriptionRequest $record) => $record->reject())
                    ->visible(fn (SubscriptionRequest $record) => $record->status === SubscriptionRequest::STATUS_PENDING || $record->status === SubscriptionRequest::STATUS_IN_REVIEW),
                Tables\Actions\Action::make('review')
                    ->label('En Revisión')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->action(fn (SubscriptionRequest $record) => $record->markForReview())
                    ->visible(fn (SubscriptionRequest $record) => $record->status === SubscriptionRequest::STATUS_PENDING),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSubscriptionRequests::route('/'),
            'create' => Pages\CreateSubscriptionRequest::route('/create'),
            'edit' => Pages\EditSubscriptionRequest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', SubscriptionRequest::STATUS_PENDING)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
