<?php

use Phinx\Migration\AbstractMigration;

class OptionValueLimit extends AbstractMigration
{
    public function up()
    {
        $this->table("option_value_history")
            ->changeColumn("value", "text", ["null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("option_value_history")
            ->changeColumn("value", "string", ["limit" => 255, "null" => true])
            ->save();
    }
}
