<?php

namespace App\Core;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Simple length-aware paginator. Mirrors the Eloquent paginator methods the
 * views use: items(), total(), currentPage(), perPage(), lastPage(),
 * hasPages(), links().
 */
class Paginator implements IteratorAggregate, Countable, JsonSerializable
{
    protected array $items;
    protected int $total;
    protected int $perPage;
    protected int $currentPage;
    protected int $lastPage;

    public function __construct(array $items, int $total, int $perPage, int $currentPage)
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = max(1, $perPage);
        $this->currentPage = max(1, $currentPage);
        $this->lastPage = max(1, (int) ceil($total / $this->perPage));
    }

    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function lastPage(): int
    {
        return $this->lastPage;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'data'         => array_map(fn ($i) => is_object($i) && method_exists($i, 'toArray') ? $i->toArray() : $i, $this->items),
            'total'        => $this->total,
            'per_page'     => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page'    => $this->lastPage,
        ];
    }

    /**
     * Render Tailwind-styled pagination links preserving existing query params.
     */
    public function links(): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $query = $_GET;
        $buildUrl = function (int $page) use ($query): string {
            $query['page'] = $page;
            return '?' . http_build_query($query);
        };

        $html = '<nav class="flex items-center justify-between"><div class="flex-1 flex justify-between sm:hidden">';
        // Mobile prev/next
        if ($this->currentPage > 1) {
            $html .= '<a href="' . e($buildUrl($this->currentPage - 1)) . '" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Previous</a>';
        } else {
            $html .= '<span></span>';
        }
        if ($this->currentPage < $this->lastPage) {
            $html .= '<a href="' . e($buildUrl($this->currentPage + 1)) . '" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Next</a>';
        } else {
            $html .= '<span></span>';
        }
        $html .= '</div>';

        // Desktop
        $html .= '<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">';
        $from = ($this->currentPage - 1) * $this->perPage + 1;
        $to = min($this->currentPage * $this->perPage, $this->total);
        $html .= '<p class="text-sm text-gray-600">Showing <span class="font-semibold">' . $from . '</span> to <span class="font-semibold">' . $to . '</span> of <span class="font-semibold">' . $this->total . '</span> results</p>';
        $html .= '<div class="flex items-center space-x-1">';

        $start = max(1, $this->currentPage - 2);
        $end = min($this->lastPage, $this->currentPage + 2);

        if ($this->currentPage > 1) {
            $html .= '<a href="' . e($buildUrl($this->currentPage - 1)) . '" class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">&laquo;</a>';
        }
        for ($page = $start; $page <= $end; $page++) {
            if ($page === $this->currentPage) {
                $html .= '<span class="px-3 py-1.5 text-sm font-semibold text-white bg-indigo-600 border border-indigo-600 rounded-lg">' . $page . '</span>';
            } else {
                $html .= '<a href="' . e($buildUrl($page)) . '" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">' . $page . '</a>';
            }
        }
        if ($this->currentPage < $this->lastPage) {
            $html .= '<a href="' . e($buildUrl($this->currentPage + 1)) . '" class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">&raquo;</a>';
        }

        $html .= '</div></div></nav>';
        return $html;
    }
}
