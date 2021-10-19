<?php

class SpecificProductTransportAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductTransportAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__TRANSPORT__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__TRANSPORT__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__TRANSPORT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__TRANSPORT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}