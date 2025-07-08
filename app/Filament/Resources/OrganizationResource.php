<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\IconColumn;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Gestión';
    protected static ?string $navigationLabel = 'Organizations';
    protected static ?string $pluralModelLabel = 'Organizations';
    protected static ?string $modelLabel = 'Organization';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información básica')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('slug')
                                ->required()
                                ->unique(ignoreRecord: true),

                            TextInput::make('domain')
                                ->label('Dominio')
                                ->nullable()
                                ->maxLength(255),

                            TextInput::make('contact_email')
                                ->email()
                                ->nullable(),

                            TextInput::make('contact_phone')
                                ->tel()
                                ->nullable(),
                        ]),

                        ColorPicker::make('primary_color'),
                        ColorPicker::make('secondary_color'),

                        TextInput::make('css_files')
                            ->label('Archivos CSS personalizados')
                            ->nullable(),

                    ]),

                Section::make('Ajustes de la cooperativa')
                    ->relationship('appSettings')
                    ->schema([
                        TextInput::make('slogan')->label('Eslogan')->maxLength(255),
                        ColorPicker::make('primary_color')->label('Color primario'),
                        ColorPicker::make('secondary_color')->label('Color secundario'),
                        TextInput::make('locale')->label('Idioma')->default('es'),
                        Textarea::make('custom_js')->label('JS personalizado')->rows(4),

                        SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('logo')
                            ->label('Logo')
                            ->image()
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable(),

                        SpatieMediaLibraryFileUpload::make('favicon')
                            ->collection('favicon')
                            ->label('Favicon')
                            ->image()
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable(),

                    ])
                    ->columns(2)

            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('domain'),
                IconColumn::make('active')->boolean()->label('Activa'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
