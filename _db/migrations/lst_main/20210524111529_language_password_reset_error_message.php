<?php

use Phinx\Migration\AbstractMigration;

class LanguagePasswordResetErrorMessage extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "reset-password-error-deactivated-user", "Sie können Ihr Passwort nicht zurücksetzen, da Sie im trebono-System deaktiviert sind");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "reset-password-error-deactivated-user", "You can't reset your password because you are deactivated in the trebono system");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "reset-password-error-deactivated-user", "Sie können Ihr Passwort nicht zurücksetzen, da Sie im trebono-System deaktiviert sind");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "reset-password-error-deactivated-user-description", "Fehlermeldung beim Zurücksetzen eines Passworts für einen deaktivierten Benutzer");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "reset-password-error-deactivated-user-description", "Error message when resetting a password for a deactivated user");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "reset-password-error-deactivated-user-description", "Fehlermeldung beim Zurücksetzen eines Passworts für einen deaktivierten Benutzer");
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
