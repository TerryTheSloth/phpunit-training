<?php

interface IExchangeRatesService {
    public function getExchangeRate(string $currencyFrom, string $currencyTo, int $timestamp) : float;
}

final class Currencies {
    const USD = 'usd';
    const EUR = 'eur';
    const PLN = 'pln';
}

final class Converter {
    const AllowedCurrencies = array (
        Currencies::USD, 
        Currencies::EUR, 
        Currencies::PLN
    );

    private $currencyFrom;
    private $currencyTo;
    private $exchangeRateService;

    public function __construct(string $currFrom, string $currTo, IExchangeRatesService $exchangeRateService) {
        if (!$this->areCurrenciesValid($currFrom, $currTo)) {
            throw new Exception('Currencies are not valid!');
        }

        $this->currencyFrom = $currFrom;
        $this->currencyTo = $currTo;
        $this->exchangeRateService = $exchangeRateService;
    }

    public function convert(float $amount, $strategy = PHP_ROUND_HALF_UP ) {
        $rate = $this->getParam();
        $converted = round($amount * $rate, 2, $strategy);

        return $converted;
    }

    private function getParam() {
        return $this->exchangeRateService->getExchangeRate(
            $this->currencyFrom, $this->currencyTo, time());
    }

    private function areCurrenciesValid($curr_in, $curr_out) {
        return in_array($curr_in, self::AllowedCurrencies, true) && 
            in_array($curr_out, self::AllowedCurrencies, true);
    }
}