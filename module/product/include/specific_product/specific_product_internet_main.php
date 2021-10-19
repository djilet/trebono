<?php

class SpecificProductInternetMain extends AbstractSpecificProduct
{
    public function SpecificProductInternetMain()
    {
        $this->monthlyPriceOptionCode = OPTION__INTERNET__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__INTERNET__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__INTERNET__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__INTERNET__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}