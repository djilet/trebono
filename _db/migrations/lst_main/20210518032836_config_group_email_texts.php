<?php

use Phinx\Migration\AbstractMigration;

class ConfigGroupEmailTexts extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-group-email_texts", "E-Mail-Texte");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-group-email_texts", "Email texts");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-group-email_texts", "E-Mail-Texte");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-registration_email_text", "Anmeldung E-Mail-text");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-registration_email_text", "Registration email text");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-registration_email_text", "Anmeldung E-Mail-text");
    }


    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
            VALUES 
            (
                nextval('\"config_ConfigID_seq\"'::regclass),
                'registration_email_text',
                '<p>Guten Tag %salutation% %first_name% %last_name%,<br /><br />
                
                wir freuen uns, dass Sie unseren trebono Cloud Service verwenden<br />
                und sich registriert haben. Ihre Zugangsdaten für die trebono Mobile-App sind:<br /><br />
                
                Login: %email%<br />
                Passwort: %Password%<br /><br />
                
                Bitte laden Sie die trebono Mobile-App aus Ihrem Apple Store oder Google Play Store herunter (Suchwort: trebono).<br />
                In der Anlage zu dieser E-Mail finden Sie eine PDF-Datei, die Ihnen die ersten Schritte erläutert. Bitte ändern Sie als erstes Ihr Passwort mit mindestens 6 Stellen und verwenden Sie Buchstaben (mindestens ein Großbuchstabe) und Ziffern.<br /><br />
                
                Unter %base_url% können Sie sich auch mit diesen Zugangsdaten anmelden und z.B. ihre Handy Nummer, Passwort oder Bankverbindung zu ändern.<br /><br />
                
                %individual_text_from_company%<br /><br />
                
                Sollten Sie Fragen haben wenden Sie sich bitte an den Super Anwender ihres Unternehmens.<br />
                Haben Sie Anregungen für uns wenden Sie sich bitte jederzeit gerne an uns, am besten mit einer E-Mail an support@trebono.de.<br /><br />
                
                So, nun wünschen wir Ihnen viel Spaß beim Digitalisieren Ihrer Belege.<br /><br />
                
                Mit freundlichen Grüßen<br /><br />
                Ihr Team von<br />
                2KS Cloud Services GmbH<br /><br /><br />
                
                <img src=%logo% /><br />
                2KS Cloud Services GmbH<br />
                Bahnhofstrasse 54, 64 367 DA-Mühltal, Germany<br /><br />
                
                Tel.: +49 6151 493 411-0<br />
                Web: www.trebono.de<br />
                Email: support@trebono.de<br /><br />
                
                Erleben Sie trebono Live auf YouTube:<br />
                https://youtu.be/ie4yHoHI3Q0<br />
                https://youtu.be/T8YTghuMfKE<br /><br />
                
                Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein<br />
                Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 87964<br /></p>
                
                <p><sub>*This message and any attachments are solely for the intended recipient and may contain confidential or privileged information. If you are not the intended recipient, any disclosure, copying, use, or distribution of the information included in this message and any attachments is prohibited. If you have received this communication in error, please notify us by reply e-mail and immediately and permanently delete this message and any attachments.</sub></p>',
                'email_texts',
                'ckeditor',
                NOW()
            )");
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }


        $this->execute("DELETE FROM config WHERE code = " . Connection::GetSQLString("registration_email_text"));
    }
}
