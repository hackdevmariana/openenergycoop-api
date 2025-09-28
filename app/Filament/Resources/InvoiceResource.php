<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Finanzas y Contabilidad';

    protected static ?string $modelLabel = 'Factura';

    protected static ?string $pluralModelLabel = 'Facturas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('invoice_number')
                                    ->label('Número de Factura')
                                    ->default(fn () => Invoice::generateInvoiceNumber())
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                Forms\Components\TextInput::make('invoice_code')
                                    ->label('Código')
                                    ->default(fn () => Invoice::generateInvoiceCode())
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'standard' => 'Estándar',
                                        'proforma' => 'Proforma',
                                        'credit_note' => 'Nota de Crédito',
                                        'debit_note' => 'Nota de Débito',
                                        'recurring' => 'Recurrente'
                                    ])
                                    ->default('standard')
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'draft' => 'Borrador',
                                        'sent' => 'Enviada',
                                        'viewed' => 'Vista',
                                        'overdue' => 'Vencida',
                                        'paid' => 'Pagada',
                                        'partially_paid' => 'Parcialmente Pagada',
                                        'cancelled' => 'Cancelada',
                                        'refunded' => 'Reembolsada'
                                    ])
                                    ->default('draft')
                                    ->required(),

                                Forms\Components\Select::make('user_id')
                                    ->label('Cliente')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('energy_cooperative_id')
                                    ->label('Cooperativa')
                                    ->relationship('energyCooperative', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),

                Forms\Components\Section::make('Fechas')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('issue_date')
                                    ->label('Fecha de Emisión')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Fecha de Vencimiento')
                                    ->default(now()->addDays(30))
                                    ->required(),

                                Forms\Components\DatePicker::make('service_period_start')
                                    ->label('Período de Servicio - Inicio'),

                                Forms\Components\DatePicker::make('service_period_end')
                                    ->label('Período de Servicio - Fin'),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Financiera')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('tax_amount')
                                    ->label('Impuestos')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('discount_amount')
                                    ->label('Descuentos')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('paid_amount')
                                    ->label('Cantidad Pagada')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('pending_amount')
                                    ->label('Cantidad Pendiente')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('tax_rate')
                                    ->label('Tasa de Impuesto')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('%'),

                                Forms\Components\Select::make('currency')
                                    ->label('Moneda')
                                    ->options([
                                        'EUR' => 'EUR',
                                        'USD' => 'USD',
                                        'GBP' => 'GBP'
                                    ])
                                    ->default('EUR')
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Información del Cliente')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Nombre del Cliente')
                                    ->required(),

                                Forms\Components\TextInput::make('customer_email')
                                    ->label('Email del Cliente')
                                    ->email()
                                    ->required(),

                                Forms\Components\Textarea::make('billing_address')
                                    ->label('Dirección de Facturación')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('customer_tax_id')
                                    ->label('ID Fiscal del Cliente'),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Contenido')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),

                        Forms\Components\KeyValue::make('line_items')
                            ->label('Elementos de Línea')
                            ->keyLabel('Concepto')
                            ->valueLabel('Importe')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Configuración')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_recurring')
                                    ->label('Es Recurrente')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_test')
                                    ->label('Es de Prueba')
                                    ->default(false),

                                Forms\Components\Select::make('recurring_frequency')
                                    ->label('Frecuencia de Recurrencia')
                                    ->options([
                                        'monthly' => 'Mensual',
                                        'quarterly' => 'Trimestral',
                                        'yearly' => 'Anual'
                                    ])
                                    ->visible(fn (Forms\Get $get) => $get('is_recurring')),

                                Forms\Components\DatePicker::make('next_billing_date')
                                    ->label('Próxima Fecha de Facturación')
                                    ->visible(fn (Forms\Get $get) => $get('is_recurring')),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'primary',
                        'proforma' => 'info',
                        'credit_note' => 'success',
                        'debit_note' => 'warning',
                        'recurring' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'viewed' => 'warning',
                        'overdue' => 'danger',
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'cancelled' => 'gray',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Pagado')
                    ->money('EUR')
                    ->toggleable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('pending_amount')
                    ->label('Pendiente')
                    ->money('EUR')
                    ->toggleable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Emitida')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),

                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('Recurrente')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'sent' => 'Enviada',
                        'viewed' => 'Vista',
                        'overdue' => 'Vencida',
                        'paid' => 'Pagada',
                        'partially_paid' => 'Parcialmente Pagada',
                        'cancelled' => 'Cancelada',
                        'refunded' => 'Reembolsada'
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'standard' => 'Estándar',
                        'proforma' => 'Proforma',
                        'credit_note' => 'Nota de Crédito',
                        'debit_note' => 'Nota de Débito',
                        'recurring' => 'Recurrente'
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_recurring')
                    ->label('Es Recurrente'),

                Tables\Filters\Filter::make('overdue')
                    ->label('Vencidas')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())->whereNotIn('status', ['paid', 'cancelled'])),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('send')
                    ->label('Enviar')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (Invoice $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => $record->markAsSent()),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Marcar como Pagada')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Invoice $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => $record->markAsPaid()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'energyCooperative']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'overdue')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 'overdue')->count() > 0 ? 'danger' : 'success';
    }
}