<?php
$pageTitle  = 'ManageTracking — Dashboard';
$activePage = 'dashboard';
$headExtra  = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
include __DIR__ . '/layouts/head.php';
?>
<body class="bg-gray-900 text-white flex h-screen overflow-hidden">

<?php include __DIR__ . '/layouts/sidebar.php'; ?>

<main class="flex-1 overflow-y-auto">
    <div class="sticky top-0 z-10 bg-gray-800 border-b border-gray-700 h-16 flex items-center px-6">
        <div>
            <h1 class="text-lg font-bold">Dashboard</h1>
            <p class="text-gray-400 text-xs">Vista general del sistema</p>
        </div>
    </div>

    <div class="p-6 space-y-4">

        <!-- Tarjetas de resumen -->
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

        <!-- Indicadores de tesis — 3 columnas -->
        <div class="grid grid-cols-3 gap-4">

            <!-- NT: Nivel de Trazabilidad -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <p class="text-xs text-cyan-400 uppercase tracking-wider font-semibold mb-0.5">NT — Nivel de Trazabilidad</p>
                <p class="text-xs text-gray-500 mb-4">Equipos con señal en las últimas 24h / Total × 100</p>
                <div class="flex flex-col items-center">
                    <div style="height:110px;width:220px">
                        <canvas id="chart-nt"></canvas>
                    </div>
                    <div class="flex flex-col items-center -mt-8">
                        <span id="ind-nt-val" class="text-2xl font-bold text-cyan-400">—</span>
                        <span class="text-xs text-gray-500">trazabilidad</span>
                    </div>
                </div>
                <p id="ind-nt-det" class="text-xs text-gray-500 text-center mt-3">—</p>
            </div>

            <!-- PID: Porcentaje de Incidencias Detectadas -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <p class="text-xs text-orange-400 uppercase tracking-wider font-semibold mb-0.5">PID — Incidencias Detectadas</p>
                <p class="text-xs text-gray-500 mb-4">Equipos perdidos o con alerta fuera de sede / Total × 100 (7 días)</p>
                <div class="flex items-center justify-center gap-5">
                    <div style="width:110px;height:110px;flex-shrink:0">
                        <canvas id="chart-pid"></canvas>
                    </div>
                    <div class="space-y-1.5 text-xs text-gray-400">
                        <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0"></span>Con incidencia</div>
                        <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-green-500 flex-shrink-0"></span>Sin incidencia</div>
                        <p id="ind-pid-val" class="text-orange-400 font-bold text-xl pt-1">—</p>
                        <p id="ind-pid-det" class="text-gray-500 leading-tight">—</p>
                    </div>
                </div>
            </div>

            <!-- TMC: Tasa de Monitoreo Continuo -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <p class="text-xs text-emerald-400 uppercase tracking-wider font-semibold mb-0.5">TMC — Monitoreo Continuo</p>
                <p class="text-xs text-gray-500 mb-4">Check-ins recibidos / (recibidos + sin ubicación) por equipo, promediado (7 días)</p>
                <div class="flex flex-col items-center">
                    <div style="height:110px;width:220px">
                        <canvas id="chart-tmc"></canvas>
                    </div>
                    <div class="flex flex-col items-center -mt-8">
                        <span id="ind-tmc-val" class="text-2xl font-bold text-emerald-400">—</span>
                        <span class="text-xs text-gray-500">monitoreo continuo</span>
                    </div>
                </div>
                <p id="ind-tmc-det" class="text-xs text-gray-500 text-center mt-3">—</p>
            </div>

        </div>

        <!-- Árbol de activos + Alertas -->
        <div class="grid grid-cols-3 gap-4 items-start">

            <!-- Árbol de activos (2/3) -->
            <div class="col-span-2 bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-sm">Árbol de Activos</h3>
                        <p class="text-xs text-gray-500">Clientes → Sedes → Equipos</p>
                    </div>
                    <button onclick="cargarArbol()"
                            class="text-xs text-gray-400 hover:text-white transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualizar
                    </button>
                </div>
                <div class="overflow-y-auto p-4" style="max-height:520px">
                    <div id="arbol-dash" class="space-y-2">
                        <p class="text-gray-500 text-sm text-center py-10">Cargando árbol...</p>
                    </div>
                </div>
            </div>

            <!-- Alertas (1/3) -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden flex flex-col">
                <div class="px-5 py-3.5 border-b border-gray-700 flex items-center justify-between flex-shrink-0">
                    <p class="text-sm font-medium">Alertas recientes</p>
                    <div class="flex items-center gap-2">
                        <button id="btn-leer-todo" onclick="marcarTodasLeidas()"
                                class="hidden text-xs text-gray-500 hover:text-green-400 transition">Leer todo</button>
                        <span id="alertas-badge" class="hidden text-xs bg-red-500 text-white px-2 py-0.5 rounded-full font-bold">0</span>
                    </div>
                </div>
                <div id="lista-alertas" class="overflow-y-auto p-4 space-y-1" style="max-height:520px">
                    <p class="text-xs text-gray-500">Cargando...</p>
                </div>
            </div>

        </div>

    </div>
