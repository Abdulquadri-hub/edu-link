<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): ?Collection
    {
        return $this->model->all();
    }

    public function create(array $data): ?Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $record = $this->findOrFail($id);
        return $record->update($data);
    }
    
    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete($id);
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): ?Model
    {
       return $this->model->findOrFail($id);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function forceDelete(int $id): bool
    {
        $record = $this->model->withTrashed()->findOrFail($id);
        return $record->forceDelete();
    }

    public function restore(int $id): bool
    {
        $record = $this->model->withTrashed()->findOrFail($id);
        return $record->restore();
    }

}
