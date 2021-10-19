<?php

class SpecificProductMobileAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductMobileAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__MOBILE__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__MOBILE__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__MOBILE__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__MOBILE__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}