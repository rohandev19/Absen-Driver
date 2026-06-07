@echo off
echo ========================================
echo   BUTTON TESTING - Website Buttons
echo ========================================
echo.

:menu
echo Pilih kategori button testing:
echo.
echo 1. Test SEMUA Button (Recommended)
echo 2. Admin Panel Buttons
echo 3. Customer Portal Buttons
echo 4. Export/Download Buttons
echo 5. Specific Button Test
echo 6. Keluar
echo.

set /p choice="Masukkan pilihan (1-6): "

if "%choice%"=="1" goto all_buttons
if "%choice%"=="2" goto admin_buttons
if "%choice%"=="3" goto customer_buttons
if "%choice%"=="4" goto export_buttons
if "%choice%"=="5" goto specific_button
if "%choice%"=="6" goto end

echo Pilihan tidak valid!
goto menu

:all_buttons
echo.
echo Menjalankan SEMUA Button Tests...
echo.
php artisan test --filter=Button
goto continue

:admin_buttons
echo.
echo Menjalankan Admin Panel Button Tests...
echo.
php artisan test --filter=ButtonInteractionTest
goto continue

:customer_buttons
echo.
echo Menjalankan Customer Portal Button Tests...
echo.
php artisan test --filter=CustomerButtonTest
goto continue

:export_buttons
echo.
echo Menjalankan Export/Download Button Tests...
echo.
php artisan test --filter=ExportDownloadButtonTest
goto continue

:specific_button
echo.
echo Contoh test yang tersedia:
echo - vehicle_list_add_button_works
echo - vehicle_list_delete_button_works
echo - maintenance_generate_alerts_button_works
echo - customer_approval_approve_button_works
echo - export_driver_history_button_works
echo.
set /p test_name="Masukkan nama test: "
echo.
echo Menjalankan test: %test_name%
echo.
php artisan test --filter=%test_name%
goto continue

:continue
echo.
echo ========================================
echo Test selesai!
echo ========================================
echo.
echo Dokumentasi lengkap: WEBSITE_BUTTON_TESTING.md
echo.
pause
goto menu

:end
echo.
echo Terima kasih!
exit
