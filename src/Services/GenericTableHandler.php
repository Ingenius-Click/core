<?php

namespace Ingenius\Core\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GenericTableHandler extends AbstractTableHandler
{
    protected function filter(array $data, Builder $query): AbstractTableHandler
    {
        if (isset($data['filters']) && is_array($data['filters'])) {
            foreach ($data['filters'] as $filter) {
                if (isset($filter['field']) && isset($filter['value'])) {
                    if (isset($filter['operator'])) {
                        // Refine operators: eq, ne, lt, gt, lte, gte, in, nin, ina, nina, contains, ncontains, containss, ncontainss, between, nbetween, null, nnull, startswith, nstartswith, startswiths, nstartswiths, endswith, nendswith, endswiths, nendswiths, or, and
                        switch ($filter['operator']) {
                            case 'eq':
                                $query->where($filter['field'], '=', $filter['value']);
                                break;
                            case 'ne':
                                $query->where($filter['field'], '!=', $filter['value']);
                                break;
                            case 'lt':
                                $query->where($filter['field'], '<', $filter['value']);
                                break;
                            case 'gt':
                                $query->where($filter['field'], '>', $filter['value']);
                                break;
                            case 'lte':
                                $query->where($filter['field'], '<=', $filter['value']);
                                break;
                            case 'gte':
                                $query->where($filter['field'], '>=', $filter['value']);
                                break;
                            case 'in':
                                $query->whereIn($filter['field'], $filter['value']);
                                break;
                            case 'nin':
                                $query->whereNotIn($filter['field'], $filter['value']);
                                break;
                            case 'ina':
                                // Case-insensitive array search for PostgreSQL
                                $query->whereIn(DB::raw('LOWER(' . $filter['field'] . ')'), array_map('strtolower', (array) $filter['value']));
                                break;
                            case 'nina':
                                // Case-insensitive array search for PostgreSQL
                                $query->whereNotIn(DB::raw('LOWER(' . $filter['field'] . ')'), array_map('strtolower', (array) $filter['value']));
                                break;
                            case 'contains':
                                $query->where($filter['field'], 'ilike', '%' . $filter['value'] . '%');
                                break;
                            case 'ncontains':
                                $query->where($filter['field'], 'not ilike', '%' . $filter['value'] . '%');
                                break;
                            case 'containss':
                                $query->where($filter['field'], 'like', '%' . $filter['value'] . '%');
                                break;
                            case 'ncontainss':
                                $query->where($filter['field'], 'not like', '%' . $filter['value'] . '%');
                                break;
                            case 'between':
                                $query->whereBetween($filter['field'], $filter['value']);
                                break;
                            case 'nbetween':
                                $query->whereNotBetween($filter['field'], $filter['value']);
                                break;
                            case 'null':
                                $query->whereNull($filter['field']);
                                break;
                            case 'nnull':
                                $query->whereNotNull($filter['field']);
                                break;
                            case 'startswith':
                                $query->where($filter['field'], 'ilike', $filter['value'] . '%');
                                break;
                            case 'nstartswith':
                                $query->where($filter['field'], 'not ilike', $filter['value'] . '%');
                                break;
                            case 'startswiths':
                                $query->where($filter['field'], 'like', $filter['value'] . '%');
                                break;
                            case 'nstartswiths':
                                $query->where($filter['field'], 'not like', $filter['value'] . '%');
                                break;
                            case 'endswith':
                                $query->where($filter['field'], 'ilike', '%' . $filter['value']);
                                break;
                            case 'nendswith':
                                $query->where($filter['field'], 'not ilike', '%' . $filter['value']);
                                break;
                            case 'endswiths':
                                $query->where($filter['field'], 'like', '%' . $filter['value']);
                                break;
                            case 'nendswiths':
                                $query->where($filter['field'], 'not like', '%' . $filter['value']);
                                break;
                            case 'or':
                                // Handle OR logic - expects filter['value'] to be an array of conditions
                                if (is_array($filter['value'])) {
                                    $query->where(function ($subQuery) use ($filter) {
                                        foreach ($filter['value'] as $orCondition) {
                                            if (isset($orCondition['field']) && isset($orCondition['operator']) && isset($orCondition['value'])) {
                                                $subQuery->orWhere($orCondition['field'], $orCondition['operator'], $orCondition['value']);
                                            }
                                        }
                                    });
                                }
                                break;
                            case 'and':
                                // Handle AND logic - expects filter['value'] to be an array of conditions
                                if (is_array($filter['value'])) {
                                    $query->where(function ($subQuery) use ($filter) {
                                        foreach ($filter['value'] as $andCondition) {
                                            if (isset($andCondition['field']) && isset($andCondition['operator']) && isset($andCondition['value'])) {
                                                $subQuery->where($andCondition['field'], $andCondition['operator'], $andCondition['value']);
                                            }
                                        }
                                    });
                                }
                                break;
                            default:
                                $query->where($filter['field'], $filter['operator'], $filter['value']);
                                break;
                        }
                    } else {
                        $query->where($filter['field'], $filter['value']);
                    }
                }
            }
        }

        return $this;
    }

    protected function sort(array $data, Builder $query): AbstractTableHandler
    {
        if (isset($data['sorts']) && is_array($data['sorts'])) {
            foreach ($data['sorts'] as $sort) {
                if (isset($sort['field']) && isset($sort['direction'])) {
                    $direction = strtolower($sort['direction']);
                    if (in_array($direction, ['asc', 'desc'])) {
                        $query->orderBy($sort['field'], $direction);
                    }
                }
            }
        }

        return $this;
    }

    protected function search(array $data, Builder $query): AbstractTableHandler
    {
        if (isset($data['search']) && is_array($data['search'])) {
            foreach ($data['search'] as $search) {
                if (isset($search['field']) && isset($search['value'])) {
                    $query->where($search['field'], 'ilike', '%' . $search['value'] . '%');
                }
            }
        }

        return $this;
    }
}
