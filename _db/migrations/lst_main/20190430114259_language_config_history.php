<?php


use Phinx\Migration\AbstractMigration;

class LanguageConfigHistory extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "block_config_history.html", "ConfigHistory", "Änderungshistorie");
        $this->langVarList[] = new LangVar("en", "template", "core", "block_config_history.html", "ConfigHistory", "Change history");
        $this->langVarList[] = new LangVar("tr", "template", "core", "block_config_history.html", "ConfigHistory", "Änderungshistorie");
        $this->langVarList[] = new LangVar("de", "template", "core", "block_config_history.html", "Created", "Erstellungsdatum");
        $this->langVarList[] = new LangVar("en", "template", "core", "block_config_history.html", "Created", "Created");
        $this->langVarList[] = new LangVar("tr", "template", "core", "block_config_history.html", "Created", "Erstellungsdatum");
        $this->langVarList[] = new LangVar("de", "template", "core", "block_config_history.html", "User", "Nutzer");
        $this->langVarList[] = new LangVar("en", "template", "core", "block_config_history.html", "User", "User");
        $this->langVarList[] = new LangVar("tr", "template", "core", "block_config_history.html", "User", "Nutzer");
        $this->langVarList[] = new LangVar("de", "template", "core", "block_config_history.html", "Version", "Version");
        $this->langVarList[] = new LangVar("en", "template", "core", "block_config_history.html", "Version", "Version");
        $this->langVarList[] = new LangVar("tr", "template", "core", "block_config_history.html", "Version", "Version");
        $this->langVarList[] = new LangVar("de", "template", "core", "block_config_history.html", "View", "Anzeige");
        $this->langVarList[] = new LangVar("en", "template", "core", "block_config_history.html", "View", "View");
        $this->langVarList[] = new LangVar("tr", "template", "core", "block_config_history.html", "View", "Anzeige");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "RevisionHistory", "Historie");
        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "RevisionHistory", "rev. history");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "RevisionHistory", "Historie");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
