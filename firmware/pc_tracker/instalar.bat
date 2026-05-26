@echo off
setlocal

REM ── Auto-elevacion UAC ────────────────────────────────────
net session >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Solicitando permisos de administrador...
    powershell -Command "Start-Process cmd -ArgumentList '/c cd /d \"%~dp0\" && \"%~f0\"' -Verb RunAs"
    exit /b
)

cd /d "%~dp0"
echo === ManageTracking - Instalador ===
echo.

REM ── Python ────────────────────────────────────────────────
python --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Python no encontrado. Instalando via winget...
    winget install Python.Python.3 --silent --accept-package-agreements --accept-source-agreements
    if %ERRORLEVEL% NEQ 0 (
        echo ERROR: No se pudo instalar Python automaticamente.
        echo Descargalo de https://python.org marcando "Add to PATH".
        pause & exit /b 1
    )
    echo Python instalado. Vuelve a ejecutar el instalador.
    pause & exit /b 0
)
echo [OK] Python detectado

REM ── Dependencias ──────────────────────────────────────────
echo Instalando dependencias...
pip install requests --quiet
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: No se pudieron instalar las dependencias.
    pause & exit /b 1
)
echo [OK] Dependencias instaladas

REM ── Compilar exe ──────────────────────────────────────────
echo Compilando tracker.exe...
pip install pyinstaller --quiet
if exist "%~dp0mtIco.ico" (
    python -m PyInstaller --noconsole --onefile --icon="%~dp0mtIco.ico" tracker.py --distpath . --workpath build --specpath build
) else (
    python -m PyInstaller --noconsole --onefile tracker.py --distpath . --workpath build --specpath build
)
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Fallo la compilacion con PyInstaller.
    pause & exit /b 1
)
echo [OK] tracker.exe generado

REM ── Registrar en el Programador de tareas ─────────────────
REM Nota: se ejecuta como el usuario actual (no SYSTEM) para que
REM la Windows Location API funcione correctamente.
echo Registrando tarea en el Programador de tareas...
schtasks /delete /tn "ManageTracking" /f >nul 2>&1
schtasks /create /tn "ManageTracking" /tr "%~dp0tracker.exe" /sc ONLOGON /delay 0001:00 /rl HIGHEST /f
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: No se pudo registrar la tarea.
    pause & exit /b 1
)
echo [OK] Tarea registrada (se ejecuta al iniciar sesion)

REM ── Ejecutar ahora ────────────────────────────────────────
echo Iniciando tracker...
schtasks /run /tn "ManageTracking"

echo.
echo =========================================================
echo  Instalacion completada.
echo  El tracker arranca con Windows y envia ubicacion cada hora.
echo  Log: %~dp0tracker.log
echo =========================================================
pause
