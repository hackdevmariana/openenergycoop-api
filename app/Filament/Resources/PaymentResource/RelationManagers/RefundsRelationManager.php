<?php

namespace App\Filament\Resources\PaymentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\CurrencyInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class RefundsRelationManager extends RelationManager
{
    protected static string $relationship = 'refunds';

    protected static ?string $recordTitleAttribute = 'refund_reason';

    protected static ?string $title = 'Reembolsos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Reembolso')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('refund_reason')
                                    ->label('Razón del Reembolso')
                                    ->options([
                                        'customer_request' => 'Solicitud del Cliente',
                                        'duplicate_payment' => 'Pago Duplicado',
                                        'service_not_received' => 'Servicio No Recibido',
                                        'quality_issue' => 'Problema de Calidad',
                                        'billing_error' => 'Error de Facturación',
                                        'fraud' => 'Fraude',
                                        'other' => 'Otro',
                                    ])
                                    ->required()
                                    ->default('customer_request'),
                                CurrencyInput::make('amount')
                                    ->label('Monto')
                                    ->required()
                                    ->currency('USD'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'pending' => 'Pendiente',
                                        'processing' => 'Procesando',
                                        'completed' => 'Completado',
                                        'failed' => 'Fallido',
                                        'cancelled' => 'Cancelado',
                                        'rejected' => 'Rechazado',
                                    ])
                                    ->required()
                                    ->default('pending'),
                                DateTimePicker::make('refund_date')
                                    ->label('Fecha de Reembolso')
                                    ->required()
                                    ->default(now()),
                            ]),
                        TextInput::make('reference_number')
                            ->label('Número de Referencia')
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->collapsible(),

                Section::make('Detalles Adicionales')
                    ->schema([
                        TextInput::make('external_id')
                            ->label('ID Externo')
                            ->maxLength(255),
                        TextInput::make('gateway_response')
                            ->label('Respuesta de la Pasarela')
                            ->maxLength(1000),
                        TextInput::make('metadata')
                            ->label('Metadatos')
                            ->maxLength(1000),
                    ])
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'secondary' => 'cancelled',
                        'gray' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                        'rejected' => 'Rechazado',
                    }),
                TextColumn::make('refund_reason')
                    ->label('Razón')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'customer_request' => 'Solicitud del Cliente',
                        'duplicate_payment' => 'Pago Duplicado',
                        'service_not_received' => 'Servicio No Recibido',
                        'quality_issue' => 'Problema de Calidad',
                        'billing_error' => 'Error de Facturación',
                        'fraud' => 'Fraude',
                        'other' => 'Otro',
                    }),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('refund_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('reference_number')
                    ->label('Referencia')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                        'rejected' => 'Rechazado',
                    ]),
                Tables\Filters\SelectFilter::make('refund_reason')
                    ->label('Razón del Reembolso')
                    ->options([
                        'customer_request' => 'Solicitud del Cliente',
                        'duplicate_payment' => 'Pago Duplicado',
                        'service_not_received' => 'Servicio No Recibido',
                        'quality_issue' => 'Problema de Calidad',
                        'billing_error' => 'Error de Facturación',
                        'fraud' => 'Fraude',
                        'other' => 'Otro',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nuevo Reembolso')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['payment_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('refund_date', 'desc');
    }
}
