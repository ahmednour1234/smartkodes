<?php

namespace App\Repositories;

use App\Models\Form;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FormRepository extends BaseRepository
{
    protected function model(): string
    {
        return Form::class;
    }

    /**
     * Get forms with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by category
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Search by name or description
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Eager load relationships
        $query->with(['formFields' => function ($q) {
            $q->orderBy('order', 'asc');
        }, 'category']);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get form with all fields for submission
     *
     * @param string $id
     * @return Form
     */
    public function getForSubmission(string $id): Form
    {
        return $this->with([
            'formFields' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'category'
        ])->find($id);
    }

    /**
     * Find published forms
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findPublished()
    {
        return $this->where(['status' => 1])->get();
    }
}

