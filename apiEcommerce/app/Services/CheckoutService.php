<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Jb\Core\HttpException;
use Jb\Database\Connection;

class CheckoutService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Order $orders,
        private readonly OrderItem $orderItems,
        private readonly Product $products,
        private readonly Customer $customers
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function place(array $payload): array
    {
        $items = $payload['items'] ?? [];
        if (!is_array($items) || $items === []) {
            throw new HttpException('Debes enviar al menos un item.', 422, 'VALIDATION_ERROR');
        }

        $customerId = $this->resolveCustomerId($payload);

        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new HttpException('Formato de item invalido.', 422, 'VALIDATION_ERROR');
            }

            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($productId <= 0 || $quantity <= 0) {
                throw new HttpException('Cada item requiere product_id y quantity > 0.', 422, 'VALIDATION_ERROR');
            }

            $normalized[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }

        $productsById = $this->products->findManyByIds(array_values(array_unique(array_column($normalized, 'product_id'))));

        $orderId = (int) $this->connection->transaction(function () use ($customerId, $normalized, $productsById): int {
            $total = 0;
            $orderId = (int) $this->orders->create([
                'customer_id' => $customerId,
                'status' => 'created',
                'currency' => 'MXN',
                'total_centavos' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($normalized as $item) {
                $product = $productsById[$item['product_id']] ?? null;
                if ($product === null || !(bool) $product['activo']) {
                    throw new HttpException('Producto no disponible: ' . $item['product_id'], 422, 'PRODUCT_NOT_AVAILABLE');
                }

                $currentStock = (int) $product['stock'];
                if ($currentStock < $item['quantity']) {
                    throw new HttpException('Stock insuficiente para: ' . $product['nombre'], 422, 'INSUFFICIENT_STOCK');
                }

                if (!$this->products->decreaseStock($item['product_id'], $item['quantity'])) {
                    throw new HttpException('No fue posible reservar stock para: ' . $product['nombre'], 409, 'STOCK_RACE_CONDITION');
                }

                $unitPrice = (int) $product['precio_centavos'];
                $subtotal = $unitPrice * (int) $item['quantity'];
                $total += $subtotal;

                $this->orderItems->create([
                    'order_id' => $orderId,
                    'product_id' => (int) $item['product_id'],
                    'cantidad' => (int) $item['quantity'],
                    'precio_unitario_centavos' => $unitPrice,
                    'subtotal_centavos' => $subtotal,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->orders->update($orderId, [
                'total_centavos' => $total,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return $orderId;
        });

        $order = $this->orders->findWithItems($orderId);
        if ($order === null) {
            throw new HttpException('No se pudo recuperar el pedido creado.', 500, 'ORDER_READ_ERROR');
        }

        return $order;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolveCustomerId(array $payload): int
    {
        $customerId = (int) ($payload['customer_id'] ?? 0);
        if ($customerId > 0) {
            $exists = $this->customers->find($customerId);
            if ($exists === null) {
                throw new HttpException('customer_id no existe.', 422, 'CUSTOMER_NOT_FOUND');
            }

            return $customerId;
        }

        $customer = $payload['customer'] ?? null;
        if (!is_array($customer)) {
            throw new HttpException('Debes enviar customer_id o customer.', 422, 'VALIDATION_ERROR');
        }

        $name = trim((string) ($customer['nombre'] ?? ''));
        $email = strtolower(trim((string) ($customer['email'] ?? '')));

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpException('customer.nombre y customer.email validos son requeridos.', 422, 'VALIDATION_ERROR');
        }

        $existing = $this->customers->findByEmail($email);
        if ($existing !== null) {
            return (int) $existing['id'];
        }

        return (int) $this->customers->create([
            'nombre' => $name,
            'email' => $email,
            'telefono' => isset($customer['telefono']) ? (string) $customer['telefono'] : null,
            'direccion' => isset($customer['direccion']) ? (string) $customer['direccion'] : null,
            'activo' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
