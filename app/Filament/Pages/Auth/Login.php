<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    // ELIMINAMOS la línea de "protected string $view..." 
    // para que recupere la vista original de Filament.

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'correo_usu' => $data['email'],
            'password' => $data['password'],
        ];
    }
}