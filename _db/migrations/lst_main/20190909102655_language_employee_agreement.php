<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeAgreement extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "send-email-contract-message", "Guten Tag %Salutation% %FirstName% %LastName%,
            
            wir freuen uns, dass Sie unseren trebono Cloud Service verwenden
            und wünschen Ihnen und ihrem Unternehmen viel Erfolg beim Steuern sparen.
            
            Sie finden die Ergänzende Vereinbarung für das Modul %ServiceName% für Ihre(n) Mitarbeiter(in) %EmployeeFirstName% %EmployeeLastName% des Unternhemens %CompanyUnitTitle% unter dem folgenden Link:
            
            %LinkToTheAgreement%
            %LinkToTheAgreementList%
            
            Wie gewohnt können Sie sich mit Ihren normalen Zugangsdaten die Ergänzende Vereinbarung herunterladen.
            
            Sollten Sie Fragen oder Anregungen haben wenden Sie sich bitte jederzeit gerne an uns,
            am besten mit einem E-Mail an support@trebono.de.
            
            So, nun wünschen wir Ihnen weiterhin viel Spaß und Erfolg
            beim Digitalisieren Ihrer Belege und dem Sparen von Abgaben!");

        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "send-email-contract-message", "Guten Tag %Salutation% %FirstName% %LastName%,
            
            wir freuen uns, dass Sie unseren trebono Cloud Service verwenden
            und wünschen Ihnen und ihrem Unternehmen viel Erfolg beim Steuern sparen.
            
            Sie finden die Ergänzende Vereinbarung für das Modul %ServiceName% für Ihre(n) Mitarbeiter(in) %EmployeeFirstName% %EmployeeLastName% des Unternhemens %CompanyUnitTitle% unter dem folgenden Link:
            
            %LinkToTheAgreement%
            %LinkToTheAgreementList%
            
            Wie gewohnt können Sie sich mit Ihren normalen Zugangsdaten die Ergänzende Vereinbarung herunterladen.
            
            Sollten Sie Fragen oder Anregungen haben wenden Sie sich bitte jederzeit gerne an uns,
            am besten mit einem E-Mail an support@trebono.de.
            
            So, nun wünschen wir Ihnen weiterhin viel Spaß und Erfolg
            beim Digitalisieren Ihrer Belege und dem Sparen von Abgaben!");

        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "send-email-contract-message", "Guten Tag %Salutation% %FirstName% %LastName%,
            
            wir freuen uns, dass Sie unseren trebono Cloud Service verwenden
            und wünschen Ihnen und ihrem Unternehmen viel Erfolg beim Steuern sparen.
            
            Sie finden die Ergänzende Vereinbarung für das Modul %ServiceName% für Ihre(n) Mitarbeiter(in) %EmployeeFirstName% %EmployeeLastName% des Unternhemens %CompanyUnitTitle% unter dem folgenden Link:
            
            %LinkToTheAgreement%
            %LinkToTheAgreementList%
            
            Wie gewohnt können Sie sich mit Ihren normalen Zugangsdaten die Ergänzende Vereinbarung herunterladen.
            
            Sollten Sie Fragen oder Anregungen haben wenden Sie sich bitte jederzeit gerne an uns,
            am besten mit einem E-Mail an support@trebono.de.
            
            So, nun wünschen wir Ihnen weiterhin viel Spaß und Erfolg
            beim Digitalisieren Ihrer Belege und dem Sparen von Abgaben!");

        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "send-email-contract-subject", "%CompanyUnitTitle%: Ergänzende Vereinbarung für %FirstName% %LastName%");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "send-email-contract-subject", "%CompanyUnitTitle%: Ergänzende Vereinbarung für %FirstName% %LastName%");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "send-email-contract-subject", "%CompanyUnitTitle%: Ergänzende Vereinbarung für %FirstName% %LastName%");

        $this->langVarList[] = new LangVar("de", "template", "agreements", "contract_mail.html", "FooterText", "2KS Cloud Services GmbH
    				Bahnhofstrasse 54, 64 367 Darmstadt-Mühltal, Germany

    				Tel.: +49 6151 493 411-0
    				Web: www.trebono.de
    				Email: support@trebono.de

    				Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein
    				Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 87964");
        $this->langVarList[] = new LangVar("en", "template", "agreements", "contract_mail.html", "FooterText", "2KS Cloud Services GmbH
    				Bahnhofstrasse 54, 64 367 Darmstadt-Mühltal, Germany

    				Tel.: +49 6151 493 411-0
    				Web: www.trebono.de
    				Email: support@trebono.de

    				Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein
    				Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 87964");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "contract_mail.html", "FooterText", "2KS Cloud Services GmbH
    				Bahnhofstrasse 54, 64 367 Darmstadt-Mühltal, Germany

    				Tel.: +49 6151 493 411-0
    				Web: www.trebono.de
    				Email: support@trebono.de

    				Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein
    				Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 87964");
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
