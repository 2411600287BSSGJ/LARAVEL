@echo off
title Gym Inventory System - Setup & Start
color 0A

echo ============================================
echo   Gym Inventory System - Auto Setup
echo ============================================
echo.

:: ---- Step 1: Check MySQL is running ----
echo [1/4] Checking MySQL connection...
D:\xampp\mysql\bin\mysql.exe -u root -e "SELECT 1;" >nul 2>&1
IF ERRORLEVEL 1 (
    echo.
    echo  ERROR: MySQL is not running!
    echo  Please start Apache and MySQL in XAMPP Control Panel first.
    echo.
    pause
    exit /b 1
)
echo       MySQL is running. OK

:: ---- Step 2: Create database if not exists ----
echo.
echo [2/4] Creating database "inventory_db" if it does not exist...
D:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
IF ERRORLEVEL 1 (
    echo  ERROR: Could not create database.
    pause
    exit /b 1
)
echo       Database ready. OK

:: ---- Step 3: Run migrations and seeders ----
echo.
echo [3/4] Running migrations and seeders...
php artisan migrate --force 2>&1
IF ERRORLEVEL 1 (
    echo  Migration failed. Trying fresh migration...
    php artisan migrate:fresh --seed --force 2>&1
) ELSE (
    php artisan db:seed --force 2>&1
)
echo       Database setup complete. OK

:: ---- Step 4: Start Laravel dev server ----
echo.
echo [4/4] Starting Laravel development server...
echo.
echo ============================================
echo   App running at: http://127.0.0.1:8000
echo   Login:  admin@gym.com / password
echo   Press Ctrl+C to stop the server
echo ============================================
echo.
php artisan serve
