<?php

namespace App\Core;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use JsonSerializable;
use Traversable;

/**
 * Lightweight collection mirroring the subset of Laravel's Collection API
 * the application relies on. Associative keys are preserved.
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    protected array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function make(array $items = []): self
    {
        return new self($items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function isNotEmpty(): bool
    {
        return $this->items !== [];
    }

    public function first(?callable $callback = null)
    {
        if ($callback === null) {
            foreach ($this->items as $item) {
                return $item;
            }
            return null;
        }
        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }
        return null;
    }

    public function map(callable $callback): self
    {
        $keys = array_keys($this->items);
        $mapped = array_map($callback, $this->items, $keys);
        return new self(array_combine($keys, $mapped));
    }

    public function filter(?callable $callback = null): self
    {
        $filtered = $callback ? array_filter($this->items, $callback) : array_filter($this->items);
        return new self($filtered);
    }

    public function each(callable $callback): self
    {
        foreach ($this->items as $item) {
            $callback($item);
        }
        return $this;
    }

    public function take(int $limit): self
    {
        return new self(array_slice($this->items, 0, $limit, true));
    }

    public function sum($key = null)
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $key === null ? $item : $this->dataGet($item, $key);
        }
        return $total;
    }

    /**
     * pluck('value') or pluck('value', 'key').
     */
    public function pluck($value, $key = null): self
    {
        $results = [];
        foreach ($this->items as $item) {
            $itemValue = $this->dataGet($item, $value);
            if ($key === null) {
                $results[] = $itemValue;
            } else {
                $results[$this->dataGet($item, $key)] = $itemValue;
            }
        }
        return new self($results);
    }

    /**
     * groupBy(key) or groupBy(callback).
     */
    public function groupBy($groupBy): self
    {
        $results = [];
        foreach ($this->items as $item) {
            $key = is_callable($groupBy) ? $groupBy($item) : $this->dataGet($item, $groupBy);
            $results[$key][] = $item;
        }
        foreach ($results as $key => $group) {
            $results[$key] = new self($group);
        }
        return new self($results);
    }

    public function unique($key = null): self
    {
        $seen = [];
        $results = [];
        foreach ($this->items as $item) {
            $id = $key === null ? $item : $this->dataGet($item, $key);
            $id = is_scalar($id) ? $id : serialize($id);
            if (!array_key_exists($id, $seen)) {
                $seen[$id] = true;
                $results[] = $item;
            }
        }
        return new self($results);
    }

    public function sortDesc(): self
    {
        $items = $this->items;
        arsort($items);
        return new self($items);
    }

    public function sort(): self
    {
        $items = $this->items;
        asort($items);
        return new self($items);
    }

    public function values(): self
    {
        return new self(array_values($this->items));
    }

    public function keys(): self
    {
        return new self(array_keys($this->items));
    }

    public function contains($value): bool
    {
        return in_array($value, $this->items, false);
    }

    public function join(string $glue): string
    {
        return implode($glue, array_map(fn ($i) => (string) $i, $this->items));
    }

    public function toArray(): array
    {
        return array_map(function ($item) {
            if ($item instanceof Collection) {
                return $item->toArray();
            }
            if (is_object($item) && method_exists($item, 'toArray')) {
                return $item->toArray();
            }
            return $item;
        }, $this->items);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    // --- Interfaces -------------------------------------------------------

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    // --- Helpers ----------------------------------------------------------

    protected function dataGet($item, $key)
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }
        if (is_object($item)) {
            return $item->{$key} ?? null;
        }
        return null;
    }
}
