<?php

class SpecificProductAdMain extends AbstractSpecificProduct
{
    public function SpecificProductAdMain()
    {
        $this->monthlyPriceOptionCode = OPTION__AD__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__AD__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__AD__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__AD__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}