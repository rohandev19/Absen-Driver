@echo off
echo ========================================
echo   QUICK TEST - Sistem Absensi Driver
echo ========================================
echo.
echo Menjalankan test cepat...
echo.

REM Clear cache
php artisan config:clear
php artisan cache:clear

REM Run tests
php artisan test --parallel --stop-on-failure

echo.
echo ========================================
echo Test selesai!
echo ========================================
pause
