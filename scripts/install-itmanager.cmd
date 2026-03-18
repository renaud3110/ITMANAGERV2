@echo off
REM IT Manager - Installation agent + moniteur
REM Double-cliquez ou exécutez en tant qu'administrateur
REM Ce script lance le script PowerShell install-itmanager.ps1

powershell -ExecutionPolicy Bypass -File "%~dp0install-itmanager.ps1"
if errorlevel 1 (
    echo.
    pause
    exit /b 1
)
echo.
pause
