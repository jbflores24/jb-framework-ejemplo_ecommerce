<?php

declare(strict_types=1);

namespace App\Models;

use Jb\Database\BaseRepository;
use Jb\Database\Connection;

class Customer extends BaseRepository
{
    /**
     * Create the repository.
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, 'customers');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->query()->where('email', strtolower(trim($email)))->first();
    }
}
