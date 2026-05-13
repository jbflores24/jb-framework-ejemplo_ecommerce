const state = {
  apiBase: localStorage.getItem('ecommerce_api_base') || 'http://localhost/ecommerce/apiEcommerce/public/api',
  categories: [],
  products: [],
  orders: [],
  cart: [],
  activeView: 'tienda',
  storeView: {
    filtered: [],
    page: 1,
    pageSize: 8,
    sortField: 'nombre',
    sortDir: 'asc',
  },
  catalogView: {
    table: 'categories',
    rows: [],
    filtered: [],
    page: 1,
    pageSize: 10,
    search: '',
    sortField: '',
    sortDir: 'asc',
  },
  ordersView: {
    filtered: [],
    page: 1,
    pageSize: 10,
    sortField: 'id',
    sortDir: 'desc',
  },
};

const fmtMoney = (cents) => `$${(Number(cents || 0) / 100).toFixed(2)}`;

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

function setApiBase(base) {
  state.apiBase = base.replace(/\/$/, '');
  localStorage.setItem('ecommerce_api_base', state.apiBase);
}

function getRowId(row) {
  const parsed = Number(row?.id ?? 0);
  return Number.isFinite(parsed) ? parsed : 0;
}

async function api(path, options = {}) {
  const response = await fetch(`${state.apiBase}${path}`, {
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options,
  });

  const body = await response.json();
  if (!response.ok || body.status === 'error') {
    throw new Error(body.message || 'Error en API');
  }

  return body.data;
}

function showOperationResult(text, isError = false) {
  const box = document.getElementById('ordersOperationResult');
  box.textContent = text;
  box.classList.toggle('error', isError);
  box.style.display = 'block';
}

function showCatalogCrudResult(text, isError = false) {
  const box = document.getElementById('catalogCrudResult');
  box.textContent = text;
  box.classList.toggle('error', isError);
  box.style.display = 'block';
}

function switchView(viewName) {
  state.activeView = viewName;

  const views = {
    tienda: document.getElementById('viewTienda'),
    catalogos: document.getElementById('viewCatalogos'),
    pedidos: document.getElementById('viewPedidos'),
  };

  for (const [key, node] of Object.entries(views)) {
    node.classList.toggle('hidden', key !== viewName);
  }

  document.querySelectorAll('.nav-btn').forEach((button) => {
    button.classList.toggle('active', button.dataset.view === viewName);
  });
}

function renderCategories() {
  const select = document.getElementById('categoryFilter');
  const currentValue = select.value;

  select.innerHTML = '<option value="">Todas las categorias</option>';
  for (const cat of state.categories) {
    const option = document.createElement('option');
    option.value = String(cat.id);
    option.textContent = cat.nombre;
    select.appendChild(option);
  }

  if (currentValue) {
    select.value = currentValue;
  }
}

function addToCart(product) {
  const existing = state.cart.find((item) => item.id === product.id);
  if (existing) {
    if (existing.quantity < product.stock) {
      existing.quantity += 1;
    }
  } else {
    state.cart.push({
      id: product.id,
      nombre: product.nombre,
      precio_centavos: product.precio_centavos,
      stock: product.stock,
      quantity: 1,
    });
  }

  renderCart();
}

function compareValues(a, b, dir) {
  const direction = dir === 'desc' ? -1 : 1;
  const av = a ?? '';
  const bv = b ?? '';

  const an = Number(av);
  const bn = Number(bv);
  if (Number.isFinite(an) && Number.isFinite(bn) && String(av) !== '' && String(bv) !== '') {
    return (an - bn) * direction;
  }

  return String(av).localeCompare(String(bv), 'es', { sensitivity: 'base' }) * direction;
}

