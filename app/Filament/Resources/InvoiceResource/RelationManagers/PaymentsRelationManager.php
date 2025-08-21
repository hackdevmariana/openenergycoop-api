<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

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

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'payment_method';

    protected static ?string $title = 'Pagos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Pago')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('payment_method')
                                    ->label('Método de Pago')
                                    ->options([
                                        'credit_card' => 'Tarjeta de Crédito',
                                        'debit_card' => 'Tarjeta de Débito',
                                        'bank_transfer' => 'Transferencia Bancaria',
                                        'cash' => 'Efectivo',
                                        'check' => 'Cheque',
                                        'digital_wallet' => 'Billetera Digital',
                                        'cryptocurrency' => 'Criptomoneda',
                                    ])
                                    ->required()
                                    ->default('credit_card'),
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
                                        'refunded' => 'Reembolsado',
                                    ])
                                    ->required()
                                    ->default('pending'),
                                DateTimePicker::make('payment_date')
                                    ->label('Fecha de Pago')
                                    ->required()
                                    ->default(now()),
                            ]),
                        TextInput::make('transaction_id')
                            ->label('ID de Transacción')
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->collapsible(),

                Section::make('Detalles Adicionales')
                    ->schema([
                        TextInput::make('reference_number')
                            ->label('Número de Referencia')
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
                        'gray' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                        'refunded' => 'Reembolsado',
                    }),
                TextColumn::make('payment_method')
                    ->label('Método')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'credit_card' => 'Tarjeta de Crédito',
                        'debit_card' => 'Tarjeta de Débito',
                        'bank_transfer' => 'Transferencia Bancaria',
                        'cash' => 'Efectivo',
                        'check' => 'Cheque',
                        'digital_wallet' => 'Billetera Digital',
                        'cryptocurrency' => 'Criptomoneda',
                    }),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label('Fecha de Pago')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('transaction_id')
                    ->label('ID Transacción')
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
                        'refunded' => 'Reembolsado',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Método de Pago')
                    ->options([
                        'credit_card' => 'Tarjeta de Crédito',
                        'debit_card' => 'Tarjeta de Débito',
                        'bank_transfer' => 'Transferencia Bancaria',
                        'cash' => 'Efectivo',
                        'check' => 'Cheque',
                        'digital_wallet' => 'Billetera Digital',
                        'cryptocurrency' => 'Criptomoneda',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nuevo Pago')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['invoice_id'] = $this->getOwnerRecord()->id;
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
            ->defaultSort('payment_date', 'desc');
    }
}
