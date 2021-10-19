<?php

class SpecificProductFoodDocumentPlausibility extends AbstractSpecificProduct
{
    public function SpecificProductFoodDocumentPlausibility()
    {
        $this->monthlyPriceOptionCode = OPTION__FOOD__DOCUMENT_PLAUSIBILITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__FOOD__DOCUMENT_PLAUSIBILITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__FOOD__DOCUMENT_PLAUSIBILITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__FOOD__DOCUMENT_PLAUSIBILITY__IMPLEMENTATION_DISCOUNT;
    }
}