<?php


use Phinx\Migration\AbstractMigration;

class AddLanguageTable extends AbstractMigration
{
    public function up()
    {           
        $this->table("language_variable", ["id" => "variable_id"])
        ->addColumn("tag_name", "string", ["null" => false, "length" => 100])
        ->addColumn("value", "text", ["null" => true])
        ->addColumn("type", "string", ["null" => false, "length" => 8])
        ->addColumn("module", "string", ["null" => true, "length" => 20])
        ->addColumn("template", "string", ["null" => true, "length" => 30])
        ->addColumn("language_code", "string", ["null" => false, "length" => 2])
        ->save();
        
        $language = new Language();
        $language->_dataLanguageCode = "de";
        $translationList = $language->LoadForTempate("dashboard.html", null, true, false);
        
        $values = array();
        
        foreach($translationList as $key => $translation)
        {
            $values[] = "(nextval('\"language_variable_variable_id_seq\"'::regclass),'".$key."','".$translation["Value"]."','template',NULL,'dashboard.html','de')";
        }
        
        //$this->execute("INSERT INTO language_variable (variable_id, tag_name, value, type, module, template, language_code) VALUES ".implode(",", $values));
        
    }
    
    public function down()
    {
        $this->dropTable("language_variable");
    }
}
