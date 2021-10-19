<?php

class SpecificProductAdAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductAdAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__AD__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__AD__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__AD__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__AD__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}