<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

/**
 * Profilseite mit zusätzlicher Sprachauswahl; gilt für Admin- und Portal-Panel.
 */
class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                Select::make('locale')
                    ->label(__('skriptdepot.fields.locale'))
                    ->options(fn (): array => collect(config('skriptdepot.locales'))
                        ->mapWithKeys(fn (string $locale) => [$locale => __("skriptdepot.locales.{$locale}")])
                        ->all())
                    ->required()
                    ->native(false),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }
}
