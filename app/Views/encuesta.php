<?php
$pageTitle = 'Encuesta de Satisfacción — ManageTracking';
$headExtra = '';
include __DIR__ . '/layouts/head.php';
?>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-lg">
    <!-- Encabezado -->
    <div class="text-center mb-8">
        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold">Encuesta de Satisfacción</h1>
        <p class="text-gray-400 text-sm mt-2">Sistema de rastreo de equipos — ManageTracking</p>
    </div>

    <!-- Formulario -->
    <div id="form-container" class="bg-gray-800 rounded-2xl border border-gray-700 p-6 shadow-2xl">
        <form id="form-encuesta" class="space-y-6">

            <?php
            $preguntas = [
                1 => '¿Qué tan útil es el sistema para localizar equipos?',
                2 => '¿El sistema es fácil de usar?',
                3 => '¿El sistema reduce el tiempo de búsqueda de equipos?',
                4 => '¿El sistema mejora el control sobre los equipos asignados?',
                5 => '¿Recomendaría el uso de este sistema?',
            ];
            $etiquetas = ['Muy malo', 'Malo', 'Regular', 'Bueno', 'Excelente'];
            foreach ($preguntas as $n => $texto): ?>
            <div>
                <p class="text-sm font-medium mb-3">
                    <span class="text-blue-400 font-bold"><?= $n ?>.</span> <?= $texto ?>
                </p>
                <div class="flex gap-2">
                    <?php for ($v = 1; $v <= 5; $v++): ?>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="p<?= $n ?>" value="<?= $v ?>" class="hidden peer" required>
                        <div class="peer-checked:bg-blue-600 peer-checked:border-blue-500 peer-checked:text-white
                                    bg-gray-700 border border-gray-600 rounded-lg p-2 text-center
                                    hover:border-blue-500 hover:bg-gray-600 transition text-xs">
                            <div class="text-lg font-bold"><?= $v ?></div>
                            <div class="text-gray-400 peer-checked:text-blue-200 text-xs"><?= $etiquetas[$v-1] ?></div>
                        </div>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div id="form-error" class="hidden bg-red-900/40 border border-red-700 rounded-lg p-3 text-red-300 text-sm"></div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl text-sm font-medium transition">
                Enviar respuestas
            </button>
        </form>
    </div>

    <!-- Mensaje de éxito -->
    <div id="exito" class="hidden bg-gray-800 rounded-2xl border border-green-700 p-8 text-center shadow-2xl">
        <div class="w-16 h-16 bg-green-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-green-400 mb-2">¡Gracias por tu respuesta!</h2>
        <p class="text-gray-400 text-sm">Tu opinión ayuda a mejorar el sistema.</p>
    </div>
</div>

<script>
document.getElementById('form-encuesta').addEventListener('submit', async e => {
    e.preventDefault();
    const errEl = document.getElementById('form-error');
    errEl.classList.add('hidden');

    const payload = {};
    for (let i = 1; i <= 5; i++) {
        const sel = document.querySelector(`input[name="p${i}"]:checked`);
        if (!sel) { errEl.textContent = `Por favor responde la pregunta ${i}.`; errEl.classList.remove('hidden'); return; }
        payload[`p${i}`] = parseInt(sel.value);
    }

    const res  = await fetch('api/guardar_encuesta.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (data.ok) {
        document.getElementById('form-container').classList.add('hidden');
        document.getElementById('exito').classList.remove('hidden');
    } else {
        errEl.textContent = data.error || 'Error al enviar. Intenta de nuevo.';
        errEl.classList.remove('hidden');
    }
});
</script>
</body>
</html>
