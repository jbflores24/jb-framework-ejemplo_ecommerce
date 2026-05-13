<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use Jb\Core\HttpException;
use Jb\Core\Request;
use Jb\Core\Response;

class ProductController
{
    public function __construct(private readonly Product $repository)
    {
    }

    /**
     * List resources.
     */
    public function index(Request $request): Response
    {
        $rows = $this->repository->catalog([
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id'),
            'only_active' => filter_var((string) $request->input('only_active', 'true'), FILTER_VALIDATE_BOOL),
        ]);

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
        $body = $request->body();
        $nombre = trim((string) ($body['nombre'] ?? ''));
        $sku = strtoupper(trim((string) ($body['sku'] ?? '')));
        $categoryId = (int) ($body['category_id'] ?? 0);
        $precioCentavos = (int) ($body['precio_centavos'] ?? -1);
        $stock = (int) ($body['stock'] ?? -1);

        if ($nombre === '' || $sku === '' || $categoryId <= 0 || $precioCentavos < 0 || $stock < 0) {
            throw new HttpException('nombre, sku, category_id, precio_centavos y stock son requeridos.', 422, 'VALIDATION_ERROR');
        }

        $id = $this->repository->create([
            'category_id' => $categoryId,
            'sku' => $sku,
            'nombre' => $nombre,
            'descripcion' => isset($body['descripcion']) ? (string) $body['descripcion'] : null,
            'precio_centavos' => $precioCentavos,
            'stock' => $stock,
            'imagen_url' => isset($body['imagen_url']) ? (string) $body['imagen_url'] : null,
            'activo' => filter_var((string) ($body['activo'] ?? 'true'), FILTER_VALIDATE_BOOL),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return Response::success(['id' => $id], 'Producto creado.');
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
        $payload = ['updated_at' => date('Y-m-d H:i:s')];

        foreach (['nombre', 'descripcion', 'imagen_url'] as $field) {
            if (array_key_exists($field, $body)) {
                $payload[$field] = $body[$field] === null ? null : (string) $body[$field];
            }
        }

        foreach (['category_id', 'precio_centavos', 'stock'] as $field) {
            if (array_key_exists($field, $body)) {
                $payload[$field] = (int) $body[$field];
            }
        }

        if (array_key_exists('sku', $body)) {
            $payload['sku'] = strtoupper(trim((string) $body['sku']));
        }
        if (array_key_exists('activo', $body)) {
            $payload['activo'] = (bool) $body['activo'];
        }

        $updated = $this->repository->update($id, $payload);

        return Response::success(['updated' => $updated], 'Producto actualizado.');
    }

    /**
     * Delete one resource.
     */
    public function destroy(Request $request): Response
    {
        $deleted = $this->repository->delete((int) $request->input('id'));

        return Response::success(['deleted' => $deleted], 'Producto eliminado.');
    }
}
