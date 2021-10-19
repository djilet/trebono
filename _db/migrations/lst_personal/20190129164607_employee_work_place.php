<?php


use Phinx\Migration\AbstractMigration;

class EmployeeWorkPlace extends AbstractMigration
{
        public function up()
    {
        $this->table("employee")
        ->addColumn("work_place", "string", ["length" => 50, "null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("employee")
        ->removeColumn("work_place")
        ->save();
    }
}
