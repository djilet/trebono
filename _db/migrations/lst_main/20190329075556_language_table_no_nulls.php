<?php


use Phinx\Migration\AbstractMigration;

class LanguageTableNoNulls extends AbstractMigration
{
    public function up()
    {
        $this->execute("DELETE FROM language_variable");
        
        $this->execute("UPDATE language_variable SET module='core' WHERE module IS NULL");
        
        $this->execute("UPDATE language_variable SET template='common' WHERE template IS NULL");
     
        $this->table('language_variable')
        ->changeColumn("module", "string", ["null" => false, "length" => 20])
        ->changeColumn("template", "string", ["null" => false, "length" => 60])
        ->save();
    }
    
    public function down()
    {
        $this->table('language_variable')
        ->changeColumn("module", "string", ["null" => true, "length" => 20])
        ->changeColumn("template", "string", ["null" => true, "length" => 60])
        ->save();
        
        $this->execute("UPDATE language_variable SET module=NULL WHERE module='core'");
        
        $this->execute("UPDATE language_variable SET template=NULL WHERE template='common'");
    }
}
