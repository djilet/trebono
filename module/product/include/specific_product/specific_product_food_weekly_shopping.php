<?php

class SpecificProductFoodWeeklyShopping extends AbstractSpecificProduct
{
    public function SpecificProductFoodWeeklyShopping()
    {
        $this->monthlyPriceOptionCode = OPTION__FOOD__WEEKLY_SHOPPING__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__FOOD__WEEKLY_SHOPPING__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__FOOD__WEEKLY_SHOPPING__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__FOOD__WEEKLY_SHOPPING__IMPLEMENTATION_DISCOUNT;
    }
}