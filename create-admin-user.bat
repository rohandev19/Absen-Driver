@echo off
echo ========================================
echo Create Admin User
echo ========================================
echo.

set /p admin_name="Enter admin name (default: Admin): "
if "%admin_name%"=="" set admin_name=Admin

set /p admin_email="Enter admin email (default: admin@example.com): "
if "%admin_email%"=="" set admin_email=admin@example.com

set /p admin_password="Enter admin password (default: admin123): "
if "%admin_password%"=="" set admin_password=admin123

echo.
echo Creating admin user with:
echo - Name: %admin_name%
echo - Email: %admin_email%
echo - Password: %admin_password%
echo.

php artisan tinker --execute="$user = new \App\Models\User(); $user->name = '%admin_name%'; $user->email = '%admin_email%'; $user->password = bcrypt('%admin_password%'); $user->role = 'admin'; $user->save(); echo 'Admin user created successfully!' . PHP_EOL; echo 'Email: ' . $user->email . PHP_EOL; echo 'Password: %admin_password%' . PHP_EOL;"

echo.
echo ========================================
echo Admin user created!
echo ========================================
echo.
echo You can now login at:
echo http://127.0.0.1:8000/admin/login
echo.
echo Credentials:
echo Email: %admin_email%
echo Password: %admin_password%
echo.
pause
