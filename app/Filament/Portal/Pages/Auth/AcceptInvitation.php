<?php

namespace App\Filament\Portal\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Locked;

/**
 * Gast-Seite im Portal: der eingeladene Nutzer setzt sein Passwort und bestätigt damit seine E-Mail-Adresse.
 *
 * Erreichbar nur über den signierten, zeitlich begrenzten Link aus der Einladungsmail.
 *
 * @property-read Schema $form
 */
class AcceptInvitation extends SimplePage
{
    use WithRateLimiting;

    #[Locked]
    public ?int $userId = null;

    public ?string $email = null;

    public ?string $password = '';

    public ?string $passwordConfirmation = '';

    public function mount(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            Notification::make()
                ->title(__('skriptdepot.invitation.already_accepted'))
                ->info()
                ->send();

            $this->redirect(Filament::getLoginUrl());

            return;
        }

        $this->userId = $user->id;
        $this->form->fill(['email' => $user->email]);
    }

    public function acceptInvitation(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::auth/pages/password-reset/reset-password.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => $exception->minutesUntilAvailable,
                ]))
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $user = User::findOrFail($this->userId);

        if ($user->hasVerifiedEmail()) {
            $this->redirect(Filament::getLoginUrl());

            return;
        }

        $user->forceFill([
            'password' => $data['password'],
            'email_verified_at' => now(),
            'remember_token' => Str::random(60),
        ])->save();

        Filament::auth()->login($user);
        session()->regenerate();

        Notification::make()
            ->title(__('skriptdepot.invitation.accepted'))
            ->success()
            ->send();

        $this->redirect(Filament::getUrl());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label(__('skriptdepot.fields.email'))
                    ->disabled(),
                TextInput::make('password')
                    ->label(__('skriptdepot.fields.password'))
                    ->password()
                    ->autocomplete('new-password')
                    ->revealable()
                    ->required()
                    ->rule(PasswordRule::default())
                    ->same('passwordConfirmation')
                    ->validationAttribute(__('skriptdepot.fields.password')),
                TextInput::make('passwordConfirmation')
                    ->label(__('skriptdepot.fields.password_confirmation'))
                    ->password()
                    ->autocomplete('new-password')
                    ->revealable()
                    ->required()
                    ->dehydrated(false),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('acceptInvitation')
            ->footer([
                Actions::make([
                    Action::make('acceptInvitation')
                        ->label(__('skriptdepot.invitation.submit'))
                        ->submit('acceptInvitation'),
                ])
                    ->fullWidth()
                    ->key('form-actions'),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('skriptdepot.invitation.title');
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('skriptdepot.invitation.heading');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('skriptdepot.invitation.subheading');
    }
}