function renderProducts() {
  const grid = document.getElementById('productGrid');
  const info = document.getElementById('storePageInfo');
  grid.innerHTML = '';

  if (!state.storeView.filtered.length) {
    grid.innerHTML = '<p>No hay productos disponibles con ese filtro.</p>';
    info.textContent = 'Pagina 1 de 1';
    document.getElementById('storePrevPage').disabled = true;
    document.getElementById('storeNextPage').disabled = true;
    return;
  }

  const rows = state.storeView.filtered;
  const pageSize = Math.max(1, Number(state.storeView.pageSize || 8));
  const pages = Math.max(1, Math.ceil(rows.length / pageSize));
  if (state.storeView.page > pages) state.storeView.page = pages;
  if (state.storeView.page < 1) state.storeView.page = 1;

  const start = (state.storeView.page - 1) * pageSize;
  const slice = rows.slice(start, start + pageSize);

  for (const product of slice) {
    const card = document.createElement('article');
    card.className = 'product-card';

    const isOut = Number(product.stock) <= 0;
    card.innerHTML = `
      <h3>${escapeHtml(product.nombre)}</h3>
      <div class="product-meta">${escapeHtml(product.categoria_nombre || 'Sin categoria')}</div>
      <div class="product-meta">SKU: ${escapeHtml(product.sku)}</div>
      <div class="price">${fmtMoney(product.precio_centavos)}</div>
      <div class="${isOut ? 'out-stock' : 'product-meta'}">Stock: ${escapeHtml(product.stock)}</div>
      <button ${isOut ? 'disabled' : ''}>${isOut ? 'Sin stock' : 'Agregar al carrito'}</button>
    `;

    if (!isOut) {
      card.querySelector('button').addEventListener('click', () => addToCart(product));
    }

    grid.appendChild(card);
  }

  info.textContent = `Pagina ${state.storeView.page} de ${pages} | ${rows.length} productos`;
  document.getElementById('storePrevPage').disabled = state.storeView.page <= 1;
  document.getElementById('storeNextPage').disabled = state.storeView.page >= pages;
}

function applyStoreSortAndPagination() {
  const rows = [...state.products];
  const field = state.storeView.sortField;
  const dir = state.storeView.sortDir;

  rows.sort((left, right) => compareValues(left?.[field], right?.[field], dir));
  state.storeView.filtered = rows;
  renderProducts();
}

function updateCartItem(productId, delta) {
  const item = state.cart.find((row) => row.id === productId);
  if (!item) return;

  item.quantity += delta;
  if (item.quantity <= 0) {
    state.cart = state.cart.filter((row) => row.id !== productId);
  }

  if (item.quantity > item.stock) {
    item.quantity = item.stock;
  }

  renderCart();
}

function renderCart() {
  const list = document.getElementById('cartItems');
  list.innerHTML = '';

  let total = 0;
  for (const item of state.cart) {
    total += item.precio_centavos * item.quantity;

    const div = document.createElement('div');
    div.className = 'cart-item';
    div.innerHTML = `
      <strong>${escapeHtml(item.nombre)}</strong>
      <span>${fmtMoney(item.precio_centavos)} x ${item.quantity}</span>
      <div class="cart-actions">
        <button data-act="minus">-</button>
        <button data-act="plus">+</button>
        <button data-act="remove" class="secondary">Quitar</button>
      </div>
    `;

    div.querySelector('[data-act="minus"]').addEventListener('click', () => updateCartItem(item.id, -1));
    div.querySelector('[data-act="plus"]').addEventListener('click', () => updateCartItem(item.id, 1));
    div.querySelector('[data-act="remove"]').addEventListener('click', () => {
      state.cart = state.cart.filter((row) => row.id !== item.id);
      renderCart();
    });

    list.appendChild(div);
  }

  document.getElementById('cartTotal').textContent = fmtMoney(total);
}

function setCatalogPreview(value) {
  const preview = document.getElementById('catalogRecordPreview');
  if (typeof value === 'string') {
    preview.textContent = value;
    return;
  }

  preview.textContent = JSON.stringify(value, null, 2);
}

function defaultCreatePayloadByTable(table) {
  const now = Date.now();
  if (table === 'categories') {
    return {
      nombre: `Categoria ${now}`,
      slug: `categoria-${now}`,
      descripcion: 'Categoria creada desde frontend',
      activo: true,
    };
  }

  if (table === 'products') {
    const firstCategoryId = Number(state.categories[0]?.id || 1);
    return {
      category_id: firstCategoryId,
      sku: `FRONT-${String(now).slice(-6)}`,
      nombre: `Producto ${now}`,
      descripcion: 'Producto creado desde frontend',
      precio_centavos: 19900,
      stock: 10,
      activo: true,
    };
  }

  if (table === 'customers') {
    return {
      nombre: `Cliente ${now}`,
      email: `cliente${now}@example.com`,
      telefono: '5550000000',
      direccion: 'Direccion demo',
      activo: true,
    };
  }

  if (table === 'order_items') {
    return {
      order_id: 1,
      product_id: 1,
      cantidad: 1,
      precio_unitario_centavos: 10000,
      subtotal_centavos: 10000,
    };
  }

  return {};
}

