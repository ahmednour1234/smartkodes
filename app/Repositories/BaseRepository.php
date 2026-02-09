<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;
    protected ?string $tenantId;

    public function __construct()
    {
        $this->model = $this->makeModel();
        $this->tenantId = $this->getTenantId();
    }

    abstract protected function model(): string;

    protected function makeModel(): Model
    {
        $model = app($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $model;
    }

    protected function getTenantId(): ?string
    {
        return auth('api')->user()?->tenant_id;
    }

    protected function applyTenantScope($query)
    {
        if ($this->tenantId && $this->hasTenantColumn()) {
            return $query->where('tenant_id', $this->tenantId);
        }
        return $query;
    }

    protected function hasTenantColumn(): bool
    {
        return in_array('tenant_id', $this->model->getFillable());
    }

    public function all(array $columns = ['*']): Collection
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);
        return $query->get($columns);
    }

    public function find(string $id)
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);
        return $query->findOrFail($id);
    }

    public function findBy(string $field, $value)
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);
        return $query->where($field, $value)->first();
    }

    public function create(array $data)
    {
        if ($this->hasTenantColumn() && $this->tenantId) {
            $data['tenant_id'] = $this->tenantId;
        }

        if (in_array('created_by', $this->model->getFillable())) {
            $data['created_by'] = auth('api')->id();
        }

        if (in_array('updated_by', $this->model->getFillable())) {
            $data['updated_by'] = auth('api')->id();
        }

        return $this->model->create($data);
    }

    public function update(string $id, array $data)
    {
        $record = $this->find($id);

        if (in_array('updated_by', $this->model->getFillable())) {
            $data['updated_by'] = auth('api')->id();
        }

        $record->update($data);
        return $record->fresh();
    }

    public function delete(string $id): bool
    {
        $record = $this->find($id);
        return $record->delete();
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);
        return $query->paginate($perPage, $columns);
    }

    public function where(array $conditions)
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    public function with(array $relations)
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);
        return $query->with($relations);
    }
}

