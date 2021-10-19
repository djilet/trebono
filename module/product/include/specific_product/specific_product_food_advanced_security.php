<?php

class SpecificProductFoodAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductFoodAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__FOOD__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__FOOD__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__FOOD__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__FOOD__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}