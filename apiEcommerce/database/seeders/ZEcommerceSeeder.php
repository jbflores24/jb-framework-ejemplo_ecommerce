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

        if ($this->table('categories')->get() === []) {
            $this->table('categories')->insert([
                'nombre' => 'Tecnologia',
                'slug' => 'tecnologia',
                'descripcion' => 'Gadgets, accesorios y perifericos',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->table('categories')->insert([
                'nombre' => 'Hogar',
                'slug' => 'hogar',
                'descripcion' => 'Productos utiles para casa',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->table('categories')->insert([
                'nombre' => 'Libros',
                'slug' => 'libros',
                'descripcion' => 'Lectura tecnica y general',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $categories = $this->table('categories')->get();
        $categoryBySlug = [];
        foreach ($categories as $category) {
            $categoryBySlug[(string) $category['slug']] = (int) $category['id'];
        }

        if ($this->table('products')->get() === []) {
            $this->table('products')->insert([
                'category_id' => $categoryBySlug['tecnologia'] ?? 1,
                'sku' => 'TEC-MOUSE-001',
                'nombre' => 'Mouse Inalambrico',
                'descripcion' => 'Mouse ergonomico de 2.4Ghz',
                'precio_centavos' => 29900,
                'stock' => 50,
                'imagen_url' => 'https://images.unsplash.com/photo-1527814050087-3793815479db',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->table('products')->insert([
                'category_id' => $categoryBySlug['tecnologia'] ?? 1,
                'sku' => 'TEC-KEY-002',
                'nombre' => 'Teclado Mecanico',
                'descripcion' => 'Teclado mecanico RGB',
                'precio_centavos' => 89900,
                'stock' => 20,
                'imagen_url' => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->table('products')->insert([
                'category_id' => $categoryBySlug['hogar'] ?? 2,
                'sku' => 'HOG-LAMP-003',
                'nombre' => 'Lampara de Escritorio',
                'descripcion' => 'Lampara LED recargable',
                'precio_centavos' => 45900,
                'stock' => 35,
                'imagen_url' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->table('products')->insert([
                'category_id' => $categoryBySlug['libros'] ?? 3,
                'sku' => 'LIB-API-004',
                'nombre' => 'Libro APIs Modernas',
                'descripcion' => 'Buenas practicas para APIs REST',
                'precio_centavos' => 39900,
                'stock' => 40,
                'imagen_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($this->table('customers')->get() === []) {
            $this->table('customers')->insert([
                'nombre' => 'Cliente Demo',
                'email' => 'cliente.demo@ecommerce.local',
                'telefono' => '5512345678',
                'direccion' => 'CDMX, Mexico',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