function applyCatalogFilters() {
  const term = state.catalogView.search.trim().toLowerCase();
  if (!term) {
    state.catalogView.filtered = [...state.catalogView.rows];
  } else {
    state.catalogView.filtered = state.catalogView.rows.filter((row) => {
      return Object.values(row || {}).some((value) => String(value ?? '').toLowerCase().includes(term));
    });
  }

  if (state.catalogView.sortField) {
    state.catalogView.filtered.sort((left, right) => {
      return compareValues(left?.[state.catalogView.sortField], right?.[state.catalogView.sortField], state.catalogView.sortDir);
    });
  }
}

function renderCatalogTable() {
  const container = document.getElementById('catalogTableContainer');
  const info = document.getElementById('catalogPageInfo');

  const rows = state.catalogView.filtered;
  const pageSize = Math.max(1, Number(state.catalogView.pageSize || 10));
  const pages = Math.max(1, Math.ceil(rows.length / pageSize));

  if (state.catalogView.page > pages) {
    state.catalogView.page = pages;
  }
  if (state.catalogView.page < 1) {
    state.catalogView.page = 1;
  }

  const start = (state.catalogView.page - 1) * pageSize;
  const slice = rows.slice(start, start + pageSize);

  if (rows.length === 0) {
    container.innerHTML = '<p>Sin datos para mostrar.</p>';
    info.textContent = 'Pagina 1 de 1';
    document.getElementById('catalogPrevPage').disabled = true;
    document.getElementById('catalogNextPage').disabled = true;
    return;
  }

  const columns = Array.from(
    rows.reduce((set, row) => {
      Object.keys(row || {}).forEach((key) => set.add(key));
      return set;
    }, new Set())
  );

  const header = columns.map((column) => `<th>${escapeHtml(column)}</th>`).join('');
  const body = slice
    .map((row) => {
      const cells = columns
        .map((column) => {
          const value = row[column];
          const text = value === null || value === undefined ? '' : String(value);
          return `<td>${escapeHtml(text)}</td>`;
        })
        .join('');

      const rowId = getRowId(row);
      const actions = rowId > 0
        ? `<td class="actions-cell">
            <button data-cat-action="view" data-cat-id="${rowId}">Ver</button>
            <button data-cat-action="prefill" data-cat-id="${rowId}" class="secondary">Editar</button>
            <button data-cat-action="delete" data-cat-id="${rowId}" class="danger">Eliminar</button>
          </td>`
        : '<td>-</td>';

      return `<tr>${cells}${actions}</tr>`;
    })
    .join('');

  container.innerHTML = `
    <table class="data-table">
      <thead>
        <tr>
          ${header}
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>${body}</tbody>
    </table>
  `;

  info.textContent = `Pagina ${state.catalogView.page} de ${pages} | ${rows.length} registros`;
  document.getElementById('catalogPrevPage').disabled = state.catalogView.page <= 1;
  document.getElementById('catalogNextPage').disabled = state.catalogView.page >= pages;
}

async function loadCatalogTable() {
  const table = document.getElementById('tableSelect').value;
  const data = await api(`/${table}`);

  state.catalogView.table = table;
  state.catalogView.rows = Array.isArray(data) ? data : [];
  state.catalogView.page = 1;
  state.catalogView.search = '';
  state.catalogView.sortField = '';
  state.catalogView.sortDir = 'asc';

  document.getElementById('catalogSearch').value = '';
  document.getElementById('catalogSortField').value = '';
  document.getElementById('catalogSortDir').value = 'asc';
  document.getElementById('catalogCreateJson').value = JSON.stringify(defaultCreatePayloadByTable(table), null, 2);
  document.getElementById('catalogUpdateJson').value = JSON.stringify({}, null, 2);
  document.getElementById('catalogReadId').value = '';
  document.getElementById('catalogUpdateId').value = '';
  document.getElementById('catalogDeleteId').value = '';

  const columns = Array.from(
    state.catalogView.rows.reduce((set, row) => {
      Object.keys(row || {}).forEach((key) => set.add(key));
      return set;
    }, new Set())
  );
  const sortField = document.getElementById('catalogSortField');
  sortField.innerHTML = '<option value="">Ordenar por...</option>';
  for (const column of columns) {
    sortField.innerHTML += `<option value="${escapeHtml(column)}">${escapeHtml(column)}</option>`;
  }

  applyCatalogFilters();
  renderCatalogTable();
  setCatalogPreview('Selecciona una fila o consulta por ID para ver detalle.');
}

