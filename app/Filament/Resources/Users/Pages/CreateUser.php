<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\SendUserInvitation;
use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Direkt nach dem Anlegen geht die Einladungsmail raus.
     */
    protected function afterCreate(): void
    {
        app(SendUserInvitation::class)->handle($this->record);

        Notification::make()
            ->title(__('skriptdepot.notifications.invitation_sent', ['email' => $this->record->email]))
            ->success()
            ->send();
    }
}
