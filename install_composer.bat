@echo off
echo ========================================
echo Composer Installation Helper
echo ========================================
echo.

REM Check if composer already exists
where composer >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo Composer is already installed!
    composer --version
    echo.
    echo Installing dependencies...
    composer install
    goto :end
)

echo Composer is NOT installed.
echo.
echo Please choose an option:
echo.
echo 1. Download Composer Installer (Recommended)
echo 2. Manual Installation Instructions
echo 3. Skip and use DOCX files instead (no installation needed)
echo.
set /p choice="Enter your choice (1-3): "

if "%choice%"=="1" (
    echo.
    echo Opening Composer download page...
    start https://getcomposer.org/download/
    echo.
    echo After installing Composer, run this script again.
    pause
    goto :end
)

if "%choice%"=="2" (
    echo.
    echo ========================================
    echo Manual Installation Steps:
    echo ========================================
    echo.
    echo 1. Download composer.phar from:
    echo    https://getcomposer.org/composer-stable.phar
    echo.
    echo 2. Save it to: C:\xampp\htdocs\Vishwacollab\composer.phar
    echo.
    echo 3. Then run: php composer.phar install
    echo.
    pause
    goto :end
)

if "%choice%"=="3" (
    echo.
    echo ========================================
    echo Using DOCX Files Instead
    echo ========================================
    echo.
    echo Good choice! DOCX files work without any installation.
    echo Just convert your PDF resume to DOCX format and upload it.
    echo.
    echo The resume parser will work automatically for DOCX files.
    echo.
    pause
    goto :end
)

:end
echo.
echo ========================================
echo Done!
echo ========================================

