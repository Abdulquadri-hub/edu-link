<?php

namespace App\Filament\Parent\Resources\Children\Pages;

use App\Filament\Parent\Resources\Children\ChildResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChild extends CreateRecord
{
    protected static string $resource = ChildResource::class;
}
