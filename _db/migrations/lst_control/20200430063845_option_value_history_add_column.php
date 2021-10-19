<?php

use Phinx\Migration\AbstractMigration;

class OptionValueHistoryAddColumn extends AbstractMigration
{
    public function up()
    {
        $this->table("option_value_history")
            ->renameColumn("created", "date_from")
            ->save();

        $this->table("option_value_history")
            ->addColumn("created", "timestamp", ["null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("option_value_history")
            ->removeColumn("created")
            ->save();

        $this->table("option_value_history")
            ->renameColumn("date_from", "created")
            ->save();
    }
}
