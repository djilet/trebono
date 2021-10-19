<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class ImportAsynch extends AbstractMigration
{
    public function up()
    {
        //$this->execute("CREATE TYPE flag AS ENUM ('Y', 'N');");
        
        $this->table("import_company_unit_history", ["id" => "import_id"])
        ->addColumn("user_id", "integer", ["null" => false])
        ->addColumn("company_unit_id", "integer", ["null" => true])
        ->addColumn("employee_count", "integer", ["null" => false, "default" => 0])
        ->addColumn("content", "text", ["null" => false])
        ->addColumn("ended", Literal::from("flag"), ["null" => false])
        ->addColumn("created", "timestamp", ["null" => false])
        ->addColumn("updated", "timestamp", ["null" => false])
        ->save();
    }
    
    public function down()
    {
        $this->dropTable("import_company_unit_history");
        
        //$this->execute("DROP TYPE flag;");
    }
}
