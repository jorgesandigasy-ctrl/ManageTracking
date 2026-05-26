<?php
$pageTitle  = 'ManageTracking — Clientes';
$activePage = 'clientes';
$headExtra  = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>';
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
                            <th class="text-left p-4">Geofence</th>
                            <th class="text-left p-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-sedes">
                        <tr><td colspan="5" class="p-8 text-center text-gray-500">Selecciona un cliente para ver sus sedes</td></tr>
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
    <div class="bg-gray-800 rounded-2xl border border-gray-700 w-full max-w-2xl p-6 shadow-2xl">
        <h2 id="modal-sede-titulo" class="text-lg font-bold mb-5">Nueva Sede</h2>
        <form id="form-sede" class="space-y-4">
            <input type="hidden" id="fs-id">
            <input type="hidden" id="fs-lat">
            <input type="hidden" id="fs-lng">

            <div class="grid grid-cols-2 gap-3">
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
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Dirección</label>
                <input id="fs-direccion" type="text" placeholder="Ej: Av. España 123"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>

            <!-- Geofence -->
            <div class="border border-gray-600 rounded-xl p-4 space-y-3">
                <p class="text-xs text-cyan-400 font-semibold uppercase tracking-wider">Geofence — ubicación de la sede</p>

                <div class="flex gap-2">
                    <input id="fs-buscar" type="text" placeholder="Buscar dirección (ej: Av. España, Trujillo)"
                           class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                    <button type="button" onclick="buscarDireccion()"
                            class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-2 rounded-lg transition flex-shrink-0">
                        Buscar
                    </button>
                </div>
                <p id="fs-buscar-error" class="hidden text-xs text-red-400">No se encontró la dirección. Intenta ser más específico.</p>

                <div id="mapa-sede" style="height:240px;border-radius:0.5rem;overflow:hidden"></div>

                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span>Radio del geofence:</span>
                    <input id="fs-radio" type="number" value="120" min="50" max="2000" step="10"
                           class="w-24 bg-gray-700 border border-gray-600 rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:border-blue-500">
                    <span>metros</span>
                    <span id="fs-coords" class="ml-auto text-gray-500">Sin ubicación</span>
                </div>
                <p class="text-xs text-gray-500">Haz clic en el mapa o usa el buscador para definir la ubicación. Arrastra el marcador para ajustar.</p>
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
let mapaSede = null, marcadorSede = null, circuloSede = null;

// ── Inicializar mapa del modal de sede ──────────────────────────────────────
function iniciarMapaSede() {
    if (mapaSede) return;
    mapaSede = L.map('mapa-sede').setView([-8.1116, -79.0289], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
         maxZoom: 19
    }).addTo(mapaSede);

    mapaSede.on('click', e => colocarMarcador(e.latlng.lat, e.latlng.lng));
}

function colocarMarcador(lat, lng) {
    const radio = parseInt(document.getElementById('fs-radio').value) || 120;

    if (marcadorSede) {
        marcadorSede.setLatLng([lat, lng]);
    } else {
        marcadorSede = L.marker([lat, lng], { draggable: true }).addTo(mapaSede);
        marcadorSede.on('dragend', e => {
            const p = e.target.getLatLng();
            actualizarCoords(p.lat, p.lng);
        });
    }

    if (circuloSede) {
        circuloSede.setLatLng([lat, lng]).setRadius(radio);
    } else {
        circuloSede = L.circle([lat, lng], {
            radius: radio, color: '#22d3ee', fillColor: '#22d3ee', fillOpacity: 0.1, weight: 2
        }).addTo(mapaSede);
    }

    mapaSede.setView([lat, lng], 16);
    actualizarCoords(lat, lng);
}

function actualizarCoords(lat, lng) {
    document.getElementById('fs-lat').value = lat;
    document.getElementById('fs-lng').value = lng;
    document.getElementById('fs-coords').textContent =
        `Lat: ${lat.toFixed(6)}  Lon: ${lng.toFixed(6)}`;

    // Actualizar radio del círculo si ya existe
    const radio = parseInt(document.getElementById('fs-radio').value) || 120;
    if (circuloSede) circuloSede.setRadius(radio);
}

document.getElementById('fs-radio').addEventListener('input', () => {
    const radio = parseInt(document.getElementById('fs-radio').value) || 120;
    if (circuloSede) circuloSede.setRadius(radio);
});

async function buscarDireccion() {
    const q    = document.getElementById('fs-buscar').value.trim();
    const errEl = document.getElementById('fs-buscar-error');
    errEl.classList.add('hidden');
    if (!q) return;

    try {
        const res  = await fetch(
            `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1`,
            { headers: { 'Accept-Language': 'es', 'User-Agent': 'ManageTracking/1.0' } }
        );
        const data = await res.json();
        if (!data.length) { errEl.classList.remove('hidden'); return; }
        colocarMarcador(parseFloat(data[0].lat), parseFloat(data[0].lon));
    } catch {
        errEl.classList.remove('hidden');
    }
}

