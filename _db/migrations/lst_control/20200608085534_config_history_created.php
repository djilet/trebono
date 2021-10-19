<?php

use Phinx\Migration\AbstractMigration;

class ConfigHistoryCreated extends AbstractMigration
{
    public function up()
    {
        $this->table("config_history")
            ->renameColumn("created", "date_from")
            ->save();

        $this->table("config_history")
            ->addColumn("created", "timestamp", ["null" => true])
            ->save();

        $this->execute("INSERT INTO config_history (id, user_id, config_id, value, date_from, created)
						VALUES 
						(
							nextval('\"config_history_id_seq\"'::regclass),
							".Connection::GetSQLString(BILLING_USER_ID).",
							".Config::GetIDByCode("invoice_vat").",
							'19',
							'01-01-2018',
							NOW()
						),
						(
							nextval('\"config_history_id_seq\"'::regclass),
							".Connection::GetSQLString(BILLING_USER_ID).",
							".Config::GetIDByCode("voucher_invoice_vat").",
							'0',
							'01-01-2018',
							NOW()
						)");
    }

    public function down()
    {
        $this->table("config_history")
            ->removeColumn("created")
            ->save();

        $this->table("config_history")
            ->renameColumn("date_from", "created")
            ->save();
    }
}
