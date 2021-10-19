<?php

class SpecificProductBonusAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductBonusAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__BONUS__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__BONUS__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__BONUS__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__BONUS__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}