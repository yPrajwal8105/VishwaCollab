@echo off
echo ========================================
echo Installing Resume Parser Dependencies
echo ========================================
echo.

REM Check if composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Composer is not installed!
    echo Please install Composer from: https://getcomposer.org/download/
    echo.
    pause
    exit /b 1
)

echo Composer found. Installing dependencies...
echo.

composer install

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo Installation completed successfully!
    echo ========================================
    echo.
    echo Next steps:
    echo 1. Make sure database tables exist by visiting:
    echo    http://localhost/Vishwacollab/add_new_features_tables.php
    echo 2. Restart your web server (XAMPP)
    echo 3. Try uploading a resume
    echo.
) else (
    echo.
    echo ========================================
    echo Installation failed!
    echo ========================================
    echo.
    echo Please check the error messages above.
    echo.
)

pause

