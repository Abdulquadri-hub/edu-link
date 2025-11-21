<?php

namespace App\Filament\Admin\Resources\ChildLinkingRequests\Pages;

use App\Filament\Admin\Resources\ChildLinkingRequests\ChildLinkingRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChildLinkingRequest extends CreateRecord
{
    protected static string $resource = ChildLinkingRequestResource::class;
}
