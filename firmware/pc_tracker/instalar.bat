@echo off
echo === ManageTracking - Instalador ===

REM ── Python ────────────────────────────────────────────
python --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Python no encontrado. Instalando...
    winget install Python.Python.3 --silent --accept-package-agreements --accept-source-agreements
    if %ERRORLEVEL% NEQ 0 (
        echo ERROR: No se pudo instalar Python automaticamente.
        echo Descargalo manualmente de https://python.org e instala marcando "Add to PATH".
        pause & exit /b 1
    )
    echo Python instalado. Reinicia el instalador.
    pause & exit /b 0
)
echo Python OK

REM ── Dependencias ──────────────────────────────────────
echo Instalando dependencias...
pip install requests --quiet
echo Dependencias OK

REM ── Compilar exe ──────────────────────────────────────
echo Compilando tracker.exe...
pip install pyinstaller --quiet
pyinstaller --noconsole --onefile tracker.py --distpath . --workpath build --specpath build
echo Compilado OK

REM ── Task Scheduler ────────────────────────────────────
echo Registrando tarea en Windows...
schtasks /delete /tn "ManageTracking" /f >nul 2>&1
schtasks /create /tn "ManageTracking" /tr "%~dp0tracker.exe" /sc ONSTART /delay 0001:00 /ru SYSTEM /f
schtasks /run /tn "ManageTracking"

echo.
echo Listo. El tracker arranca con Windows y envia ubicacion cada hora.
echo Log: %~dp0tracker.log
pause
