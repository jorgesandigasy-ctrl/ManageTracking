<aside class="w-56 bg-gray-800 border-r border-gray-700 flex flex-col flex-shrink-0">
    <div class="h-16 flex items-center px-4 border-b border-gray-700 gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="auto" fill="none" viewBox="0 0 554 512"><path fill="#0052a9" d="M39.677 419.47h474.989s86.532 92.53 0 92.53H39.677c-89.273 0 0-92.53 0-92.53"/><path fill="#d9d9d9" d="M218.569 471.904h120.289v12.337H218.569z"/><path fill="#0052a9" d="M156.475 92.53a126 126 0 0 0-.963 15.595c0 7.3.624 14.453 1.822 21.405h-56.487c-9.941 0-18 8.059-18 18v204.603c0 9.941 8.059 18 18 18h352.65c9.941 0 18-8.059 18-18V147.53c0-9.941-8.059-18-18-18H397.35a126 126 0 0 0 1.825-21.405c0-5.282-.328-10.487-.962-15.595h55.284c30.376 0 55 24.624 55 55v204.603l-.004.711c-.378 29.811-24.474 53.906-54.285 54.284l-.711.005h-352.65l-.711-.005c-29.812-.378-53.907-24.473-54.284-54.284l-.005-.711V147.53c0-30.376 24.624-55 55-55z"/><path fill="#0052a9" d="M277.343 0c60.472 0 109.494 49.713 109.494 111.036 0 16.942-3.744 32.997-10.435 47.363-24.256 54.45-99.108 146.921-99.132 146.951 0 0-82.48-99.974-102.202-154.584-4.663-12.336-7.219-25.73-7.219-39.73C167.849 49.713 216.871 0 277.343 0m1.542 61.687c-32.365 0-58.602 26.237-58.602 58.602s26.237 58.603 58.602 58.603 58.603-26.238 58.603-58.603-26.238-58.602-58.603-58.602"/></svg>
        <span class="text-base font-bold truncate">ManageTracking</span>
    </div>
    <nav class="flex-1 p-3 space-y-1">
        <?php
        $navItems = [
            [
                'href'  => 'index.php',
                'page'  => 'dashboard',
                'label' => 'Activos',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 2048 2048"><path fill="currentColor" d="M1792 1280h256v768H1024v-768h256v-256h512zm-384-128v128h256v-128zm512 768v-256h-128v128h-128v-128h-256v128h-128v-128h-128v256zm0-384v-128h-768v128zm-768-512v128H896v256H640v-128h128v-128H512v256H0V640h128V128h1536v768h-128V256H256v384h256v384zm-768 256V768H128v512z"/></svg>',
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
                'href'  => 'metricas.php',
                'page'  => 'metricas',
                'label' => 'Indicadores',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><path d="M15 12a3 3 0 1 1-6 0a3 3 0 0 1 6 0"/><path stroke="currentColor" stroke-linecap="square" stroke-width="2" d="M19.567 4.414L14.11 9.87m0 0a3 3 0 1 0-4.223 4.263A3 3 0 0 0 14.11 9.87Z"/><path stroke="currentColor" stroke-linecap="square" stroke-width="2" d="M7 20.662A10 10 0 0 1 2 12C2 6.477 6.477 2 12 2a10 10 0 0 1 3.135.501m1.868 18.16A10 10 0 0 0 22 12a10 10 0 0 0-.501-3.136"/></g></svg>',
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
        <?php if (in_array($activePage ?? '', ['dashboard', 'metricas'])): ?>
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
