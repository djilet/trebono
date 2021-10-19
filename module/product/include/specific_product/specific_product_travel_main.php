<?php

class SpecificProductTravelMain extends AbstractSpecificProduct
{
    public function SpecificProductTravelMain()
    {
        $this->monthlyPriceOptionCode = OPTION__TRAVEL__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__TRAVEL__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__TRAVEL__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__TRAVEL__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}
