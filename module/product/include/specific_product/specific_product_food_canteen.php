<?php

class SpecificProductFoodCanteen extends AbstractSpecificProduct
{
    public function SpecificProductFoodCanteen()
    {
        $this->monthlyPriceOptionCode = OPTION__FOOD__CANTEEN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__FOOD__CANTEEN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__FOOD__CANTEEN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__FOOD__CANTEEN__IMPLEMENTATION_DISCOUNT;
    }
}