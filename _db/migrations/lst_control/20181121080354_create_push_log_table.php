<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreatePushLogTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TYPE flag AS ENUM ('Y', 'N');");
        
        $this->table("push_history", ["id" => "push_id"])
        ->addColumn("user_id", "integer", ["null" => false])
        ->addColumn("device_id", "string", ["length" => 255, "null" => false])
        ->addColumn("is_sended", Literal::from("flag"), ["null" => false])
        ->addColumn("text", "text", ["null" => true])
        ->addColumn("error_message", "text", ["null" => true])
        ->addColumn("created", "timestamp", ["null" => false])
        ->save();
    }
    
    public function down()
    {
        $this->dropTable("push_history");
        
        $this->execute("DROP TYPE flag;");
    }
}
