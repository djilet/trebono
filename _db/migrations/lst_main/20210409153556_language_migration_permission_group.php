<?php

use Phinx\Migration\AbstractMigration;

class LanguageMigrationPermissionGroup extends AbstractMigration
{
    /** @var LangVar[] */
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "php", "core", "common", "permission-group-internal-roles", "Internal Roles"),
            new LangVar("de", "php", "core", "common", "permission-group-internal-roles", "Interne Rollen"),
            new LangVar("tr", "php", "core", "common", "permission-group-internal-roles", "Interne Rollen"),

            new LangVar("en", "php", "core", "common", "permission-group-internal-travel-receipt-mgt-service-roles", "Internal / Travel Receipt Mgt Service Roles"),
            new LangVar("de", "php", "core", "common", "permission-group-internal-travel-receipt-mgt-service-roles", "Interne / Reisebestätigung Mgt Service Rollen"),
            new LangVar("tr", "php", "core", "common", "permission-group-internal-travel-receipt-mgt-service-roles", "Interne / Reisebestätigung Mgt Service Rollen"),

            new LangVar("en", "php", "core", "common", "permission-group-customer-roles", "Customer Roles"),
            new LangVar("de", "php", "core", "common", "permission-group-customer-roles", "Kundenrollen"),
            new LangVar("tr", "php", "core", "common", "permission-group-customer-roles", "Kundenrollen"),
        ];
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
