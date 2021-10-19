<?php


use Phinx\Migration\AbstractMigration;

class MigrateToLanguageTable2 extends AbstractMigration
{
    public function up()
    {
        $module = new Module();
        $moduleList = $module->GetModuleList();
        
        $values = array();
        
        $language = new Language();
        $language->_dataLanguageCode = "en";
        
        $language->LoadForTempate("", null, true, false);
        
        foreach($moduleList as $moduleItem)
        {
            $language->LoadForTempate("", $moduleItem["Folder"], true, false);
        }

        foreach($language->_translateTemplate as $moduleKey => $moduleValues)
        {
            $module = str_replace("module", "", $moduleKey);
            foreach($moduleValues as $sectionKey => $templateList)
            {
                if($sectionKey == "common")
                {
                    foreach($templateList as $tagName => $valueArray)
                    {
                        $values[] = "(nextval('\"language_variable_variable_id_seq\"'::regclass),'".$tagName."',".Connection::GetSQLString($valueArray["Value"]).",'template',".($module == "" ? "NULL" : "'".$module."'").",NULL,'en')";
                    }
                }
                elseif($sectionKey == "admin")
                {
                    foreach($templateList as $templateKey => $templateValues)
                    {
                        foreach($templateValues as $tagName => $valueArray)
                        {
                            $values[] = "(nextval('\"language_variable_variable_id_seq\"'::regclass),'".$tagName."',".Connection::GetSQLString($valueArray["Value"]).",'template',".($module == "" ? "NULL" : "'".$module."'").",'".$templateKey."','en')";
                        }
                    }
                }
            }
        }
        
        $language = new Language();
        $language->_dataLanguageCode = "de";
        
        $language->LoadForTempate("", null, true, false);
        
        foreach($moduleList as $moduleItem)
        {
            $language->LoadForTempate("", $moduleItem["Folder"], true, false);
        }
        
        foreach($language->_translateTemplate as $moduleKey => $moduleValues)
        {
            $module = str_replace("module", "", $moduleKey);
            foreach($moduleValues as $sectionKey => $templateList)
            {
                if($sectionKey == "common")
                {
                    foreach($templateList as $tagName => $valueArray)
                    {
                        $values[] = "(nextval('\"language_variable_variable_id_seq\"'::regclass),'".$tagName."',".Connection::GetSQLString($valueArray["Value"]).",'template',".($module == "" ? "NULL" : "'".$module."'").",NULL,'de')";
                    }
                }
                elseif($sectionKey == "admin")
                {
                    foreach($templateList as $templateKey => $templateValues)
                    {
                        foreach($templateValues as $tagName => $valueArray)
                        {
                            $values[] = "(nextval('\"language_variable_variable_id_seq\"'::regclass),'".$tagName."',".Connection::GetSQLString($valueArray["Value"]).",'template',".($module == "" ? "NULL" : "'".$module."'").",'".$templateKey."','de')";
                        }
                    }
                }
            }
        }
        
        $language = new Language();
        $language->_dataLanguageCode = "en";
        
        $language->_LoadForPHP(null);
        
        foreach($moduleList as $moduleItem)
        {
            $language->_LoadForPHP($moduleItem["Folder"]);
        }
        
        foreach($language->_translatePHP as $moduleKey => $valueList)
        {
            $module = str_replace("module", "", $moduleKey);
            foreach($valueList as $tagName => $valueArray)
            {
                $values[] = "(nextval('\"language_variable_variable_id_seq\"'::regclass),'".$tagName."',".Connection::GetSQLString($valueArray["Value"]).",'php',".($module == "" ? "NULL" : "'".$module."'").",NULL,'en')";
            }
        }
        
        $language = new Language();
        $language->_dataLanguageCode = "de";
        
        $language->_LoadForPHP(null);
        
        foreach($moduleList as $moduleItem)
        {
            $language->_LoadForPHP($moduleItem["Folder"]);
        }
        
        foreach($language->_translatePHP as $moduleKey => $valueList)
        {
            $module = str_replace("module", "", $moduleKey);
            foreach($valueList as $tagName => $valueArray)
            {
                $values[] = "(nextval('\"language_variable_variable_id_seq\"'::regclass),'".$tagName."',".Connection::GetSQLString($valueArray["Value"]).",'php',".($module == "" ? "NULL" : "'".$module."'").",NULL,'de')";
            }
        }
        
         
        $this->execute("INSERT INTO language_variable (variable_id, tag_name, value, type, module, template, language_code) VALUES ".implode(",", $values)."ON CONFLICT (tag_name, type, module, template, language_code) DO NOTHING");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM language_variable WHERE language_code='en'");
        $this->execute("DELETE FROM language_variable WHERE language_code='de'");
    }
}
