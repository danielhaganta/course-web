<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\ManageSubscriptions;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
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

    protected static ?string $modelLabel = 'subscription';

    protected static ?string $pluralModelLabel = 'subscriptions';

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
                    ->label('Plan')
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
                    ->label('Starts')
                    ->default(now())
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('Ends')
                    ->helperText('Filled in from the plan duration. You can change it.')
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
                    ->label('Plan'),
                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Subscription $record): string => match (true) {
                        $record->ends_at->isPast() => 'Ended',
                        $record->starts_at->isFuture() => 'Not started',
                        default => 'Active',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Not started' => 'info',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Action::make('endNow')
                    ->label('End now')
                    ->icon(Heroicon::OutlinedStopCircle)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('The member loses access from this subscription immediately.')
                    ->visible(fn (Subscription $record): bool => $record->ends_at->isFuture())
                    ->action(fn (Subscription $record) => $record->update(['ends_at' => now()]))
                    ->successNotificationTitle('Subscription ended'),
                // Langganan dari order berbayar tetap disimpan sebagai riwayat, cukup diakhiri
                DeleteAction::make()
                    ->hidden(fn (Subscription $record): bool => $record->order_id !== null),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSubscriptions::route('/'),
        ];
    }
}
