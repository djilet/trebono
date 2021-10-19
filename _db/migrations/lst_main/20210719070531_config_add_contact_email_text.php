<?php

use Phinx\Migration\AbstractMigration;

class ConfigAddContactEmailText extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-contact_registration_email_text", "Kontakt Registrierung E-Mail Text");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-contact_registration_email_text", "Contact registration email text");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-contact_registration_email_text", "İletişim kayıt e-posta metni");
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
                'contact_registration_email_text',
                '<p>Guten Tag %salutation% %first_name% %last_name%,<br /><br />

                Sie sind soeben als Administrator ihres Unternehmens im trebono Cloud Service registriert worden.<br />
                Unter %base_url% können sie sich mit folgenden Benutzerdaten einloggen:<br /><br />

                Login: %email%<br />
                Password: %password%<br /><br />

                Sollten Sie Fragen oder Anregungen haben wenden Sie sich bitte jederzeit gerne an uns, am besten mit einer E-Mail an support@trebono.de.<br /><br />

                So, nun wünschen wir Ihnen viel Spaß beim Digitalisieren Ihrer Belege und mit mehr Nettoeinkommen!<br /><br />
                
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


        $this->execute("DELETE FROM config WHERE code = " . Connection::GetSQLString("contact_registration_email_text"));
    }

}
