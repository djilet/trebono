<?php

class SpecificProductBonusMain extends AbstractSpecificProduct
{
    public function SpecificProductBonusMain()
    {
        $this->monthlyPriceOptionCode = OPTION__BONUS__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__BONUS__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__BONUS__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__BONUS__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}