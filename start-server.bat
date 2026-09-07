@echo off
title Akebono Approval Server
color 0A

echo.
echo  ╔════════════════════════════════════════════════════════════════════╗
echo  ║                    AKEBONO APPROVAL SERVER                         ║
echo  ╚════════════════════════════════════════════════════════════════════╝
echo.
echo  Starting Laravel Development Server...
echo  Server will be available at: http://127.0.0.1:8000
echo.
echo  Press Ctrl+C to stop the server
echo.

cd /d "e:\KERJAAN\akebono-approval"
php artisan serve --host=127.0.0.1 --port=8000

pause