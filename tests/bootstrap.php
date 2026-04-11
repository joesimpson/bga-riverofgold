<?php

declare(strict_types=1);

require_once __DIR__ . '/../modules/php/constants.inc.php';
require_once __DIR__ . '/stubs/BgaFrameworkGlobalStubs.php';
require_once __DIR__ . '/Utils/TestDatas.php';
require_once __DIR__ . '/stubs/BgaFrameworkStubs.php';
require_once __DIR__ . '/../riverofgold.game.php';
require_once __DIR__ . '/stubs/GameSpecificStub.php';
require_once __DIR__ . '/Utils/PHPUnitUtil.php';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}