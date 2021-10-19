<?php

use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboardObjectType extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_imp_lbw_txt", "payroll_imp_lbw_txt");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_imp_lbw_txt", "Lohnexport Addison TXT");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_imp_lbw_txt", "Lohnexport Addison TXT");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_Lex_txt", "payroll_Lex_txt");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_Lex_txt", "Lohnexport Lexware TXT");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_Lex_txt", "Lohnexport Lexware TXT");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "recreation_confirmation", "recreation_confirmation");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "recreation_confirmation", "Erholungsbestätigung");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "recreation_confirmation", "Erholungsbestätigung");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "company_contract_documents", "company_contract_documents");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "company_contract_documents", "Unternehmensvertragsunterlagen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "company_contract_documents", "Unternehmensvertragsunterlagen");
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
