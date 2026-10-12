<?php

namespace App\Filament\Resources\CoursePurchases;

use App\Filament\Resources\CoursePurchases\Pages\ManageCoursePurchases;
use App\Models\CoursePurchase;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class CoursePurchaseResource extends Resource
{
    protected static ?string $model = CoursePurchase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $modelLabel = 'pembelian course';

    protected static ?string $pluralModelLabel = 'pembelian course';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn (User $record): string => "{$record->name} ({$record->email})")
                    ->searchable(['name', 'email'])
                    ->required(),
                Select::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('user_id', $get('user_id')))
                    ->validationMessages(['unique' => 'User ini sudah punya akses ke course tersebut.']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->description(fn (CoursePurchase $record): string => $record->user->email)
                    ->searchable(['name', 'email']),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCoursePurchases::route('/'),
        ];
    }
}
