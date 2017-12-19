<?php

use Dblencowe\Wealth\Wealth;

require_once __DIR__ . '/vendor/autoload.php';

$currencyCode = strtoupper($argv[1]) ?? 'GBP';
$refresh = $argv[2] ?? false;

$wealth = new Wealth($currencyCode, $refresh);
$wealth->run();
