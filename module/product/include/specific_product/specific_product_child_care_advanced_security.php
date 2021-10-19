<?php

class SpecificProductChildCareAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductChildCareAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}