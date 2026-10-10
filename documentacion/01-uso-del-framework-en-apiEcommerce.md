# Uso del Framework JB en apiEcommerce

Este documento describe exactamente como se utilizo JB Framework en el backend.

## 1. Generacion base del proyecto

Desde el repositorio del framework (`jb-framework`) se ejecuto:

```bash
php bin/jb new ..\ecommerce\apiEcommerce
```

Esto genero estructura estandar de proyecto JB:

- `app/`
- `config/`
- `database/`
- `routes/`
- `public/`
- `tests/`

## 2. Scaffolding inicial de recursos

Dentro de `apiEcommerce` se ejecuto:

```bash
php jb make:scaffold Category
php jb make:scaffold Product
php jb make:scaffold Customer
php jb make:scaffold Order
php jb make:scaffold OrderItem
```

Con esto se obtuvieron controladores/modelos/migraciones/seeders base.

## 3. Ajustes de dominio reales

Sobre los scaffolds se implemento:

- tabla `categories` (corrigiendo pluralizacion)
- `products` con `category_id`, `sku`, `precio_centavos`, `stock`
- `customers` con `email` unico
- `orders` con `customer_id`, `status`, `total_centavos`
- `order_items` con `cantidad` y subtotales

## 4. Capa de negocio

Se agrego `app/Services/CheckoutService.php` con responsabilidades:

- validar payload de checkout,
- resolver/crear cliente,
- validar disponibilidad de producto,
- decrementar stock de forma segura,
- crear pedido + items en transaccion,
- recalcular total final.

## 5. Ruteo REST

`routes/api.php` expone:

- `/categories`
- `/products`
- `/customers`
- `/orders`
- `/order_items`
- `/checkout`

## 6. Datos demo

Seeder principal:

- `database/seeders/ZEcommerceSeeder.php`
- `database/seeders/ZDemoOrdersSeeder.php`
- `database/seeders/ZBulkProductsSeeder.php`

Carga:

- categorias,
- productos,
- cliente demo,
- pedido demo,
- catalogo ampliado (10-15 productos por categoria).

## 7. Comandos operativos usados

```bash
php jb migrate:fresh
php jb seed ZEcommerceSeeder
php jb seed ZDemoOrdersSeeder
php jb seed ZBulkProductsSeeder
php jb docs:generate
```

## 8. Observaciones de validacion

- Migraciones y seed: OK.
- `docs/swagger.yaml` generado: OK.
- Pruebas PHPUnit no se ejecutaron por bloqueo de archivos en Windows al instalar `phpunit` (antivirus/indexador).
