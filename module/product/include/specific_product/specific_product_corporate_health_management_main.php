<?php

class SpecificProductCorporateHealthManagementMain extends AbstractSpecificProduct
{
    public function SpecificProductCorporateHealthManagementMain()
    {
        $this->monthlyPriceOptionCode = OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}