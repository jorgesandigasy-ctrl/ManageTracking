<?php
$pageTitle = 'ManageTracking — Detalle del Equipo';
$headExtra = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>#mapa-detalle{height:400px}</style>';
include __DIR__ . '/layouts/head.php';
?>
<body class="bg-gray-900 text-white min-h-screen">

<nav class="bg-gray-800 border-b border-gray-700 h-16 flex items-center px-6 justify-between">
    <div class="flex items-center gap-3">
        <a href="dispositivos.php" class="text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span class="text-xl font-bold">ManageTracking</span>
    </div>
    <a href="dispositivos.php" class="text-sm text-gray-300 hover:text-white bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg transition">
        Gestionar Equipos
    </a>
</nav>

<div class="max-w-6xl mx-auto p-6">
    <div id="contenido">
        <p class="text-gray-400 text-center py-20">Cargando información del equipo...</p>
    </div>
</div>

<script>
const mac = new URLSearchParams(location.search).get('mac');
if (!mac) document.getElementById('contenido').innerHTML = '<p class="text-red-400 text-center py-20">No se especificó ningún equipo.</p>';

function tiempoRelativo(f) {
    if (!f) return 'Sin datos';
    const d = Math.floor((Date.now() - new Date(f)) / 1000);
    if (d < 60)    return `hace ${d}s`;
    if (d < 3600)  return `hace ${Math.floor(d/60)}min`;
    if (d < 86400) return `hace ${Math.floor(d/3600)}h`;
    return new Date(f).toLocaleString('es-PE');
}

const badgeEstado = e => ({ activo: 'bg-green-900 text-green-300', perdido: 'bg-red-900 text-red-300', inactivo: 'bg-gray-700 text-gray-400' }[e] || 'bg-gray-700 text-gray-400');

let todosRegistros = [];
let paginaActual   = 0;
const POR_PAGINA   = 20;

function renderPagina(n) {
    const total = todosRegistros.length;
    const totalPaginas = Math.max(1, Math.ceil(total / POR_PAGINA));
    paginaActual = Math.max(0, Math.min(n, totalPaginas - 1));
    const inicio  = paginaActual * POR_PAGINA;
    const pagina  = todosRegistros.slice(inicio, inicio + POR_PAGINA);

    document.getElementById('tbody-historial').innerHTML = pagina.map((r, i) => {
        if (r.tipo === 'sin_ubicacion') {
            return `
            <tr class="border-b border-gray-700/50 bg-red-900/10">
                <td class="p-4 text-gray-400 text-sm">${new Date(r.registrado_en).toLocaleString('es-PE')}</td>
                <td class="p-4" colspan="4">
                    <span class="inline-flex items-center gap-1.5 text-xs text-red-400">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        Check-in no recibido
                    </span>
                </td>
            </tr>`;
        }
        const esUltimo = (paginaActual === 0 && i === 0);
        return `
        <tr class="border-b border-gray-700 hover:bg-gray-700/30 ${esUltimo ? 'bg-blue-900/20' : ''}">
            <td class="p-4 text-gray-300 text-sm">${new Date(r.registrado_en).toLocaleString('es-PE')}</td>
            <td class="p-4 font-mono text-xs">${parseFloat(r.latitud).toFixed(6)}</td>
            <td class="p-4 font-mono text-xs">${parseFloat(r.longitud).toFixed(6)}</td>
            <td class="p-4 text-sm">${r.altitud ? parseFloat(r.altitud).toFixed(0)+' m' : '—'}</td>
            <td class="p-4 text-sm">${r.satelites ?? '—'}</td>
        </tr>`;
    }).join('') || '<tr><td colspan="5" class="p-8 text-center text-gray-500">Sin registros</td></tr>';

    const pag = document.getElementById('paginacion');
    if (totalPaginas <= 1) { pag.innerHTML = ''; return; }

    const btnCls  = 'px-3 py-1.5 rounded-lg text-xs font-medium transition';
    const activo  = `${btnCls} bg-gray-700 hover:bg-gray-600 text-white`;
    const inactivo = `${btnCls} text-gray-600 cursor-not-allowed`;

    pag.innerHTML = `
        <button onclick="renderPagina(paginaActual - 1)" ${paginaActual === 0 ? 'disabled' : ''}
                class="${paginaActual === 0 ? inactivo : activo}">← Anterior</button>
        <span class="text-xs text-gray-400">
            Página <span class="text-white font-medium">${paginaActual + 1}</span> de ${totalPaginas}
            <span class="text-gray-600 mx-1">·</span>
            ${inicio + 1}–${Math.min(inicio + POR_PAGINA, total)} de ${total}
        </span>
        <button onclick="renderPagina(paginaActual + 1)" ${paginaActual >= totalPaginas - 1 ? 'disabled' : ''}
                class="${paginaActual >= totalPaginas - 1 ? inactivo : activo}">Siguiente →</button>`;
}

