<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2) // تنظيم الحقول في عمودين
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter the title for the content')
                            ->label('Title'),

                        Forms\Components\Select::make('file_type')
                            ->options([
                                'video' => '🎥 Video',
                                'image' => '🖼️ Image',
                                'doc' => '📄 Document',
                                'text' => '📝 Text',
                            ])
                            ->required()
                            ->label('File Type')
                            ->reactive(),
                    ]),

                Forms\Components\Textarea::make('des')
                    ->required()
                    ->rows(5)
                    ->placeholder('Write a detailed description for the content...')
                    ->label('Description')
                    ->columnSpanFull(), // اجعل الحقل يأخذ العرض الكامل

                Forms\Components\FileUpload::make('file')
                    ->label('Upload File')
                    ->directory('uploads/contents')
                    ->required()
                    ->visible(fn (callable $get) => $get('file_type') !== 'text') // إظهار فقط إذا لم يكن النوع نصًا
                    ->imagePreviewHeight('100') // معاينة الصور
                    ->downloadable() // تفعيل تنزيل الملفات
                    ->openable() // تفعيل فتح الملفات
                    ->rules(['required_if:file_type,video,image,doc']),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id())
                    ->required(),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('file')
                    ->label('File')
                    ->url(fn ($record) => asset('storage/' . $record->file), true) // رابط لتحميل الملف
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : 'No File') // عرض اسم الملف
                    ->searchable(),

                Tables\Columns\TextColumn::make('file_type')
                ->badge()
                    ->label('File Type')
                    ->colors([
                        'success' => 'text',
                        'primary' => 'image',
                        'danger' => 'video',
                        'warning' => 'doc',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'video' => 'Video',
                        'image' => 'Image',
                        'doc' => 'Document',
                        'text' => 'Text',
                        default => 'Unknown',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('file_type')
                    ->label('Filter by File Type')
                    ->options([
                        'video' => 'Video',
                        'image' => 'Image',
                        'doc' => 'Document',
                        'text' => 'Text',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id()); // تصفية السجلات لتكون خاصة بالمستخدم الحالي
    }
}
