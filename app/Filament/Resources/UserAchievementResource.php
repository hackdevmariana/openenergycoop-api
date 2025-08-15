<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAchievementResource\Pages;
use App\Filament\Resources\UserAchievementResource\RelationManagers;
use App\Models\UserAchievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserAchievementResource extends Resource
{
    protected static ?string $model = UserAchievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Gamificación';

    protected static ?string $navigationLabel = 'Logros de Usuarios';

    protected static ?string $modelLabel = 'Logro de Usuario';

    protected static ?string $pluralModelLabel = 'Logros de Usuarios';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Logro')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('achievement_id')
                            ->label('Achievement')
                            ->relationship('achievement', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('earned_at')
                            ->label('Obtenido en')
                            ->required()
                            ->default(now()),

                        Forms\Components\Textarea::make('custom_message')
                            ->label('Mensaje Personalizado')
                            ->maxLength(500)
                            ->rows(3),

                        Forms\Components\Toggle::make('reward_granted')
                            ->label('Recompensa Otorgada')
                            ->default(false)
                            ->helperText('Marca si se ha otorgado algún premio por este logro'),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('achievement.name')
                    ->label('Achievement')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('achievement.type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'energy' => 'success',
                        'participation' => 'info',
                        'community' => 'warning',
                        'milestone' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('achievement.points_reward')
                    ->label('Puntos')
                    ->numeric()
                    ->sortable()
                    ->suffix(' pts'),

                Tables\Columns\IconColumn::make('reward_granted')
                    ->label('Recompensa')
                    ->boolean()
                    ->trueIcon('heroicon-o-gift')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('earned_at')
                    ->label('Obtenido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('custom_message')
                    ->label('Mensaje')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state ? $state : null;
                    })
                    ->placeholder('Sin mensaje'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('achievement.type')
                    ->label('Tipo de Achievement')
                    ->relationship('achievement', 'type')
                    ->options([
                        'energy' => 'Energía',
                        'participation' => 'Participación',
                        'community' => 'Comunidad',
                        'milestone' => 'Hito',
                    ]),

                Tables\Filters\TernaryFilter::make('reward_granted')
                    ->label('Recompensa Otorgada'),

                Tables\Filters\Filter::make('recent')
                    ->label('Recientes (última semana)')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('earned_at', '>=', now()->subWeek())
                    ),

                Tables\Filters\Filter::make('high_points')
                    ->label('Alto Puntaje (>100 pts)')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('achievement', fn (Builder $query) =>
                            $query->where('points_reward', '>', 100)
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('grant_reward')
                    ->label('Otorgar Recompensa')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (UserAchievement $record) => $record->grantReward())
                    ->visible(fn (UserAchievement $record): bool => !$record->reward_granted),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('grant_rewards')
                        ->label('Otorgar Recompensas')
                        ->icon('heroicon-o-gift')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (!$record->reward_granted) {
                                    $record->grantReward();
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('earned_at', 'desc');
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
            'index' => Pages\ListUserAchievements::route('/'),
            'create' => Pages\CreateUserAchievement::route('/create'),
            'edit' => Pages\EditUserAchievement::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::pendingReward()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