async function cargarDetalle() {
    const data = await fetch(`api/historial_dispositivo.php?mac=${encodeURIComponent(mac)}&limite=200`).then(r => r.json());
    if (!data.dispositivo) {
        document.getElementById('contenido').innerHTML = '<p class="text-red-400 text-center py-20">Equipo no encontrado.</p>';
        return;
    }
    const d    = data.dispositivo;
    todosRegistros = data.registros || [];
    const ultimo   = todosRegistros[0];
    const usuario  = [d.nombre_usuario, d.apellido_usuario].filter(Boolean).join(' ') || '—';

    document.title = `${d.hostname || d.mac_address} — ManageTracking`;

    document.getElementById('contenido').innerHTML = `
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">${d.hostname || d.mac_address}</h1>
            <p class="text-gray-400">${usuario} · <span class="capitalize">${d.tipo}</span> · <span class="font-mono text-xs">${d.mac_address}</span></p>
            ${d.sede_nombre ? `<p class="text-blue-400 text-sm mt-1">📍 ${d.sede_nombre}</p>` : ''}
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-medium capitalize ${badgeEstado(d.estado)}">${d.estado}</span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <p class="text-gray-400 text-xs uppercase tracking-wider">Última señal</p>
            <p class="text-white font-semibold mt-1">${tiempoRelativo(d.ultima_vez)}</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <p class="text-gray-400 text-xs uppercase tracking-wider">Check-ins (7 días)</p>
            <p class="text-white font-semibold mt-1">${todosRegistros.filter(r=>r.tipo==='ubicacion').length} <span class="text-xs text-gray-500 font-normal">/ ${todosRegistros.length} slots</span></p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <p class="text-gray-400 text-xs uppercase tracking-wider">Último satélites</p>
            <p class="text-white font-semibold mt-1">${ultimo?.satelites ?? '—'}</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <p class="text-gray-400 text-xs uppercase tracking-wider">Última altitud</p>
            <p class="text-white font-semibold mt-1">${ultimo?.altitud ? parseFloat(ultimo.altitud).toFixed(0)+' m' : '—'}</p>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 mb-6 p-5">
        <h2 class="font-semibold mb-4">Especificaciones del equipo</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><p class="text-gray-400 text-xs">Procesador</p><p class="text-white mt-0.5">${d.procesador || '—'}</p></div>
            <div><p class="text-gray-400 text-xs">RAM</p><p class="text-white mt-0.5">${d.ram_gb ? d.ram_gb+' GB' : '—'}</p></div>
            <div><p class="text-gray-400 text-xs">Almacenamiento</p><p class="text-white mt-0.5">${d.almacenamiento_gb ? d.almacenamiento_gb+' GB' : '—'}</p></div>
            <div><p class="text-gray-400 text-xs">Windows</p><p class="text-white mt-0.5">${d.windows_version || '—'}</p></div>
            <div><p class="text-gray-400 text-xs">Número de serie</p><p class="text-white font-mono text-xs mt-0.5">${d.serie_equipo || '—'}</p></div>
            <div><p class="text-gray-400 text-xs">MAC Address</p><p class="text-white font-mono text-xs mt-0.5">${d.mac_address}</p></div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 mb-6 overflow-hidden">
        <div class="p-4 border-b border-gray-700">
            <h2 class="font-semibold">Ruta reciente (${todosRegistros.filter(r=>r.tipo==='ubicacion').length} ubicaciones)</h2>
        </div>
        <div id="mapa-detalle"></div>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="p-4 border-b border-gray-700 flex justify-between items-center">
            <h2 class="font-semibold">Historial de ubicaciones</h2>
            <span class="text-xs text-gray-400">${todosRegistros.length} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-700 text-gray-400 text-xs uppercase">
                        <th class="text-left p-4">Fecha y hora</th>
                        <th class="text-left p-4">Latitud</th>
                        <th class="text-left p-4">Longitud</th>
                        <th class="text-left p-4">Altitud</th>
                        <th class="text-left p-4">Satélites</th>
                    </tr>
                </thead>
                <tbody id="tbody-historial"></tbody>
            </table>
        </div>
        <div id="paginacion" class="px-4 py-3 border-t border-gray-700 flex items-center justify-between"></div>
    </div>`;

    const ubicaciones = todosRegistros.filter(r => r.tipo === 'ubicacion' && r.latitud);

    if (ubicaciones.length > 0) {
        const mapa = L.map('mapa-detalle');
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(mapa);
        const puntos = ubicaciones.map(r => [parseFloat(r.latitud), parseFloat(r.longitud)]);
        const ruta   = L.polyline(puntos, { color: '#3b82f6', weight: 3, opacity: 0.7 }).addTo(mapa);
        L.circleMarker(puntos[puntos.length-1], { radius:8, color:'#22c55e', fillColor:'#22c55e', fillOpacity:1 }).addTo(mapa).bindTooltip('Inicio');
        L.circleMarker(puntos[0], { radius:8, color:'#ef4444', fillColor:'#ef4444', fillOpacity:1 }).addTo(mapa).bindTooltip('Último');
        mapa.fitBounds(ruta.getBounds(), { padding: [30, 30] });
    } else {
        document.getElementById('mapa-detalle').innerHTML = '<p class="text-gray-500 text-center py-16">Sin datos de ubicación</p>';
    }

    paginaActual = 0;
    renderPagina(0);
}

if (mac) cargarDetalle();
</script>
</body>
</html>
