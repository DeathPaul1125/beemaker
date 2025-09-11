<?php

namespace beemaker;

use beemaker\Utils;

class Model
{
	const OK = " -> OK.\n";

	public function createModel()
	{

		$model = readline("\033[32mNombre del\033[0m \033[33mModelo\033[0m: ");

		$tableName = readline("\033[32mNombre de la\033[0m \033[33mTabla\033[0m: ");

		//abrimos el archivo Model.txt dentro de la carpeta template
		$template = file_get_contents(__DIR__.'\..\template\modelTemplate.txt');

		if (empty($model)) {
			Utils::echo("El nombre del modelo no puede estar vacío.\n");
			return;
		}
		//reemplazamos en el template la palabra Model por el nombre del modelo
		$template = str_replace("[[MODEL]]", $model, $template);
		$template = str_replace("[[MODEL_TABLE]]", $tableName, $template);
		//creamos el archivo Model.php dentro de la carpeta app/models
		$basePath = getcwd();
		$modelPath = $basePath.'/app/models/'.$model.'.php';
		$file = fopen($modelPath, "w");
		if ($file === false) {
			Utils::echo("No se pudo crear el archivo en la ruta: $modelPath\n");
			return;
		}
		fwrite($file, $template);
		//ahora solicitamos los atributos del modelo y el formato

		$schema = [];
		$variables = [];
		while (true) {
			//agregar datos por defecto, id, created_at, updated_at
			//consultamos si desea agregar un atributo
			$default = readline("¿Desea agregar un atributo por defecto? (s/n): ");
			if (strtolower($default) === 's' && !isset($defaultAdded)) {
				$attributeLine = "    public \$id;\n    public \$created_at;\n    public \$updated_at;\n";
				$formatLine = "        \$table->add_column('id' ,'INT', 11, false, true, true);\n        \$table->add_column('created_at' ,'TIMESTAMP');\n        \$table->add_column('updated_at' ,'TIMESTAMP');\n";
				$template = str_replace("[[MODEL_VARIABLES_DEFECT]]", $attributeLine, $template);
				$schema[] = $formatLine;
				$defaultAdded = true;
				continue;
			} elseif (strtolower($default) === 'n') {
				continue;
			}

			$attribute = readline("\033[32mNombre del atributo (null para salir): \033[0m");
			if (empty($attribute)) {
				break;
			}
			$format = readline("Ingrese el formato del atributo (ej: 's' para string, 'i' para integer, 'd' para double): ");
			//reemplazamos en el archivo el atributo y el formato
			$attributeLine = "    public \$$attribute;\n";
			$formatLine = "        \$table->add_column('".$attribute."' ,'".$format."');\n";
			//necesito un array con los formatos de los atributos
			$schema[] = $formatLine;
			$variables[] = $attributeLine;
		}
		$template = str_replace("[[MODEL_VARIABLES]]", implode("", $variables), $template);
		//ahora insertamos las lineas de los formatos antes de la linea que contiene "[[TABLE_SCHEMA]]"
		$template = str_replace("[[TABLE_SCHEMA]]", implode("", $schema), $template);

		//reescribimos el archivo con los nuevos datos
		ftruncate($file, 0);
		rewind($file);
		fwrite($file, $template);
		fclose($file);
		Utils::echo("Modelo '$model' creado en la ruta: $modelPath". self::OK);
	}
}