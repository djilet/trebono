<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class AgreementsNewOnly extends AbstractMigration
{
    public function up()
    {
        $this->table("agreements_history")
            ->addColumn("new_only", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();
    }

    public function down()
    {
        $this->table("agreements_history")
            ->removeColumn("new_only")
            ->save();
    }
}
