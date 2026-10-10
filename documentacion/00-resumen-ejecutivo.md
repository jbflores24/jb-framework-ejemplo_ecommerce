# Ecommerce - Resumen Ejecutivo

Se creo una solucion en la carpeta `ecommerce` del servidor web local con tres componentes:

- `apiEcommerce`: backend REST sobre JB Framework.
- `appEcommerce`: frontend web que consume la API.
- `documentacion`: guia de implementacion y uso del framework en este proyecto.

## Objetivo cumplido

Construir un sistema e-commerce pequeno pero real:

- catalogo de categorias y productos,
- clientes,
- pedidos y detalle,
- checkout con validacion de stock.

## Decisiones tecnicas

- Framework backend: JB Framework.
- Persistencia por defecto: SQLite local para simplicidad de puesta en marcha.
- Montos monetarios: enteros en centavos.
- Frontend: HTML/CSS/JS sin framework para auditabilidad y despliegue simple.
