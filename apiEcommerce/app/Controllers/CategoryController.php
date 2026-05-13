<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use Jb\Core\HttpException;
use Jb\Core\Request;
use Jb\Core\Response;

class CategoryController
{
    public function __construct(private readonly Category $repository)
    {
    }

    /**
     * List resources.
     */
    public function index(Request $request): Response
    {
        $onlyActive = filter_var((string) $request->input('only_active', 'false'), FILTER_VALIDATE_BOOL);
        $rows = $onlyActive ? $this->repository->activeList() : $this->repository->all();

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
        $slug = trim((string) ($body['slug'] ?? ''));

        if ($nombre === '' || $slug === '') {
            throw new HttpException('nombre y slug son requeridos.', 422, 'VALIDATION_ERROR');
        }

        $id = $this->repository->create([
            'nombre' => $nombre,
            'slug' => strtolower($slug),
            'descripcion' => isset($body['descripcion']) ? (string) $body['descripcion'] : null,
            'activo' => filter_var((string) ($body['activo'] ?? 'true'), FILTER_VALIDATE_BOOL),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return Response::success(['id' => $id], 'Categoria creada.');
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
        $payload = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (array_key_exists('nombre', $body)) {
            $payload['nombre'] = trim((string) $body['nombre']);
        }
        if (array_key_exists('slug', $body)) {
            $payload['slug'] = strtolower(trim((string) $body['slug']));
        }
        if (array_key_exists('descripcion', $body)) {
            $payload['descripcion'] = $body['descripcion'] === null ? null : (string) $body['descripcion'];
        }
        if (array_key_exists('activo', $body)) {
            $payload['activo'] = (bool) $body['activo'];
        }

        $updated = $this->repository->update($id, $payload);

        return Response::success(['updated' => $updated], 'Categoria actualizada.');
    }

    /**
     * Delete one resource.
     */
    public function destroy(Request $request): Response
    {
        $deleted = $this->repository->delete((int) $request->input('id'));

        return Response::success(['deleted' => $deleted], 'Categoria eliminada.');
    }
}
