<?php

namespace App\Models;

class PaginationData
{
    public array $data;
    public ?string $next_cursor;
    public ?int $total = 0;
    public ?int $count = 0;
    public ?bool $hasMore = false;

    public function __construct(array $data, ?string $next_cursor = null, ?int $total = 0, ?int $count = 0, ?bool $hasMore = false)
    {
        $this->data = $data;
        $this->next_cursor = $next_cursor;
        $this->total = $total;
        $this->count = $count;
        $this->hasMore = $hasMore;
    }
}
