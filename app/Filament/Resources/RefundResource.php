<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundResource\Pages;
use App\Models\Refund;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = 'Gestión Financiera';

    protected static ?string $modelLabel = 'Reembolso';

    protected static ?string $pluralModelLabel = 'Reembolsos';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('refund_code')
                                    ->label('Código')
                                    ->default(fn () => Refund::generateRefundCode())
                                    ->required(),

                                Forms\Components\Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable(),

                                Forms\Components\Select::make('payment_id')
                                    ->label('Pago Original')
                                    ->relationship('payment', 'payment_code')
                                    ->required()
                                    ->searchable(),

                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'full' => 'Completo',
                                        'partial' => 'Parcial',
                                        'overpayment' => 'Sobrepago',
                                        'chargeback' => 'Chargeback'
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('reason')
                                    ->label('Razón')
                                    ->options([
                                        'customer_request' => 'Solicitud del Cliente',
                                        'service_cancellation' => 'Cancelación de Servicio',
                                        'billing_error' => 'Error de Facturación',
                                        'technical_error' => 'Error Técnico'
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('refund_amount')
                                    ->label('Monto a Reembolsar')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€'),
                            ]),
                    ]),

                Forms\Components\Section::make('Descripción')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('refund_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment.payment_code')
                    ->label('Pago')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Monto')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('requested_at')
                    ->label('Solicitado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'completed' => 'Completado',
                        'failed' => 'Fallido'
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'full' => 'Completo',
                        'partial' => 'Parcial'
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefunds::route('/'),
            'create' => Pages\CreateRefund::route('/create'),
            'view' => Pages\ViewRefund::route('/{record}'),
            'edit' => Pages\EditRefund::route('/{record}/edit'),
        ];
    }
}