<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationWebApi extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "contract-incorrect-level", "Please, provide correct contract level");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "contract-incorrect-level", "Bitte geben Sie das korrekte Vertragsniveau an");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "contract-incorrect-level", "Bitte geben Sie das korrekte Vertragsniveau an");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "contract-incorrect-product-id", "Please, provide correct product code");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "contract-incorrect-product-id", "Bitte geben Sie den korrekten Produktcode an");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "contract-incorrect-product-id", "Bitte geben Sie den korrekten Produktcode an");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-incorrect-level", "Please, provide correct option level");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-incorrect-level", "Bitte geben Sie das korrekte Vertragsniveau an");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-incorrect-level", "Bitte geben Sie das korrekte Vertragsniveau an");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-incorrect-id", "Please, provide correct option code");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-incorrect-id", "Bitte geben Sie den richtigen Optionscode an");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-incorrect-id", "Bitte geben Sie den richtigen Optionscode an");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-access-denied", "You don't have rights to change that option");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-access-denied", "Sie haben keine Rechte, um diese Option zu ändern");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-access-denied", "Sie haben keine Rechte, um diese Option zu ändern");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "permission-webapi", "Web administrator");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "permission-webapi", "Webadministrator");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "permission-webapi", "Webadministrator");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-validation-failed", "You don't have rights to change that entity");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-validation-failed", "Sie haben keine Rechte, um diese Entität zu ändern");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-validation-failed", "Sie haben keine Rechte, um diese Entität zu ändern");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "contract-validation-failed", "You don't have rights to change that entity");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "contract-validation-failed", "Sie haben keine Rechte, um diese Entität zu ändern");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "contract-validation-failed", "Sie haben keine Rechte, um diese Entität zu ändern");
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
