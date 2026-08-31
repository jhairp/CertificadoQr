<?php

namespace App\Filament\Pages\Auth;

use App\Models\Usuario;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    protected static string $layout = 'filament-panels::components.layout.base';

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'correo_usu' => $data['email'],
            'password' => $data['password'],
        ];
    }

    /**
     * Personaliza el mensaje cuando las credenciales no son válidas.
     *
     * Si el correo existe, la contraseña es correcta y la cuenta
     * está deshabilitada, se muestra el mensaje específico.
     */
    protected function throwFailureValidationException(): never
    {
        $email = $this->data['email'] ?? null;
        $password = $this->data['password'] ?? null;

        if (filled($email) && filled($password)) {
            $usuario = Usuario::where('correo_usu', $email)->first();

            if (
                $usuario !== null &&
                ! $usuario->estado_usu &&
                Hash::check($password, $usuario->password_usu)
            ) {
                throw ValidationException::withMessages([
                    'data.email' => 'Esta cuenta ha sido deshabilitada.',
                ]);
            }
        }

        parent::throwFailureValidationException();
    }
}