</main>

<script>
// ── Helpers compartidos ───────────────────────────────────────────────────────
function tiempoRelativo(f) {
    if (!f) return '—';
    const d = Math.floor((Date.now() - new Date(f)) / 1000);
    if (d < 60)    return `hace ${d}s`;
    if (d < 3600)  return `hace ${Math.floor(d/60)}min`;
    if (d < 86400) return `hace ${Math.floor(d/3600)}h`;
    return `hace ${Math.floor(d/86400)}d`;
}

const badgeEstado = e => ({
    activo:   'bg-green-900/60 text-green-300 border border-green-700',
    perdido:  'bg-red-900/60 text-red-300 border border-red-700',
    inactivo: 'bg-gray-700/60 text-gray-400 border border-gray-600'
}[e] || 'bg-gray-700/60 text-gray-400 border border-gray-600');

const dotEstado = e => ({
    activo:   'bg-green-400',
    perdido:  'bg-red-400',
    inactivo: 'bg-gray-500'
}[e] || 'bg-gray-500');

// ── Tarjetas de resumen ───────────────────────────────────────────────────────
async function cargarResumen() {
    try {
        const data   = await fetch('api/obtener_dispositivos.php').then(r => r.json());
        const equipos = data.dispositivos || [];
        let activos = 0, perdidos = 0, inactivos = 0;
        equipos.forEach(d => {
            if (d.estado === 'activo') activos++;
            else if (d.estado === 'perdido') perdidos++;
            else inactivos++;
        });
        document.getElementById('m-total').textContent     = equipos.length;
        document.getElementById('m-activos').textContent   = activos;
        document.getElementById('m-perdidos').textContent  = perdidos;
        document.getElementById('m-inactivos').textContent = inactivos;
    } catch (e) { console.error('Error resumen:', e); }
}

// ── Indicadores ───────────────────────────────────────────────────────────────
let graficaNT = null, graficaPID = null, graficaTMC = null;

