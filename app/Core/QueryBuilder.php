<?php

namespace App\Core;

use Closure;

/**
 * Fluent SQL query builder + lightweight ORM query layer.
 * Supports the subset of Eloquent the application uses: where/orWhere,
 * whereIn/whereNotIn, whereDate/whereMonth, whereHas, scopes, ordering,
 * grouping, pagination, eager loading (with), withCount and mass
 * update/delete.
 */
class QueryBuilder
{
    public string $modelClass;
    public string $table;
    protected string $primaryKey = 'id';

    protected array $columns = ['*'];
    protected array $selectRaws = [];     // [['sql'=>..,'bindings'=>[]]]
    protected array $wheres = [];         // [['boolean'=>'AND','sql'=>..,'bindings'=>[]]]
    protected array $orders = [];
    protected array $groups = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected bool $distinct = false;

    protected array $eagerLoad = [];      // relations to eager load
    protected array $withCounts = [];     // ['relation'=>'alias']

    // Relationship metadata (set when this builder represents a relation)
    public ?string $relType = null;       // belongsTo | hasMany | belongsToMany
    public ?string $relRelated = null;
    public ?string $relForeignKey = null;
    public ?string $relOwnerKey = null;
    public ?string $relPivot = null;
    public ?string $relPivotParent = null;
    public ?string $relPivotRelated = null;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $instance = new $modelClass();
        $this->table = $instance->getTable();
        $this->primaryKey = $instance->getKeyName();
    }

    // --- Relationship configuration --------------------------------------

    public function asRelation(string $type, array $meta): self
    {
        $this->relType = $type;
        $this->relRelated = $meta['related'] ?? null;
        $this->relForeignKey = $meta['foreignKey'] ?? null;
        $this->relOwnerKey = $meta['ownerKey'] ?? null;
        $this->relPivot = $meta['pivot'] ?? null;
        $this->relPivotParent = $meta['pivotParent'] ?? null;
        $this->relPivotRelated = $meta['pivotRelated'] ?? null;
        return $this;
    }

    public function getResults()
    {
        return $this->relType === 'belongsTo' ? $this->first() : $this->get();
    }

    // --- WHERE clauses ----------------------------------------------------

    public function where($column, $operator = null, $value = null, string $boolean = 'AND'): self
    {
        if ($column instanceof Closure) {
            $sub = new self($this->modelClass);
            $column($sub);
            [$sql, $bindings] = $sub->compileWheres(false);
            if ($sql !== '') {
                $this->wheres[] = ['boolean' => $boolean, 'sql' => '(' . $sql . ')', 'bindings' => $bindings];
            }
            return $this;
        }

        // where('col', 'value') shorthand
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'boolean'  => $boolean,
            'sql'      => $this->wrap($column) . ' ' . $operator . ' ?',
            'bindings' => [$value],
        ];
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null): self
    {
        if ($column instanceof Closure || func_num_args() === 2) {
            return $this->where($column, $operator, null, 'OR');
        }
        return $this->where($column, $operator, $value, 'OR');
    }

    public function whereIn(string $column, $values, string $boolean = 'AND', bool $not = false): self
    {
        $values = $values instanceof Collection ? $values->all() : (array) $values;
        $type = $not ? 'NOT IN' : 'IN';

        if ($values === []) {
            // IN () -> always false; NOT IN () -> always true
            $this->wheres[] = ['boolean' => $boolean, 'sql' => $not ? '1 = 1' : '0 = 1', 'bindings' => []];
            return $this;
        }

        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = [
            'boolean'  => $boolean,
            'sql'      => $this->wrap($column) . " {$type} ({$placeholders})",
            'bindings' => array_values($values),
        ];
        return $this;
    }

    public function whereNotIn(string $column, $values, string $boolean = 'AND'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    public function whereNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = ['boolean' => $boolean, 'sql' => $this->wrap($column) . ' IS NULL', 'bindings' => []];
        return $this;
    }

    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = ['boolean' => $boolean, 'sql' => $this->wrap($column) . ' IS NOT NULL', 'bindings' => []];
        return $this;
    }

    public function whereDate(string $column, $operator, $date = null, string $boolean = 'AND'): self
    {
        if (func_num_args() === 2 || ($date === null && !in_array($operator, ['=', '!=', '<', '>', '<=', '>='], true))) {
            $date = $operator;
            $operator = '=';
        }
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        }
        $this->wheres[] = [
            'boolean'  => $boolean,
            'sql'      => 'DATE(' . $this->wrap($column) . ') ' . $operator . ' ?',
            'bindings' => [$date],
        ];
        return $this;
    }

    public function whereMonth(string $column, $month, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'boolean'  => $boolean,
            'sql'      => 'MONTH(' . $this->wrap($column) . ') = ?',
            'bindings' => [$month],
        ];
        return $this;
    }

    public function whereRaw(string $sql, array $bindings = [], string $boolean = 'AND'): self
    {
        $this->wheres[] = ['boolean' => $boolean, 'sql' => $sql, 'bindings' => $bindings];
        return $this;
    }

    public function whereHas(string $relation, ?Closure $callback = null): self
    {
        $parent = new $this->modelClass();
        /** @var self $rel */
        $rel = $parent->{$relation}();

        $sub = new self($rel->relRelated);
        if ($callback) {
            $callback($sub);
        }
        [$subWhere, $subBindings] = $sub->compileWheres(false);
        $subWhere = $subWhere === '' ? '1 = 1' : $subWhere;

        if ($rel->relType === 'belongsTo') {
            $ownerKey = $rel->relOwnerKey ?? 'id';
            $sql = $this->wrap($rel->relForeignKey) . " IN (SELECT `{$ownerKey}` FROM `{$sub->table}` WHERE {$subWhere})";
        } else { // hasMany
            $sql = $this->wrap($this->primaryKey) . " IN (SELECT `{$rel->relForeignKey}` FROM `{$sub->table}` WHERE {$subWhere})";
        }

        $this->wheres[] = ['boolean' => 'AND', 'sql' => $sql, 'bindings' => $subBindings];
        return $this;
    }

    // --- Ordering / grouping / limiting ----------------------------------

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $this->orders[] = $this->wrap($column) . ' ' . $direction;
        return $this;
    }

    public function groupBy(...$columns): self
    {
        foreach ($columns as $column) {
            $this->groups[] = $this->wrap($column);
        }
        return $this;
    }

    public function limit(int $value): self
    {
        $this->limit = $value;
        return $this;
    }

    public function take(int $value): self
    {
        return $this->limit($value);
    }

    public function offset(int $value): self
    {
        $this->offset = $value;
        return $this;
    }

    public function select(...$columns): self
    {
        $this->columns = $columns === [] ? ['*'] : (is_array($columns[0]) ? $columns[0] : $columns);
        return $this;
    }

    public function selectRaw(string $sql, array $bindings = []): self
    {
        $this->selectRaws[] = ['sql' => $sql, 'bindings' => $bindings];
        return $this;
    }

    public function distinct(): self
    {
        $this->distinct = true;
        return $this;
    }

    public function with($relations): self
    {
        foreach ((array) $relations as $relation) {
            $this->eagerLoad[] = $relation;
        }
        return $this;
    }

    public function withCount($relations): self
    {
        foreach ((array) $relations as $relation) {
            $this->withCounts[] = $relation;
        }
        return $this;
    }

    // --- Terminal methods -------------------------------------------------

    public function get(): Collection
    {
        $sql = $this->compileSelect();
        $rows = Database::select($sql, $this->getBindings());

        $models = [];
        foreach ($rows as $row) {
            $models[] = ($this->modelClass)::hydrate($row);
        }

        $collection = new Collection($models);

        if ($this->eagerLoad !== [] && $models !== []) {
            $this->eagerLoadRelations($collection);
        }

        return $collection;
    }

    public function first()
    {
        $clone = clone $this;
        $clone->limit = 1;
        return $clone->get()->first();
    }

    public function find($id)
    {
        $clone = clone $this;
        $clone->where($this->primaryKey, $id);
        return $clone->first();
    }

    public function findOrFail($id)
    {
        $model = $this->find($id);
        if ($model === null) {
            throw new HttpException(404, 'Record not found.');
        }
        return $model;
    }

    public function all(): Collection
    {
        return $this->get();
    }

    public function count(): int
    {
        $clone = clone $this;
        $clone->columns = ['COUNT(*) AS aggregate'];
        $clone->orders = [];
        $clone->limit = null;
        $clone->offset = null;
        $clone->selectRaws = [];
        $clone->withCounts = [];
        $sql = $clone->compileSelect(true);
        $row = Database::selectOne($sql, $clone->getBindings());
        return (int) ($row['aggregate'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function value(string $column)
    {
        $clone = clone $this;
        $clone->columns = [$column];
        $clone->limit = 1;
        $row = Database::selectOne($clone->compileSelect(), $clone->getBindings());
        return $row[$column] ?? null;
    }

    public function pluck(string $column, ?string $key = null): Collection
    {
        $clone = clone $this;
        $clone->columns = $key ? [$key, $column] : [$column];
        $clone->eagerLoad = [];
        $rows = Database::select($clone->compileSelect(), $clone->getBindings());

        $results = [];
        foreach ($rows as $row) {
            if ($key === null) {
                $results[] = $row[$column] ?? null;
            } else {
                $results[$row[$key]] = $row[$column] ?? null;
            }
        }
        return new Collection($results);
    }

    public function paginate(int $perPage = 15, int $page = 1): Paginator
    {
        $total = $this->count();

        $clone = clone $this;
        $clone->limit = $perPage;
        $clone->offset = ($page - 1) * $perPage;
        $items = $clone->get();

        return new Paginator($items->all(), $total, $perPage, $page);
    }

    public function update(array $values): int
    {
        if ($values === []) {
            return 0;
        }
        $sets = [];
        $bindings = [];
        foreach ($values as $column => $value) {
            $sets[] = $this->wrap($column) . ' = ?';
            $bindings[] = $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : $value;
        }
        [$whereSql, $whereBindings] = $this->compileWheres();
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets) . $whereSql;
        return Database::statement($sql, array_merge($bindings, $whereBindings));
    }

    public function delete(): int
    {
        [$whereSql, $whereBindings] = $this->compileWheres();
        $sql = "DELETE FROM `{$this->table}`" . $whereSql;
        return Database::statement($sql, $whereBindings);
    }

    // --- Scope dispatch ---------------------------------------------------

    public function __call(string $method, array $arguments)
    {
        $scope = 'scope' . ucfirst($method);
        $instance = new $this->modelClass();
        if (method_exists($instance, $scope)) {
            $result = $instance->{$scope}($this, ...$arguments);
            return $result instanceof self ? $result : $this;
        }
        throw new \BadMethodCallException("Method {$method} does not exist on query builder.");
    }

    // --- Compilation ------------------------------------------------------

    protected function compileSelect(bool $aggregate = false): string
    {
        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = $column === '*' || $aggregate ? $column : $this->wrap($column);
        }
        foreach ($this->selectRaws as $raw) {
            $columns[] = $raw['sql'];
        }
        if (!$aggregate) {
            foreach ($this->withCounts as $relation) {
                $columns[] = $this->compileWithCount($relation);
            }
        }

        $sql = 'SELECT ' . ($this->distinct ? 'DISTINCT ' : '') . implode(', ', $columns)
            . " FROM `{$this->table}`";

        [$whereSql] = $this->compileWheres();
        $sql .= $whereSql;

        if ($this->groups !== []) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }
        if (!$aggregate && $this->orders !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
            if ($this->offset !== null) {
                $sql .= ' OFFSET ' . $this->offset;
            }
        }

        return $sql;
    }

    protected function compileWithCount(string $relation): string
    {
        $parent = new $this->modelClass();
        /** @var self $rel */
        $rel = $parent->{$relation}();
        $alias = $relation . '_count';
        return "(SELECT COUNT(*) FROM `{$rel->table}` WHERE `{$rel->table}`.`{$rel->relForeignKey}` = `{$this->table}`.`{$this->primaryKey}`) AS {$alias}";
    }

    /**
     * @return array{0:string,1:array} [sql fragment, bindings]
     */
    public function compileWheres(bool $withKeyword = true): array
    {
        if ($this->wheres === []) {
            return ['', []];
        }
        $sql = '';
        $bindings = [];
        foreach ($this->wheres as $index => $where) {
            $prefix = $index === 0 ? '' : ' ' . $where['boolean'] . ' ';
            $sql .= $prefix . $where['sql'];
            $bindings = array_merge($bindings, $where['bindings']);
        }
        return [($withKeyword ? ' WHERE ' : '') . $sql, $bindings];
    }

    protected function getBindings(): array
    {
        $bindings = [];
        foreach ($this->selectRaws as $raw) {
            $bindings = array_merge($bindings, $raw['bindings']);
        }
        [, $whereBindings] = $this->compileWheres();
        return array_merge($bindings, $whereBindings);
    }

    protected function wrap(string $column): string
    {
        $column = trim($column);
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            return "`{$column}`";
        }
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            [$t, $c] = explode('.', $column);
            return "`{$t}`.`{$c}`";
        }
        return $column; // raw expression (already safe / no user input)
    }

    // --- Eager loading ----------------------------------------------------

    public function eagerLoadInto(Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }
        $this->eagerLoadRelations($models);
    }

    protected function eagerLoadRelations(Collection $models): void
    {
        $nested = [];
        foreach ($this->eagerLoad as $relation) {
            if (str_contains($relation, '.')) {
                [$head, $tail] = explode('.', $relation, 2);
                $nested[$head][] = $tail;
            } else {
                $nested[$relation] = $nested[$relation] ?? [];
            }
        }

        foreach ($nested as $relation => $children) {
            $this->loadRelation($models, $relation, $children);
        }
    }

    protected function loadRelation(Collection $models, string $relation, array $children): void
    {
        $parent = new $this->modelClass();
        if (!method_exists($parent, $relation)) {
            return;
        }
        /** @var self $rel */
        $rel = $parent->{$relation}();
        $relatedClass = $rel->relRelated;

        if ($rel->relType === 'belongsTo') {
            $foreignKey = $rel->relForeignKey;
            $ownerKey = $rel->relOwnerKey ?? 'id';
            $keys = $this->collectKeys($models, $foreignKey);
            $relatedModels = $keys === [] ? new Collection()
                : (new self($relatedClass))->whereIn($ownerKey, $keys)->get();

            $dictionary = [];
            foreach ($relatedModels as $related) {
                $dictionary[$related->{$ownerKey}] = $related;
            }
            foreach ($models as $model) {
                $fk = $model->{$foreignKey};
                $model->setRelation($relation, $dictionary[$fk] ?? null);
            }
            if ($children !== []) {
                $this->loadChildren($relatedModels, $relatedClass, $children);
            }
        } elseif ($rel->relType === 'hasMany') {
            $foreignKey = $rel->relForeignKey;
            $localKey = $rel->relOwnerKey ?? 'id';
            $keys = $this->collectKeys($models, $localKey);
            $relatedModels = $keys === [] ? new Collection()
                : (new self($relatedClass))->whereIn($foreignKey, $keys)->get();

            $dictionary = [];
            foreach ($relatedModels as $related) {
                $dictionary[$related->{$foreignKey}][] = $related;
            }
            foreach ($models as $model) {
                $key = $model->{$localKey};
                $model->setRelation($relation, new Collection($dictionary[$key] ?? []));
            }
            if ($children !== []) {
                $this->loadChildren($relatedModels, $relatedClass, $children);
            }
        } elseif ($rel->relType === 'belongsToMany') {
            $parentIds = $this->collectKeys($models, $this->primaryKey);
            if ($parentIds === []) {
                foreach ($models as $model) {
                    $model->setRelation($relation, new Collection());
                }
                return;
            }
            $placeholders = implode(', ', array_fill(0, count($parentIds), '?'));
            $pivotRows = Database::select(
                "SELECT `{$rel->relPivotParent}` AS parent_id, `{$rel->relPivotRelated}` AS related_id "
                . "FROM `{$rel->relPivot}` WHERE `{$rel->relPivotParent}` IN ({$placeholders})",
                $parentIds
            );

            $relatedIds = array_values(array_unique(array_map(fn ($r) => $r['related_id'], $pivotRows)));
            $relatedModels = $relatedIds === [] ? new Collection()
                : (new self($relatedClass))->whereIn('id', $relatedIds)->get();

            $relatedById = [];
            foreach ($relatedModels as $related) {
                $relatedById[$related->id] = $related;
            }

            $grouped = [];
            foreach ($pivotRows as $pivot) {
                if (isset($relatedById[$pivot['related_id']])) {
                    $grouped[$pivot['parent_id']][] = $relatedById[$pivot['related_id']];
                }
            }
            foreach ($models as $model) {
                $model->setRelation($relation, new Collection($grouped[$model->id] ?? []));
            }
            if ($children !== []) {
                $this->loadChildren($relatedModels, $relatedClass, $children);
            }
        }
    }

    protected function loadChildren(Collection $relatedModels, string $relatedClass, array $children): void
    {
        if ($relatedModels->isEmpty()) {
            return;
        }
        $builder = new self($relatedClass);
        $builder->eagerLoad = $children;
        $builder->eagerLoadRelations($relatedModels);
    }

    protected function collectKeys(Collection $models, string $key): array
    {
        $keys = [];
        foreach ($models as $model) {
            $value = $model->{$key};
            if ($value !== null) {
                $keys[$value] = true;
            }
        }
        return array_keys($keys);
    }
}
