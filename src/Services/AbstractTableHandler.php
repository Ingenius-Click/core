<?php

namespace Ingenius\Core\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class AbstractTableHandler
{
    protected $perPage = 15;

    public function paginate(array $data, Builder $query): LengthAwarePaginator
    {
        $this->search($data, $query)
            ->filter($data, $query)
            ->sort($data, $query);

        $this->perPage = $data['per_page'] ?? $this->perPage;

        return $query->paginate($this->perPage);
    }

    protected abstract function filter(array $data, Builder $query): AbstractTableHandler;

    protected abstract function sort(array $data, Builder $query): AbstractTableHandler;

    protected abstract function search(array $data, Builder $query): AbstractTableHandler;
}
