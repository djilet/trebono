<?php

class SpecificProductBaseInterruption extends AbstractSpecificProduct
{
    public function SpecificProductBaseInterruption()
    {
        $this->monthlyPriceOptionCode = OPTION__BASE__INTERRUPTION__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__BASE__INTERRUPTION__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__BASE__INTERRUPTION__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__BASE__INTERRUPTION__IMPLEMENTATION_DISCOUNT;
    }
}