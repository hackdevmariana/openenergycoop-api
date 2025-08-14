<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationRoleResource\Pages;
use App\Models\OrganizationRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrganizationRoleResource extends Resource
{
    protected static ?string $model = OrganizationRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Gestión de Roles';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Rol')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Rol')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Administrador de Proyectos'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->placeholder('Se genera automáticamente')
                            ->helperText('Identificador único del rol (se genera automáticamente si se deja vacío)'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->placeholder('Descripción del rol y sus responsabilidades'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permisos')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Permisos del Rol')
                            ->options([
                                'customer-profile.view-any' => 'Ver todos los perfiles',
                                'customer-profile.view-own' => 'Ver perfiles propios',
                                'customer-profile.view-org' => 'Ver perfiles de la organización',
                                'customer-profile.create' => 'Crear perfiles',
                                'customer-profile.update-own' => 'Actualizar perfiles propios',
                                'customer-profile.update-org' => 'Actualizar perfiles de la organización',
                                'customer-profile.delete-own' => 'Eliminar perfiles propios',
                                'customer-profile.delete-org' => 'Eliminar perfiles de la organización',
                                'customer-profile.manage-all' => 'Gestionar todos los perfiles',
                                'organization.view' => 'Ver organización',
                                'organization.update' => 'Actualizar organización',
                                'user.view-own' => 'Ver usuarios propios',
                                'user.view-org' => 'Ver usuarios de la organización',
                                'user.create' => 'Crear usuarios',
                                'user.update-own' => 'Actualizar usuarios propios',
                                'user.update-org' => 'Actualizar usuarios de la organización',
                                'subscription-request.view' => 'Ver solicitudes',
                                'subscription-request.process' => 'Procesar solicitudes',
                                'company.view' => 'Ver empresas',
                                'company.create' => 'Crear empresas',
                                'company.update' => 'Actualizar empresas',
                            ])
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('organization')
                    ->relationship('organization', 'name')
                    ->label('Organización')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->dateFilter('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->dateFilter('created_at', '<=', $date),
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
            'index' => Pages\ListOrganizationRoles::route('/'),
            'create' => Pages\CreateOrganizationRole::route('/create'),
            'edit' => Pages\EditOrganizationRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
