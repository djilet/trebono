<?php

class SpecificProductChildCareMain extends AbstractSpecificProduct
{
    public function SpecificProductChildCareMain()
    {
        $this->monthlyPriceOptionCode = OPTION__CHILD_CARE__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__CHILD_CARE__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}