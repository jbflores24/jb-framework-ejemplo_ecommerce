# appEcommerce

Frontend web ligero (HTML/CSS/JS) para consumir `apiEcommerce`.

## Funcionalidad

- Catalogo de productos con filtro por categoria y busqueda.
- Carrito local con control de cantidades.
- Checkout real contra `POST /api/checkout`.

## Uso rapido

1. Asegura que la API este levantada en `apiEcommerce`.
2. Abre `index.html` en navegador (o sirvelo con un servidor estatico).
3. Ajusta el campo `API Base` si tu URL cambia.

URL API por defecto en frontend:

- `http://localhost/ecommerce/apiEcommerce/public/api`

## Notas

- El frontend no usa framework JS para mantenerlo simple y auditable.
- Toda la logica de negocio (stock, total, creacion de pedido) vive en `apiEcommerce`.
