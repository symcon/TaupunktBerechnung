<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class TaupunktValidationTest extends TestCaseSymconValidation
{
    public function testValidateTaupunktBerechnung(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateDewPointTemperatureCalculationModule(): void
    {
        $this->validateModule(__DIR__ . '/../DewPointTemperatureCalculation');
    }
}
