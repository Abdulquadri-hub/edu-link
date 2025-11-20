<?php

namespace App\Filament\Parent\Resources\LinkChildren\Pages;

use App\Filament\Parent\Resources\LinkChildren\LinkChildResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLinkChild extends CreateRecord
{
    protected static string $resource = LinkChildResource::class;
}
