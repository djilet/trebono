<?php

class SpecificProductBenefitVoucherMain extends AbstractSpecificProduct
{
    public function SpecificProductBenefitVoucherMain()
    {
        $this->monthlyPriceOptionCode = OPTION__BENEFIT_VOUCHER__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__BENEFIT_VOUCHER__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__BENEFIT_VOUCHER__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__BENEFIT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}