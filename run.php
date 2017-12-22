<?php

use Dblencowe\Wealth\Wealth;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$currencyCode = getenv('BASE_CURRENCY');
$refresh = $argv[2] ?? false;
$lookupCurrencies = (array) json_decode(getenv('CURRENCIES'));
$locale = getenv('LOCALE');

$wealth = new Wealth($currencyCode, $lookupCurrencies, $locale, $refresh);
$wealth->run();
