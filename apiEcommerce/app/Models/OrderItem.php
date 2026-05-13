<?php

declare(strict_types=1);

namespace App\Models;

use Jb\Database\BaseRepository;
use Jb\Database\Connection;

class OrderItem extends BaseRepository
{
    /**
     * Create the repository.
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, 'order_items');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function byOrderId(int $orderId): array
    {
        return $this->query()->where('order_id', $orderId)->get();
    }
}
