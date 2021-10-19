<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitDatevEncoding extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "DatevEncoding", "Datev encoding");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "DatevEncoding", "Datev-Codierung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "DatevEncoding", "Datev-Codierung");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "Utf8", "UTF-8");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "Utf8", "UTF-8");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "Utf8", "UTF-8");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "Ansi", "ANSI");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "Ansi", "ANSI");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "Ansi", "ANSI");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "utf8", "UTF-8");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "utf8", "UTF-8");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "utf8", "UTF-8");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "ansi", "ANSI");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "ansi", "ANSI");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "ansi", "ANSI");
    }

    public function up()
    {
        $this->table('company_unit')
            ->addColumn('datev_encoding', 'string', ['default' => 'utf-8'])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table('company_unit')
            ->removeColumn('datev_encoding')
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
