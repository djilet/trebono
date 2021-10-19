<?php


use Phinx\Migration\AbstractMigration;

class ApiPortalDevice extends AbstractMigration
{
    public function up()
    {
        $privateKey = md5("psi_portal".date("U").rand(1000, 9999));
        
        $query = "INSERT INTO device (device_id, client, private_key, created) VALUES (
            'psi_portal',
            'web',
            ".Connection::GetSQLString($privateKey).",
            ".Connection::GetSQLString(GetCurrentDateTime()).");";
        
        $this->execute($query);
    }

    public function down()
    {
        $this->execute("DELETE FROM device WHERE device_id='psi_portal'");
    }
}
