<?php

namespace App\Filament\Parent\Resources\ParentAssignments\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Parent\Resources\ParentAssignments\ParentAssignmentResource;

// ===== ListParentAssignments.php =====
class ListParentAssignments extends ListRecords
{
    protected static string $resource = ParentAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Upload Assignment')
                ->icon('heroicon-o-plus'),
        ];
    }
}

// ===== CreateParentAssignment.php =====
class CreateParentAssignment extends CreateRecord
{
    protected static string $resource = ParentAssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = Auth::user()->parent->id;
        $data['status'] = 'pending';
        $data['uploaded_at'] = now();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Assignment uploaded successfully';
    }
}

// ===== EditParentAssignment.php =====
class EditParentAssignment extends EditRecord
{
    protected static string $resource = ParentAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Assignment updated successfully';
    }

    // Prevent editing if already submitted
    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        $record = $this->getRecord();
        
        if ($record->status !== 'pending') {
            abort(403, 'Cannot edit submitted assignments');
        }
    }
}

// ===== ViewParentAssignment.php =====
class ViewParentAssignment extends ViewRecord
{
    protected static string $resource = ParentAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->getRecord()->status === 'pending'),
        ];
    }
}