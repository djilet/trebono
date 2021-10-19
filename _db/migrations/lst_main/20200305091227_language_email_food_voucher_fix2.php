<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmailFoodVoucherFix2 extends AbstractMigration
{
    private $langVarList = array();
    private $delangVarList = array();

    public function init()
    {
        $this->delangVarList[] = new LangVar("en", "php", "company", "common", "send-email-food-voucher-new", "
Guten Tag %salutation% %first_name% %last_name%,<br /><br />
wir wünschen Ihnen besonders viel Freude mit Ihrem %product_group%.<br />
Sie bekommen heute von ihrem Arbeitgeber %company name% folgende neue Gutscheine:<br />

<table border='0'>
    <tr>
        <td>Anzahl neue Gutscheine:</td>
        <td>%count%</td>
    </tr>
    <tr>
        <td>Wert pro Gutschein:</td>
        <td>%voucher_amount% Euro</td>
    </tr>
    <tr>
        <td>Gesamt Wert:</td>
        <td>%voucher_total_amount% Euro</td>
    </tr>
</table>
Er steht Ihnen wie gewohnt in Ihrer trebono Mobile App sofort zur Verfügung.<br /><br />

Mit freundlichen Grüßen<br />
Ihr trebono Team<br /><br />

<img src=\"cid:logo\" /><br />
2KS Gutschein Handels GmbH<br />
Bahnhofstrasse 54, 64 367 Darmstadt, Germany<br /><br />

Tel.:      +49 6151 493 411-0<br />
Web:    www.trebono.de<br />
Email:  support@trebono.de<br /><br />

Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein<br />
Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 100014<br /><br />

Erleben Sie trebono Live auf YouTube:<br />
https://youtu.be/ie4yHoHI3Q0<br />
https://youtu.be/T8YTghuMfKE<br /><br />

<small>*This message and any attachments are solely for the intended recipient and may contain confidential or privileged information. If you are not the intended recipient, any disclosure, copying, use, or distribution of the information included in this message and any attachments is prohibited. If you have received this communication in error, please notify us by reply e-mail and immediately and permanently delete this message and any attachments.</small>
<br />
<br />
<br />
");
        $this->delangVarList[] = new LangVar("tr", "php", "company", "common", "send-email-food-voucher-new", "
Guten Tag %salutation% %first_name% %last_name%,<br /><br />
wir wünschen Ihnen besonders viel Freude mit Ihrem %product_group%.<br />
Sie bekommen heute von ihrem Arbeitgeber %company name% folgende neue Gutscheine:<br />

<table border='0'>
    <tr>
        <td>Anzahl neue Gutscheine:</td>
        <td>%count%</td>
    </tr>
    <tr>
        <td>Wert pro Gutschein:</td>
        <td>%voucher_amount% Euro</td>
    </tr>
    <tr>
        <td>Gesamt Wert:</td>
        <td>%voucher_total_amount% Euro</td>
    </tr>
</table>
Er steht Ihnen wie gewohnt in Ihrer trebono Mobile App sofort zur Verfügung.<br /><br />

Mit freundlichen Grüßen<br />
Ihr trebono Team<br /><br />

<img src=\"cid:logo\" /><br />
2KS Gutschein Handels GmbH<br />
Bahnhofstrasse 54, 64 367 Darmstadt, Germany<br /><br />

Tel.:      +49 6151 493 411-0<br />
Web:    www.trebono.de<br />
Email:  support@trebono.de<br /><br />

Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein<br />
Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 100014<br /><br />

Erleben Sie trebono Live auf YouTube:<br />
https://youtu.be/ie4yHoHI3Q0<br />
https://youtu.be/T8YTghuMfKE<br /><br />

<small>*This message and any attachments are solely for the intended recipient and may contain confidential or privileged information. If you are not the intended recipient, any disclosure, copying, use, or distribution of the information included in this message and any attachments is prohibited. If you have received this communication in error, please notify us by reply e-mail and immediately and permanently delete this message and any attachments.</small>
<br />
<br />
<br />
");
        $this->delangVarList[] = new LangVar("de", "php", "company", "common", "send-email-food-voucher-new", "
Guten Tag %salutation% %first_name% %last_name%,<br /><br />
wir wünschen Ihnen besonders viel Freude mit Ihrem %product_group%.<br />
Sie bekommen heute von ihrem Arbeitgeber %company name% folgende neue Gutscheine:<br />

<table border='0'>
    <tr>
        <td>Anzahl neue Gutscheine:</td>
        <td>%count%</td>
    </tr>
    <tr>
        <td>Wert pro Gutschein:</td>
        <td>%voucher_amount% Euro</td>
    </tr>
    <tr>
        <td>Gesamt Wert:</td>
        <td>%voucher_total_amount% Euro</td>
    </tr>
</table>
Er steht Ihnen wie gewohnt in Ihrer trebono Mobile App sofort zur Verfügung.<br /><br />

Mit freundlichen Grüßen<br />
Ihr trebono Team<br /><br />

<img src=\"cid:logo\" /><br />
2KS Gutschein Handels GmbH<br />
Bahnhofstrasse 54, 64 367 Darmstadt, Germany<br /><br />

Tel.:      +49 6151 493 411-0<br />
Web:    www.trebono.de<br />
Email:  support@trebono.de<br /><br />

Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein<br />
Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 100014<br /><br />

Erleben Sie trebono Live auf YouTube:<br />
https://youtu.be/ie4yHoHI3Q0<br />
https://youtu.be/T8YTghuMfKE<br /><br />

<small>*This message and any attachments are solely for the intended recipient and may contain confidential or privileged information. If you are not the intended recipient, any disclosure, copying, use, or distribution of the information included in this message and any attachments is prohibited. If you have received this communication in error, please notify us by reply e-mail and immediately and permanently delete this message and any attachments.</small>
<br />
<br />
<br />
");
      $this->langVarList[] = new LangVar("en", "php", "company", "common", "send-email-food-voucher-new", "
Guten Tag %salutation% %first_name% %last_name%,<br /><br />
wir wünschen Ihnen besonders viel Freude mit Ihrem %product_group%.<br />
Sie bekommen heute von ihrem Arbeitgeber %company name% folgende neue Gutscheine:<br /><br />

<table border='0'>
    <tr>
        <td>Anzahl neue Gutscheine:</td>
        <td>%count%</td>
    </tr>
    <tr>
        <td>Wert pro Gutschein:</td>
        <td>%voucher_amount% Euro</td>
    </tr>
    <tr>
        <td>Gesamt Wert:</td>
        <td>%voucher_total_amount% Euro</td>
    </tr>
</table>
Er steht Ihnen wie gewohnt in Ihrer trebono Mobile App sofort zur Verfügung.<br /><br />

Mit freundlichen Grüßen<br />
Ihr trebono Team<br /><br />

<img src=\"cid:logo\" /><br />
2KS Gutschein Handels GmbH<br />
Bahnhofstrasse 54, 64 367 Darmstadt, Germany<br /><br />

Tel.:      +49 6151 493 411-0<br />
Web:    www.trebono.de<br />
Email:  support@trebono.de<br /><br />

Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein<br />
Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 100014<br /><br />

Erleben Sie trebono Live auf YouTube:<br />
https://youtu.be/ie4yHoHI3Q0<br />
https://youtu.be/T8YTghuMfKE<br /><br />

<small>*This message and any attachments are solely for the intended recipient and may contain confidential or privileged information. If you are not the intended recipient, any disclosure, copying, use, or distribution of the information included in this message and any attachments is prohibited. If you have received this communication in error, please notify us by reply e-mail and immediately and permanently delete this message and any attachments.</small>
<br />
<br />
<br />
");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "send-email-food-voucher-new", "
Guten Tag %salutation% %first_name% %last_name%,<br /><br />
wir wünschen Ihnen besonders viel Freude mit Ihrem %product_group%.<br />
Sie bekommen heute von ihrem Arbeitgeber %company name% folgende neue Gutscheine:<br /><br />

<table border='0'>
    <tr>
        <td>Anzahl neue Gutscheine:</td>
        <td>%count%</td>
    </tr>
    <tr>
        <td>Wert pro Gutschein:</td>
        <td>%voucher_amount% Euro</td>
    </tr>
    <tr>
        <td>Gesamt Wert:</td>
        <td>%voucher_total_amount% Euro</td>
    </tr>
</table>
Er steht Ihnen wie gewohnt in Ihrer trebono Mobile App sofort zur Verfügung.<br /><br />

Mit freundlichen Grüßen<br />
Ihr trebono Team<br /><br />

<img src=\"cid:logo\" /><br />
2KS Gutschein Handels GmbH<br />
Bahnhofstrasse 54, 64 367 Darmstadt, Germany<br /><br />

Tel.:      +49 6151 493 411-0<br />
Web:    www.trebono.de<br />
Email:  support@trebono.de<br /><br />

Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein<br />
Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 100014<br /><br />

Erleben Sie trebono Live auf YouTube:<br />
https://youtu.be/ie4yHoHI3Q0<br />
https://youtu.be/T8YTghuMfKE<br /><br />

<small>*This message and any attachments are solely for the intended recipient and may contain confidential or privileged information. If you are not the intended recipient, any disclosure, copying, use, or distribution of the information included in this message and any attachments is prohibited. If you have received this communication in error, please notify us by reply e-mail and immediately and permanently delete this message and any attachments.</small>
<br />
<br />
<br />
");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "send-email-food-voucher-new", "
Guten Tag %salutation% %first_name% %last_name%,<br /><br />
wir wünschen Ihnen besonders viel Freude mit Ihrem %product_group%.<br />
Sie bekommen heute von ihrem Arbeitgeber %company name% folgende neue Gutscheine:<br /><br />

<table border='0'>
    <tr>
        <td>Anzahl neue Gutscheine:</td>
        <td>%count%</td>
    </tr>
    <tr>
        <td>Wert pro Gutschein:</td>
        <td>%voucher_amount% Euro</td>
    </tr>
    <tr>
        <td>Gesamt Wert:</td>
        <td>%voucher_total_amount% Euro</td>
    </tr>
</table>
Er steht Ihnen wie gewohnt in Ihrer trebono Mobile App sofort zur Verfügung.<br /><br />

Mit freundlichen Grüßen<br />
Ihr trebono Team<br /><br />

<img src=\"cid:logo\" /><br />
2KS Gutschein Handels GmbH<br />
Bahnhofstrasse 54, 64 367 Darmstadt, Germany<br /><br />

Tel.:      +49 6151 493 411-0<br />
Web:    www.trebono.de<br />
Email:  support@trebono.de<br /><br />

Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein<br />
Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 100014<br /><br />

Erleben Sie trebono Live auf YouTube:<br />
https://youtu.be/ie4yHoHI3Q0<br />
https://youtu.be/T8YTghuMfKE<br /><br />

<small>*This message and any attachments are solely for the intended recipient and may contain confidential or privileged information. If you are not the intended recipient, any disclosure, copying, use, or distribution of the information included in this message and any attachments is prohibited. If you have received this communication in error, please notify us by reply e-mail and immediately and permanently delete this message and any attachments.</small>
<br />
<br />
<br />
");
    }

    public function up()
    {
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
