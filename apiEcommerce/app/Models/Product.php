<?php

declare(strict_types=1);

namespace App\Models;

use Jb\Database\BaseRepository;
use Jb\Database\Connection;

class Product extends BaseRepository
{
    /**
     * Create the repository.
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, 'products');
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function catalog(array $filters = []): array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria_nombre
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['only_active'])) {
            $sql .= ' AND p.activo = 1';
        }

        if (!empty($filters['category_id'])) {
            $sql .= ' AND p.category_id = :category_id';
            $params[':category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (p.nombre LIKE :search OR p.descripcion LIKE :search)';
            $params[':search'] = '%' . trim((string) $filters['search']) . '%';
        }

        $sql .= ' ORDER BY p.id DESC';

        $statement = $this->connection->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * @param list<int> $ids
     * @return array<int, array<string, mixed>>
     */
    public function findManyByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $key = ':id' . $index;
            $placeholders[] = $key;
            $params[$key] = (int) $id;
        }

        $sql = 'SELECT * FROM products WHERE id IN (' . implode(', ', $placeholders) . ')';
        $statement = $this->connection->pdo()->prepare($sql);
        $statement->execute($params);

        $rows = $statement->fetchAll();
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['id']] = $row;
        }

        return $indexed;
    }

    public function decreaseStock(int $productId, int $quantity): bool
    {
        $statement = $this->connection->pdo()->prepare(
            'UPDATE products SET stock = stock - :quantity, updated_at = :updated_at
             WHERE id = :id AND stock >= :quantity'
        );

        $statement->execute([
            ':id' => $productId,
            ':quantity' => $quantity,
            ':updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $statement->rowCount() > 0;
    }
}
