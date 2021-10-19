<?php


use Phinx\Migration\AbstractMigration;

class MigrateToLanguageTable extends AbstractMigration
{
    public function up()
    {   
        $language = new Language();
        $language->_dataLanguageCode = "en";
        $translationList = $language->LoadForTempate("dashboard.html", null, true, false);
        
        foreach($translationList as $key => $translation)
        {
            $values[] = "(nextval('\"language_variable_variable_id_seq\"'::regclass),'".$key."','".$translation["Value"]."','template',NULL,'dashboard.html','en')";
        }
        
        $this->execute("INSERT INTO language_variable (variable_id, tag_name, value, type, module, template, language_code) VALUES ".implode(",", $values));
    }
    
    public function down()
    {
        $this->execute("DELETE FROM language_variable WHERE language_code='en'");
    }
}
