<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreateEmailLogTable extends AbstractMigration
{
    public function up()
    {
        //$this->execute("CREATE TYPE flag AS ENUM ('Y', 'N');");

        $this->table("email_history", ["id" => "email_id"])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("email", "text", ["null" => false])
            ->addColumn("is_sended", Literal::from("flag"), ["null" => false])
            ->addColumn("title", "text", ["null" => true])
            ->addColumn("file_name", "text", ["null" => true])
            ->addColumn("error_message", "text", ["null" => true])
            ->addColumn("created", "timestamp", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->dropTable("email_history");

        //$this->execute("DROP TYPE flag;");
    }
}
