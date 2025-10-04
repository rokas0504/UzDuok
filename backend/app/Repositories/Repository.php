<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class Repository
{
    protected Model $model;
    protected Builder $query;

    /**
     * @return void
     */
    protected function initializeQuery(): void
    {
        $this->query = $this->model::query();
    }

    /**
     * @param array $data
     * @return Model|null
     */
    public function store(array $data): ?Model
    {
        return $this->model::query()->create($data);
    }

    /**
     * Delete (Destroy) record
     * @param Model $model
     * @return bool|null
     */
    public function destroy(Model $model): bool|null
    {
        return $model->delete();
    }

    /**
     * Update record
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Update or create record
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values): Model
    {
        return $this->model::query()->updateOrCreate($attributes, $values);
    }

    /**
     * @param array $where
     * @return Model
     */
    public function item(array $where): Model
    {
        return $this->model::query()->where($where)->firstOrFail();
    }

    /**
     * @param array $where
     * @return Collection
     */
    public function items(array $where): Collection
    {
        return $this->model::query()->where($where)->get();
    }

    /**
     * @param array $whereIn
     * @return Collection
     */
    public function itemsWithWhereIn(array $whereIn): Collection
    {
        $query = $this->model::query();

        foreach ($whereIn as $column => $values) {
            $query->whereIn($column, $values);
        }

        return $query->get();
    }

    /**
     * @return Collection
     */
    public function list(): Collection
    {
        return $this->model::all();
    }

    /**
     * @param array $where
     * @return LengthAwarePaginator
     */
    public function listPaginated(array $where = []): LengthAwarePaginator
    {
        return $this->model::query()->where($where)->paginate();
    }

    /**
     * Changes column value to opposite.
     * Ex: from active = 1 to active = 0
     * @param Model $model
     * @param string $column
     * @return bool
     */
    public function toggle(Model $model, string $column): bool
    {
        return $model->update(array(
            $column => !$model->$column
        ));
    }

    /**
     * @param string $labelColumn
     * @param string $idColumn
     * @return Collection
     */
    public function selectOptions(string $labelColumn, string $idColumn): Collection
    {
        return $this->model::query()
            ->orderBy($labelColumn)
            ->get([
                $idColumn,
                $labelColumn
            ]);
    }
}
