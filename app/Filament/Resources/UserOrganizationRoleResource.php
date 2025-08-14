<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserOrganizationRoleResource\Pages;
use App\Models\UserOrganizationRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserOrganizationRoleResource extends Resource
{
    protected static ?string $model = UserOrganizationRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Gestión de Roles';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asignación de Rol')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('organization_role_id')
                            ->label('Rol de la Organización')
                            ->relationship('organizationRole', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get) {
                                $organizationId = $get('organization_id');
                                if (!$organizationId) {
                                    return [];
                                }
                                
                                return \App\Models\OrganizationRole::where('organization_id', $organizationId)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }),

                        Forms\Components\DateTimePicker::make('assigned_at')
                            ->label('Fecha de Asignación')
                            ->default(now())
                            ->required(),
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

                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('organizationRole.name')
                    ->label('Rol')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('organizationRole.description')
                    ->label('Descripción del Rol')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('assigned_at')
                    ->label('Asignado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('Usuario')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('organization')
                    ->relationship('organization', 'name')
                    ->label('Organización')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('organizationRole')
                    ->relationship('organizationRole', 'name')
                    ->label('Rol')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('assigned_at')
                    ->form([
                        Forms\Components\DatePicker::make('assigned_from'),
                        Forms\Components\DatePicker::make('assigned_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['assigned_from'],
                                fn (Builder $query, $date): Builder => $query->dateFilter('assigned_at', '>=', $date),
                            )
                            ->when(
                                $data['assigned_until'],
                                fn (Builder $query, $date): Builder => $query->dateFilter('assigned_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('assigned_at', 'desc');
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
            'index' => Pages\ListUserOrganizationRoles::route('/'),
            'create' => Pages\CreateUserOrganizationRole::route('/create'),
            'edit' => Pages\EditUserOrganizationRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
