<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
* @covers Converter
 */
final class ConverterTest extends TestCase
{
	public function testCanBeCreatedWithValidCurrencies() {
        $mockService = $this->createMock(IExchangeRatesService::class);

        $mockService->method('getExchangeRate');//->willReturn(0);
		$converter = new Converter(Currencies::USD, Currencies::PLN, $mockService);
		
		$this->assertContains(
		            Currencies::USD,
		            Converter::AllowedCurrencies
		        );
		
		$this->assertContains(
		            Currencies::PLN,
		            Converter::AllowedCurrencies
		        );
		
		$this->assertInstanceOf(
		            Converter::class,
		            $converter
		        );
	}
	
	
	public function testThrowsAnExceptionWithInvalidCurrencies() {
		$this->expectExceptionMessage('Currencies are not valid!');

        $mockService = $this->createMock(IExchangeRatesService::class);

		$converter = new Converter('gbp', Currencies::PLN, $mockService);
	}
	
	public function testCallsExchangeRatesServiceWithCorrectParameters() {
		$currencyFrom = Currencies::USD;
		$currencyTo = Currencies::PLN;
		$timestamp = time();
		$amount = 2;
		
		$mockService = $this->createMock(IExchangeRatesService::class);
		
		$mockService->expects($this->once())
		                    ->method('getExchangeRate')
		                    ->with(
		                        $this->equalTo($currencyFrom),
		                        $this->equalTo($currencyTo),
		                        $this->greaterThanOrEqual($timestamp)
		                    );
		
		$converter = new Converter($currencyFrom, $currencyTo, $mockService);
		
		$result = $converter->convert($amount);
	}

    public function testCalculatesResultAsAmountTimesExchangeRate() {
        $currencyFrom = Currencies::USD;
		$currencyTo = Currencies::PLN;
		$amount = 3;
        $mockService = $this->createMock(IExchangeRatesService::class);
        $exchangeRates = [ 0.5, 1.0, 2.333 ];
		
        $mockService->method('getExchangeRate')
		            ->will($this->onConsecutiveCalls(...$exchangeRates));

        $currenciesConverter = new Converter($currencyFrom, $currencyTo, $mockService);

        foreach ($exchangeRates as $key => $value) {
            $converted = round($amount * $value, 2, PHP_ROUND_HALF_UP);
            echo "test: ".$converted;
            $this->assertEquals($converted, $currenciesConverter->convert($amount));
        }            
    }
	
	public function testApproximatesResultsAccordingToGivenStrategy() {
		$currencyFrom = Currencies::USD;
		$currencyTo = Currencies::PLN;
		$amount = 3;
		$exchangeRate = 1.125;
		$converted = $amount * $exchangeRate;
		$mockService = $this->createMock(IExchangeRatesService::class);
		
        $mockService->method('getExchangeRate')
		            ->willReturn($exchangeRate);
		
		$converter = new Converter($currencyFrom, $currencyTo, $mockService);
		
		$approxUp = $converter->convert($amount);
		$approxDown = $converter->convert($amount, PHP_ROUND_HALF_DOWN);
		
		$this->assertEquals(
		            round($converted, 2, PHP_ROUND_HALF_UP),
		            $approxUp
		        );
		
		$this->assertEquals(
		            round($converted, 2, PHP_ROUND_HALF_DOWN),
		            $approxDown
		        );
	}
}
