<?php

namespace App\Services\Framework\Http;

use App\Exceptions\ClientExceptions\AttributeNotFiltrableException;
use App\Exceptions\ClientExceptions\AttributeNotSortableException;
use App\Exceptions\ServerException;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Support\Str;

abstract class EloquentCollectionResponse implements Arrayable, Responsable
{
    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $attributeMappings = [];

    /**
     * @var array
     */
    protected $virtualColumns = [];

    /**
     * @var array
     */
    protected $nonFilterable = [];

    /**
     * @var array
     */
    protected $nonSortable = [];

    /**
     * @var bool
     */
    protected $paginated = true;

    /**
     * @var int
     */
    protected $defaultPerPage = 20;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var CollectionRequest
     */
    private $request;

    /**
     * @var Builder|Relation
     */
    private $baseQuery;

    /**
     * @var Relation[]
     */
    private $joinedRelations = [];

    /**
     * @var JoinClause[]
     */
    private $baseJoinedTables = [];

    /**
     * @var \Closure|null
     */
    private ?Closure $beforeToResource = null;

    const OPERATORS_ARRAY = [
        '=',
        '!=',
        'like',
        'ilike',
        '<',
        '>',
        '<=',
        '>=',
        'in',
        'not in',
        'is null',
        'is not null',
        'between'
    ];

    /**
     * @param CollectionRequest $request
     * @param Builder|Relation|BelongsToMany $query
     * @throws \App\Exceptions\TealsyException
     */
    public function __construct(CollectionRequest $request, $query)
    {
        if ($this->resource === null) {
            throw new ServerException('Property resource can not be null');
        }

        if ($this->model === null) {
            throw new ServerException('Property model can not be null');
        }

        if (!$query instanceof Builder && !$query instanceof Relation) {
            throw new ServerException('Query is not instance of ' . Builder::class);
        }

        if (!$query->getModel() instanceof $this->model) {
            throw new ServerException("Query model is not instance of {$this->model}");
        }

        $this->request = $request;
        $this->baseQuery = $query;
    }

