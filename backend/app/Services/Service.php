<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class Service
{
    protected Repository $repository;

    /**
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->repository->store($data);
    }

    /**
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function update(Model $model, array $data): bool
    {
        return $this->repository->update($model, $data);
    }

    /**
     * @param Model $model
     * @return bool|null
     */
    public function destroy(Model $model): bool|null
    {
        return $this->repository->destroy($model);
    }

    /**
     * @param array $where
     * @return Collection
     */
    public function items(array $where = []): Collection
    {
        return $this->repository->items($where);
    }

    /**
     * @return Collection
     */
    public function getList(): Collection
    {
        return $this->repository->list();
    }

    /**
     * @param array $where
     * @return LengthAwarePaginator
     */
    public function getPaginatedList(array $where = []): LengthAwarePaginator
    {
        return $this->repository->listPaginated($where);
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
        return $this->repository->toggle($model, $column);
    }

    /**
     * @param string $labelColumn
     * @param string $idColumn
     * @return Collection
     */
    public function getSelectOptions(string $labelColumn = 'name', string $idColumn = 'id'): Collection
    {
        return $this->repository->selectOptions($labelColumn, $idColumn);
    }
}
