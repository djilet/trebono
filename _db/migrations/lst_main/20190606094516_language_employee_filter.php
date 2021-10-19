<?php


use Phinx\Migration\AbstractMigration;

class LanguageEmployeeFilter extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "employee-filter-restriction", "Search was run only for first %employee_filter_count% employees. Please, try to narrow your search to see full results.");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "employee-filter-restriction", "Die Suche wurde nur für die ersten %employee_filter_count% Mitarbeiter durchgeführt. Bitte versuchen Sie, Ihre Suche einzugrenzen, um die vollständigen Ergebnisse zu sehen.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "employee-filter-restriction", "Die Suche wurde nur für die ersten %employee_filter_count% Mitarbeiter durchgeführt. Bitte versuchen Sie, Ihre Suche einzugrenzen, um die vollständigen Ergebnisse zu sehen.");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "employee-filter-no-pagination", "Pagination is not supported for current search.");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "employee-filter-no-pagination", "Die Paginierung wird für die aktuelle Suche nicht unterstützt.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "employee-filter-no-pagination", "Die Paginierung wird für die aktuelle Suche nicht unterstützt.");
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
