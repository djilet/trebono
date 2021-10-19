<?php

class SpecificProductTravelAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductTravelAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__TRAVEL__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__TRAVEL__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__TRAVEL__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__TRAVEL__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}