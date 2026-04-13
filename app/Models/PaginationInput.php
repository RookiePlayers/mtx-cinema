<?php

namespace App\Models;

class PaginationInput
{
    public ?string $cursor = null;
    public int $limit = 10;
    public int $offset = 0;

    public function __construct(?string $cursor = null, int $limit = 10, int $offset = 0)
    {
        $this->cursor = $cursor;
        $this->limit = $limit;
        $this->offset = $offset;
    }
}
