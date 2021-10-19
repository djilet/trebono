<?php

class SpecificProductBaseMain extends AbstractSpecificProduct
{
    public function SpecificProductBaseMain()
    {
        $this->monthlyPriceOptionCode = OPTION__BASE__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__BASE__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__BASE__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__BASE__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}