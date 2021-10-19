<?php

class SpecificProductBenefitAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductBenefitAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__BENEFIT__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__BENEFIT__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__BENEFIT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__BENEFIT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}