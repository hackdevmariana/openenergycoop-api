<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegalDocumentResource\Pages;
use App\Models\LegalDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\CommonEnums;

class LegalDocumentResource extends Resource
{
    protected static ?string $model = LegalDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Clientes';
    protected static ?string $navigationLabel = 'Documentos Legales';
    protected static ?string $pluralModelLabel = 'Documentos Legales';
    protected static ?string $modelLabel = 'Documento Legal';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del documento')
                    ->schema([
                        Forms\Components\Select::make('customer_profile_id')
                            ->relationship('customerProfile', 'legal_name')
                            ->searchable()
                            ->required()
                            ->label('Perfil de Cliente'),

                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->required()
                            ->label('Organización'),

                        Forms\Components\Select::make('type')
                            ->options(CommonEnums::DOCUMENT_TYPES)
                            ->required()
                            ->label('Tipo de Documento'),

                        Forms\Components\DateTimePicker::make('uploaded_at')
                            ->required()
                            ->label('Fecha de Subida')
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Fecha de Verificación')
                            ->after('uploaded_at'),

                        Forms\Components\Select::make('verifier_user_id')
                            ->relationship('verifier', 'name')
                            ->searchable()
                            ->label('Verificado por')
                            ->placeholder('Seleccionar verificador'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Archivo del documento')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('document')
                            ->collection('legal_documents')
                            ->label('Documento')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/gif'])
                            ->maxSize(10240) // 10MB
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable()
                            ->helperText('Formatos permitidos: PDF, JPG, PNG, GIF. Máximo 10MB.')
                            ->required(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customerProfile.legal_name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dni' => 'success',
                        'iban_receipt' => 'info',
                        'contract' => 'warning',
                        'invoice' => 'primary',
                        'other' => 'gray',
                    }),

                Tables\Columns\SpatieMediaLibraryImageColumn::make('document')
                    ->collection('legal_documents')
                    ->label('Vista Previa')
                    ->circular()
                    ->width(40)
                    ->height(40),

                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Subido')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verificado')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Pendiente de verificación')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verificador')
                    ->sortable()
                    ->placeholder('Sin verificar'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('organization_id')
                    ->relationship('organization', 'name')
                    ->label('Filtrar por Organización'),

                Tables\Filters\SelectFilter::make('type')
                    ->options(CommonEnums::DOCUMENT_TYPES)
                    ->label('Filtrar por Tipo'),

                Tables\Filters\Filter::make('verified')
                    ->label('Solo verificados')
                    ->query(fn ($query) => $query->whereNotNull('verified_at')),

                Tables\Filters\Filter::make('pending_verification')
                    ->label('Pendientes de verificación')
                    ->query(fn ($query) => $query->whereNull('verified_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->label('Verificar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (LegalDocument $record) {
                        $record->update([
                            'verified_at' => now(),
                            'verifier_user_id' => auth()->id(),
                        ]);
                    })
                    ->visible(fn (LegalDocument $record) => is_null($record->verified_at))
                    ->requiresConfirmation(),

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
            'index' => Pages\ListLegalDocuments::route('/'),
            'create' => Pages\CreateLegalDocument::route('/create'),
            'edit' => Pages\EditLegalDocument::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pendingVerificationCount = static::getModel()::whereNull('verified_at')->count();
        
        if ($pendingVerificationCount > 0) {
            return 'warning';  // 🟡 Naranja para documentos pendientes de verificación
        }
        
        $verifiedCount = static::getModel()::whereNotNull('verified_at')->count();
        $totalCount = static::getModel()::count();
        
        if ($verifiedCount === $totalCount && $totalCount > 0) {
            return 'success';  // 🟢 Verde cuando todos los documentos están verificados
        }
        
        return 'info';         // 🔵 Azul para estado mixto
    }
}
