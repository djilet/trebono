<?php

class SpecificProductTransportMain extends AbstractSpecificProduct
{
    public function SpecificProductTransportMain()
    {
        $this->monthlyPriceOptionCode = OPTION__TRANSPORT__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__TRANSPORT__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__TRANSPORT__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__TRANSPORT__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}