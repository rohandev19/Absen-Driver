@echo off
echo ========================================
echo   TESTING SUITE - SISTEM ABSENSI DRIVER
echo ========================================
echo.

:menu
echo Pilih jenis testing:
echo.
echo 1. Jalankan SEMUA Test
echo 2. API Tests (Mobile App)
echo 3. Web Tests (Admin Panel)
echo 4. Unit Tests (Business Logic)
echo 5. Security Tests
echo 6. Test dengan Coverage Report
echo 7. Test Parallel (Cepat)
echo 8. Keluar
echo.

set /p choice="Masukkan pilihan (1-8): "

if "%choice%"=="1" goto all_tests
if "%choice%"=="2" goto api_tests
if "%choice%"=="3" goto web_tests
if "%choice%"=="4" goto unit_tests
if "%choice%"=="5" goto security_tests
if "%choice%"=="6" goto coverage_tests
if "%choice%"=="7" goto parallel_tests
if "%choice%"=="8" goto end

echo Pilihan tidak valid!
goto menu

:all_tests
echo.
echo Menjalankan SEMUA Test...
echo.
php artisan test
goto continue

:api_tests
echo.
echo Menjalankan API Tests...
echo.
php artisan test --testsuite=Feature --filter=Api
goto continue

:web_tests
echo.
echo Menjalankan Web Tests...
echo.
php artisan test --testsuite=Feature --filter=Web
goto continue

:unit_tests
echo.
echo Menjalankan Unit Tests...
echo.
php artisan test --testsuite=Unit
goto continue

:security_tests
echo.
echo Menjalankan Security Tests...
echo.
php artisan test --testsuite=Feature --filter=Security
goto continue

:coverage_tests
echo.
echo Menjalankan Test dengan Coverage Report...
echo.
php artisan test --coverage-html coverage-report
echo.
echo Coverage report tersimpan di: coverage-report/index.html
goto continue

:parallel_tests
echo.
echo Menjalankan Test Parallel...
echo.
php artisan test --parallel
goto continue

:continue
echo.
echo ========================================
echo Test selesai!
echo ========================================
echo.
pause
goto menu

:end
echo.
echo Terima kasih!
exit
