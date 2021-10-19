<?php

class SpecificProductInternetAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductInternetAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__INTERNET__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__INTERNET__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__INTERNET__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__INTERNET__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}