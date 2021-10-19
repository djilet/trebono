<?php

use Phinx\Migration\AbstractMigration;

class LanguageAgreementsEmailFix extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "agreements", "contract_mail.html", "FooterAddress", "2KS Cloud Services GmbH
    				Bahnhofstrasse 54, 64 367 Darmstadt-Mühltal, Germany

    				Tel.: +49 6151 493 411-0
    				Web: www.trebono.de
    				Email: support@trebono.de

    				Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein
    				Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 87964");
        $this->langVarList[] = new LangVar("en", "template", "agreements", "contract_mail.html", "FooterAddress", "2KS Cloud Services GmbH
    				Bahnhofstrasse 54, 64 367 Darmstadt-Mühltal, Germany

    				Tel.: +49 6151 493 411-0
    				Web: www.trebono.de
    				Email: support@trebono.de

    				Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein
    				Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 87964");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "contract_mail.html", "FooterAddress", "2KS Cloud Services GmbH
    				Bahnhofstrasse 54, 64 367 Darmstadt-Mühltal, Germany

    				Tel.: +49 6151 493 411-0
    				Web: www.trebono.de
    				Email: support@trebono.de

    				Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein
    				Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 87964");

        $this->langVarList[] = new LangVar("de", "template", "agreements", "contract_mail.html", "FooterText", "Mit freundlichen Grüßen
    				Ihr trebono Team");
        $this->langVarList[] = new LangVar("en", "template", "agreements", "contract_mail.html", "FooterText", "Mit freundlichen Grüßen
    				Ihr trebono Team");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "contract_mail.html", "FooterText", "Mit freundlichen Grüßen
    				Ihr trebono Team");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetUpdateQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetUpdateQuery();
            $this->execute($query);
        }
    }
}
