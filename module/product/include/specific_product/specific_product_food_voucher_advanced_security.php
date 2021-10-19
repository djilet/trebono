<?php

class SpecificProductFoodVoucherAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductFoodVoucherAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}