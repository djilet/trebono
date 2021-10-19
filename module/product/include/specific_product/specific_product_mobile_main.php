<?php

class SpecificProductMobileMain extends AbstractSpecificProduct
{
    public function SpecificProductMobileMain()
    {
        $this->monthlyPriceOptionCode = OPTION__MOBILE__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__MOBILE__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__MOBILE__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__MOBILE__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}