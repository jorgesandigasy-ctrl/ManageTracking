<aside class="w-56 bg-gray-800 border-r border-gray-700 flex flex-col flex-shrink-0">
    <div class="h-16 flex items-center px-4 border-b border-gray-700 gap-3">
        <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="text-base font-bold truncate">ManageTracking</span>
    </div>
    <nav class="flex-1 p-3 space-y-1">
        <?php
        $navItems = [
            [
                'href'  => 'index.php',
                'page'  => 'dashboard',
                'label' => 'Dashboard',
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            ],
            [
                'href'  => 'dispositivos.php',
                'page'  => 'dispositivos',
                'label' => 'Equipos',
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
            ],
            [
                'href'  => 'clientes.php',
                'page'  => 'clientes',
                'label' => 'Clientes',
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
            ],
            [
                'href'  => 'encuesta.php',
                'page'  => 'encuesta',
                'label' => 'Encuesta',
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            ],
        ];
        foreach ($navItems as $nav):
            $active = ($activePage ?? '') === $nav['page'];
        ?>
        <a href="<?= $nav['href'] ?>"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?= $active ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?= $nav['icon'] ?>
            </svg>
            <?= $nav['label'] ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="p-4 border-t border-gray-700 space-y-3">
        <?php if (($activePage ?? '') === 'dashboard'): ?>
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse flex-shrink-0"></span>
            En vivo · cada 15s
        </div>
        <?php endif; ?>
        <a href="logout.php" class="flex items-center gap-2 text-xs text-gray-400 hover:text-red-400 transition">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Cerrar sesión
        </a>
    </div>
</aside>
