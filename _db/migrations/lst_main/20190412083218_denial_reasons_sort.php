<?php


use Phinx\Migration\AbstractMigration;

class DenialReasonsSort extends AbstractMigration
{
    public function up()
    {
        $this->table("config")
            ->addColumn("sort_order", "integer", ["null" => true, "default" => 0])
            ->save();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('config')
            ->set('sort_order', '1')
            ->where(['code' => 'receipt_autodeny_day_limit'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('config')
            ->set('sort_order', '2')
            ->where(['code' => 'receipt_autodeny_integrity_check'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('config')
            ->set('sort_order', '3')
            ->where(['code' => 'receipt_autodeny_month_limit'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('config')
            ->set('sort_order', '4')
            ->where(['code' => 'receipt_autodeny_old_receipt'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('config')
            ->set('sort_order', '5')
            ->where(['code' => 'receipt_autodeny_week_limit'])
            ->execute();
        $builder = $this->getQueryBuilder();
        $builder
            ->update('config')
            ->set('sort_order', '6')
            ->where(['code' => 'receipt_autodeny_year_limit'])
            ->execute();

    }

    public function down()
    {
        $this->table("config")
            ->removeColumn("sort_order")
            ->save();
    }
}
