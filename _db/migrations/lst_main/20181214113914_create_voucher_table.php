<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreateVoucherTable extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher", ["id" => "voucher_id"])
        ->addColumn("employee_id", "integer", ["null" => false])
        ->addColumn("amount", "integer", ["null" => false])
        ->addColumn("created", "timestamp", ["null" => false])
        ->addColumn("created_user_id", "integer", ["null" => false])
        ->addColumn("voucher_date", "date", ["null" => false])
        ->addColumn("reason", "string", ["length" => 255, "null" => false])
        ->addColumn("recurring", Literal::from("flag"), ["null" => false])
        ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "N"])
        ->save();

    }
    
    public function down()
    {
        $this->dropTable("voucher");
    }
}