document.getElementById('fs-buscar').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); buscarDireccion(); }
});

// ── Clientes ────────────────────────────────────────────────────────────────
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

function seleccionarCliente(id, nombre) {
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
        document.getElementById('tbody-sedes').innerHTML = '<tr><td colspan="5" class="p-8 text-center text-gray-500">Selecciona un cliente para ver sus sedes</td></tr>';
        document.getElementById('cliente-seleccionado').textContent = 'Selecciona un cliente';
        document.getElementById('btn-nueva-sede').disabled = true;
    }
    cargarClientes();
}

// ── Sedes ───────────────────────────────────────────────────────────────────
async function cargarSedes(clienteId) {
    const data = await fetch(`api/obtener_sedes.php?cliente_id=${clienteId}`).then(r => r.json());
    document.getElementById('tbody-sedes').innerHTML = (data.sedes || []).map(s => {
        const tieneGeo = s.latitud && s.longitud;
        const geoLabel = tieneGeo
            ? `<span class="text-cyan-400 text-xs">${s.radio_metros}m</span>`
            : `<span class="text-gray-600 text-xs">—</span>`;
        return `
        <tr class="border-b border-gray-700 hover:bg-gray-700/30">
            <td class="p-4 font-medium">${s.nombre}</td>
            <td class="p-4 text-gray-400">${s.ciudad || '—'}</td>
            <td class="p-4 text-gray-400 text-xs">${s.direccion || '—'}</td>
            <td class="p-4">${geoLabel}</td>
            <td class="p-4">
                <div class="flex gap-2">
                    <button onclick='abrirModalSede(${JSON.stringify(s).replace(/'/g,"\\'")})'
                            class="text-xs bg-blue-900 hover:bg-blue-800 text-blue-300 px-2 py-1 rounded transition">Editar</button>
                    <button onclick="eliminarSede(${s.id}, '${s.nombre.replace(/'/g,"\\'")}' )"
                            class="text-xs bg-red-900 hover:bg-red-800 text-red-300 px-2 py-1 rounded transition">Eliminar</button>
                </div>
            </td>
        </tr>`;
    }).join('') || '<tr><td colspan="5" class="p-8 text-center text-gray-500">Sin sedes registradas</td></tr>';
}

function abrirModalSede(s = null) {
    document.getElementById('modal-sede-titulo').textContent = s ? 'Editar Sede' : 'Nueva Sede';
    document.getElementById('fs-id').value        = s?.id        || '';
    document.getElementById('fs-nombre').value    = s?.nombre    || '';
    document.getElementById('fs-ciudad').value    = s?.ciudad    || '';
    document.getElementById('fs-direccion').value = s?.direccion || '';
    document.getElementById('fs-radio').value     = s?.radio_metros || 120;
    document.getElementById('fs-lat').value       = s?.latitud   || '';
    document.getElementById('fs-lng').value       = s?.longitud  || '';
    document.getElementById('fs-buscar').value    = '';
    document.getElementById('fs-buscar-error').classList.add('hidden');
    document.getElementById('error-sede').classList.add('hidden');
    document.getElementById('fs-coords').textContent = s?.latitud ? `Lat: ${parseFloat(s.latitud).toFixed(6)}  Lon: ${parseFloat(s.longitud).toFixed(6)}` : 'Sin ubicación';
    document.getElementById('modal-sede').classList.remove('hidden');

    // Inicializar mapa (requiere que el modal sea visible primero)
    setTimeout(() => {
        iniciarMapaSede();
        mapaSede.invalidateSize();

        // Limpiar marcador y círculo previos
        if (marcadorSede) { mapaSede.removeLayer(marcadorSede); marcadorSede = null; }
        if (circuloSede)  { mapaSede.removeLayer(circuloSede);  circuloSede  = null; }

        if (s?.latitud && s?.longitud) {
            colocarMarcador(parseFloat(s.latitud), parseFloat(s.longitud));
        } else {
            mapaSede.setView([-8.1116, -79.0289], 13);
        }
    }, 50);
}

function cerrarModalSede() {
    document.getElementById('modal-sede').classList.add('hidden');
}

document.getElementById('form-sede').addEventListener('submit', async e => {
    e.preventDefault();
    const id    = document.getElementById('fs-id').value;
    const url   = id ? 'api/editar_sede.php' : 'api/crear_sede.php';
    const errEl = document.getElementById('error-sede');
    errEl.classList.add('hidden');
    const data  = await fetch(url, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id:           id ? parseInt(id) : undefined,
            cliente_id:   clienteActivo,
            nombre:       document.getElementById('fs-nombre').value,
            ciudad:       document.getElementById('fs-ciudad').value,
            direccion:    document.getElementById('fs-direccion').value,
            latitud:      document.getElementById('fs-lat').value  || null,
            longitud:     document.getElementById('fs-lng').value  || null,
            radio_metros: parseInt(document.getElementById('fs-radio').value) || 120,
        })
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
