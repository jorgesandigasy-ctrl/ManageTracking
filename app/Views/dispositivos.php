<?php
$pageTitle  = 'ManageTracking — Equipos';
$activePage = 'dispositivos';
include __DIR__ . '/layouts/head.php';
?>
<body class="bg-gray-900 text-white flex h-screen overflow-hidden">

<?php include __DIR__ . '/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <div class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6 flex-shrink-0">
        <div class="flex items-center gap-3">
            <h1 class="text-lg font-bold">Gestión de Equipos</h1>
            <span id="total-equipos" class="text-gray-400 text-sm"></span>
        </div>
        <button onclick="abrirModalNuevo()"
                class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar Equipo
        </button>
    </div>

    <div class="flex-1 overflow-y-auto p-6">
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-700 text-gray-400 text-xs uppercase">
                        <th class="text-left p-4">MAC Address</th>
                        <th class="text-left p-4">Hostname</th>
                        <th class="text-left p-4">Usuario</th>
                        <th class="text-left p-4">Tipo</th>
                        <th class="text-left p-4">Sede</th>
                        <th class="text-left p-4">Procesador / RAM</th>
                        <th class="text-left p-4">Estado</th>
                        <th class="text-left p-4">Última señal</th>
                        <th class="text-left p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-equipos">
                    <tr><td colspan="9" class="p-8 text-center text-gray-500">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Agregar / Editar -->
<div id="modal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-2xl border border-gray-700 w-full max-w-lg p-6 shadow-2xl">
        <h2 id="modal-titulo" class="text-lg font-bold mb-5">Agregar Equipo</h2>
        <form id="form-equipo" class="space-y-4">
            <input type="hidden" id="f-accion" value="crear">
            <div>
                <label class="block text-xs text-gray-400 mb-1">MAC Address *</label>
                <input id="f-mac" type="text" placeholder="AA:BB:CC:DD:EE:FF"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:border-blue-500" required>
                <p id="f-mac-hint" class="text-xs text-gray-500 mt-1">Identificador único del equipo.</p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Hostname</label>
                <input id="f-hostname" type="text" placeholder="PC-SALA"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Nombre usuario</label>
                    <input id="f-nombre" type="text" placeholder="Juan"
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Apellido usuario</label>
                    <input id="f-apellido" type="text" placeholder="Pérez"
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Teléfono</label>
                <input id="f-telefono" type="text" placeholder="999888777"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Cliente</label>
                    <select id="f-cliente" onchange="filtrarSedes(this.value)"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="">Sin cliente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Sede</label>
                    <select id="f-sede"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="">Sin sede</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tipo</label>
                    <select id="f-tipo" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="laptop">Laptop</option>
                        <option value="pc">PC</option>
                        <option value="gps">GPS / ESP32</option>
                        <option value="esp8266">ESP8266</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Estado</label>
                    <select id="f-estado" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="perdido">Perdido</option>
                    </select>
                </div>
            </div>
            <div id="api-key-container" class="hidden bg-blue-900/30 border border-blue-700 rounded-lg p-3">
                <p class="text-xs text-blue-300 mb-1">API Key generada — cópiala al firmware:</p>
                <code id="api-key-valor" class="text-xs text-white font-mono break-all"></code>
            </div>
            <div id="form-error" class="hidden text-red-400 text-sm"></div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="cerrarModal()"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg text-sm transition">Cancelar</button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-lg text-sm transition font-medium">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
const badgeEstado = e => ({ activo: 'bg-green-900 text-green-300', perdido: 'bg-red-900 text-red-300', inactivo: 'bg-gray-700 text-gray-400' }[e] || 'bg-gray-700 text-gray-400');

function tiempoRelativo(f) {
    if (!f) return '—';
    const d = Math.floor((Date.now() - new Date(f)) / 1000);
    if (d < 60)    return `hace ${d}s`;
    if (d < 3600)  return `hace ${Math.floor(d/60)}min`;
    if (d < 86400) return `hace ${Math.floor(d/3600)}h`;
    return new Date(f).toLocaleDateString('es-PE');
}

let todosLosClientes = [];
let todasLasSedes    = [];

