<?php

class SpecificProductGiftAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductGiftAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__GIFT__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__GIFT__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__GIFT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__GIFT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}