    /**
     * @param Builder|Relation $query
     * @return EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public static function create($query)
    {
        return new static(app(CollectionRequest::class), $query);
    }

    /**
     * @return array
     * @throws \App\Exceptions\TealsyException
     */
    public function toArray()
    {
        return $this->makeCollection()->toArray($this->request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function toResponse($request)
    {
        return $this->makeCollection()->toResponse($this->request);
    }

    /**
     * @param \Closure $closure
     *
     * @return $this
     */
    public function setBeforeToResource(Closure $closure): self
    {
        $this->beforeToResource = $closure;
        return $this;
    }

    /**
     * @param string $relationName
     * @throws \App\Exceptions\TealsyException
     */
    protected function joinRelation($relationName)
    {
        if (array_key_exists($relationName, $this->joinedRelations)) {
            return;
        }

        $relation = $this->getModelRelation($relationName);
        $table = "{$relation->getRelated()->getTable()} as {$relationName}";

        $this->query->leftJoin($table, function (JoinClause $join) use ($relation, $relationName) {
            if ($relation instanceof BelongsTo) {
                $one = "{$relationName}.{$relation->getOwnerKeyName()}";
                $two = $relation->getQualifiedForeignKeyName();
            } else {
                $one = "{$relationName}.{$relation->getForeignKeyName()}";
                $two = $relation->getQualifiedParentKeyName();
            }

            $join->on($one, '=', $two);
        });

        $this->joinedRelations[$relationName] = $relation;
    }

    /**
     * @return ResourceCollection
     * @throws \App\Exceptions\TealsyException
     */
    private function makeCollection()
    {
        $collection = $this->execQuery();

        if ($this->beforeToResource) {
            call_user_func($this->beforeToResource, $collection);
        }

        try {
            $reflection = new \ReflectionClass($this->resource);
        } catch (\ReflectionException $e) {
            throw ServerException::wrap($e);
        }

        if ($reflection->isSubclassOf(ResourceCollection::class)) {
            $wrapped = call_user_func([$this->resource, 'make'], $collection);
        } elseif ($reflection->isSubclassOf(JsonResource::class)) {
            $wrapped = call_user_func([$this->resource, 'collection'], $collection);
        } else {
            throw new ServerException("Invalid resource class {$this->resource}");
        }

        return $wrapped;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \App\Exceptions\TealsyException
     */
    private function execQuery()
    {
        $this->prepareQuery();

        if ($this->paginated) {
            $page = (int)$this->request->page ?: 1;
            $size = (int)$this->request->per_page ?: $this->defaultPerPage;

            return $this->baseQuery->paginate($size, ['*'], 'page', $page);
        }

        return $this->baseQuery->get();
    }

    /**
     * @throws \App\Exceptions\TealsyException
     */
    protected function prepareQuery()
    {
        if ($this->baseQuery instanceof Builder) {
            $this->query = $this->baseQuery;
        } else {
            $this->query = $this->baseQuery->getQuery();
        }

        $this->identifyBaseJoinedTables();

        $this->applyWhere();
        $this->applyOrderBy();

        if (!$this->baseQuery instanceof BelongsToMany) {
            $this->maybeSelectAsterisk();
        }
    }

    /**
     * @throws \App\Exceptions\TealsyException
     */
    private function applyWhere()
    {
        $where = $this->request->where ?: [];

        foreach ($where as $condition) {
            $this->where(...$condition);
        }
    }

    /**
     * @throws \App\Exceptions\TealsyException
     */
    private function applyOrderBy()
    {
        $orderBy = $this->request->order_by ?: [];

        foreach ($orderBy as $attribute => $direction) {
            $this->orderBy($attribute, $direction);
        }
    }

    /**
     * @return void
     */
    private function maybeSelectAsterisk()
    {
        if (empty($this->joinedRelations)) {
            return;
        }

        $query = $this->query->getQuery();
        $model = $this->query->getModel();

        if ($query->columns === null) {
            $query->select($model->qualifyColumn('*'));
        }
    }

    /**
     * @param string $attribute
     * @param string $operator
     * @param string|null $value
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    private function where($attribute, $operator, $value = null)
    {
        $operator = strtolower($operator);

        if (!in_array($attribute, $this->attributes, true)
            || in_array($attribute, $this->nonFilterable, true)
            || in_array($attribute, $this->virtualColumns, true)
        ) {
            throw new AttributeNotFiltrableException($attribute);
        }

        if (($proxy = $this->getProxyMethod('where', $attribute)) !== null) {
            return call_user_func($proxy, $operator, $value);
        }

        $column = $this->getQualifiedColumn($attribute);

        return $this::whereWithAnyOperator($this->query, $column, $operator, $value);
    }

    /**
     * @param string $attribute
     * @param string $direction
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    private function orderBy($attribute, $direction)
    {
        $direction = strtolower($direction);

        if (!in_array($attribute, $this->attributes, true) || in_array($attribute, $this->nonSortable, true)) {
            throw new AttributeNotSortableException($attribute);
        }

        if (($proxy = $this->getProxyMethod('orderBy', $attribute)) !== null) {
            return call_user_func($proxy, $direction);
        }

        $column = $this->getQualifiedColumn($attribute);

        return $this->query->orderBy($column, $direction);
    }

    /**
     * @param string $relationName
     * @return BelongsTo|HasOne
     * @throws \App\Exceptions\TealsyException
     */
    private function getModelRelation($relationName)
    {
        try {
            $relation = $this->query->getModel()->$relationName();
        } catch (\Throwable $e) {
            throw new ServerException("Relation {$relationName} is not found");
        }

        if (!$relation instanceof BelongsTo && !$relation instanceof HasOne) {
            throw new ServerException("Relation {$relationName} is not instance of BelongsTo/HasOne");
        }

        return $relation;
    }

    /**
     * @param string $prefix
     * @param string $attribute
     * @return callable
     */
    private function getProxyMethod($prefix, $attribute)
    {
        $proxy = [$this, $prefix . Str::studly($attribute)];

        return is_callable($proxy) ? $proxy : null;
    }

    /**
     * @param string $attribute
     * @return string
     * @throws \App\Exceptions\TealsyException
     */
    protected function getQualifiedColumn($attribute)
    {
        if (in_array($attribute, $this->virtualColumns, true)) {
            return $attribute;
        }

        $column = Arr::get($this->attributeMappings, $attribute, $attribute);

        $this->maybeJoin($column);

        return $this->query->getModel()->qualifyColumn($column);
    }

    /**
     * @return void
     */
    private function identifyBaseJoinedTables()
    {
        $query = $this->query->getQuery();
        $joins = (array)$query->joins;

        /** @var JoinClause $join */
        foreach ($joins as $join) {
            $table = $join->table;
            $alias = explode(' as ', $table);
            $table = last($alias);

            $table = str_replace(['`', '"', '\''], '', $table);
            $this->baseJoinedTables[$table] = $join;
        }
    }

    /**
     * @param string $table
     * @return bool
     */
    protected function baseQueryJoinExist($table)
    {
        return array_key_exists($table, $this->baseJoinedTables);
    }

    /**
     * @param string $column
     * @throws \App\Exceptions\TealsyException
     */
    private function maybeJoin($column)
    {
        $column = explode('.', $column, 2);

        if (count($column) === 2 && !$this->baseQueryJoinExist($column[0])) {
            $this->joinRelation($column[0]);
        }
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param $column
     * @param $operator
     * @param $value
     *
     * @return Builder
     */
    public static function whereWithAnyOperator($query, $column, $operator, $value)
    {
        switch ($operator) {
            case 'in':
                $query->whereIn($column, (array) $value);
                break;
            case 'not in':
                $query->whereNotIn($column, (array) $value);
                break;
            case 'is null':
                $query->whereNull($column);
                break;
            case 'is not null':
                $query->whereNotNull($column);
                break;
            case 'between':
                $query->whereBetween($column, (array) $value);
                break;
            default:
                if (is_array($value)) {
                    throw new BadRequestHttpException('Массив значений передаётся с оператором "IN" или "NOT IN"');
                }
                $query->where($column, $operator, $value);
        }

        return $query;
    }
}
