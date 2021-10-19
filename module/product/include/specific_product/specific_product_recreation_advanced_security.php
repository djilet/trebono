<?php

class SpecificProductRecreationAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductRecreationAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__RECREATION__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__RECREATION__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__RECREATION__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__RECREATION__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}