<?php

namespace beemaker;

class Table
{
	const OK = " -> OK.\n";

	public function createTable()
	{
		$basePath = getcwd();
		//solicitamos el nombre de la tabla
		$table = readline("\033[32mNombre de la tabla: \033[0m");
		if (empty($table)) {
			Utils::echo("El nombre de la tabla no puede estar vacío.\n");
			return;
		}

		//Leemos los datos de la base de datos
		$content = file_get_contents($basePath.'/app/core/settings.php');
		//leemos las variables define('LDB_HOST'                , 'localhost');
		preg_match("/define\(\s*'LDB_HOST'\s*,\s*'([^']*)'\s*\)/", $content, $host);
		preg_match("/define\(\s*'LDB_NAME'\s*,\s*'([^']*)'\s*\)/", $content, $name);
		preg_match("/define\(\s*'LDB_USER'\s*,\s*'([^']*)'\s*\)/", $content, $user);
		preg_match("/define\(\s*'LDB_PASS'\s*,\s*'([^']*)'\s*\)/", $content, $pass);

			$host = $host[1];
			$name = $name[1];
			$user = $user[1];
			$pass = $pass[1];

		//conectamos con la base de datos
		$conn = new \mysqli($host, $user, $pass, $name);
		if ($conn->connect_error) {
			Utils::echo("Error de conexión a la base de datos: " . $conn->connect_error . "\n");
			return;
		}
		//validamos si existe la tabla
		$sql = "SHOW TABLES LIKE '$table'";
		if ($conn->query($sql) === TRUE) {
			Utils::echo("La tabla '$table' ya existe.\n");
			return;
		}

		$tabletxt = $basePath."/".$table.".txt";
		$fp = fopen($tabletxt, "w");

		if ($fp === false) {
			die("No se pudo crear o abrir el archivo en la ruta: $tabletxt");
		}
		//ahora recorremos un ciclo hasta que el usuario ingrese un valor null
		while (true) {
			//solicitamos al usuario el nombre de la columna y el tipo de dato
			$column = readline("\033[32mNombre de la columna (null para salir): \033[0m");
			if (empty($column)) {
				break;
			}

			$tipo = readline("Ingrese el tipo de dato (ej: INT, VARCHAR(255), DATE): ");

			// Guardar columna
			$columnas[] = [
				'nombre' => $column,
				'tipo' => strtoupper($tipo)
			];
		}

		$sql = "CREATE TABLE $table (\n";
		foreach ($columnas as $i => $col) {
			$coma = ($i === array_key_last($columnas)) ? "" : ",";
			$sql .= "  {$col['nombre']} {$col['tipo']}{$coma}\n";
		}
		$sql .= ");";
		if ($conn->query($sql) === TRUE) {
			Utils::echo("Tabla '$table' creada con éxito." . self::OK);
		}
		fclose($fp);

// Calcular ancho máximo de columnas
		$maxNombre = max(array_map(fn($c) => strlen($c['nombre']), $columnas));
		$maxTipo   = max(array_map(fn($c) => strlen($c['tipo']), $columnas));

		$linea = "+" . str_repeat("-", $maxNombre + 2) . "+"
			. str_repeat("-", $maxTipo + 2) . "+\n";

// Encabezado
		echo $linea;
		echo "| " . str_pad("Columna", $maxNombre) . " | " . str_pad("Tipo", $maxTipo) . " |\n";
		echo $linea;

// Filas
		foreach ($columnas as $col) {
			echo "| " . str_pad($col['nombre'], $maxNombre) . " | " . str_pad($col['tipo'], $maxTipo) . " |\n";
		}
		echo $linea;
	}
}