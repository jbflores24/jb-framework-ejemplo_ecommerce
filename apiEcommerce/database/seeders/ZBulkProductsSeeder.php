<?php

declare(strict_types=1);

use Jb\Database\Connection;
use Jb\Database\Seeder;

return new class (Connection::getInstance()) extends Seeder {
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $categories = $this->table('categories')->get();
        if ($categories === []) {
            return;
        }

        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[(string) $category['slug']] = (int) $category['id'];
        }

        $dataset = [
            'tecnologia' => [
                ['TEC-HEAD-005', 'Audifonos Bluetooth', 'Audifonos inalambricos con cancelacion de ruido', 129900, 30],
                ['TEC-WEB-006', 'Camara Web HD', 'Camara 1080p para videollamadas', 79900, 25],
                ['TEC-HUB-007', 'Hub USB-C', 'Adaptador multipuerto USB-C', 55900, 35],
                ['TEC-SSD-008', 'SSD Externo 1TB', 'Unidad SSD portable de alto rendimiento', 189900, 18],
                ['TEC-PAD-009', 'Mouse Pad XL', 'Superficie extendida para escritorio', 25900, 40],
                ['TEC-STAND-010', 'Soporte para Laptop', 'Soporte ergonomico de aluminio', 64900, 20],
                ['TEC-MIC-011', 'Microfono USB', 'Microfono cardioide para streaming', 99900, 16],
                ['TEC-LIGHT-012', 'Aro de Luz', 'Ring light con ajuste de brillo', 69900, 22],
                ['TEC-KB-013', 'Teclado Compacto', 'Teclado mecanico 75%', 109900, 14],
                ['TEC-MOUSE-014', 'Mouse Gamer', 'Mouse RGB de alta precision', 74900, 19],
                ['TEC-DOCK-015', 'Docking Station', 'Base de expansion para laptop', 159900, 10],
                ['TEC-WIFI-016', 'Repetidor WiFi', 'Extensor de senal de doble banda', 58900, 28],
            ],
            'hogar' => [
                ['HOG-ORG-005', 'Organizador Modular', 'Organizador de escritorio apilable', 32900, 45],
                ['HOG-MUG-006', 'Taza Termica', 'Taza de acero inoxidable 500ml', 27900, 50],
                ['HOG-CUSH-007', 'Cojin Lumbar', 'Soporte lumbar para silla', 46900, 26],
                ['HOG-BOTT-008', 'Botella Reutilizable', 'Botella deportiva libre de BPA', 21900, 60],
                ['HOG-CLOCK-009', 'Reloj Digital', 'Reloj LED con alarma', 39900, 24],
                ['HOG-PLANT-010', 'Maceta Inteligente', 'Maceta con autoregado', 89900, 12],
                ['HOG-BLANK-011', 'Manta Ligera', 'Manta suave para sala', 51900, 18],
                ['HOG-BOARD-012', 'Pizarron Semanal', 'Planner magnetico para pared', 35900, 27],
                ['HOG-SHELF-013', 'Repisa Flotante', 'Set de dos repisas', 68900, 16],
                ['HOG-LAMP-014', 'Lampara de Pie', 'Lampara minimalista para sala', 119900, 11],
                ['HOG-TRAY-015', 'Bandeja Multiuso', 'Bandeja antideslizante', 18900, 55],
                ['HOG-DIFF-016', 'Difusor de Aroma', 'Difusor ultrasónico con luces', 62900, 21],
            ],
            'libros' => [
                ['LIB-JS-005', 'JavaScript Practico', 'Guia de JS moderno', 34900, 38],
                ['LIB-PHP-006', 'PHP 8 a Fondo', 'Patrones y buenas practicas', 42900, 32],
                ['LIB-ARCH-007', 'Arquitectura de Software', 'Diseño de sistemas escalables', 49900, 24],
                ['LIB-DB-008', 'Modelado de Datos', 'Modelado relacional para apps', 37900, 30],
                ['LIB-UX-009', 'UX para Producto', 'Diseno centrado en usuario', 33900, 29],
                ['LIB-TEST-010', 'Testing Profesional', 'Pruebas unitarias e integracion', 45900, 20],
                ['LIB-SEC-011', 'Seguridad Web', 'Guia OWASP aplicada', 51900, 17],
                ['LIB-DOCKER-012', 'Docker Esencial', 'Contenedores en proyectos reales', 36900, 31],
                ['LIB-CLEAN-013', 'Clean Code en Practica', 'Refactor y mantenibilidad', 39900, 28],
                ['LIB-REST-014', 'REST API Design', 'Contrato, versionado y errores', 44900, 26],
                ['LIB-CLOUD-015', 'Cloud Native Basics', 'Microservicios y observabilidad', 48900, 19],
                ['LIB-PROD-016', 'Productividad para Devs', 'Habitos y flujo de trabajo', 29900, 34],
            ],
        ];

        foreach ($dataset as $slug => $products) {
            $categoryId = $categoryIds[$slug] ?? null;
            if ($categoryId === null) {
                continue;
            }

            foreach ($products as [$sku, $nombre, $descripcion, $precioCentavos, $stock]) {
                $exists = $this->table('products')->where('sku', $sku)->first();
                if ($exists !== null) {
                    continue;
                }

                $this->table('products')->insert([
                    'category_id' => $categoryId,
                    'sku' => $sku,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'precio_centavos' => $precioCentavos,
                    'stock' => $stock,
                    'imagen_url' => null,
                    'activo' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