async function cargarIndicadores() {
    try {
        const d = await fetch('api/indicadores.php').then(r => r.json());

        const NT = d.trazabilidad.porcentaje;
        document.getElementById('ind-nt-val').textContent = NT + '%';
        document.getElementById('ind-nt-det').textContent =
            `${d.trazabilidad.con_senal} de ${d.trazabilidad.total} equipos reportaron en las últimas 24h`;
        const colorNT = NT >= 75 ? '#22d3ee' : NT >= 50 ? '#f59e0b' : '#ef4444';
        if (graficaNT) graficaNT.destroy();
        graficaNT = new Chart(document.getElementById('chart-nt').getContext('2d'), {
            type: 'doughnut',
            data: { datasets: [{ data: [NT, 100 - NT], backgroundColor: [colorNT, '#374151'], borderWidth: 0, circumference: 180, rotation: -90 }] },
            options: { responsive: true, cutout: '72%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });

        const PID = d.incidencias.porcentaje;
        document.getElementById('ind-pid-val').textContent = PID + '%';
        document.getElementById('ind-pid-det').textContent =
            `${d.incidencias.detectadas} equipo(s) con incidencia (${d.incidencias.perdidos} perdidos)`;
        if (graficaPID) graficaPID.destroy();
        graficaPID = new Chart(document.getElementById('chart-pid').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Con incidencia', 'Sin incidencia'],
                datasets: [{ data: [d.incidencias.detectadas || 0.01, d.incidencias.sin_incidencia || 0.01], backgroundColor: ['#ef4444', '#22c55e'], borderWidth: 0, hoverOffset: 4 }]
            },
            options: { responsive: true, cutout: '60%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${Math.round(ctx.parsed)}` } } } }
        });

        const TMC = d.monitoreo_continuo.porcentaje;
        document.getElementById('ind-tmc-val').textContent = TMC + '%';
        document.getElementById('ind-tmc-det').textContent =
            `${d.monitoreo_continuo.total} equipo(s) monitoreados · slots reales de los últimos 7 días`;
        const colorTMC = TMC >= 75 ? '#34d399' : TMC >= 50 ? '#f59e0b' : '#ef4444';
        if (graficaTMC) graficaTMC.destroy();
        graficaTMC = new Chart(document.getElementById('chart-tmc').getContext('2d'), {
            type: 'doughnut',
            data: { datasets: [{ data: [TMC, 100 - TMC], backgroundColor: [colorTMC, '#374151'], borderWidth: 0, circumference: 180, rotation: -90 }] },
            options: { responsive: true, cutout: '72%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });

    } catch (e) { console.error('Error indicadores:', e); }
}

// ── Alertas ───────────────────────────────────────────────────────────────────
async function cargarAlertas() {
    try {
        const data     = await fetch('api/obtener_alertas.php').then(r => r.json());
        const alertas  = data.alertas || [];
        const noLeidas = data.no_leidas || 0;

        const badge = document.getElementById('alertas-badge');
        badge.textContent = noLeidas;
        badge.classList.toggle('hidden', noLeidas === 0);
        document.getElementById('btn-leer-todo').classList.toggle('hidden', noLeidas === 0);

        const lista = document.getElementById('lista-alertas');
        if (!alertas.length) {
            lista.innerHTML = '<p class="text-xs text-gray-500 py-4 text-center">Sin alertas registradas</p>';
            return;
        }
        lista.innerHTML = alertas.map(a => {
            const esFuera = a.tipo === 'fuera_de_sedes';
            const color   = esFuera ? 'text-red-400' : 'text-yellow-400';
            const icono   = esFuera ? '🔴' : '🟡';
            const detalle = esFuera ? 'Fuera de todas las sedes' : `En: ${a.sede_detectada || '—'}`;
            return `
            <div class="flex items-start justify-between gap-2 py-2 border-b border-gray-700/50 last:border-0 ${a.leida ? 'opacity-40' : ''}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span>${icono}</span>
                        <span class="text-xs font-medium text-white truncate">${a.equipo}</span>
                    </div>
                    <p class="text-xs ${color} mt-0.5">${detalle}</p>
                    <p class="text-xs text-gray-500">${tiempoRelativo(a.creada_en)}</p>
                </div>
                ${!a.leida ? `<button onclick="marcarLeida(${a.id},this)" class="text-gray-500 hover:text-green-400 transition flex-shrink-0 mt-0.5 text-sm" title="Marcar leída">✓</button>` : ''}
            </div>`;
        }).join('');
    } catch (e) { console.error('Error alertas:', e); }
}

async function marcarLeida(id, btn) {
    await fetch('api/marcar_alerta_leida.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    btn.closest('div.flex').classList.add('opacity-40');
    btn.remove();
    cargarAlertas();
}

async function marcarTodasLeidas() {
    await fetch('api/marcar_alerta_leida.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ all: true })
    });
    cargarAlertas();
}

// ── Árbol de activos ──────────────────────────────────────────────────────────
function resumenEstados(devs) {
    const a = devs.filter(d => d.estado === 'activo').length;
    const p = devs.filter(d => d.estado === 'perdido').length;
    const i = devs.filter(d => d.estado === 'inactivo').length;
    const partes = [];
    if (a) partes.push(`<span class="text-green-400">${a} activo${a>1?'s':''}</span>`);
    if (i) partes.push(`<span class="text-gray-400">${i} inactivo${i>1?'s':''}</span>`);
    if (p) partes.push(`<span class="text-red-400">${p} perdido${p>1?'s':''}</span>`);
    return partes.length ? partes.join(', ') : '<span class="text-gray-600">sin equipos</span>';
}

function renderDispositivo(d, sedeAsignadaId) {
    const nombre = [d.nombre_usuario, d.apellido_usuario].filter(Boolean).join(' ') || '—';
    let geobadge;
    if (!d.ultima_vez) {
        geobadge = `<span class="px-1.5 py-0.5 rounded-full text-xs bg-gray-700/60 text-gray-500 border border-gray-600 flex-shrink-0">Sin GPS</span>`;
    } else if (!d.ultima_sede_detectada_id) {
        geobadge = `<span class="px-1.5 py-0.5 rounded-full text-xs bg-red-900/60 text-red-300 border border-red-700 flex-shrink-0">⚠ Fuera</span>`;
    } else if (String(d.ultima_sede_detectada_id) === String(sedeAsignadaId)) {
        geobadge = `<span class="px-1.5 py-0.5 rounded-full text-xs bg-green-900/60 text-green-300 border border-green-700 flex-shrink-0">✓ En sede</span>`;
    } else {
        geobadge = `<span class="px-1.5 py-0.5 rounded-full text-xs bg-yellow-900/60 text-yellow-300 border border-yellow-700 flex-shrink-0" title="${d.sede_detectada_nombre||'?'}">↗ ${d.sede_detectada_nombre||'Otra sede'}</span>`;
    }
    return `
    <div class="flex items-center gap-2 pl-5 py-1.5 border-b border-gray-700/30 last:border-0 hover:bg-gray-700/20 rounded transition group">
        <span class="w-2 h-2 rounded-full flex-shrink-0 ${dotEstado(d.estado)}"></span>
        <div class="flex-1 min-w-0">
            <span class="text-xs font-medium text-white">${d.hostname || d.mac_address}</span>
            <span class="text-xs text-gray-500 ml-1.5">${nombre}</span>
        </div>
        <span class="text-xs text-gray-500 capitalize flex-shrink-0">${d.tipo}</span>
        ${geobadge}
        <span class="px-1.5 py-0.5 rounded-full text-xs font-medium capitalize flex-shrink-0 ${badgeEstado(d.estado)}">${d.estado}</span>
        <span class="text-xs text-gray-500 w-16 text-right flex-shrink-0">${tiempoRelativo(d.ultima_vez)}</span>
        <a href="detalle.php?mac=${encodeURIComponent(d.mac_address)}"
           class="text-xs bg-gray-700 hover:bg-gray-600 px-2 py-0.5 rounded transition opacity-0 group-hover:opacity-100 flex-shrink-0">Ver</a>
    </div>`;
}

function renderSede(sede, idxSede) {
    const id = `ds-sede-${idxSede}`;
    const total = sede.dispositivos?.length || 0;
    return `
    <div class="border border-gray-700/60 rounded-lg overflow-hidden">
        <button onclick="toggleNodo('${id}')"
                class="w-full flex items-center gap-2 px-3 py-2 bg-gray-700/20 hover:bg-gray-700/40 transition text-left">
            <svg id="${id}-icon" class="w-3.5 h-3.5 text-gray-400 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="text-xs font-medium">${sede.nombre}</span>
            <span class="text-xs text-gray-500">${sede.ciudad || ''}</span>
            <span class="ml-auto text-xs text-gray-400">${resumenEstados(sede.dispositivos || [])}</span>
        </button>
        <div id="${id}-body" class="hidden px-3 py-1 bg-gray-800/30">
            ${total
                ? (sede.dispositivos || []).map(d => renderDispositivo(d, sede.id)).join('')
                : '<p class="text-xs text-gray-500 py-2 pl-5">Sin equipos en esta sede</p>'
            }
        </div>
    </div>`;
}

function renderCliente(c, idx) {
    const id    = `ds-cli-${idx}`;
    const total = c.sedes.reduce((n, s) => n + (s.dispositivos?.length || 0), 0);
    return `
    <div class="bg-gray-700/20 border border-gray-700 rounded-xl overflow-hidden">
        <button onclick="toggleNodo('${id}')"
                class="w-full flex items-center gap-2 px-4 py-3 hover:bg-gray-700/30 transition text-left">
            <svg id="${id}-icon" class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white">${c.nombre}</p>
                <p class="text-xs text-gray-400">RUC: ${c.ruc || '—'}</p>
            </div>
            <div class="text-right text-xs text-gray-400 flex-shrink-0">
                <p>${c.sedes.length} sede${c.sedes.length !== 1 ? 's' : ''}</p>
                <p>${total} equipo${total !== 1 ? 's' : ''}</p>
            </div>
        </button>
        <div id="${id}-body" class="hidden border-t border-gray-700 px-3 py-2 space-y-1.5">
            ${c.sedes.length
                ? c.sedes.map((s, i) => renderSede(s, `${idx}-${i}`)).join('')
                : '<p class="text-xs text-gray-500 py-2 pl-2">Sin sedes registradas</p>'
            }
        </div>
    </div>`;
}

function toggleNodo(id) {
    const body = document.getElementById(`${id}-body`);
    const icon = document.getElementById(`${id}-icon`);
    const abierto = !body.classList.contains('hidden');
    body.classList.toggle('hidden', abierto);
    icon.style.transform = abierto ? '' : 'rotate(90deg)';
}

async function cargarArbol() {
    document.getElementById('arbol-dash').innerHTML =
        '<p class="text-gray-500 text-sm text-center py-10">Cargando...</p>';
    try {
        const data     = await fetch('api/obtener_arbol_activos.php').then(r => r.json());
        const clientes = data.clientes || [];
        const sinSede  = data.sin_sede  || [];

        let html = clientes.map((c, i) => renderCliente(c, i)).join('');

        if (sinSede.length) {
            const id = 'ds-sinsede';
            html += `
            <div class="bg-gray-700/20 border border-gray-700 border-dashed rounded-xl overflow-hidden">
                <button onclick="toggleNodo('${id}')"
                        class="w-full flex items-center gap-2 px-4 py-3 hover:bg-gray-700/30 transition text-left">
                    <svg id="${id}-icon" class="w-4 h-4 text-gray-500 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-400">Sin sede asignada</span>
                    <span class="ml-auto text-xs text-gray-500">${sinSede.length} equipo${sinSede.length !== 1 ? 's' : ''}</span>
                </button>
                <div id="${id}-body" class="hidden border-t border-gray-700 px-3 py-1">
                    ${sinSede.map(d => renderDispositivo(d, null)).join('')}
                </div>
            </div>`;
        }

        document.getElementById('arbol-dash').innerHTML = html ||
            '<p class="text-gray-500 text-sm text-center py-10">No hay clientes registrados</p>';

    } catch (e) {
        document.getElementById('arbol-dash').innerHTML =
            '<p class="text-red-400 text-sm text-center py-10">Error al cargar activos</p>';
        console.error(e);
    }
}

// ── Arranque ──────────────────────────────────────────────────────────────────
cargarResumen();
cargarIndicadores();
cargarAlertas();
cargarArbol();
setInterval(cargarResumen,     60000);
setInterval(cargarIndicadores, 60000);
setInterval(cargarAlertas,     30000);
setInterval(cargarArbol,       60000);
</script>
</body>
</html>
