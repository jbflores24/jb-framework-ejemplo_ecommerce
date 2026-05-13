<?php

declare(strict_types=1);

use Jb\Database\Connection;
use Jb\Database\Seeder;

return new class (Connection::getInstance()) extends Seeder {
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $customers = $this->table('customers')->get();
        if ($customers === []) {
            return;
        }

        // Evita duplicar pedidos demo si ya existen
        if ($this->table('orders')->get() !== []) {
            return;
        }

        $products = $this->table('products')->where('activo', 1)->get();
        if (count($products) < 2) {
            return;
        }

        $customerId = (int) $customers[0]['id'];

        $p1 = $products[0];
        $p2 = $products[1];

        $qty1 = 1;
        $qty2 = 2;

        $subtotal1 = (int) $p1['precio_centavos'] * $qty1;
        $subtotal2 = (int) $p2['precio_centavos'] * $qty2;
        $total = $subtotal1 + $subtotal2;

        $orderId = (int) $this->table('orders')->insert([
            'customer_id' => $customerId,
            'status' => 'paid',
            'currency' => 'MXN',
            'total_centavos' => $total,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => (int) $p1['id'],
            'cantidad' => $qty1,
            'precio_unitario_centavos' => (int) $p1['precio_centavos'],
            'subtotal_centavos' => $subtotal1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => (int) $p2['id'],
            'cantidad' => $qty2,
            'precio_unitario_centavos' => (int) $p2['precio_centavos'],
            'subtotal_centavos' => $subtotal2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