async function cargarClientesYSedes() {
    const [rc, rs] = await Promise.all([
        fetch('api/obtener_clientes.php').then(r => r.json()),
        fetch('api/obtener_sedes.php').then(r => r.json())
    ]);
    todosLosClientes = rc.clientes || [];
    todasLasSedes    = rs.sedes    || [];
    const sel = document.getElementById('f-cliente');
    todosLosClientes.forEach(c => {
        const o = document.createElement('option');
        o.value = c.id; o.textContent = c.nombre;
        sel.appendChild(o);
    });
}

function filtrarSedes(clienteId) {
    const sel = document.getElementById('f-sede');
    const sedeActual = sel.value;
    sel.innerHTML = '<option value="">Sin sede</option>';
    const filtradas = clienteId
        ? todasLasSedes.filter(s => String(s.cliente_id) === String(clienteId))
        : todasLasSedes;
    filtradas.forEach(s => {
        const o = document.createElement('option');
        o.value = s.id; o.textContent = s.nombre;
        sel.appendChild(o);
    });
    if ([...sel.options].some(o => o.value === sedeActual)) sel.value = sedeActual;
}

async function cargarEquipos() {
    const data    = await fetch('api/obtener_dispositivos.php').then(r => r.json());
    const equipos = data.dispositivos || [];
    document.getElementById('total-equipos').textContent = `${equipos.length} equipo(s)`;

    document.getElementById('tbody-equipos').innerHTML = equipos.map(d => `
        <tr class="border-b border-gray-700 hover:bg-gray-700/30">
            <td class="p-4 font-mono text-xs text-gray-300">${d.mac_address}</td>
            <td class="p-4 text-gray-200">${d.hostname || '—'}</td>
            <td class="p-4">
                <div class="text-white text-sm">${[d.nombre_usuario, d.apellido_usuario].filter(Boolean).join(' ') || '—'}</div>
                ${d.telefono_usuario ? `<div class="text-xs text-gray-400">${d.telefono_usuario}</div>` : ''}
            </td>
            <td class="p-4 capitalize text-gray-300 text-xs">${d.tipo}</td>
            <td class="p-4 text-xs">
                ${d.sede_detectada_nombre
                    ? `<span class="text-blue-300">${d.sede_detectada_nombre}</span><span class="ml-1 text-gray-600 text-xs">GPS</span>`
                    : (d.sede_nombre ? `<span class="text-gray-400">${d.sede_nombre}</span>` : '<span class="text-gray-600">—</span>')}
            </td>
            <td class="p-4">
                <div class="text-xs text-gray-300 truncate max-w-[180px]" title="${d.procesador||''}">${d.procesador ? d.procesador.substring(0,30)+'…' : '—'}</div>
                ${d.ram_gb ? `<div class="text-xs text-gray-500">${d.ram_gb} GB RAM · ${d.almacenamiento_gb||'?'} GB</div>` : ''}
            </td>
            <td class="p-4"><span class="px-2 py-1 rounded-full text-xs font-medium capitalize ${badgeEstado(d.estado)}">${d.estado}</span></td>
            <td class="p-4 text-gray-400 text-xs">${tiempoRelativo(d.ultima_vez)}</td>
            <td class="p-4">
                <div class="flex gap-2">
                    <a href="detalle.php?mac=${encodeURIComponent(d.mac_address)}"
                       class="text-xs bg-gray-700 hover:bg-gray-600 px-2 py-1 rounded transition">Ver</a>
                    <button onclick='abrirModalEditar(${JSON.stringify(d).replace(/'/g,"\\'")})'
                            class="text-xs bg-blue-900 hover:bg-blue-800 text-blue-300 px-2 py-1 rounded transition">Editar</button>
                    <button onclick="eliminarEquipo('${d.mac_address}', '${(d.hostname||d.mac_address).replace(/'/g,"\\'")}')"
                            class="text-xs bg-red-900 hover:bg-red-800 text-red-300 px-2 py-1 rounded transition">Eliminar</button>
                </div>
            </td>
        </tr>`).join('') || '<tr><td colspan="9" class="p-8 text-center text-gray-500">No hay equipos registrados</td></tr>';
}

