@echo off
REM Menjalankan server PHP (pengganti Apache Laragon) di http://localhost:8080
REM Endpoint: http://localhost:8080/pertemuan_1/mahasiswa.php
cd /d "%~dp0.."
php -S localhost:8080 -t .
