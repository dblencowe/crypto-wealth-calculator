<?php

namespace Dblencowe\Wealth;

use GuzzleHttp\Client;

class Wealth
{
    private $lookupCurrencies = [
        'BTC' => 0.0185722,
        'LTC' => 1,
        'ADA' => 1005.992000,
        'XLM' => 731.258,
        'ETH' => 0.55173161,
    ];
    private $locale = 'en_GB';
    private $client;
    private $baseCurrency;
    private $currencies;

    public function __construct(string $baseCurrency = 'GBP', bool $refresh = false)
    {
        $this->baseCurrency = $baseCurrency;
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
        $currencies = json_decode(file_get_contents(__DIR__ . '/../storage/currencies.json'));
        foreach ($currencies as $currencyCode => $currency) {
            $this->currencies[$currencyCode] = new Currency($currencyCode, $currency->latestRate, $currency->balance);
        }
    }

    private function getCurrencies()
    {
        $response = $this->client->request('GET', "/data/price?fsym={$this->baseCurrency}&tsyms="  . implode(',', array_keys($this->lookupCurrencies)));
        $currencies = json_decode($response->getBody()->getContents());

        foreach ($currencies as $currencyCode => $rate) {
            $this->currencies[$currencyCode] = new Currency($currencyCode, $rate, $this->lookupCurrencies[$currencyCode]);
        }
    }

    public function run()
    {
        setlocale(LC_MONETARY, $this->locale);
        /** @var Currency $currency */
        foreach ($this->currencies as $currency) {
            echo sprintf('%s: %s' . PHP_EOL, $currency->getCode(), money_format('%.2n', $currency->wealth()));
        }
    }
}
