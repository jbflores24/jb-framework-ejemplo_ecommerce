<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use Jb\Core\HttpException;
use Jb\Core\Request;
use Jb\Core\Response;

class OrderController
{
    public function __construct(
        private readonly Order $repository,
        private readonly CheckoutService $checkoutService
    ) {
    }

    /**
     * List resources.
     */
    public function index(Request $request): Response
    {
        $customerId = (int) $request->input('customer_id', 0);

        return Response::success($this->repository->listWithCustomer($customerId > 0 ? $customerId : null));
    }

    /**
     * Show one resource.
     */
    public function show(Request $request): Response
    {
        $row = $this->repository->findWithItems((int) $request->input('id'));

        return $row ? Response::success($row) : throw new HttpException('No encontrado.', 404);
    }

    /**
     * Store one resource.
     */
    public function store(Request $request): Response
    {
        $order = $this->checkoutService->place($request->body());

        return Response::success($order, 'Pedido creado correctamente.');
    }

    /**
     * Update one resource.
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->input('id');
        $current = $this->repository->find($id);
        if ($current === null) {
            throw new HttpException('No encontrado.', 404, 'NOT_FOUND');
        }

        $body = $request->body();
        $status = strtolower(trim((string) ($body['status'] ?? '')));
        $validStatuses = ['created', 'paid', 'shipped', 'cancelled'];
        if ($status === '' || !in_array($status, $validStatuses, true)) {
            throw new HttpException('status invalido.', 422, 'VALIDATION_ERROR');
        }

        $updated = $this->repository->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return Response::success(['updated' => $updated], 'Pedido actualizado.');
    }

    /**
     * Delete one resource.
     */
    public function destroy(Request $request): Response
    {
        $deleted = $this->repository->delete((int) $request->input('id'));

        return Response::success(['deleted' => $deleted], 'Pedido eliminado.');
    }
}
