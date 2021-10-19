<?php

class SpecificProductFoodVoucherMain extends AbstractSpecificProduct
{
    public function SpecificProductFoodVoucherMain()
    {
        $this->monthlyPriceOptionCode = OPTION__FOOD_VOUCHER__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__FOOD_VOUCHER__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}