<?php
$pageTitle  = 'ManageTracking — Clientes';
$activePage = 'clientes';
include __DIR__ . '/layouts/head.php';
?>
<body class="bg-gray-900 text-white flex h-screen overflow-hidden">

<?php include __DIR__ . '/layouts/sidebar.php'; ?>

<div class="flex-1 flex overflow-hidden">

    <!-- Panel izquierdo: Clientes -->
    <div class="w-80 flex flex-col border-r border-gray-700 flex-shrink-0">
        <div class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-4 flex-shrink-0">
            <h2 class="font-bold">Clientes</h2>
            <button onclick="abrirModalCliente()"
                    class="bg-blue-600 hover:bg-blue-500 text-white text-xs px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-2" id="lista-clientes">
            <p class="text-gray-500 text-sm text-center py-8">Cargando...</p>
        </div>
    </div>

    <!-- Panel derecho: Sedes -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <div class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6 flex-shrink-0">
            <div>
                <h2 class="font-bold">Sedes</h2>
                <p id="cliente-seleccionado" class="text-xs text-gray-400">Selecciona un cliente</p>
            </div>
            <button onclick="abrirModalSede()" id="btn-nueva-sede" disabled
                    class="bg-blue-600 hover:bg-blue-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva sede
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-xs uppercase">
                            <th class="text-left p-4">Nombre</th>
                            <th class="text-left p-4">Ciudad</th>
                            <th class="text-left p-4">Dirección</th>
                            <th class="text-left p-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-sedes">
                        <tr><td colspan="4" class="p-8 text-center text-gray-500">Selecciona un cliente para ver sus sedes</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cliente -->
<div id="modal-cliente" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-2xl border border-gray-700 w-full max-w-sm p-6 shadow-2xl">
        <h2 id="modal-cliente-titulo" class="text-lg font-bold mb-5">Nuevo Cliente</h2>
        <form id="form-cliente" class="space-y-4">
            <input type="hidden" id="fc-id">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Nombre de la empresa *</label>
                <input id="fc-nombre" type="text" placeholder="Ej: Empresa SAC"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">RUC</label>
                <input id="fc-ruc" type="text" placeholder="20XXXXXXXXX"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 font-mono">
            </div>
            <div id="error-cliente" class="hidden text-red-400 text-sm"></div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="cerrarModalCliente()"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg text-sm transition">Cancelar</button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-lg text-sm font-medium transition">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sede -->
<div id="modal-sede" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-2xl border border-gray-700 w-full max-w-sm p-6 shadow-2xl">
        <h2 id="modal-sede-titulo" class="text-lg font-bold mb-5">Nueva Sede</h2>
        <form id="form-sede" class="space-y-4">
            <input type="hidden" id="fs-id">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Nombre de la sede *</label>
                <input id="fs-nombre" type="text" placeholder="Ej: Oficina Principal"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Ciudad</label>
                <input id="fs-ciudad" type="text" placeholder="Ej: Trujillo"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Dirección</label>
                <input id="fs-direccion" type="text" placeholder="Ej: Av. España 123"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div id="error-sede" class="hidden text-red-400 text-sm"></div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="cerrarModalSede()"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg text-sm transition">Cancelar</button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-lg text-sm font-medium transition">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
let clienteActivo = null;

async function cargarClientes() {
    const data  = await fetch('api/obtener_clientes.php').then(r => r.json());
    const lista = document.getElementById('lista-clientes');
    lista.innerHTML = (data.clientes || []).map(c => `
        <div onclick="seleccionarCliente(${c.id}, '${c.nombre.replace(/'/g,"\\'")}', ${c.total_sedes})"
             class="cursor-pointer rounded-xl p-3 border transition ${clienteActivo===c.id ? 'bg-blue-900/40 border-blue-600' : 'bg-gray-800 border-gray-700 hover:border-gray-500'}">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate">${c.nombre}</p>
                    <p class="text-xs text-gray-400 mt-0.5">${c.ruc || 'Sin RUC'} · ${c.total_sedes} sede(s)</p>
                </div>
                <div class="flex gap-1 ml-2 flex-shrink-0">
                    <button onclick="event.stopPropagation(); abrirModalCliente(${JSON.stringify(c).replace(/"/g,'&quot;')})"
                            class="text-xs bg-blue-900 hover:bg-blue-800 text-blue-300 px-2 py-1 rounded transition">Editar</button>
                    <button onclick="event.stopPropagation(); eliminarCliente(${c.id}, '${c.nombre.replace(/'/g,"\\'")}', ${c.total_sedes})"
                            class="text-xs bg-red-900 hover:bg-red-800 text-red-300 px-2 py-1 rounded transition">Eliminar</button>
                </div>
            </div>
        </div>`).join('') || '<p class="text-gray-500 text-sm text-center py-8">Sin clientes registrados</p>';
}

function seleccionarCliente(id, nombre, totalSedes) {
    clienteActivo = id;
    document.getElementById('cliente-seleccionado').textContent = nombre;
    document.getElementById('btn-nueva-sede').disabled = false;
    cargarClientes();
    cargarSedes(id);
}

