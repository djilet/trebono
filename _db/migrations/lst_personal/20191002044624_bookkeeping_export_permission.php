<?php

use Phinx\Migration\AbstractMigration;

class BookkeepingExportPermission extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO permission(
			permission_id, name, title, link_to)
			VALUES (12, 'bookkeeping_export', 'Bookkeeping receiver', 'company_unit')");

        $this->table("employee")
            ->renameColumn("acc_corporate_hospitality", "acc_hospitality")
            ->renameColumn("acc_other_costs", "acc_other")
            ->save();
    }

    public function down()
    {
        $this->execute("DELETE FROM user_permissions WHERE permission_id=12");
        $this->execute("DELETE FROM permission WHERE name='bookkeeping_export'");

        $this->table("employee")
            ->renameColumn("acc_hospitality", "acc_corporate_hospitality")
            ->renameColumn("acc_other", "acc_other_costs")
            ->save();
    }
}
