<?php


use Phinx\Migration\AbstractMigration;

class UserAccessToken extends AbstractMigration
{
    public function up()
    {
        $this->table("user_info")
            ->addColumn("access_token", "string", ["length" => 255, "null" => true])
            ->addColumn("access_token_expire_date", "timestamp", ["null" => true])
            ->save();
    }
    
    public function down()
    {
        $this->table("user_info")
        ->removeColumn("access_token", "string", ["length" => 255, "null" => true])
        ->removeColumn("access_token_expire_date", "timestamp", ["null" => true])
        ->save();
    }
}
