<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationProductOptionList extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "product", "option_list.html", "Monthly", "Monthly");
        $this->langVarList[] = new LangVar("de", "template", "product", "option_list.html", "Monthly", "monatlich");
        $this->langVarList[] = new LangVar("tr", "template", "product", "option_list.html", "Monthly", "monatlich");

        $this->langVarList[] = new LangVar("en", "template", "product", "option_list.html", "Yearly", "Yearly");
        $this->langVarList[] = new LangVar("de", "template", "product", "option_list.html", "Yearly", "jährlich");
        $this->langVarList[] = new LangVar("tr", "template", "product", "option_list.html", "Yearly", "jährlich");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InheritedValueSourceGlobal", "Global settings value");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InheritedValueSourceGlobal", "Wert für globale Einstellungen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InheritedValueSourceGlobal", "Wert für globale Einstellungen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InheritedValueSourceCompanyUnit", "Parent company unit settings value");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InheritedValueSourceCompanyUnit", "Einstellungswert der übergeordneten Unternehmenseinheit");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InheritedValueSourceCompanyUnit", "Einstellungswert der übergeordneten Unternehmenseinheit");

        $this->delLangVarList[] = new LangVar("en", "template", "product", "price_list.html", "Title", "Title");
        $this->delLangVarList[] = new LangVar("de", "template", "product", "price_list.html", "Title", "Titel");
        $this->delLangVarList[] = new LangVar("tr", "template", "product", "price_list.html", "Title", "Titel");

        $this->delLangVarList[] = new LangVar("en", "template", "product", "price_list.html", "CurrentValue", "Current Value");
        $this->delLangVarList[] = new LangVar("de", "template", "product", "price_list.html", "CurrentValue", "Aktueller Wert");
        $this->delLangVarList[] = new LangVar("tr", "template", "product", "price_list.html", "CurrentValue", "Aktueller Wert");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "SetSpecificValue", "(Set a special value?)");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "SetSpecificValue", "(Einen bestimmten Wert eingeben?)");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "SetSpecificValue", "(Einen bestimmten Wert eingeben?)");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "NoInheritedValue", "No inherited value");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "NoInheritedValue", "Kein vererbter Wert");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "NoInheritedValue", "Kein vererbter Wert");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
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

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
