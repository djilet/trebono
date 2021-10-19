<?php

class SpecificProductFoodLumpSumTaxExamination extends AbstractSpecificProduct
{
    public function SpecificProductFoodLumpSumTaxExamination()
    {
        $this->monthlyPriceOptionCode = OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__IMPLEMENTATION_DISCOUNT;
    }
}