<?php

use Phinx\Migration\AbstractMigration;

class NewPolePartner extends AbstractMigration
{
    public function up(){
        $query = "INSERT INTO permission (permission_id, name, title, link_to)
                    VALUES(14, 'partner', 'Partner', '')";
        $this->execute($query);
    }

    public function down(){
        $query = "DELETE FROM permission WHERE name='payroll'";
        $this->execute($query);
    }
}
