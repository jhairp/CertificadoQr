<?php

namespace App\Filament\Resources\UsuarioResource\Pages;

use App\Filament\Resources\UsuarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsuarios extends ListRecords
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        
        // Verificar si el usuario tiene permisos para crear usando el método estático del Resource
        if (UsuarioResource::canCreate()) {
            $actions[] = CreateAction::make()
                ->label('Nuevo usuario');
        }
        
        return $actions;
    }
}