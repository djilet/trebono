<?php

use Phinx\Migration\AbstractMigration;

class AdvancedSecurityOptionsSortOrder extends AbstractMigration
{
    public function up()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'food__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'food__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'benefit__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'benefit__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'internet__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'internet__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'ad__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'ad__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'recreation__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'recreation__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'mobile__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'mobile__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'gift__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'gift__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'bonus__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'bonus__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'transport__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'transport__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'child_care__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'child_care__advanced_security__monthly_discount'])
            ->execute();


        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'travel__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'travel__advanced_security__monthly_discount'])
            ->execute();
    }

    public function down()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'food__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '1')
            ->where(['code' => 'food__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'benefit__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '1')
            ->where(['code' => 'benefit__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'internet__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'internet__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'ad__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '1')
            ->where(['code' => 'internet__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'recreation__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'recreation__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'mobile__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'mobile__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'gift__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'gift__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'bonus__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '1')
            ->where(['code' => 'bonus__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'transport__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '2')
            ->where(['code' => 'transport__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'child_care__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '1')
            ->where(['code' => 'child_care__advanced_security__monthly_discount'])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '3')
            ->where(['code' => 'travel__advanced_security__implementation_price'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('sort_order', '1')
            ->where(['code' => 'travel__advanced_security__monthly_discount'])
            ->execute();
    }
}
