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
            <h1 class="text-lg font-bold">Métricas</h1>
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

        <!-- ═══════════════════════════════════════════════════════════════
             INDICADORES DE TESIS
             Se calculan en api/indicadores.php y se renderizan con Chart.js
             ═══════════════════════════════════════════════════════════════ -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700">
                <h3 class="font-medium">Indicadores</h3>
                <p class="text-xs text-gray-500 mt-0.5">Actualizados cada 60 segundos</p>
            </div>
            <div class="p-5 grid grid-cols-2 gap-6">

                <!-- ── NT: Nivel de Trazabilidad ──────────────────────────
                     Fórmula: (equipos con señal ≤24h / total) × 100
                     Gráfico: velocímetro (doughnut semicircular)          -->
                <div class="bg-gray-700/40 rounded-xl p-4">
                    <p class="text-xs text-cyan-400 uppercase tracking-wider font-semibold mb-1">NT — Nivel de Trazabilidad</p>
                    <p class="text-xs text-gray-400 mb-3">Equipos con señal en las últimas 24h / Total equipos × 100</p>
                    <div class="relative flex flex-col items-center">
                        <div style="height:120px;width:240px;position:relative">
                            <canvas id="chart-nt"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-end pb-1 pointer-events-none">
                                <span id="ind-nt-val" class="text-2xl font-bold text-cyan-400">—</span>
                                <span class="text-xs text-gray-400">trazabilidad</span>
                            </div>
                        </div>
                    </div>
                    <p id="ind-nt-det" class="text-xs text-gray-500 text-center mt-2">—</p>
                </div>

                <!-- ── PID: Porcentaje de Incidencias Detectadas ──────────
                     Fórmula: (incidencias automáticas / total) × 100
                     Incidencia: estado='perdido' O sin señal >24h
                     Gráfico: dona (detectadas vs sin incidencia)          -->
                <div class="bg-gray-700/40 rounded-xl p-4">
                    <p class="text-xs text-orange-400 uppercase tracking-wider font-semibold mb-1">PID — Incidencias Detectadas</p>
                    <p class="text-xs text-gray-400 mb-3">Incidencias automáticas (perdido o sin señal) / Total equipos × 100</p>
                    <div class="flex items-center justify-center gap-6">
                        <div style="width:140px;height:140px">
                            <canvas id="chart-pid"></canvas>
                        </div>
                        <div class="space-y-2 text-xs text-gray-400">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500 flex-shrink-0"></span>Detectadas</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>Sin incidencia</div>
                            <p id="ind-pid-val" class="text-orange-400 font-bold text-lg mt-2">—</p>
                            <p id="ind-pid-det" class="text-gray-500">—</p>
                        </div>
                    </div>
                </div>

                <!-- ── TPU: Tiempo Promedio de Ubicación ─────────────────
                     Fórmula: Σ(tiempo_respuesta_ms) / N peticiones
                     Fuente: registros_gps.tiempo_respuesta_ms
                     Gráfico: barras horizontales por equipo               -->
                <div class="bg-gray-700/40 rounded-xl p-4">
                    <p class="text-xs text-blue-400 uppercase tracking-wider font-semibold mb-1">TPU — Tiempo Promedio de Ubicación</p>
                    <p class="text-xs text-gray-400 mb-3">Promedio del tiempo de respuesta de la API por equipo (ms)</p>
                    <p id="ind-tpu-global" class="text-2xl font-bold text-blue-400 mb-3">— ms</p>
                    <div style="min-height:80px">
                        <canvas id="chart-tpu"></canvas>
                    </div>
                    <p id="ind-tpu-det" class="text-xs text-gray-500 mt-2">Sin datos de tiempo de respuesta aún</p>
                </div>

                <!-- ── NSP: Nivel de Satisfacción del Personal ────────────
                     Fórmula: SUM(p1..p5) / (25 × total_encuestas) × 100
                     25 = puntaje máximo por encuesta (5 preguntas × 5 pts)
                     Gráfico: barras horizontales por pregunta              -->
                <div class="bg-gray-700/40 rounded-xl p-4">
                    <p class="text-xs text-purple-400 uppercase tracking-wider font-semibold mb-1">NSP — Satisfacción del Personal</p>
                    <p class="text-xs text-gray-400 mb-3">SUM(respuestas) / (25 × encuestas) × 100 — escala Likert 1–5</p>
                    <p id="ind-nsp-global" class="text-2xl font-bold text-purple-400 mb-3">—</p>
                    <div style="min-height:120px">
                        <canvas id="chart-nsp"></canvas>
                    </div>
                    <p id="ind-nsp-det" class="text-xs text-gray-500 mt-2">Sin encuestas respondidas aún</p>
                </div>

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

// ── Variables para los 4 gráficos de indicadores (se recrean en cada carga) ──
let graficaNT = null, graficaPID = null, graficaTPU = null, graficaNSP = null;

// Etiquetas cortas para el gráfico de barras del NSP
const NSP_ETIQUETAS = ['Utilidad', 'Facilidad de uso', 'Reduce tiempo', 'Mejora control', 'Recomendaría'];

