<?php

namespace App\Filament\Resources\ContactCatResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts'; // Ensure this matches the relationship in the ContactCat model

    protected static ?string $title = 'Contacts';
    protected static ?string $label = 'Contact';
    protected static ?string $pluralLabel = 'Contacts';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone'),
            ])
            ->filters([]) // Add filters if needed
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Name'),
                    Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\TextInput::make('phone')
                    ->required()
                    ->label('Phone'),
            ]);
    }
}