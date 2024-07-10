@echo off
set OUTPUT_FILE=file_list.txt

rem Lösche die Ausgabedatei, falls sie bereits existiert
if exist "%OUTPUT_FILE%" del "%OUTPUT_FILE%"

rem Verzeichnisse, deren Dateien analysiert werden sollen
set "DIRECTORIES=C:\xampp\htdocs\laravel\app C:\xampp\htdocs\laravel\database C:\xampp\htdocs\laravel\resources C:\xampp\htdocs\laravel\routes"

rem Schleife durch jedes Verzeichnis und erfasse den Quellcode jeder Datei
for %%d in (%DIRECTORIES%) do (
    echo Scanning files in directory: %%d
    call :ProcessDirectory "%%d"
)

echo Dateien wurden erfolgreich in %OUTPUT_FILE% gespeichert.
pause
exit /b

:ProcessDirectory
rem Durchlaufe alle Dateien (und Unterordner) im angegebenen Verzeichnis
for /r %1 %%f in (*) do (
    echo File: %%f >> "%OUTPUT_FILE%"
    echo --------------------- >> "%OUTPUT_FILE%"
    type "%%f" >> "%OUTPUT_FILE%"
    echo. >> "%OUTPUT_FILE%"
    echo. >> "%OUTPUT_FILE%"
)
exit /b
