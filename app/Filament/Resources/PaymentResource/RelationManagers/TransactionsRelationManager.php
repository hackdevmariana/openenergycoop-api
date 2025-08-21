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

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $recordTitleAttribute = 'transaction_type';

    protected static ?string $title = 'Transacciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Transacción')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('transaction_type')
                                    ->label('Tipo de Transacción')
                                    ->options([
                                        'payment' => 'Pago',
                                        'refund' => 'Reembolso',
                                        'adjustment' => 'Ajuste',
                                        'fee' => 'Comisión',
                                        'tax' => 'Impuesto',
                                        'discount' => 'Descuento',
                                        'credit' => 'Crédito',
                                        'debit' => 'Débito',
                                    ])
                                    ->required()
                                    ->default('payment'),
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
                                        'reversed' => 'Revertido',
                                    ])
                                    ->required()
                                    ->default('pending'),
                                DateTimePicker::make('transaction_date')
                                    ->label('Fecha de Transacción')
                                    ->required()
                                    ->default(now()),
                            ]),
                        TextInput::make('reference_id')
                            ->label('ID de Referencia')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Descripción')
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
                        'gray' => 'reversed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                        'reversed' => 'Revertido',
                    }),
                TextColumn::make('transaction_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'payment' => 'Pago',
                        'refund' => 'Reembolso',
                        'adjustment' => 'Ajuste',
                        'fee' => 'Comisión',
                        'tax' => 'Impuesto',
                        'discount' => 'Descuento',
                        'credit' => 'Crédito',
                        'debit' => 'Débito',
                    }),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('transaction_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('reference_id')
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
                        'reversed' => 'Revertido',
                    ]),
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->label('Tipo de Transacción')
                    ->options([
                        'payment' => 'Pago',
                        'refund' => 'Reembolso',
                        'adjustment' => 'Ajuste',
                        'fee' => 'Comisión',
                        'tax' => 'Impuesto',
                        'discount' => 'Descuento',
                        'credit' => 'Crédito',
                        'debit' => 'Débito',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva Transacción')
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
            ->defaultSort('transaction_date', 'desc');
    }
}
