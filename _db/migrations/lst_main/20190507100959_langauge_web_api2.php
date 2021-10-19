<?php


use Phinx\Migration\AbstractMigration;

class LangaugeWebApi2 extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-list-incorrect-ids", "Error occurred while checking company unit ID");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-list-incorrect-ids", "Fehler beim Prüfen der Firmeneinheits-ID");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-list-incorrect-ids", "Fehler beim Prüfen der Firmeneinheits-ID");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-list-no-ids-provided", "No company unit ID was provided");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-list-no-ids-provided", "Es wurde keine Firmeneinheits-ID bereitgestellt");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-list-no-ids-provided", "Es wurde keine Firmeneinheits-ID bereitgestellt");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-list-no-ids-provided", "No contact ID was provided");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-list-no-ids-provided", "Es wurde keine Kontakt-ID angegeben");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-list-no-ids-provided", "Es wurde keine Kontakt-ID angegeben");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "employee-list-remove-error", "Error occurred while removing employee");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "employee-list-remove-error", "Beim Entfernen des Mitarbeiters ist ein Fehler aufgetreten");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "employee-list-remove-error", "Beim Entfernen des Mitarbeiters ist ein Fehler aufgetreten");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "employee-list-no-ids-provided", "No employee ID was provided");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "employee-list-no-ids-provided", "Es wurde keine Mitarbeiter-ID angegeben");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "employee-list-no-ids-provided", "Es wurde keine Mitarbeiter-ID angegeben");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-not-found", "No company unit with such ID found");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-not-found", "Keine Firmeneinheit mit dieser ID gefunden");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-not-found", "Keine Firmeneinheit mit dieser ID gefunden");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-validation-failed", "You don't have rights to change that entity");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-validation-failed", "Sie haben keine Rechte, um diese Entität zu ändern");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-validation-failed", "Sie haben keine Rechte, um diese Entität zu ändern");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contact-not-found", "No contact person with such ID found");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contact-not-found", "Keine Kontaktperson mit dieser ID gefunden");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contact-not-found", "Keine Kontaktperson mit dieser ID gefunden");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "employee-not-found", "No employee with such ID found");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "employee-not-found", "Kein Mitarbeiter mit einem solchen Ausweis gefunden");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "employee-not-found", "Kein Mitarbeiter mit einem solchen Ausweis gefunden");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "employee-validation-failed", "You don't have rights to change that entity");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "employee-validation-failed", "Sie haben keine Rechte, um diese Entität zu ändern");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "employee-validation-failed", "Sie haben keine Rechte, um diese Entität zu ändern");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "incorrect-payroll-file-format", "You provided incorrect datev format");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "incorrect-payroll-file-format", "Sie haben ein falsches datev Format angegeben");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "incorrect-payroll-file-format", "Sie haben ein falsches datev Format angegeben");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "contract-not-found", "No contract with such ID found");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "contract-not-found", "Kein Vertrag mit einer solchen ID gefunden");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "contract-not-found", "Kein Vertrag mit einer solchen ID gefunden");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "payroll-validation-failed", "You don't have rights to see that entity");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "payroll-validation-failed", "Sie haben keine Rechte, diese Entität zu sehen");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "payroll-validation-failed", "Sie haben keine Rechte, diese Entität zu sehen");
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
