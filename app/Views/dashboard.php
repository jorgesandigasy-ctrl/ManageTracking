<?php
$pageTitle  = 'ManageTracking — Dashboard';
$activePage = 'dashboard';
$headExtra  = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
include __DIR__ . '/layouts/head.php';
?>
<body class="bg-gray-900 text-white flex h-screen overflow-hidden">

<?php include __DIR__ . '/layouts/sidebar.php'; ?>

<main class="flex-1 overflow-y-auto">
    <div class="sticky top-0 z-10 bg-gray-800 border-b border-gray-700 h-16 flex items-center justify-between px-6">
        <div>
            <h1 class="text-lg font-bold">Dashboard</h1>
            <p class="text-gray-400 text-xs">Vista general del sistema</p>
        </div>
    </div>

    <div class="p-6 space-y-6">

        <!-- Métricas -->
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Total</p>
                <p id="m-total" class="text-3xl font-bold">—</p>
                <p class="text-xs text-gray-500 mt-1">equipos registrados</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-l-4 border-l-green-500 p-5">
                <p class="text-xs text-green-400 uppercase tracking-wider mb-2">Activos</p>
                <p id="m-activos" class="text-3xl font-bold text-green-400">—</p>
                <p class="text-xs text-gray-500 mt-1">en funcionamiento</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-l-4 border-l-red-500 p-5">
                <p class="text-xs text-red-400 uppercase tracking-wider mb-2">Perdidos</p>
                <p id="m-perdidos" class="text-3xl font-bold text-red-400">—</p>
                <p class="text-xs text-gray-500 mt-1">reportados</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-l-4 border-l-gray-500 p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Inactivos</p>
                <p id="m-inactivos" class="text-3xl font-bold">—</p>
                <p class="text-xs text-gray-500 mt-1">sin señal reciente</p>
            </div>
        </div>

        <!-- Indicadores de tesis -->
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-t-4 border-t-blue-500 p-5">
                <p class="text-xs text-blue-400 uppercase tracking-wider mb-1">Tiempo de respuesta</p>
                <p id="ind-tiempo" class="text-3xl font-bold text-blue-400">—</p>
                <p class="text-xs text-gray-500 mt-1">ms promedio (API)</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-t-4 border-t-cyan-500 p-5">
                <p class="text-xs text-cyan-400 uppercase tracking-wider mb-1">Trazabilidad</p>
                <p id="ind-trazabilidad" class="text-3xl font-bold text-cyan-400">—</p>
                <p id="ind-trazabilidad-det" class="text-xs text-gray-500 mt-1">equipos con GPS</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-t-4 border-t-orange-500 p-5">
                <p class="text-xs text-orange-400 uppercase tracking-wider mb-1">Incidencias</p>
                <p id="ind-incidencias" class="text-3xl font-bold text-orange-400">—</p>
                <p id="ind-incidencias-det" class="text-xs text-gray-500 mt-1">dispositivos afectados</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-t-4 border-t-purple-500 p-5">
                <p class="text-xs text-purple-400 uppercase tracking-wider mb-1">Satisfacción</p>
                <p id="ind-satisfaccion" class="text-3xl font-bold text-purple-400">—</p>
                <p id="ind-satisfaccion-det" class="text-xs text-gray-500 mt-1">promedio encuesta (1–5)</p>
            </div>
        </div>

        <!-- Gráfico + Actividad -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 flex flex-col">
                <h3 class="text-sm font-medium text-gray-300 mb-4">Distribución de estados</h3>
                <div class="flex-1 flex flex-col items-center justify-center">
                    <div class="relative" style="width:180px;height:180px">
                        <canvas id="chart-estados"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span id="chart-total" class="text-3xl font-bold">0</span>
                            <span class="text-xs text-gray-400">equipos</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-center gap-x-3 gap-y-1 mt-5 text-xs text-gray-400">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>Activos</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>Perdidos</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-500 flex-shrink-0"></span>Inactivos</span>
                    </div>
                </div>
            </div>
            <div class="col-span-2 bg-gray-800 rounded-xl border border-gray-700 p-5">
                <h3 class="text-sm font-medium text-gray-300 mb-4">Actividad reciente</h3>
                <div id="actividad-reciente" class="space-y-1">
                    <p class="text-gray-500 text-sm">Cargando...</p>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
                <h3 class="font-medium">Equipos registrados</h3>
                <a href="dispositivos.php" class="text-xs text-blue-400 hover:text-blue-300 transition">Gestionar →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-xs uppercase">
                            <th class="text-left px-5 py-3">Equipo</th>
                            <th class="text-left px-5 py-3">Tipo</th>
                            <th class="text-left px-5 py-3">Sede</th>
                            <th class="text-left px-5 py-3">Estado</th>
                            <th class="text-left px-5 py-3">Última señal</th>
                            <th class="text-left px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-equipos">
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mapa -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
                <h3 class="font-medium">Mapa en vivo</h3>
                <span class="text-xs text-gray-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    Actualizando cada 15s
                </span>
            </div>
            <div id="mapa" style="height: 480px"></div>
        </div>

    </div>
