@echo off
echo ========================================
echo Starting Laravel Queue Worker
echo ========================================
echo.
echo Queue Connection: database
echo Press Ctrl+C to stop the worker
echo.
php artisan queue:work --tries=3 --timeout=90
