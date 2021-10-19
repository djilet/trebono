<?php

class SpecificProductGiftVoucherMain extends AbstractSpecificProduct
{
    public function SpecificProductGiftVoucherMain()
    {
        $this->monthlyPriceOptionCode = OPTION__GIFT_VOUCHER__MAIN__MONTHLY_PRICE;
        $this->monthlyDiscountOptionCode = OPTION__GIFT_VOUCHER__MAIN__MONTHLY_DISCOUNT;
        $this->implementationPriceOptionCode = OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_PRICE;
        $this->implementationDiscountOptionCode = OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT;
    }
}