<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation): void {
                        // Jangan timpa slug yang sudah diubah manual
                        if ($operation !== 'create' || ($get('slug') ?? '') !== Str::slug($old ?? '')) {
                            return;
                        }

                        $set('slug', Str::slug($state ?? ''));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(),
                Textarea::make('summary')
                    ->label('Summary')
                    ->rows(3)
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                FileUpload::make('thumbnail_path')
                    ->label('Thumbnail')
                    ->image()
                    ->disk('public')
                    ->directory('courses'),
                TextInput::make('price')
                    ->label('Single purchase price')
                    ->helperText('Leave empty if the course is not sold individually')
                    ->prefix('Rp')
                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                    ->stripCharacters('.')
                    ->integer()
                    ->minValue(0),
                Toggle::make('is_published')
                    ->label('Published'),
            ]);
    }
}
