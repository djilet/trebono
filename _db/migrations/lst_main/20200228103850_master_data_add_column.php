<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class MasterDataAddColumn extends AbstractMigration
{
    public function up()
    {
        $this->table("master_data")->addColumn("new", Literal::from("flag"), ["null" => false, "default" => "Y"])->save();
    }

    public function down()
    {
        $this->table("master_data")->removeColumn("new")->save();
    }
}