async function catalogReadById(id) {
  const row = await api(`/${state.catalogView.table}/${id}`);
  setCatalogPreview(row);
  showCatalogCrudResult(`Registro #${id} consultado.`);
}

async function catalogCreate(payload) {
  const result = await api(`/${state.catalogView.table}`, {
    method: 'POST',
    body: JSON.stringify(payload),
  });
  return Number(result?.id || 0);
}

async function catalogUpdate(id, payload) {
  await api(`/${state.catalogView.table}/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  });
}

async function catalogDelete(id) {
  await api(`/${state.catalogView.table}/${id}`, {
    method: 'DELETE',
  });
}

function normalizeDate(dateLike) {
  const parsed = new Date(dateLike);
  if (Number.isNaN(parsed.getTime())) {
    return null;
  }
  return parsed;
}

function applyOrderFilters() {
  const status = document.getElementById('orderStatusFilter').value;
  const customer = document.getElementById('orderCustomerFilter').value.trim().toLowerCase();
  const minTotal = Number(document.getElementById('orderMinTotal').value || 0);
  const maxTotalRaw = document.getElementById('orderMaxTotal').value;
  const maxTotal = maxTotalRaw === '' ? null : Number(maxTotalRaw);
  const dateFrom = document.getElementById('orderDateFrom').value;
  const dateTo = document.getElementById('orderDateTo').value;

  state.ordersView.filtered = state.orders.filter((order) => {
    if (status && String(order.status) !== status) {
      return false;
    }

    if (customer) {
      const candidate = `${String(order.customer_nombre || '')} ${String(order.customer_email || '')}`.toLowerCase();
      if (!candidate.includes(customer)) {
        return false;
      }
    }

    const total = Number(order.total_centavos || 0);
    if (Number.isFinite(minTotal) && total < minTotal) {
      return false;
    }

    if (maxTotal !== null && Number.isFinite(maxTotal) && total > maxTotal) {
      return false;
    }

    if (dateFrom || dateTo) {
      const orderDate = normalizeDate(order.created_at);
      if (orderDate === null) {
        return false;
      }

      if (dateFrom) {
        const from = new Date(`${dateFrom}T00:00:00`);
        if (orderDate < from) {
          return false;
        }
      }

      if (dateTo) {
        const to = new Date(`${dateTo}T23:59:59`);
        if (orderDate > to) {
          return false;
        }
      }
    }

    return true;
  });

  state.ordersView.filtered.sort((left, right) => {
    return compareValues(left?.[state.ordersView.sortField], right?.[state.ordersView.sortField], state.ordersView.sortDir);
  });

  state.ordersView.page = 1;
  renderOrdersList();
}

function resetOrderFilters() {
  document.getElementById('orderStatusFilter').value = '';
  document.getElementById('orderCustomerFilter').value = '';
  document.getElementById('orderMinTotal').value = '';
  document.getElementById('orderMaxTotal').value = '';
  document.getElementById('orderDateFrom').value = '';
  document.getElementById('orderDateTo').value = '';

  state.ordersView.filtered = [...state.orders];
  state.ordersView.sortField = 'id';
  state.ordersView.sortDir = 'desc';
  document.getElementById('orderSortField').value = 'id';
  document.getElementById('orderSortDir').value = 'desc';
  state.ordersView.page = 1;
  state.ordersView.filtered.sort((left, right) => compareValues(left?.id, right?.id, 'desc'));
  renderOrdersList();
}

function renderOrderDetail(order) {
  const box = document.getElementById('orderDetail');
  if (!order) {
    box.innerHTML = 'Selecciona un pedido para ver el detalle.';
    return;
  }

  const items = Array.isArray(order.items) ? order.items : [];
  const itemsHtml = items
    .map((item) => {
      return `
        <tr>
          <td>${escapeHtml(item.product_id)}</td>
          <td>${escapeHtml(item.product_nombre || '')}</td>
          <td>${escapeHtml(item.cantidad)}</td>
          <td>${fmtMoney(item.precio_unitario_centavos)}</td>
          <td>${fmtMoney(item.subtotal_centavos)}</td>
        </tr>
      `;
    })
    .join('');

  box.innerHTML = `
    <div class="order-detail-head">
      <div><strong>ID:</strong> ${escapeHtml(order.id)}</div>
      <div><strong>Estado:</strong> ${escapeHtml(order.status)}</div>
      <div><strong>Cliente:</strong> ${escapeHtml(order.customer_nombre || '')} (${escapeHtml(order.customer_email || '')})</div>
      <div><strong>Total:</strong> ${fmtMoney(order.total_centavos)}</div>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Producto ID</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Unitario</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>${itemsHtml || '<tr><td colspan="5">Sin items.</td></tr>'}</tbody>
      </table>
    </div>
  `;
}

function renderOrdersList() {
  const container = document.getElementById('ordersList');
  const info = document.getElementById('ordersPageInfo');

  const rows = state.ordersView.filtered;
  const pageSize = Math.max(1, Number(state.ordersView.pageSize || 10));
  const pages = Math.max(1, Math.ceil(rows.length / pageSize));

  if (state.ordersView.page > pages) {
    state.ordersView.page = pages;
  }
  if (state.ordersView.page < 1) {
    state.ordersView.page = 1;
  }

  const start = (state.ordersView.page - 1) * pageSize;
  const slice = rows.slice(start, start + pageSize);

  if (!rows.length) {
    container.innerHTML = '<p>No hay pedidos con los filtros actuales.</p>';
    info.textContent = 'Pagina 1 de 1';
    document.getElementById('ordersPrevPage').disabled = true;
    document.getElementById('ordersNextPage').disabled = true;
    return;
  }

  const body = slice
    .map((order) => {
      return `
        <tr>
          <td>${escapeHtml(order.id)}</td>
          <td>${escapeHtml(order.customer_nombre || '')}</td>
          <td>${escapeHtml(order.customer_email || '')}</td>
          <td>${escapeHtml(order.status)}</td>
          <td>${fmtMoney(order.total_centavos)}</td>
          <td>${escapeHtml(order.created_at || '')}</td>
          <td class="actions-cell">
            <button data-order-action="view" data-order-id="${order.id}">Ver</button>
            <button data-order-action="set-paid" data-order-id="${order.id}" class="secondary">Marcar paid</button>
            <button data-order-action="delete" data-order-id="${order.id}" class="danger">Eliminar</button>
          </td>
        </tr>
      `;
    })
    .join('');

  container.innerHTML = `
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Email</th>
          <th>Status</th>
          <th>Total</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>${body}</tbody>
    </table>
  `;

  info.textContent = `Pagina ${state.ordersView.page} de ${pages} | ${rows.length} pedidos`;
  document.getElementById('ordersPrevPage').disabled = state.ordersView.page <= 1;
  document.getElementById('ordersNextPage').disabled = state.ordersView.page >= pages;
}

async function loadCatalog() {
  const search = document.getElementById('searchInput').value.trim();
  const categoryId = document.getElementById('categoryFilter').value;

  const params = new URLSearchParams();
  params.set('only_active', 'true');
  if (search) params.set('search', search);
  if (categoryId) params.set('category_id', categoryId);

  const [categories, products] = await Promise.all([
    api('/categories?only_active=true'),
    api(`/products?${params.toString()}`),
  ]);

  state.categories = categories;
  state.products = products;
  state.storeView.page = 1;

  renderCategories();
  applyStoreSortAndPagination();
}

async function loadOrders() {
  state.orders = await api('/orders');
  state.ordersView.filtered = [...state.orders].sort((left, right) => compareValues(left?.id, right?.id, state.ordersView.sortDir));
  state.ordersView.page = 1;
  renderOrdersList();
}

async function viewOrder(orderId) {
  const order = await api(`/orders/${orderId}`);
  renderOrderDetail(order);
}

async function updateOrderStatus(orderId, status) {
  await api(`/orders/${orderId}`, {
    method: 'PUT',
    body: JSON.stringify({ status }),
  });
}

async function deleteOrder(orderId) {
  await api(`/orders/${orderId}`, {
    method: 'DELETE',
  });
}

function defaultOrderPayload() {
  return JSON.stringify(
    {
      customer: {
        nombre: 'Cliente API Front',
        email: 'cliente.api.front@example.com',
        telefono: '5551234567',
        direccion: 'Direccion demo desde frontend',
      },
      items: [{ product_id: 1, quantity: 1 }],
    },
    null,
    2
  );
}

async function submitCheckout(event) {
  event.preventDefault();
  const formEl = event.currentTarget;

  const result = document.getElementById('orderResult');
  result.style.display = 'none';
  result.classList.remove('error');

  if (state.cart.length === 0) {
    result.textContent = 'Tu carrito esta vacio.';
    result.classList.add('error');
    result.style.display = 'block';
    return;
  }

  const form = new FormData(event.currentTarget);
  const payload = {
    customer: {
      nombre: String(form.get('nombre') || ''),
      email: String(form.get('email') || ''),
      telefono: String(form.get('telefono') || ''),
      direccion: String(form.get('direccion') || ''),
    },
    items: state.cart.map((item) => ({
      product_id: item.id,
      quantity: item.quantity,
    })),
  };

  try {
    const order = await api('/checkout', {
      method: 'POST',
      body: JSON.stringify(payload),
    });

    result.textContent = `Pedido #${order.id} creado con total ${fmtMoney(order.total_centavos)}.`;
    result.style.display = 'block';

    state.cart = [];
    renderCart();
    await loadCatalog();
    await loadOrders();

    if (formEl && typeof formEl.reset === 'function') {
      formEl.reset();
    }
  } catch (error) {
    result.textContent = error.message;
    result.classList.add('error');
    result.style.display = 'block';
  }
}

