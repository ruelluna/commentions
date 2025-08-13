<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Concerns\HasSidebar;

class SubscriptionTableAction extends Action
{
    use HasSidebar;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (Model $record): string => $this->computeSubscriptionLabel($record))
            ->icon(fn (Model $record): string => $this->computeSubscriptionIcon($record))
            ->color(fn (Model $record): string => $this->computeSubscriptionColor($record, 'table'))
            ->action(function (Model $record) {
                $subscribed = $this->toggleSubscriptionForRecord($record);

                if ($subscribed === null) {
                    return;
                }

                $this->successNotificationTitle(
                    $subscribed ? 'Subscribed to notifications' : 'Unsubscribed from notifications'
                );

                $this->success();

                // Ask any listening components (like the sidebar) to refresh
                /** @var \Livewire\Component|null $livewire */
                $livewire = $this->getLivewire();
                $livewire?->dispatch('commentions:subscription:toggled')->to('commentions::subscription-sidebar');
            })
            ->requiresConfirmation(false);
    }

    public static function getDefaultName(): ?string
    {
        return 'subscriptionList';
    }
}
