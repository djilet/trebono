<?php


use Phinx\Migration\AbstractMigration;

class GlobalPriceValues2 extends AbstractMigration
{
    public function up()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('level_global', 'Y')
            ->where(['title' => 'Implementation fee'])
            ->execute();
    }

    public function down()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('level_global', 'N')
            ->where(['title' => 'Implementation fee'])
            ->execute();
    }
}
