<?php

class SpecificProductBenefitVoucherAdvancedSecurity extends AbstractSpecificProduct
{
    public function SpecificProductBenefitVoucherAdvancedSecurity()
    {
        $this->monthlyPriceOptionCode = OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT;
    }
}