<?php

class SpecificProductGiftMain extends AbstractSpecificProduct
{
    public function SpecificProductGiftMain()
    {
        $this->monthlyPriceOptionCode = OPTION__GIFT__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__GIFT__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__GIFT__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__GIFT__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}