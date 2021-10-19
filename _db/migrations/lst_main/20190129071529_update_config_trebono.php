<?php


use Phinx\Migration\AbstractMigration;

class UpdateConfigTrebono extends AbstractMigration
{
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("UPDATE config SET value = replace(value, 'FIN-easy', 'trebono'), updated=".$updated." WHERE value LIKE '%FIN-easy%'");
        $this->execute("UPDATE config SET value = replace(value, 'FINEasy', 'trebono'), updated=".$updated." WHERE value LIKE '%FINEasy%'");
        $this->execute("UPDATE config SET value = replace(value, 'FINEASY', 'TREBONO'), updated=".$updated." WHERE value LIKE '%FINEASY%'");
        $this->execute("UPDATE config SET value = replace(value, '2kscs.de', 'trebono.de'), updated=".$updated." WHERE value LIKE '%2kscs.de%'");
        $this->execute("UPDATE config SET value = replace(value, '2ks-cs.de', 'trebono.de'), updated=".$updated." WHERE value LIKE '%2ks-cs.de%'");
    }
    
    public function down(){
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("UPDATE config SET value = replace(value, 'trebono.de', '2kscs.de'), updated=".$updated." WHERE value LIKE '%trebono.de%'");
        $this->execute("UPDATE config SET value = replace(value, 'TREBONO', 'FINEASY'), updated=".$updated." WHERE value LIKE '%TREBONO%'");
        $this->execute("UPDATE config SET value = replace(value, 'trebono', 'FIN-easy'), updated=".$updated." WHERE value LIKE '%trebono%'");
    }
}

