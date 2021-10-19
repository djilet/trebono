<?php


use Phinx\Migration\AbstractMigration;

class ConfigDontSendEmail extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-send_mail", "Senden Sie E-Mails");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-send_mail", "Send emails");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-send_mail", "Senden Sie E-Mails");
    }
    
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'send_mail','Y',
							'misc'::character varying,
							'flag'::character varying,
							'2019-05-27 00:00:00'
						)");
    }
    
    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
        
        
        $this->execute("DELETE FROM config WHERE code='send_mail'");
    }
}