async function cargarIndicadores() {
    try {
        const d = await fetch('api/indicadores.php').then(r => r.json());

        // ── NT: Nivel de Trazabilidad ────────────────────────────────────────
        // Velocímetro semicircular: doughnut con circumference=180 y rotation=-90
        const NT = d.trazabilidad.porcentaje;
        document.getElementById('ind-nt-val').textContent = NT + '%';
        document.getElementById('ind-nt-det').textContent =
            `${d.trazabilidad.con_senal} de ${d.trazabilidad.total} equipos reportaron en las últimas 24h`;

        const ctxNT = document.getElementById('chart-nt').getContext('2d');
        const colorNT = NT >= 75 ? '#22d3ee' : NT >= 50 ? '#f59e0b' : '#ef4444';
        if (graficaNT) graficaNT.destroy();
        graficaNT = new Chart(ctxNT, {
            type: 'doughnut',
            data: { datasets: [{ data: [NT, 100 - NT], backgroundColor: [colorNT, '#374151'], borderWidth: 0, circumference: 180, rotation: -90 }] },
            options: { responsive: true, cutout: '72%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });

        // ── PID: Porcentaje de Incidencias Detectadas ────────────────────────
        // Dona: segmento rojo = detectadas automáticamente, verde = sin incidencia
        const PID = d.incidencias.porcentaje;
        document.getElementById('ind-pid-val').textContent = PID + '%';
        document.getElementById('ind-pid-det').textContent =
            `${d.incidencias.detectadas} detectadas (${d.incidencias.perdidos} perdidos, ${d.incidencias.sin_senal} sin señal)`;

        const ctxPID = document.getElementById('chart-pid').getContext('2d');
        if (graficaPID) graficaPID.destroy();
        graficaPID = new Chart(ctxPID, {
            type: 'doughnut',
            data: {
                labels: ['Detectadas', 'Sin incidencia'],
                datasets: [{ data: [d.incidencias.detectadas || 0.01, d.incidencias.sin_incidencia || 0.01], backgroundColor: ['#ef4444', '#22c55e'], borderWidth: 0, hoverOffset: 4 }]
            },
            options: { responsive: true, cutout: '60%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${Math.round(ctx.parsed)}` } } } }
        });

        // ── TPU: Tiempo Promedio de Ubicación ────────────────────────────────
        // Barras horizontales: un bar por equipo con su promedio en ms
        const tpu = d.tiempo_ubicacion;
        document.getElementById('ind-tpu-global').textContent =
            tpu.promedio_ms !== null ? Math.round(tpu.promedio_ms) + ' ms' : '— ms';

        if (tpu.por_equipo && tpu.por_equipo.length > 0) {
            document.getElementById('ind-tpu-det').textContent =
                `${tpu.por_equipo.reduce((s, e) => s + e.total_peticiones, 0)} peticiones totales registradas`;
            const ctxTPU = document.getElementById('chart-tpu').getContext('2d');
            if (graficaTPU) graficaTPU.destroy();
            graficaTPU = new Chart(ctxTPU, {
                type: 'bar',
                data: {
                    labels: tpu.por_equipo.map(e => e.nombre),
                    datasets: [{ label: 'ms', data: tpu.por_equipo.map(e => e.promedio_ms), backgroundColor: '#3b82f6', borderRadius: 4 }]
                },
                options: {
                    indexAxis: 'y', responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: '#374151' }, ticks: { color: '#9ca3af' }, title: { display: true, text: 'ms', color: '#9ca3af' } },
                        y: { grid: { display: false }, ticks: { color: '#9ca3af' } }
                    }
                }
            });
        }

        // ── NSP: Nivel de Satisfacción del Personal ──────────────────────────
        // Barras horizontales: una por pregunta, escala 0–5
        const nsp = d.satisfaccion;
        if (nsp.total > 0) {
            document.getElementById('ind-nsp-global').textContent = nsp.porcentaje + '%';
            document.getElementById('ind-nsp-det').textContent =
                `${nsp.total} encuesta${nsp.total > 1 ? 's' : ''} respondida${nsp.total > 1 ? 's' : ''}`;
            const ctxNSP = document.getElementById('chart-nsp').getContext('2d');
            if (graficaNSP) graficaNSP.destroy();
            graficaNSP = new Chart(ctxNSP, {
                type: 'bar',
                data: {
                    labels: NSP_ETIQUETAS,
                    datasets: [{ label: 'Promedio', data: nsp.por_pregunta, backgroundColor: '#8b5cf6', borderRadius: 4 }]
                },
                options: {
                    indexAxis: 'y', responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { min: 0, max: 5, grid: { color: '#374151' }, ticks: { color: '#9ca3af' }, title: { display: true, text: 'Puntaje (1–5)', color: '#9ca3af' } },
                        y: { grid: { display: false }, ticks: { color: '#9ca3af' } }
                    }
                }
            });
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
