@echo off
setlocal

REM Caminho para o php.ini (ajuste se necessário)
set PHP_INI="C:\wamp64\bin\apache\apache2.4.59\bin\php.ini"

REM Altera ou adiciona as configurações desejadas
powershell -Command "(Get-Content %PHP_INI%) -replace 'upload_max_filesize\s*=.*', 'upload_max_filesize = 1000M' | Set-Content %PHP_INI%"
powershell -Command "(Get-Content %PHP_INI%) -replace 'post_max_size\s*=.*', 'post_max_size = 1000M' | Set-Content %PHP_INI%"
powershell -Command "(Get-Content %PHP_INI%) -replace 'max_execution_time\s*=.*', 'max_execution_time = 300' | Set-Content %PHP_INI%"
powershell -Command "(Get-Content %PHP_INI%) -replace 'max_input_time\s*=.*', 'max_input_time = 300' | Set-Content %PHP_INI%"

echo Configurações aplicadas com sucesso!
pause
