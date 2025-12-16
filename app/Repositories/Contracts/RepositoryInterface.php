<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function all(array $columns = ['*']): Collection;
    public function find(string $id);
    public function findBy(string $field, $value);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id): bool;
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
    public function where(array $conditions);
    public function with(array $relations);
}

