<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Concerns\HasSidebar;

class SubscriptionAction extends Action
{
    use HasSidebar;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (Model $record): string => $this->computeSubscriptionLabel($record))
            ->icon(fn (Model $record): string => $this->computeSubscriptionIcon($record))
            ->color(fn (Model $record): string => $this->computeSubscriptionColor($record, 'action'))
            ->action(function (Model $record) {
                $subscribed = $this->toggleSubscriptionForRecord($record);

                if ($subscribed === null) {
                    return;
                }

                $this->success($subscribed ? 'Subscribed to notifications' : 'Unsubscribed from notifications');
            })
            ->requiresConfirmation(false);
    }

    public static function getDefaultName(): ?string
    {
        return 'subscription';
    }
}
