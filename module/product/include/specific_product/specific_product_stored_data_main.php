<?php

class SpecificProductStoredDataMain extends AbstractSpecificProduct
{
    public function SpecificProductStoredDataMain()
    {
        $this->monthlyPriceOptionCode = OPTION__STORED_DATA__MAIN__MONTHLY_PRICE;
        $this->quarterlyPriceOptionCode = OPTION__STORED_DATA__MAIN__QUARTERLY_PRICE;
        $this->yearlyPriceOptionCode = OPTION__STORED_DATA__MAIN__YEARLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__STORED_DATA__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__STORED_DATA__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__STORED_DATA__MAIN__IMPLEMENTATION_DISCOUNT;
        $this->frequencyOptionCode = OPTION__STORED_DATA__MAIN__FREQUENCY;
    }
}