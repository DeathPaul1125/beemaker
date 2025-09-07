<?php

use beemaker\Install;
use beemaker\Utils;

if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ser ejecutado desde la línea de comandos.");
}

include $_composer_autoload_path ?? __DIR__ . '/vendor/autoload.php';

$app = new beemaker($argv);

final class Beemaker
{
    const VERSION = '1.0';
    const OK = " -> OK.\n";

    public function __construct($argv)
    {
        if (count($argv) < 2) {
            $this->help();
            return;
        }

        Utils::setFolder(__DIR__);

        switch ($argv[1]) {
            case 'install':
                $install = new Install();
                $install->newInstalation();
                break;

            case 'help':
            default:
                $this->help();
                break;
        }
    }

    private function help(): void
    {
        echo('BeeFramework Maker v' . self::VERSION . "\n\n"
                . "Uso:\n"
                . "$ beemaker install\n"
                . "$ beemaker model\n"
                . "$ beemaker controller\n\n");
    }
}