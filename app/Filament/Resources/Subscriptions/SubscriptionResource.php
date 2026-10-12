<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\ManageSubscriptions;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $modelLabel = 'langganan';

    protected static ?string $pluralModelLabel = 'langganan';

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
                Select::make('plan_id')
                    ->label('Paket')
                    ->relationship('plan', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        $plan = Plan::find($state);

                        if (! $plan) {
                            return;
                        }

                        $set('ends_at', Carbon::parse($get('starts_at') ?? now())
                            ->addDays($plan->duration_days)
                            ->toDateTimeString());
                    }),
                DateTimePicker::make('starts_at')
                    ->label('Mulai')
                    ->default(now())
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('Berakhir')
                    ->helperText('Terisi otomatis dari durasi paket, bisa diubah.')
                    ->after('starts_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ends_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->description(fn (Subscription $record): string => $record->user->email)
                    ->searchable(['name', 'email']),
                TextColumn::make('plan.name')
                    ->label('Paket'),
                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Subscription $record): string => match (true) {
                        $record->ends_at->isPast() => 'Berakhir',
                        $record->starts_at->isFuture() => 'Belum mulai',
                        default => 'Aktif',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Belum mulai' => 'info',
                        default => 'gray',
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSubscriptions::route('/'),
        ];
    }
}
