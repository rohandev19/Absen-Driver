@echo off
echo ========================================
echo Troubleshooting Login Admin
echo ========================================
echo.

echo [1/5] Checking MySQL connection...
netstat -ano | findstr :3306
if %errorlevel% neq 0 (
    echo.
    echo ERROR: MySQL not running on port 3306
    echo Please start MySQL server first!
    echo.
    echo Solutions:
    echo - If using XAMPP: Open XAMPP Control Panel and start MySQL
    echo - If using MySQL Service: Run "net start MySQL80" as Administrator
    echo - If using Laragon: Open Laragon and click "Start All"
    echo.
    pause
    exit /b 1
)
echo OK: MySQL is running on port 3306
echo.

echo [2/5] Testing database connection...
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection OK';"
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Cannot connect to database
    echo Please check:
    echo - Database credentials in .env file
    echo - Database "absen_driver_db" exists
    echo - MySQL username and password are correct
    echo.
    pause
    exit /b 1
)
echo.

echo [3/5] Checking admin users...
php artisan tinker --execute="echo 'Total admin users: ' . DB::table('users')->where('role', 'admin')->count() . PHP_EOL;"
echo.

echo [4/5] Clearing Laravel cache...
php artisan cache:clear >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan view:clear >nul 2>&1
echo OK: All caches cleared
echo.

echo [5/5] All checks completed!
echo.
echo ========================================
echo Next Steps:
echo ========================================
echo 1. Start development server: php artisan serve
echo 2. Open browser: http://127.0.0.1:8000/admin/login
echo 3. Login with admin credentials
echo.
echo If you need to create admin user, run:
echo    php artisan tinker
echo Then paste this code:
echo    $user = new \App\Models\User();
echo    $user->name = 'Admin';
echo    $user->email = 'admin@example.com';
echo    $user->password = bcrypt('admin123');
echo    $user->role = 'admin';
echo    $user->save();
echo    exit;
echo.
pause
