<?php

class SpecificProductBonusVoucherMain extends AbstractSpecificProduct
{
    public function SpecificProductBonusVoucherMain()
	{
	    $this->monthlyPriceOptionCode = OPTION__BONUS_VOUCHER__MAIN__MONTHLY_PRICE;
	    $this->monthlyDiscountOptionCode = OPTION__BONUS_VOUCHER__MAIN__MONTHLY_DISCOUNT;
	    $this->implementationPriceOptionCode = OPTION__BONUS_VOUCHER__MAIN__IMPLEMENTATION_PRICE;
	    $this->implementationDiscountOptionCode = OPTION__BONUS_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT;
	}
}