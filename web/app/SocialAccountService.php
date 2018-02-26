<?php

namespace App;

use Laravel\Socialite\Contracts\Provider;
use Illuminate\Support\Facades\Hash;

class SocialAccountService
{
    public function createOrGetUser(Provider $provider)
    {
        $providerUser = $provider->user();
        $providerName = class_basename($provider);

        $account = SocialAccount::whereProvider($providerName)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {
            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider'         => $providerName,
                'avatar'           => $providerUser->getAvatar(),
            ]);

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'email'          => $providerUser->getEmail(),
                    'name'           => $providerUser->getName(),
                    'password'       => Hash::make(time()),
                    'remember_token' => true,
                ]);
            }

            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }
}