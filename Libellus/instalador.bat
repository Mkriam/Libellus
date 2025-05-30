@echo off
setlocal

REM Cambiar al directorio donde está este script
cd /d "%~dp0"

REM Iniciar Docker Desktop si no está en ejecución
tasklist /FI "IMAGENAME eq Docker Desktop.exe" | find /I "Docker Desktop.exe" >nul
if errorlevel 1 (
    echo Iniciando Docker Desktop...
    start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
)

REM Esperar a que Docker esté listo (máximo 60 segundos)
echo Esperando a que Docker esté listo...
set DOCKER_READY=0
for /L %%i in (1,1,30) do (
    docker info >nul 2>&1
    if not errorlevel 1 (
        set DOCKER_READY=1
        goto docker_ready
    )
    timeout /t 2 >nul
)
echo Docker no respondió a tiempo. Abortando...
exit /b

:docker_ready
echo Docker está listo.

REM Levantar contenedores
echo Iniciando contenedores...
docker compose up -d

REM Esperar 5 segundos luego de levantar los contenedores
echo Esperando 5 segundos para asegurar que los servicios se inicien...
timeout /t 5 >nul

REM Definir URLs
set "URL1=https://localhost:443"

REM Buscar navegador disponible
set "BROWSER_PATH="

REM Buscar Microsoft Edge
if exist "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" (
    set "BROWSER_PATH=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
)

REM ABRIR PRIMERA URL (8080)
echo Abriendo primera URL: %URL1%
if defined BROWSER_PATH (
    start "" "%BROWSER_PATH%" --new-window --app=%URL1%
) else (
    start %URL1%
)

REM Esperar 5 segundos antes de abrir la segunda URL
echo Esperando 5 segundos antes de abrir la siguiente URL...
timeout /t 5 >nul

REM ABRIR SEGUNDA URL (443)
echo Abriendo segunda URL: %URL2%
if defined BROWSER_PATH (
    start "" "%BROWSER_PATH%" --new-window --app=%URL2%
) else (
    start %URL2%
)

:skip_wait
echo Por favor, presiona una tecla para cerrar Docker manualmente cuando termine.
pause

echo Deteniendo contenedores...
docker compose down

echo Cerrando Docker Desktop...
taskkill /IM "Docker Desktop.exe" /F >nul 2>&1

echo Proceso finalizado.
endlocal