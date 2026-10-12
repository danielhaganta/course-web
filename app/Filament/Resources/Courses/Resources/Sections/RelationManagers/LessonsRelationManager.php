<?php

namespace App\Filament\Resources\Courses\Resources\Sections\RelationManagers;

use App\Enums\LessonType;
use App\Models\LessonAttachment;
use App\Services\BunnyStream;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Lessons';

    protected static ?string $modelLabel = 'lesson';

    protected static ?string $pluralModelLabel = 'lessons';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Type')
                    ->options(LessonType::class)
                    ->default(LessonType::Video)
                    ->required()
                    ->live(),
                TextInput::make('bunny_video_id')
                    ->label('Bunny Video ID')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => $get('type') === LessonType::Video)
                    ->required(fn (Get $get): bool => $get('type') === LessonType::Video),
                TextInput::make('duration_seconds')
                    ->label('Duration')
                    ->helperText('In seconds. For videos, it is filled in from Bunny on save.')
                    ->suffix('seconds')
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                RichEditor::make('content')
                    ->label('Content')
                    ->visible(fn (Get $get): bool => $get('type') === LessonType::Text)
                    ->columnSpanFull(),
                Toggle::make('is_preview')
                    ->label('Preview')
                    ->helperText('Watchable without a subscription'),
                Toggle::make('is_exclusive')
                    ->label('Exclusive')
                    ->helperText('Premium members only'),
                Repeater::make('attachments')
                    ->label('Practice files')
                    ->relationship()
                    ->addActionLabel('Add file')
                    ->defaultItems(0)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('path')
                            ->label('File')
                            ->disk(LessonAttachment::diskName())
                            ->visibility('private')
                            ->maxSize(Config::integer('filesystems.attachments.max_size_mb') * 1024)
                            // Disimpan manual setelah lesson punya id, lihat storeAttachment()
                            ->storeFiles(false)
                            ->previewable(false)
                            ->required(),
                        TextInput::make('name')
                            ->label('Display name')
                            ->helperText('Leave empty to use the original file name.')
                            ->maxLength(255),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data, Repeater $component): array => $this->storeAttachment($data, $component))
                    ->mutateRelationshipDataBeforeSaveUsing(fn (array $data, Repeater $component): array => $this->storeAttachment($data, $component)),
            ]);
    }

    /**
     * File baru disimpan di folder per lesson, size_bytes dan nama diisi dari file asli.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function storeAttachment(array $data, Repeater $component): array
    {
        $file = Arr::first(Arr::wrap($data['path'] ?? null));

        if (blank($data['name'] ?? null)) {
            unset($data['name']);
        }

        if (! $file instanceof TemporaryUploadedFile) {
            return [...$data, 'path' => $file];
        }

        $lessonId = $component->getRelationship()->getParent()->getKey();
        $extension = $file->getClientOriginalExtension();
        $attributes = [
            'name' => $file->getClientOriginalName(),
            ...$data,
            'path' => $file->storeAs(
                "lessons/{$lessonId}",
                Str::random(40).($extension === '' ? '' : ".{$extension}"),
                LessonAttachment::diskName(),
            ) ?: throw new RuntimeException('Could not save the practice file.'),
            'size_bytes' => $file->getSize(),
        ];
        $file->delete();

        return $attributes;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label('Title'),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(fn (int $state): string => sprintf('%d:%02d', intdiv($state, 60), $state % 60)),
                IconColumn::make('is_preview')
                    ->label('Preview')
                    ->boolean(),
                IconColumn::make('is_exclusive')
                    ->label('Exclusive')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->fillDurationFromBunny($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->fillDurationFromBunny($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Gagal ambil durasi tidak boleh menggagalkan simpan, nilai lama dipertahankan.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function fillDurationFromBunny(array $data): array
    {
        $isVideo = in_array($data['type'] ?? null, [LessonType::Video, LessonType::Video->value], true);

        if (! $isVideo || blank($data['bunny_video_id'] ?? null)) {
            return $data;
        }

        $duration = app(BunnyStream::class)->videoDuration($data['bunny_video_id']);

        return $duration ? [...$data, 'duration_seconds' => $duration] : $data;
    }
}
