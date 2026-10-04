<?php

namespace App\Filament\Resources\Courses\Resources\Sections;

use App\Filament\Resources\Courses\CourseResource;
use App\Filament\Resources\Courses\Resources\Sections\Pages\CreateSection;
use App\Filament\Resources\Courses\Resources\Sections\Pages\EditSection;
use App\Filament\Resources\Courses\Resources\Sections\RelationManagers\LessonsRelationManager;
use App\Filament\Resources\Courses\Resources\Sections\Schemas\SectionForm;
use App\Filament\Resources\Courses\Resources\Sections\Tables\SectionsTable;
use App\Models\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $parentResource = CourseResource::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'bagian';

    protected static ?string $pluralModelLabel = 'bagian';

    public static function form(Schema $schema): Schema
    {
        return SectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'lessons' => LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateSection::route('/create'),
            'edit' => EditSection::route('/{record}/edit'),
        ];
    }
}
