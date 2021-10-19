<?php

use Phinx\Migration\AbstractMigration;

class PermissionPartnerLinkTo extends AbstractMigration
{
    public function up(){
        $query = "UPDATE permission SET link_to = 'partner' WHERE name='partner'";
        $this->execute($query);
    }

    public function down(){
        $query = "UPDATE permission SET link_to = '' WHERE name='partner'";
        $this->execute($query);
    }
}