</main>

<script>
const mapa = L.map('mapa').setView([-8.1116, -79.0289], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(mapa);

const marcadores    = {};
const colorEstado   = { activo: '#22c55e', perdido: '#ef4444', inactivo: '#6b7280' };
const claseEstado   = e => ({ activo: 'bg-green-900 text-green-300', perdido: 'bg-red-900 text-red-300', inactivo: 'bg-gray-700 text-gray-400' }[e] || 'bg-gray-700 text-gray-400');

function crearIcono(estado) {
    const c = colorEstado[estado] || '#6b7280';
    return L.divIcon({ className: '', html: `<div style="background:${c};width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 0 6px ${c}88"></div>`, iconSize: [14, 14], iconAnchor: [7, 7] });
}

let grafica = null;
function actualizarGrafica(activos, perdidos, inactivos) {
    document.getElementById('chart-total').textContent = activos + perdidos + inactivos;
    const datos = [activos, perdidos, inactivos];
    if (grafica) { grafica.data.datasets[0].data = datos; grafica.update(); return; }
    grafica = new Chart(document.getElementById('chart-estados'), {
        type: 'doughnut',
        data: { labels: ['Activos', 'Perdidos', 'Inactivos'], datasets: [{ data: datos, backgroundColor: ['#22c55e', '#ef4444', '#6b7280'], borderWidth: 0, hoverOffset: 4 }] },
        options: { responsive: false, cutout: '72%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } } }
    });
}

function tiempoRelativo(f) {
    if (!f) return '—';
    const d = Math.floor((Date.now() - new Date(f)) / 1000);
    if (d < 60)    return `hace ${d}s`;
    if (d < 3600)  return `hace ${Math.floor(d/60)}min`;
    if (d < 86400) return `hace ${Math.floor(d/3600)}h`;
    return `hace ${Math.floor(d/86400)}d`;
}

async function cargarDispositivos() {
    try {
        const data = await fetch('api/obtener_dispositivos.php').then(r => r.json());
        const equipos = data.dispositivos || [];

        let activos = 0, perdidos = 0, inactivos = 0;
        equipos.forEach(d => { if (d.estado==='activo') activos++; else if (d.estado==='perdido') perdidos++; else inactivos++; });

        document.getElementById('m-total').textContent    = equipos.length;
        document.getElementById('m-activos').textContent  = activos;
        document.getElementById('m-perdidos').textContent = perdidos;
        document.getElementById('m-inactivos').textContent = inactivos;
        actualizarGrafica(activos, perdidos, inactivos);

        // Actividad reciente
        const recientes = [...equipos].filter(d => d.ultima_ubicacion)
            .sort((a, b) => new Date(b.ultima_ubicacion) - new Date(a.ultima_ubicacion)).slice(0, 5);
        document.getElementById('actividad-reciente').innerHTML = recientes.length
            ? recientes.map(d => `
                <div class="flex items-center justify-between py-2.5 border-b border-gray-700/60 last:border-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:${colorEstado[d.estado]}"></span>
                        <span class="text-sm font-medium truncate">${d.hostname || d.mac_address}</span>
                        <span class="text-xs text-gray-500 flex-shrink-0 capitalize">${d.tipo}</span>
                    </div>
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-3">${tiempoRelativo(d.ultima_ubicacion)}</span>
                </div>`).join('')
            : '<p class="text-gray-500 text-sm py-4">Sin actividad GPS registrada</p>';

        // Tabla
        document.getElementById('tbody-equipos').innerHTML = equipos.length
            ? equipos.map(d => `
                <tr class="border-b border-gray-700 hover:bg-gray-700/30 last:border-0 transition">
                    <td class="px-5 py-3">
                        <p class="font-medium">${d.hostname || '—'}</p>
                        <p class="text-xs text-gray-500 font-mono">${d.mac_address}</p>
                    </td>
                    <td class="px-5 py-3 capitalize text-gray-300 text-sm">${d.tipo}</td>
                    <td class="px-5 py-3 text-gray-400 text-sm">${d.sede_nombre || '—'}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium capitalize ${claseEstado(d.estado)}">${d.estado}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-400 text-xs">${tiempoRelativo(d.ultima_vez)}</td>
                    <td class="px-5 py-3">
                        <a href="detalle.php?mac=${encodeURIComponent(d.mac_address)}"
                           class="text-xs bg-gray-700 hover:bg-gray-600 px-2.5 py-1.5 rounded-lg transition">Ver detalle</a>
                    </td>
                </tr>`).join('')
            : '<tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No hay equipos registrados</td></tr>';

        // Marcadores
        equipos.forEach(d => {
            if (!d.latitud) return;
            const lat = parseFloat(d.latitud), lng = parseFloat(d.longitud);
            if (marcadores[d.mac_address]) {
                marcadores[d.mac_address].setLatLng([lat, lng]).setIcon(crearIcono(d.estado));
            } else {
                marcadores[d.mac_address] = L.marker([lat, lng], { icon: crearIcono(d.estado) })
                    .bindTooltip(d.hostname || d.mac_address).addTo(mapa);
            }
        });
    } catch (e) { console.error('Error:', e); }
}

async function cargarIndicadores() {
    try {
        const d = await fetch('api/indicadores.php').then(r => r.json());

        const ms = d.tiempo_promedio_ms;
        document.getElementById('ind-tiempo').textContent = ms !== null ? Math.round(ms) : '—';

        document.getElementById('ind-trazabilidad').textContent = d.trazabilidad.porcentaje + '%';
        document.getElementById('ind-trazabilidad-det').textContent =
            `${d.trazabilidad.con_registros} / ${d.trazabilidad.total} equipos con GPS`;

        document.getElementById('ind-incidencias').textContent = d.incidencias.porcentaje + '%';
        document.getElementById('ind-incidencias-det').textContent =
            `${d.incidencias.total} afectados (${d.incidencias.perdidos} perdidos, ${d.incidencias.sin_senal} sin señal)`;

        const sat = d.satisfaccion;
        if (sat.total > 0) {
            document.getElementById('ind-satisfaccion').textContent = sat.promedio.toFixed(1);
            document.getElementById('ind-satisfaccion-det').textContent =
                `${sat.porcentaje}% satisfacción · ${sat.total} respuestas`;
        } else {
            document.getElementById('ind-satisfaccion').textContent = '—';
            document.getElementById('ind-satisfaccion-det').textContent = 'sin respuestas aún';
        }
    } catch (e) { console.error('indicadores:', e); }
}

cargarDispositivos();
cargarIndicadores();
setInterval(cargarDispositivos, 15000);
setInterval(cargarIndicadores, 60000);
</script>
</body>
</html>
