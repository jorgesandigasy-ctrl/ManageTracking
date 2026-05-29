<?php
$pageTitle  = 'ManageTracking — Métricas';
$activePage = 'metricas';
$headExtra  = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
include __DIR__ . '/layouts/head.php';
?>
<body class="bg-gray-900 text-white flex h-screen overflow-hidden">

<?php include __DIR__ . '/layouts/sidebar.php'; ?>

<main class="flex-1 overflow-y-auto">
    <div class="sticky top-0 z-10 bg-gray-800 border-b border-gray-700 h-16 flex items-center px-6">
        <div>
            <h1 class="text-lg font-bold">Métricas</h1>
            <p class="text-gray-400 text-xs">Indicadores de rendimiento del sistema</p>
        </div>
    </div>

    <div class="p-6 space-y-4">

        <!-- Tarjetas de resumen -->
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-gray-800 rounded-xl border border-gray-700 px-5 py-3.5">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1.5">Total</p>
                <p id="m-total" class="text-3xl font-bold">—</p>
                <p class="text-xs text-gray-500 mt-0.5">equipos registrados</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-l-4 border-l-green-500 px-5 py-3.5">
                <p class="text-xs text-green-400 uppercase tracking-wider mb-1.5">Activos</p>
                <p id="m-activos" class="text-3xl font-bold text-green-400">—</p>
                <p class="text-xs text-gray-500 mt-0.5">en funcionamiento</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-l-4 border-l-red-500 px-5 py-3.5">
                <p class="text-xs text-red-400 uppercase tracking-wider mb-1.5">Perdidos</p>
                <p id="m-perdidos" class="text-3xl font-bold text-red-400">—</p>
                <p class="text-xs text-gray-500 mt-0.5">reportados</p>
            </div>
            <div class="bg-gray-800 rounded-xl border border-gray-700 border-l-4 border-l-gray-500 px-5 py-3.5">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1.5">Inactivos</p>
                <p id="m-inactivos" class="text-3xl font-bold">—</p>
                <p class="text-xs text-gray-500 mt-0.5">sin señal reciente</p>
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

    </div>
</main>

<script>
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

// ── Arranque ──────────────────────────────────────────────────────────────────
cargarResumen();
cargarIndicadores();
setInterval(cargarResumen,     60000);
setInterval(cargarIndicadores, 60000);
</script>
</body>
</html>
