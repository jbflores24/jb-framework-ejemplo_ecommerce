<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Customer;
use Jb\Core\HttpException;
use Jb\Core\Request;
use Jb\Core\Response;

class CustomerController
{
    public function __construct(private readonly Customer $repository)
    {
    }

    /**
     * List resources.
     */
    public function index(Request $request): Response
    {
        return Response::success($this->repository->all());
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
        $email = strtolower(trim((string) ($body['email'] ?? '')));

        if ($nombre === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpException('nombre y email valido son requeridos.', 422, 'VALIDATION_ERROR');
        }

        $id = $this->repository->create([
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => isset($body['telefono']) ? (string) $body['telefono'] : null,
            'direccion' => isset($body['direccion']) ? (string) $body['direccion'] : null,
            'activo' => filter_var((string) ($body['activo'] ?? 'true'), FILTER_VALIDATE_BOOL),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return Response::success(['id' => $id], 'Cliente creado.');
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

        foreach (['nombre', 'telefono', 'direccion'] as $field) {
            if (array_key_exists($field, $body)) {
                $payload[$field] = $body[$field] === null ? null : (string) $body[$field];
            }
        }

        if (array_key_exists('email', $body)) {
            $email = strtolower(trim((string) $body['email']));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new HttpException('email invalido.', 422, 'VALIDATION_ERROR');
            }
            $payload['email'] = $email;
        }

        if (array_key_exists('activo', $body)) {
            $payload['activo'] = (bool) $body['activo'];
        }

        $updated = $this->repository->update($id, $payload);

        return Response::success(['updated' => $updated], 'Cliente actualizado.');
    }

    /**
     * Delete one resource.
     */
    public function destroy(Request $request): Response
    {
        $deleted = $this->repository->delete((int) $request->input('id'));

        return Response::success(['deleted' => $deleted], 'Cliente eliminado.');
    }
}
