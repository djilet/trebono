<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class AddArchiveColumnToInvoice extends AbstractMigration
{
    public function up()
    {
        $this->table("invoice")
        ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "N"])
        ->save();
    }
    
    public function down()
    {
        $this->table("invoice")
        ->removeColumn("archive")
        ->save();
    }
}
