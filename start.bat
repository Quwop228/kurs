@echo off
chcp 65001 >nul
title Encyclopedia - Launcher
cd /d "%~dp0"

echo.
echo ============================================
echo   Online Encyclopedia - Dev Servers
echo ============================================
echo.

if not exist "vendor\autoload.php" (
    echo [!] vendor not found, running composer install...
    call composer install
)

if not exist "node_modules" (
    echo [!] node_modules not found, running npm install...
    call npm install
)

if not exist ".env" (
    echo [!] .env not found, copying from .env.example...
    copy .env.example .env
    php artisan key:generate
)

echo [*] Starting Laravel backend on http://127.0.0.1:8000
start "Laravel (artisan serve)" cmd /k "php artisan serve"

echo [*] Starting Vite dev server
start "Vite (npm run dev)" cmd /k "npm run dev"

timeout /t 3 /nobreak >nul
echo.
echo ============================================
echo   Both servers started in separate windows.
echo   Open http://127.0.0.1:8000 in your browser.
echo   Close this window or the server windows to stop.
echo ============================================
echo.
pause
