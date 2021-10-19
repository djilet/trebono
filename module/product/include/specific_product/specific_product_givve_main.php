<?php

class SpecificProductGivveMain extends AbstractSpecificProduct
{
    public function SpecificProductGivveMain()
    {
        $this->monthlyPriceOptionCode = OPTION__GIVVE__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__GIVVE__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__GIVVE__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__GIVVE__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}