function abrirModalNuevo() {
    document.getElementById('modal-titulo').textContent = 'Agregar Equipo';
    document.getElementById('f-accion').value           = 'crear';
    document.getElementById('f-mac').value              = '';
    document.getElementById('f-mac').readOnly           = false;
    document.getElementById('f-mac').classList.remove('opacity-50');
    document.getElementById('f-mac-hint').textContent   = 'Identificador único del equipo.';
    document.getElementById('f-hostname').value         = '';
    document.getElementById('f-nombre').value           = '';
    document.getElementById('f-apellido').value         = '';
    document.getElementById('f-telefono').value         = '';
    document.getElementById('f-cliente').value          = '';
    filtrarSedes('');
    document.getElementById('f-tipo').value             = 'laptop';
    document.getElementById('f-estado').value           = 'activo';
    document.getElementById('api-key-container').classList.add('hidden');
    document.getElementById('form-error').classList.add('hidden');
    document.getElementById('modal').classList.remove('hidden');
}

function abrirModalEditar(d) {
    document.getElementById('modal-titulo').textContent = 'Editar Equipo';
    document.getElementById('f-accion').value           = 'editar';
    document.getElementById('f-mac').value              = d.mac_address;
    document.getElementById('f-mac').readOnly           = true;
    document.getElementById('f-mac').classList.add('opacity-50');
    document.getElementById('f-mac-hint').textContent   = 'La MAC no puede modificarse.';
    document.getElementById('f-hostname').value         = d.hostname || '';
    document.getElementById('f-nombre').value           = d.nombre_usuario || '';
    document.getElementById('f-apellido').value         = d.apellido_usuario || '';
    document.getElementById('f-telefono').value         = d.telefono_usuario || '';
    const sede = todasLasSedes.find(s => String(s.id) === String(d.sede_id));
    const clienteId = sede?.cliente_id || '';
    document.getElementById('f-cliente').value = clienteId;
    filtrarSedes(clienteId);
    document.getElementById('f-sede').value    = d.sede_id || '';
    document.getElementById('f-tipo').value    = d.tipo;
    document.getElementById('f-estado').value  = d.estado;
    document.getElementById('api-key-container').classList.add('hidden');
    document.getElementById('form-error').classList.add('hidden');
    document.getElementById('modal').classList.remove('hidden');
}

function cerrarModal() { document.getElementById('modal').classList.add('hidden'); }

document.getElementById('form-equipo').addEventListener('submit', async e => {
    e.preventDefault();
    const accion = document.getElementById('f-accion').value;
    const errEl  = document.getElementById('form-error');
    errEl.classList.add('hidden');
    const payload = {
        mac_address:      document.getElementById('f-mac').value.toUpperCase(),
        hostname:         document.getElementById('f-hostname').value,
        nombre_usuario:   document.getElementById('f-nombre').value,
        apellido_usuario: document.getElementById('f-apellido').value,
        telefono_usuario: document.getElementById('f-telefono').value,
        sede_id:          document.getElementById('f-sede').value || null,
        tipo:             document.getElementById('f-tipo').value,
        estado:           document.getElementById('f-estado').value,
    };
    const url  = accion === 'crear' ? 'api/registrar_dispositivo.php' : 'api/editar_dispositivo.php';
    const data = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }).then(r => r.json());
    if (data.error) { errEl.textContent = data.error; errEl.classList.remove('hidden'); return; }
    if (accion === 'crear' && data.api_key) {
        document.getElementById('api-key-valor').textContent = data.api_key;
        document.getElementById('api-key-container').classList.remove('hidden');
        setTimeout(() => { cerrarModal(); cargarEquipos(); }, 8000);
    } else {
        cerrarModal(); cargarEquipos();
    }
});

async function eliminarEquipo(mac, nombre) {
    if (!confirm(`¿Eliminar el equipo "${nombre}"? Se borrarán todos sus registros GPS.`)) return;
    const data = await fetch('api/eliminar_dispositivo.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mac_address: mac })
    }).then(r => r.json());
    if (data.ok) cargarEquipos();
    else alert('Error: ' + data.error);
}

cargarClientesYSedes();
cargarEquipos();
</script>
</body>
</html>
