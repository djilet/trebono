<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmailVoucher extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "send-email-voucher-new", ' Guten Tag %salutation% %first_name% %last_name%,<br /><br />
    	
    	wir wünschen Ihnen besonders viel Freude mit Ihrem %product_group% in Höhe von %voucher_amount% Euro ihres Arbeitgebers %company name%. Er steht Ihnen wie gewohnt in Ihrer trebono Mobile App sofort zur Verfügung.<br /><br />
    	
    	Mit freundlichen Grüßen<br />
    	Ihr trebono Team<br /><br /><br />
    	 
    	
    	<img src=\"cid:logo\" /><br />
    	2KS Gutschein GmbH<br />
    	Bahnhofstrasse 54, 64 367 Darmstadt, Germany<br /><br />
    	
    	Tel.: +49 6151 493 411-0<br />
    	Web: www.trebono.de<br />
    	Email: support@trebono.de<br /><br />
    	
    	Geschäftsführer/Managing Directors: Jörg Klingler, Thorsten Stein<br />
    	Handelsregister/Commercial Register: Amtsgericht Darmstadt HRB 87964<br /><br />
    	
    	<small>*This message and any attachments are solely for the intended recipient and may contain confidential or privileged information. If you are not the intended recipient, any disclosure, copying, use, or distribution of the information included in this message and any attachments is prohibited. If you have received this communication in error, please notify us by reply e-mail and immediately and permanently delete this message and any attachments.</small>
    	<br />
    	<br />
    	<br />
    	 ');
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            //$query = $langVar->GetUpdateQuery();
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
