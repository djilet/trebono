<?php

class SpecificProductFoodMain extends AbstractSpecificProduct
{
    public function SpecificProductFoodMain()
    {
        $this->monthlyPriceOptionCode = OPTION__FOOD__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__FOOD__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__FOOD__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__FOOD__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}