function bindEvents() {
  const apiInput = document.getElementById('apiBase');
  apiInput.value = state.apiBase;

  document.getElementById('mainNav').addEventListener('click', (event) => {
    const button = event.target.closest('.nav-btn');
    if (!button) return;

    const view = button.dataset.view;
    switchView(view);

    if (view === 'catalogos') {
      loadCatalogTable().catch((error) => showCatalogCrudResult(error.message, true));
    }

    if (view === 'pedidos') {
      loadOrders().catch((error) => showOperationResult(error.message, true));
    }
  });

  document.getElementById('saveApiBase').addEventListener('click', async () => {
    try {
      setApiBase(apiInput.value);
      await Promise.all([loadCatalog(), loadOrders()]);
      if (state.activeView === 'catalogos') {
        await loadCatalogTable();
      }
      showOperationResult('API base actualizada y datos recargados.');
    } catch (error) {
      showOperationResult(error.message, true);
    }
  });

  document.getElementById('searchInput').addEventListener('input', () => {
    loadCatalog().catch(console.error);
  });

  document.getElementById('categoryFilter').addEventListener('change', () => {
    loadCatalog().catch(console.error);
  });

  document.getElementById('refreshBtn').addEventListener('click', () => {
    loadCatalog().catch(console.error);
  });

  document.getElementById('productSort').addEventListener('change', () => {
    const [field, dir] = String(document.getElementById('productSort').value || 'nombre:asc').split(':');
    state.storeView.sortField = field || 'nombre';
    state.storeView.sortDir = dir || 'asc';
    state.storeView.page = 1;
    applyStoreSortAndPagination();
  });

  document.getElementById('storePageSize').addEventListener('change', () => {
    state.storeView.pageSize = Number(document.getElementById('storePageSize').value || 8);
    state.storeView.page = 1;
    renderProducts();
  });

  document.getElementById('storePrevPage').addEventListener('click', () => {
    state.storeView.page -= 1;
    renderProducts();
  });

  document.getElementById('storeNextPage').addEventListener('click', () => {
    state.storeView.page += 1;
    renderProducts();
  });

  document.getElementById('clearCart').addEventListener('click', () => {
    state.cart = [];
    renderCart();
  });

  document.getElementById('checkoutForm').addEventListener('submit', submitCheckout);

  document.getElementById('tableSelect').addEventListener('change', () => {
    loadCatalogTable().catch((error) => showCatalogCrudResult(error.message, true));
  });

  document.getElementById('loadTableBtn').addEventListener('click', () => {
    loadCatalogTable().catch((error) => showCatalogCrudResult(error.message, true));
  });

  document.getElementById('catalogSearch').addEventListener('input', () => {
    state.catalogView.search = document.getElementById('catalogSearch').value;
    state.catalogView.page = 1;
    applyCatalogFilters();
    renderCatalogTable();
  });

  document.getElementById('catalogSortField').addEventListener('change', () => {
    state.catalogView.sortField = document.getElementById('catalogSortField').value;
    state.catalogView.page = 1;
    applyCatalogFilters();
    renderCatalogTable();
  });

  document.getElementById('catalogSortDir').addEventListener('change', () => {
    state.catalogView.sortDir = document.getElementById('catalogSortDir').value || 'asc';
    state.catalogView.page = 1;
    applyCatalogFilters();
    renderCatalogTable();
  });

  document.getElementById('catalogPageSize').addEventListener('change', () => {
    state.catalogView.pageSize = Number(document.getElementById('catalogPageSize').value || 10);
    state.catalogView.page = 1;
    renderCatalogTable();
  });

  document.getElementById('catalogPrevPage').addEventListener('click', () => {
    state.catalogView.page -= 1;
    renderCatalogTable();
  });

  document.getElementById('catalogNextPage').addEventListener('click', () => {
    state.catalogView.page += 1;
    renderCatalogTable();
  });

  document.getElementById('catalogReadForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = Number(document.getElementById('catalogReadId').value || 0);
    if (!id) {
      showCatalogCrudResult('Debes indicar un ID valido.', true);
      return;
    }

    try {
      await catalogReadById(id);
    } catch (error) {
      showCatalogCrudResult(error.message, true);
    }
  });

  document.getElementById('catalogCreateForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
      const payload = JSON.parse(document.getElementById('catalogCreateJson').value);
      const id = await catalogCreate(payload);
      showCatalogCrudResult(`Registro creado en ${state.catalogView.table}${id > 0 ? ` con ID #${id}` : ''}.`);
      await loadCatalogTable();
      if (id > 0) {
        await catalogReadById(id);
      }
    } catch (error) {
      showCatalogCrudResult(error.message, true);
    }
  });

  document.getElementById('catalogUpdateForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = Number(document.getElementById('catalogUpdateId').value || 0);
    if (!id) {
      showCatalogCrudResult('Debes indicar un ID para actualizar.', true);
      return;
    }

    try {
      const payload = JSON.parse(document.getElementById('catalogUpdateJson').value);
      await catalogUpdate(id, payload);
      showCatalogCrudResult(`Registro #${id} actualizado en ${state.catalogView.table}.`);
      await loadCatalogTable();
      await catalogReadById(id);
    } catch (error) {
      showCatalogCrudResult(error.message, true);
    }
  });

  document.getElementById('catalogDeleteForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = Number(document.getElementById('catalogDeleteId').value || 0);
    if (!id) {
      showCatalogCrudResult('Debes indicar un ID para eliminar.', true);
      return;
    }

    try {
      await catalogDelete(id);
      showCatalogCrudResult(`Registro #${id} eliminado de ${state.catalogView.table}.`);
      setCatalogPreview('Registro eliminado.');
      await loadCatalogTable();
    } catch (error) {
      showCatalogCrudResult(error.message, true);
    }
  });

  document.getElementById('catalogTableContainer').addEventListener('click', async (event) => {
    const button = event.target.closest('button[data-cat-action]');
    if (!button) return;

    const id = Number(button.dataset.catId || 0);
    if (!id) return;

    const action = button.dataset.catAction;
    if (action === 'delete') {
      if (!window.confirm(`Eliminar registro #${id} de ${state.catalogView.table}?`)) {
        return;
      }
    }

    try {
      if (action === 'view') {
        await catalogReadById(id);
        return;
      }

      if (action === 'prefill') {
        const row = await api(`/${state.catalogView.table}/${id}`);
        document.getElementById('catalogUpdateId').value = String(id);
        document.getElementById('catalogUpdateJson').value = JSON.stringify(row, null, 2);
        setCatalogPreview(row);
        showCatalogCrudResult(`Registro #${id} cargado para edicion.`);
        return;
      }

      if (action === 'delete') {
        await catalogDelete(id);
        showCatalogCrudResult(`Registro #${id} eliminado de ${state.catalogView.table}.`);
        setCatalogPreview('Registro eliminado.');
        await loadCatalogTable();
      }
    } catch (error) {
      showCatalogCrudResult(error.message, true);
    }
  });

  document.getElementById('refreshOrdersBtn').addEventListener('click', () => {
    loadOrders().catch((error) => showOperationResult(error.message, true));
  });

  document.getElementById('applyOrderFilters').addEventListener('click', () => {
    applyOrderFilters();
  });

  document.getElementById('orderSortField').addEventListener('change', () => {
    state.ordersView.sortField = document.getElementById('orderSortField').value || 'id';
    applyOrderFilters();
  });

  document.getElementById('orderSortDir').addEventListener('change', () => {
    state.ordersView.sortDir = document.getElementById('orderSortDir').value || 'desc';
    applyOrderFilters();
  });

  document.getElementById('resetOrderFilters').addEventListener('click', () => {
    resetOrderFilters();
  });

  document.getElementById('ordersPrevPage').addEventListener('click', () => {
    state.ordersView.page -= 1;
    renderOrdersList();
  });

  document.getElementById('ordersNextPage').addEventListener('click', () => {
    state.ordersView.page += 1;
    renderOrdersList();
  });

  document.getElementById('ordersList').addEventListener('click', async (event) => {
    const button = event.target.closest('button[data-order-action]');
    if (!button) return;

    const orderId = Number(button.dataset.orderId || 0);
    if (!orderId) return;

    const action = button.dataset.orderAction;

    try {
      if (action === 'view') {
        await viewOrder(orderId);
        return;
      }

      if (action === 'set-paid') {
        await updateOrderStatus(orderId, 'paid');
        showOperationResult(`Pedido #${orderId} actualizado a paid.`);
        await loadOrders();
        applyOrderFilters();
        await viewOrder(orderId);
        return;
      }

      if (action === 'delete') {
        await deleteOrder(orderId);
        showOperationResult(`Pedido #${orderId} eliminado.`);
        renderOrderDetail(null);
        await loadOrders();
        applyOrderFilters();
      }
    } catch (error) {
      showOperationResult(error.message, true);
    }
  });

  document.getElementById('createOrderForm').addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
      const payload = JSON.parse(document.getElementById('createOrderJson').value);
      const order = await api('/orders', {
        method: 'POST',
        body: JSON.stringify(payload),
      });

      showOperationResult(`Pedido #${order.id} creado desde JSON.`);
      await Promise.all([loadOrders(), loadCatalog()]);
      applyOrderFilters();
      await viewOrder(order.id);
    } catch (error) {
      showOperationResult(error.message, true);
    }
  });

  document.getElementById('updateOrderForm').addEventListener('submit', async (event) => {
    event.preventDefault();

    const orderId = Number(document.getElementById('updateOrderId').value || 0);
    const status = document.getElementById('updateOrderStatus').value;

    if (!orderId) {
      showOperationResult('Debes indicar un ID valido.', true);
      return;
    }

    try {
      await updateOrderStatus(orderId, status);
      showOperationResult(`Pedido #${orderId} actualizado a ${status}.`);
      await loadOrders();
      applyOrderFilters();
      await viewOrder(orderId);
    } catch (error) {
      showOperationResult(error.message, true);
    }
  });

  document.getElementById('deleteOrderForm').addEventListener('submit', async (event) => {
    event.preventDefault();

    const orderId = Number(document.getElementById('deleteOrderId').value || 0);
    if (!orderId) {
      showOperationResult('Debes indicar un ID valido.', true);
      return;
    }

    try {
      await deleteOrder(orderId);
      showOperationResult(`Pedido #${orderId} eliminado.`);
      renderOrderDetail(null);
      await loadOrders();
      applyOrderFilters();
    } catch (error) {
      showOperationResult(error.message, true);
    }
  });
}

async function bootstrap() {
  bindEvents();
  renderCart();
  state.storeView.sortField = 'nombre';
  state.storeView.sortDir = 'asc';
  state.storeView.pageSize = Number(document.getElementById('storePageSize').value || 8);
  state.ordersView.sortField = document.getElementById('orderSortField').value || 'id';
  state.ordersView.sortDir = document.getElementById('orderSortDir').value || 'desc';
  setCatalogPreview('Selecciona una fila o consulta por ID para ver detalle.');
  document.getElementById('createOrderJson').value = defaultOrderPayload();
  document.getElementById('catalogCreateJson').value = JSON.stringify(defaultCreatePayloadByTable('categories'), null, 2);
  document.getElementById('catalogUpdateJson').value = JSON.stringify({}, null, 2);

  try {
    await Promise.all([loadCatalog(), loadOrders()]);
    applyOrderFilters();
  } catch (error) {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = `<p>No fue posible cargar el catalogo: ${escapeHtml(error.message)}</p>`;
    showOperationResult(error.message, true);
  }
}

bootstrap();
