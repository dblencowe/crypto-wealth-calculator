<?php

namespace Dblencowe\Wealth;

use Dblencowe\Wealth\Exceptions\CurrencyException;
use GuzzleHttp\Client;
use LucidFrame\Console\ConsoleTable;

class Wealth
{
    private $lookupCurrencies;
    private $locale;
    private $client;
    private $baseCurrency;
    private $currencies;

    public function __construct(string $baseCurrency = 'GBP', $lookupCurrencies, $locale = 'en_GB', bool $refresh = false)
    {
        if (empty($lookupCurrencies)) {
            throw new CurrencyException('Please specify currencies to lookup');
        }

        $this->baseCurrency = strtoupper($baseCurrency);
        $this->lookupCurrencies = $lookupCurrencies;
        $this->locale = $locale;
        $this->client = new Client([
            'base_uri' => 'https://min-api.cryptocompare.com',
        ]);
        $this->loadCurrencies();

        if ($refresh) {
            $this->getCurrencies();
        }
    }

    public function __destruct()
    {
        $currencies = [];
        foreach ($this->currencies as $code => $curr) {
            $currencies[$code] = $curr;
        }
        file_put_contents(__DIR__ . '/../storage/currencies.json', json_encode($currencies));
    }

    private function loadCurrencies()
    {
        $file = __DIR__ . '/../storage/currencies.json';
        if (!is_file(__DIR__ . '/../storage/currencies.json')) {
            $this->getCurrencies();

            return;
        }

        $currencies = json_decode(file_get_contents($file));
        foreach ($currencies as $currencyCode => $currency) {
            $this->currencies[$currencyCode] = new Currency($currencyCode, $currency->latestRate, $currency->balance, $currency->wealth);
        }
    }

    private function getCurrencies()
    {
        $response = $this->client->request('GET', "/data/price?fsym={$this->baseCurrency}&tsyms="  . implode(',', array_keys($this->lookupCurrencies)));

        $responseBody = json_decode($response->getBody()->getContents());
        if ($response->getStatusCode() !== 200 || $responseBody->Response === 'Error') {
            throw new CurrencyException('Error fetching currencies: ' . $responseBody->Message);
        }

        foreach ($responseBody as $currencyCode => $rate) {
            if (!is_float($rate)) {
                throw new CurrencyException($currencyCode . ' balance must be represented as a float');
            }

            if (isset($this->currencies[$currencyCode]) && is_object($this->currencies[$currencyCode]) && get_class($this->currencies[$currencyCode]) === Currency::class) {
                /** @var Currency $curr */
                $curr = $this->currencies[$currencyCode];
                $curr->setLatestRate($rate)
                     ->setBalance($this->lookupCurrencies[$currencyCode]);
            } else {
                $this->currencies[$currencyCode] = new Currency($currencyCode, $rate, $this->lookupCurrencies[$currencyCode]);
            }
        }
    }

    public function run()
    {
        setlocale(LC_MONETARY, $this->locale);

        $table = new ConsoleTable();
        $table->setHeaders(['Currency', 'Current Wealth', 'Change']);

        /** @var Currency $currency */
        foreach ($this->currencies as $currency) {
            $balance = money_format('%.2n', $currency->wealth());
            $change = money_format('%.2n', $currency->change());
            $table->addRow([$currency->getCode(), $balance, $this->outputChange($currency, $change)]);
        }

        $table->setPadding(3)->display();
    }

    private function outputChange(Currency $currency, string $display)
    {
        $fg = '1;31';
        if ((float) $currency->change() >= 0) {
            $fg = '1;32';
        }

        return sprintf("\e[%sm%s\e[0m", $fg, $display);
    }
}
