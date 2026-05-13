<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\OrderItem;
use Jb\Core\HttpException;
use Jb\Core\Request;
use Jb\Core\Response;

class OrderItemController
{
    public function __construct(private readonly OrderItem $repository)
    {
    }

    /**
     * List resources.
     */
    public function index(Request $request): Response
    {
        $orderId = (int) $request->input('order_id', 0);
        $rows = $orderId > 0 ? $this->repository->byOrderId($orderId) : $this->repository->all();

        return Response::success($rows);
    }

    /**
     * Show one resource.
     */
    public function show(Request $request): Response
    {
        $row = $this->repository->find((int) $request->input('id'));
        return $row ? Response::success($row) : throw new HttpException('No encontrado.', 404);
    }

    /**
     * Store one resource.
     */
    public function store(Request $request): Response
    {
        return Response::success(['id' => $this->repository->create($request->body())], 'Creado.');
    }

    /**
     * Update one resource.
     */
    public function update(Request $request): Response
    {
        $updated = $this->repository->update((int) $request->input('id'), $request->body());
        return Response::success(['updated' => $updated], 'Actualizado.');
    }

    /**
     * Delete one resource.
     */
    public function destroy(Request $request): Response
    {
        $deleted = $this->repository->delete((int) $request->input('id'));

        return Response::success(['deleted' => $deleted], 'Item eliminado.');
    }
}
