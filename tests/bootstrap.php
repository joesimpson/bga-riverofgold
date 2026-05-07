<?php

declare(strict_types=1);

require_once __DIR__ . '/../modules/php/constants.inc.php';
require_once __DIR__ . '/../modules/php/Models/Enums.php';
require_once __DIR__ . '/stubs/BgaFrameworkGlobalStubs.php';
require_once __DIR__ . '/Utils/TestDatas.php';
require_once __DIR__ . '/stubs/BgaFrameworkStubs.php';
require_once __DIR__ . '/../riverofgoldnightmarket.game.php';
require_once __DIR__ . '/stubs/GameSpecificStub.php';
require_once __DIR__ . '/Utils/PHPUnitUtil.php';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Bga\\Games\\RiverOfGoldNightMarket\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = __DIR__ . '/../modules/php/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});