function abrirModalCliente(c = null) {
    document.getElementById('modal-cliente-titulo').textContent = c ? 'Editar Cliente' : 'Nuevo Cliente';
    document.getElementById('fc-id').value     = c?.id     || '';
    document.getElementById('fc-nombre').value = c?.nombre || '';
    document.getElementById('fc-ruc').value    = c?.ruc    || '';
    document.getElementById('error-cliente').classList.add('hidden');
    document.getElementById('modal-cliente').classList.remove('hidden');
}
function cerrarModalCliente() { document.getElementById('modal-cliente').classList.add('hidden'); }

document.getElementById('form-cliente').addEventListener('submit', async e => {
    e.preventDefault();
    const id    = document.getElementById('fc-id').value;
    const url   = id ? 'api/editar_cliente.php' : 'api/crear_cliente.php';
    const errEl = document.getElementById('error-cliente');
    errEl.classList.add('hidden');
    const data  = await fetch(url, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id ? parseInt(id) : undefined, nombre: document.getElementById('fc-nombre').value, ruc: document.getElementById('fc-ruc').value })
    }).then(r => r.json());
    if (data.error) { errEl.textContent = data.error; errEl.classList.remove('hidden'); return; }
    cerrarModalCliente(); cargarClientes();
});

async function eliminarCliente(id, nombre, totalSedes) {
    const msg = totalSedes > 0 ? `¿Eliminar "${nombre}"? Se eliminarán también sus ${totalSedes} sede(s).` : `¿Eliminar "${nombre}"?`;
    if (!confirm(msg)) return;
    await fetch('api/eliminar_cliente.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
    if (clienteActivo === id) {
        clienteActivo = null;
        document.getElementById('tbody-sedes').innerHTML = '<tr><td colspan="4" class="p-8 text-center text-gray-500">Selecciona un cliente para ver sus sedes</td></tr>';
        document.getElementById('cliente-seleccionado').textContent = 'Selecciona un cliente';
        document.getElementById('btn-nueva-sede').disabled = true;
    }
    cargarClientes();
}

async function cargarSedes(clienteId) {
    const data = await fetch(`api/obtener_sedes.php?cliente_id=${clienteId}`).then(r => r.json());
    document.getElementById('tbody-sedes').innerHTML = (data.sedes || []).map(s => `
        <tr class="border-b border-gray-700 hover:bg-gray-700/30">
            <td class="p-4 font-medium">${s.nombre}</td>
            <td class="p-4 text-gray-400">${s.ciudad || '—'}</td>
            <td class="p-4 text-gray-400 text-xs">${s.direccion || '—'}</td>
            <td class="p-4">
                <div class="flex gap-2">
                    <button onclick='abrirModalSede(${JSON.stringify(s).replace(/'/g,"\\'")})'
                            class="text-xs bg-blue-900 hover:bg-blue-800 text-blue-300 px-2 py-1 rounded transition">Editar</button>
                    <button onclick="eliminarSede(${s.id}, '${s.nombre.replace(/'/g,"\\'")}' )"
                            class="text-xs bg-red-900 hover:bg-red-800 text-red-300 px-2 py-1 rounded transition">Eliminar</button>
                </div>
            </td>
        </tr>`).join('') || '<tr><td colspan="4" class="p-8 text-center text-gray-500">Sin sedes registradas</td></tr>';
}

function abrirModalSede(s = null) {
    document.getElementById('modal-sede-titulo').textContent = s ? 'Editar Sede' : 'Nueva Sede';
    document.getElementById('fs-id').value        = s?.id        || '';
    document.getElementById('fs-nombre').value    = s?.nombre    || '';
    document.getElementById('fs-ciudad').value    = s?.ciudad    || '';
    document.getElementById('fs-direccion').value = s?.direccion || '';
    document.getElementById('error-sede').classList.add('hidden');
    document.getElementById('modal-sede').classList.remove('hidden');
}
function cerrarModalSede() { document.getElementById('modal-sede').classList.add('hidden'); }

document.getElementById('form-sede').addEventListener('submit', async e => {
    e.preventDefault();
    const id    = document.getElementById('fs-id').value;
    const url   = id ? 'api/editar_sede.php' : 'api/crear_sede.php';
    const errEl = document.getElementById('error-sede');
    errEl.classList.add('hidden');
    const data  = await fetch(url, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id ? parseInt(id) : undefined, cliente_id: clienteActivo, nombre: document.getElementById('fs-nombre').value, ciudad: document.getElementById('fs-ciudad').value, direccion: document.getElementById('fs-direccion').value })
    }).then(r => r.json());
    if (data.error) { errEl.textContent = data.error; errEl.classList.remove('hidden'); return; }
    cerrarModalSede(); cargarSedes(clienteActivo);
});

async function eliminarSede(id, nombre) {
    if (!confirm(`¿Eliminar la sede "${nombre}"?`)) return;
    await fetch('api/eliminar_sede.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
    cargarSedes(clienteActivo);
}

cargarClientes();
</script>
</body>
</html>
