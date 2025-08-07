<?php

namespace Ingenius\Core\Services;

use Illuminate\Database\Eloquent\Builder;

class GenericTableHandler extends AbstractTableHandler
{
    protected function filter(array $data, Builder $query): AbstractTableHandler
    {
        if (isset($data['filters']) && is_array($data['filters'])) {
            foreach ($data['filters'] as $filter) {
                if (isset($filter['field']) && isset($filter['value'])) {
                    if (isset($filter['operator'])) {
                        // operator can be like, not like, in, not in, between, not between, is, is not, null, not null, gt, gte, lt, lte, eq, neq, contains, not contains, starts with, not starts with, ends with, not ends with
                        switch ($filter['operator']) {
                            case 'like':
                                $query->where($filter['field'], 'like', '%' . $filter['value'] . '%');
                                break;
                            case 'not like':
                                $query->where($filter['field'], 'not like', '%' . $filter['value'] . '%');
                                break;
                            case 'in':
                                $query->whereIn($filter['field'], $filter['value']);
                                break;
                            case 'not in':
                                $query->whereNotIn($filter['field'], $filter['value']);
                                break;
                            case 'between':
                                $query->whereBetween($filter['field'], $filter['value']);
                                break;
                            case 'not between':
                                $query->whereNotBetween($filter['field'], $filter['value']);
                                break;
                            case 'is':
                                $query->where($filter['field'], '=', $filter['value']);
                                break;
                            case 'is not':
                                $query->where($filter['field'], '!=', $filter['value']);
                                break;
                            case 'null':
                                $query->whereNull($filter['field']);
                                break;
                            case 'not null':
                                $query->whereNotNull($filter['field']);
                                break;
                            case 'gt':
                                $query->where($filter['field'], '>', $filter['value']);
                                break;
                            case 'gte':
                                $query->where($filter['field'], '>=', $filter['value']);
                                break;
                            case 'lt':
                                $query->where($filter['field'], '<', $filter['value']);
                                break;
                            case 'lte':
                                $query->where($filter['field'], '<=', $filter['value']);
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
                    $query->where($search['field'], 'like', '%' . $search['value'] . '%');
                }
            }
        }

        return $this;
    }
}
