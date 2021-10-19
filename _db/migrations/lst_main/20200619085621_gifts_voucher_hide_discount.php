<?php

use Phinx\Migration\AbstractMigration;

class GiftsVoucherHideDiscount extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE option SET level_global='N' WHERE code IN ('".OPTION__GIFT_VOUCHER__MAIN__MONTHLY_DISCOUNT."', '".OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT."')");
    }

    public function down()
    {
        $this->execute("UPDATE option SET level_global='Y' WHERE code IN ('".OPTION__GIFT_VOUCHER__MAIN__MONTHLY_DISCOUNT."', '".OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT."')");
    }
}
