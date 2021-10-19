<?php

class SpecificProductRecreationMain extends AbstractSpecificProduct
{
    public function SpecificProductRecreationMain()
    {
        $this->monthlyPriceOptionCode = OPTION__RECREATION__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__RECREATION__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__RECREATION__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__RECREATION__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}