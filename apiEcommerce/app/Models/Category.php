<?php

declare(strict_types=1);

namespace App\Models;

use Jb\Database\BaseRepository;
use Jb\Database\Connection;

class Category extends BaseRepository
{
    /**
     * Create the repository.
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, 'categories');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function activeList(): array
    {
        return $this->query()->where('activo', 1)->orderBy('nombre')->get();
    }
}
