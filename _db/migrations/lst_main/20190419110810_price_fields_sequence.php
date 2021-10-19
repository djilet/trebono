<?php


use Phinx\Migration\AbstractMigration;

class PriceFieldsSequence extends AbstractMigration
{
    private $codeList;

    public function init()
    {
        $this->codeList = array(OPTION__BONUS__MAIN__MONTHLY_DISCOUNT, OPTION__CHILD_CARE__MAIN__MONTHLY_DISCOUNT, OPTION__GIFT__ADVANCED_SECURITY__MONTHLY_DISCOUNT, OPTION__GIVVE__MAIN__MONTHLY_DISCOUNT,
            OPTION__INTERNET__ADVANCED_SECURITY__MONTHLY_DISCOUNT, OPTION__MOBILE__ADVANCED_SECURITY__MONTHLY_DISCOUNT, OPTION__RECREATION__ADVANCED_SECURITY__MONTHLY_DISCOUNT,
            OPTION__TRANSPORT__ADVANCED_SECURITY__MONTHLY_DISCOUNT, OPTION__TRANSPORT__MAIN__MONTHLY_DISCOUNT, OPTION__TRAVEL__MAIN__MONTHLY_DISCOUNT);
    }

    public function up()
    {
        foreach ($this->codeList as $code)
        {
            $builder = $this->getQueryBuilder();
            $builder
                ->update('option')
                ->set('sort_order', '2')
                ->where(['code' =>$code])
                ->execute();
        }
    }

    public function down()
    {
        foreach ($this->codeList as $code)
        {
            $builder = $this->getQueryBuilder();
            $builder
                ->update('option')
                ->set('sort_order', '1')
                ->where(['code' =>$code])
                ->execute();
        }
    }
}
