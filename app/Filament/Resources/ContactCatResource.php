<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;

use App\Models\ContactCat;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\ContactCatResource\Pages;
use App\Filament\Resources\ContactCatResource\RelationManagers\ContactsRelationManager;



class ContactCatResource extends Resource
{
    protected static ?string $model = ContactCat::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Category Name'),
                    Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),

                // Forms\Components\Select::make('user_id')
                //     ->relationship('user', 'name')
                //     ->searchable()
                //     ->required()
                //     ->preload()
                //     ->label('User'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Category Name'),
                // Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Created At'),
            ])
            ->filters([
                // Tables\Filters\Filter::make('By User')
                //     ->form([
                //         Forms\Components\Select::make('user_id')
                //             ->relationship('user', 'name')
                //             ->searchable()
                //             ->label('User'),
                //     ])
                //     ->query(function ($query, array $data) {
                //         return $query->where('user_id', $data['user_id']);
                //     }),
            ]) ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // ContactsRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactCats::route('/'),
            'create' => Pages\CreateContactCat::route('/create'),
            'edit' => Pages\EditContactCat::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id()) // تصفية السجلات الخاصة بالمستخدم الحالي
            ->with('user'); // تحميل العلاقة مع المستخدم
    }


}
