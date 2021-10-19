<?php

class SpecificProductBenefitMain extends AbstractSpecificProduct
{
    public function SpecificProductBenefitMain()
    {
        $this->monthlyPriceOptionCode = OPTION__BENEFIT__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__BENEFIT__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__BENEFIT__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__BENEFIT__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}