<?php

namespace App\Core;

use Carbon\Carbon;
use JsonSerializable;

/**
 * Base model: a thin active-record layer over QueryBuilder + PDO.
 * Replaces Eloquent for this application.
 */
abstract class Model implements JsonSerializable
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected bool $incrementing = true;
    protected bool $timestamps = true;

    protected array $fillable = [];
    protected array $hidden = [];

    protected array $attributes = [];
    protected array $relations = [];
    public bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    // --- Table / key helpers ---------------------------------------------

    public function getTable(): string
    {
        if ($this->table !== '') {
            return $this->table;
        }
        // Fallback: snake_case plural of class name.
        $base = self::classBasename(static::class);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $base)) . 's';
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getKey()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function casts(): array
    {
        return [];
    }

    // --- Hydration --------------------------------------------------------

    public static function hydrate(array $row): static
    {
        $model = new static();
        $model->attributes = $row;
        $model->exists = true;
        return $model;
    }

    // --- Magic accessors --------------------------------------------------

    public function __get($name)
    {
        if (array_key_exists($name, $this->relations)) {
            return $this->relations[$name];
        }

        if (array_key_exists($name, $this->attributes)) {
            return $this->castAttribute($name, $this->attributes[$name]);
        }

        $accessor = 'get' . self::studly($name) . 'Attribute';
        if (method_exists($this, $accessor)) {
            return $this->{$accessor}();
        }

        if (method_exists($this, $name)) {
            $relation = $this->{$name}();
            if ($relation instanceof QueryBuilder && $relation->relType !== null) {
                $result = $relation->getResults();
                $this->relations[$name] = $result;
                return $result;
            }
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __isset($name)
    {
        if (array_key_exists($name, $this->relations) || array_key_exists($name, $this->attributes)) {
            return true;
        }
        $accessor = 'get' . self::studly($name) . 'Attribute';
        if (method_exists($this, $accessor)) {
            return true;
        }
        return method_exists($this, $name);
    }

    public function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function getRawAttributes(): array
    {
        return $this->attributes;
    }

    // --- Relations --------------------------------------------------------

    public function setRelation(string $name, $value): void
    {
        $this->relations[$name] = $value;
    }

    public function relationLoaded(string $name): bool
    {
        return array_key_exists($name, $this->relations);
    }

    protected function belongsTo(string $related, ?string $foreignKey = null, string $ownerKey = 'id'): QueryBuilder
    {
        $foreignKey = $foreignKey ?? strtolower(self::classBasename($related)) . '_id';
        $qb = new QueryBuilder($related);
        $qb->asRelation('belongsTo', [
            'related'    => $related,
            'foreignKey' => $foreignKey,
            'ownerKey'   => $ownerKey,
        ]);
        $value = $this->attributes[$foreignKey] ?? null;
        if ($value !== null) {
            $qb->where($ownerKey, $value);
        }
        return $qb;
    }

    protected function hasMany(string $related, ?string $foreignKey = null, string $localKey = 'id'): QueryBuilder
    {
        $foreignKey = $foreignKey ?? strtolower(self::classBasename(static::class)) . '_id';
        $qb = new QueryBuilder($related);
        $qb->asRelation('hasMany', [
            'related'    => $related,
            'foreignKey' => $foreignKey,
            'ownerKey'   => $localKey,
        ]);
        $value = $this->attributes[$localKey] ?? null;
        if ($value !== null) {
            $qb->where($foreignKey, $value);
        }
        return $qb;
    }

    protected function belongsToMany(string $related, string $pivot, ?string $pivotParent = null, ?string $pivotRelated = null): QueryBuilder
    {
        $pivotParent = $pivotParent ?? strtolower(self::classBasename(static::class)) . '_id';
        $pivotRelated = $pivotRelated ?? strtolower(self::classBasename($related)) . '_id';
        $qb = new QueryBuilder($related);
        $qb->asRelation('belongsToMany', [
            'related'      => $related,
            'pivot'        => $pivot,
            'pivotParent'  => $pivotParent,
            'pivotRelated' => $pivotRelated,
        ]);
        $value = $this->attributes['id'] ?? null;
        if ($value !== null) {
            $qb->whereRaw(
                "`{$qb->table}`.`id` IN (SELECT `{$pivotRelated}` FROM `{$pivot}` WHERE `{$pivotParent}` = ?)",
                [$value]
            );
        }
        return $qb;
    }

    public function load($relations): static
    {
        $relations = is_array($relations) ? $relations : func_get_args();
        $qb = new QueryBuilder(static::class);
        $qb->with($relations);
        $qb->eagerLoadInto(new Collection([$this]));
        return $this;
    }

    // --- Persistence ------------------------------------------------------

    public static function create(array $attributes): static
    {
        $instance = new static();
        $data = $instance->filterFillable($attributes);

        if ($instance->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnList = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
        $sql = "INSERT INTO `{$instance->getTable()}` ({$columnList}) VALUES ({$placeholders})";

        if ($instance->incrementing) {
            $id = Database::insertGetId($sql, array_values($data));
            return static::find($id) ?? static::hydrate($data + [$instance->primaryKey => $id]);
        }

        Database::statement($sql, array_values($data));
        return static::hydrate($data);
    }

    public function update(array $attributes): bool
    {
        $data = $this->filterFillable($attributes);
        if ($data === []) {
            return false;
        }

        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $sets = [];
        $bindings = [];
        foreach ($data as $column => $value) {
            $sets[] = "`{$column}` = ?";
            $bindings[] = $this->normalize($value);
            $this->attributes[$column] = $value;
        }
        $bindings[] = $this->getKey();

        $sql = "UPDATE `{$this->getTable()}` SET " . implode(', ', $sets)
            . " WHERE `{$this->primaryKey}` = ?";
        Database::statement($sql, $bindings);

        return true;
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->update($this->attributes);
        }
        $fresh = static::create($this->attributes);
        $this->attributes = $fresh->getRawAttributes();
        $this->exists = true;
        return true;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        $sql = "DELETE FROM `{$this->getTable()}` WHERE `{$this->primaryKey}` = ?";
        Database::statement($sql, [$this->getKey()]);
        $this->exists = false;
        return true;
    }

    public static function updateOrCreate(array $attributes, array $values = []): static
    {
        $query = static::query();
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }
        $existing = $query->first();

        if ($existing) {
            $existing->update(array_merge($attributes, $values));
            return $existing;
        }
        return static::create(array_merge($attributes, $values));
    }

    // --- Static query entrypoints ----------------------------------------

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::class);
    }

    public static function find($id): ?static
    {
        return static::query()->find($id);
    }

    public static function findOrFail($id): static
    {
        return static::query()->findOrFail($id);
    }

    public static function all(): Collection
    {
        return static::query()->get();
    }

    public static function __callStatic($method, $arguments)
    {
        return (new QueryBuilder(static::class))->{$method}(...$arguments);
    }

    // --- Serialization ----------------------------------------------------

    public function toArray(): array
    {
        $result = [];
        foreach ($this->attributes as $key => $value) {
            if (in_array($key, $this->hidden, true)) {
                continue;
            }
            $result[$key] = $this->castForArray($key, $value);
        }
        foreach ($this->relations as $key => $relation) {
            if ($relation instanceof Collection) {
                $result[$key] = $relation->toArray();
            } elseif ($relation instanceof Model) {
                $result[$key] = $relation->toArray();
            } else {
                $result[$key] = $relation;
            }
        }
        return $result;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    // --- Casting ----------------------------------------------------------

    protected function resolvedCasts(): array
    {
        $casts = $this->casts();
        // Timestamp columns are always Carbon instances (like Eloquent).
        if ($this->timestamps) {
            $casts['created_at'] = $casts['created_at'] ?? 'datetime';
            $casts['updated_at'] = $casts['updated_at'] ?? 'datetime';
        }
        return $casts;
    }

    protected function castAttribute(string $key, $value)
    {
        if ($value === null) {
            return null;
        }
        $casts = $this->resolvedCasts();
        if (!isset($casts[$key])) {
            return $value;
        }
        return match ($casts[$key]) {
            'int', 'integer'   => (int) $value,
            'float', 'double'  => (float) $value,
            'bool', 'boolean'  => (bool) (int) $value,
            'array', 'json'    => is_array($value) ? $value : json_decode($value, true),
            'date', 'datetime' => $value instanceof Carbon ? $value : Carbon::parse($value),
            default            => $value,
        };
    }

    protected function castForArray(string $key, $value)
    {
        if ($value === null) {
            return null;
        }
        $casts = $this->resolvedCasts();
        if (!isset($casts[$key])) {
            return $value;
        }
        return match ($casts[$key]) {
            'int', 'integer'   => (int) $value,
            'float', 'double'  => (float) $value,
            'bool', 'boolean'  => (bool) (int) $value,
            'array', 'json'    => is_array($value) ? $value : json_decode($value, true),
            'date'             => Carbon::parse($value)->toDateString(),
            'datetime'         => Carbon::parse($value)->toDateTimeString(),
            default            => $value,
        };
    }

    // --- Internal helpers -------------------------------------------------

    protected function filterFillable(array $attributes): array
    {
        if ($this->fillable === []) {
            return $attributes;
        }
        return array_intersect_key($attributes, array_flip($this->fillable));
    }

    protected function normalize($value)
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        return $value;
    }

    protected static function classBasename(string $class): string
    {
        $pos = strrpos($class, '\\');
        return $pos === false ? $class : substr($class, $pos + 1);
    }

    protected static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}
