<?php

namespace beemaker;

class Install
{
	const OK = " -> OK.\n";
	const path = __DIR__ . '/../../';

	public function newInstalation()
	{
		//solicitamos la ruta de la instalacion
		$pathInstall = getcwd();
		if (!empty($pathInstall)) {
			$pathInstall = rtrim($pathInstall, '/') . '/';
			if (!is_dir($pathInstall)) {
				Utils::echo("La ruta no existe.\n");
				return;
			}

		}else{
			define('self::path', self::path);
		}


		//necesito pedir el nombre de la carpeta para instalar
		echo "Instalando BeeFramework...\n";
		$folder = readline("\033[32mNombre de la carpeta (default: beeframework): \033[0m");
		if (empty($folder)) {
			$folder = "beeframework";
		}else{
			$folder = trim($folder);
			//validamos si el folder existe
			if (is_dir(self::path . $folder)) {
				Utils::echo("La carpeta ya existe.\n");
				//return;
			}

			Utils::echo("Carpeta '$folder' creada." . self::OK);
			$git_installed = shell_exec("git --version");
			if (empty($git_installed)) {
				Utils::echo("Git no está instalado. Por favor, instálalo e inténtalo de nuevo.\n");
				return;
			}
			//clonamos el repositorio
			$clone = shell_exec("git clone https://github.com/Moxtrip69/Bee-Framework.git " . $pathInstall.$folder);
			if (empty($clone)) {
				//Utils::echo("Error al clonar el repositorio. Abortando.\n");
				//return;
			}

			//solicitar si se desea ejecutar el comando composer install
			$composer = readline("\033[32m¿Deseas ejecutar 'composer install' ahora? (s/n, default: n): \033[0m");
			if (strtolower($composer) === 's') {
				//verificamos si composer esta instalado
				$composer_installed = shell_exec("composer --version");
				if (empty($composer_installed)) {
					Utils::echo("Composer no está instalado. Por favor, instálalo e inténtalo de nuevo.\n");
					return;
				}

				//ejecutamos el comando composer install
				chdir($pathInstall.$folder.'/app');
				$composer_install = shell_exec("composer install");
				if (empty($composer_install)) {
					//Utils::echo("Error al ejecutar 'composer install'. Abortando.\n");
					//return;
				}

				Utils::echo("'composer install' ejecutado correctamente." . self::OK);
			} else {
				Utils::echo("Recuerda ejecutar 'composer install' en la carpeta '$folder' para instalar las dependencias." . self::OK);
			}
			//abrimos el archivo settings.php dentro de la carpeta app/core y buscamos el define('LDB_NAME'                , 'db_beeframework');
			//solicitamos el nombre de la base de datos
			$database = readline("\033[32mNombre de la base de datos (default: db_beeframework): \033[0m");
			if (empty($database)) {
				$database = "db_beeframework";
			}

			fopen($pathInstall.$folder.'/app/core/settings.php', 'r+');
			$settings = file_get_contents($pathInstall.$folder.'/app/core/settings.php');
			$settings = str_replace("define('LDB_NAME'                , 'db_beeframework');", "define('LDB_NAME'                , '$database');", $settings);
			//tambien reemplazamos la linea define('PREPROS'                 , true);    por define('PREPROS'                 , false);
			$settings = str_replace("define('PREPROS'                 , true);", "define('PREPROS'                 , false);", $settings);
			file_put_contents($pathInstall.$folder.'/app/core/settings.php', $settings);
			Utils::echo("Archivo settings.php actualizado con el nombre de la base de datos '$database'." . self::OK);

			//tambien sustituimos en el archivo app/core la constante define('DEV_PATH'     , '/beetest/');
			$settings = file_get_contents($pathInstall.$folder.'/app/config/bee_config.php');
			$settings = str_replace("define('DEV_PATH'     , '/Bee-Framework/'); // Ruta del proyecto en desarrollo después de htdocs o www", "define('DEV_PATH'     , '/$folder/');", $settings);
			file_put_contents($pathInstall.$folder.'/app/config/bee_config.php', $settings);
			Utils::echo("Archivo settings.php actualizado con el path de desarrollo '/$folder/'." . self::OK);

			//validamos si la base de datos existe, en caso contrario la creamos
			$db_host = readline("\033[32mHost de la base de datos (default: localhost): \033[0m");
			if (empty($db_host)) {
				$db_host = "localhost";
			}
			$db_user = readline("\033[32mUsuario de la base de datos (default: root): \033[0m");
			if (empty($db_user)) {
				$db_user = "root";
			}
			$db_pass = readline("\033[32mContraseña de la base de datos (default: vacía): \033[0m");
			//creamos la conexion
			$conn = new \mysqli($db_host, $db_user, $db_pass);
			//verificamos la conexion
			if ($conn->connect_error) {
				Utils::echo("Error de conexión a la base de datos: " . $conn->connect_error . "\n");
				return;
			}

			//creamos la base de datos
			$sql = "CREATE DATABASE IF NOT EXISTS $database";
			if ($conn->query($sql) === TRUE) {
				Utils::echo("Base de datos '$database' creada o ya existe." . self::OK);
			} else {
				Utils::echo("Error al crear la base de datos: " . $conn->error . "\n");
				return;
			}

			$conn->close();

			//dentro de la carpeta creada del proyecto, el montamos la base de datos db_beeframework.sql en la base creada
			$import_db = readline("\033[32m¿Deseas importar la base de datos inicial ahora? (s/n, default: s): \033[0m");
			if (strtolower($import_db) === 'n') {
				Utils::echo("Recuerda importar el archivo 'db_beeframework.sql' en la base de datos '$database'." . self::OK);
			} else {
				//importamos la base de datos
				$import = shell_exec("mysql -h $db_host -u $db_user " . (!empty($db_pass) ? "-p$db_pass" : "") . " $database < " . $pathInstall.$folder.'/db_beeframework.sql');
				if (empty($import)) {
					//Utils::echo("Error al importar la base de datos. Abortando.\n");
					//return;
				}

				Utils::echo("Base de datos importada correctamente en '$database'." . self::OK);
			}

			Utils::echo("Instalación completada. Puedes acceder a tu proyecto en la carpeta '$folder'.\n");

			//abrimos el proyecto en el navegador
			$open_browser = readline("\033[32m¿Deseas abrir el proyecto en el navegador ahora? (s/n, default: n): \033[0m");
			if (strtolower($open_browser) === 's') {
				//abrimos el navegador
				$open = shell_exec("start http://localhost/$folder");
				if (empty($open)) {
					//Utils::echo("Error al abrir el navegador. Abortando.\n");
					//return;
				}
				Utils::echo("Proyecto abierto en el navegador." . self::OK);

				//Preguntamos si desean abrir el proyecto en visual studio code
				$open_vscode = readline("\033[32m¿Deseas abrir el proyecto en Visual Studio Code ahora? (s/n, default: n): \033[0m");
				if (strtolower($open_vscode) === 's') {
					//abrimos el proyecto en visual studio code
					$open_code = shell_exec("code " . $pathInstall.$folder);
					if (empty($open_code)) {
						Utils::echo("Error al abrir Visual Studio Code. Abortando.\n");
						return;
					}
					Utils::echo("Proyecto abierto en Visual Studio Code." . self::OK);
				}

			} else {
				Utils::echo("Recuerda abrir el proyecto en el navegador en http://localhost/$folder" . self::OK);
			}
		}
	}
}