@echo off

rem ... creamos las variables que usaremos como path para llamar a php y a beemaker.php
set pathPHP=C:\laragon\bin\php\php-8.4.7-Win32-vs17-x64
set pathBEEMAKER=C:\laragon\www\beemaker\beemaker

rem ... creamos la variable de entorno donde está este .bat para en futuras ocasiones poder llamar a este .bat sin necesidad de poner su path
rem ... setx path "%path%;%pathBEEMAKER%" ... no funciona bien ... no se queda guardado como variable de entorno después de ejecutar el .bat

%pathPHP%\php %pathBEEMAKER%\beemaker.php %1