<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvitationTokenResource\Pages;
use App\Filament\Resources\InvitationTokenResource\RelationManagers;
use App\Models\InvitationToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvitationTokenResource extends Resource
{
    protected static ?string $model = InvitationToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Gestión de Usuarios';

    protected static ?string $navigationLabel = 'Tokens de Invitación';

    protected static ?string $modelLabel = 'Token de Invitación';

    protected static ?string $pluralModelLabel = 'Tokens de Invitación';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Invitación')
                    ->schema([
                        Forms\Components\TextInput::make('token')
                            ->label('Token')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Se genera automáticamente'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email del Invitado')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Opcional - email predefinido del invitado')
                            ->helperText('Si se especifica, solo esta dirección podrá usar el token'),

                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('organization_role_id')
                            ->label('Rol a Asignar')
                            ->relationship('organizationRole', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('invited_by')
                            ->label('Invitado por')
                            ->relationship('invitedByUser', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuración de Expiración')
                    ->schema([
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Fecha de Expiración')
                            ->nullable()
                            ->helperText('Dejar vacío para que nunca expire')
                            ->default(now()->addDays(7)),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'used' => 'Usado',
                                'expired' => 'Expirado',
                                'revoked' => 'Revocado',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\DateTimePicker::make('used_at')
                            ->label('Usado en')
                            ->disabled()
                            ->helperText('Se marca automáticamente cuando se usa'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('token')
                    ->label('Token')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Token copiado al portapapeles')
                    ->fontFamily('mono')
                    ->limit(20),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('Sin especificar')
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('organizationRole.name')
                    ->label('Rol')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('invitedByUser.name')
                    ->label('Invitado por')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'used' => 'success',
                        'expired' => 'danger',
                        'revoked' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'used' => 'Usado',
                        'expired' => 'Expirado',
                        'revoked' => 'Revocado',
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expira')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Nunca')
                    ->color(fn ($record) => $record?->isExpired() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('used_at')
                    ->label('Usado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No usado'),

                Tables\Columns\TextColumn::make('created_at')
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
                        'used' => 'Usado',
                        'expired' => 'Expirado',
                        'revoked' => 'Revocado',
                    ]),

                Tables\Filters\SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('expires_soon')
                    ->label('Expira pronto')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('expires_at', '<=', now()->addDays(7))
                              ->where('expires_at', '>', now())
                    ),

                Tables\Filters\Filter::make('expired')
                    ->label('Expirados')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('expires_at', '<', now())
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('copy_url')
                    ->label('Copiar URL')
                    ->icon('heroicon-o-clipboard')
                    ->action(function (InvitationToken $record) {
                        \Filament\Notifications\Notification::make()
                            ->title('URL de invitación')
                            ->body("URL: {$record->invitation_url}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (InvitationToken $record): bool => $record->isValid()),

                Tables\Actions\Action::make('revoke')
                    ->label('Revocar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (InvitationToken $record) => $record->revoke())
                    ->visible(fn (InvitationToken $record): bool => $record->status === 'pending'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('revoke_selected')
                        ->label('Revocar seleccionados')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->revoke();
                                }
                            }
                        }),
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
            'index' => Pages\ListInvitationTokens::route('/'),
            'create' => Pages\CreateInvitationToken::route('/create'),
            'edit' => Pages\EditInvitationToken::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
