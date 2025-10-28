<?php

namespace App\Filament\Parent\Resources\Children\Schemas;

use Filament\Schemas\Schema;

class ChildForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
