<?php

declare(strict_types=1);

namespace App\Models;

use Jb\Database\BaseRepository;
use Jb\Database\Connection;

class Order extends BaseRepository
{
    /**
     * Create the repository.
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, 'orders');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listWithCustomer(?int $customerId = null): array
    {
        $sql = 'SELECT o.*, c.nombre AS customer_nombre, c.email AS customer_email
                FROM orders o
                INNER JOIN customers c ON c.id = o.customer_id';
        $params = [];

        if ($customerId !== null && $customerId > 0) {
            $sql .= ' WHERE o.customer_id = :customer_id';
            $params[':customer_id'] = $customerId;
        }

        $sql .= ' ORDER BY o.id DESC';

        $statement = $this->connection->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithItems(int $id): ?array
    {
        $orderStatement = $this->connection->pdo()->prepare(
            'SELECT o.*, c.nombre AS customer_nombre, c.email AS customer_email, c.telefono AS customer_telefono, c.direccion AS customer_direccion
             FROM orders o
             INNER JOIN customers c ON c.id = o.customer_id
             WHERE o.id = :id'
        );
        $orderStatement->execute([':id' => $id]);
        $order = $orderStatement->fetch();
        if (!is_array($order)) {
            return null;
        }

        $itemsStatement = $this->connection->pdo()->prepare(
            'SELECT oi.*, p.nombre AS product_nombre, p.sku AS product_sku
             FROM order_items oi
             INNER JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.id ASC'
        );
        $itemsStatement->execute([':order_id' => $id]);

        $order['items'] = $itemsStatement->fetchAll();

        return $order;
    }
}
