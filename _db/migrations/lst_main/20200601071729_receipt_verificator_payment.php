<?php

use Phinx\Migration\AbstractMigration;

class ReceiptVerificatorPayment extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "ProcessingDetails", "Verarbeitungsdetails");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "ProcessingDetails", "Processing details");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "ProcessingDetails", "Verarbeitungsdetails");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_processing_details.html", "ProcessingDetailsOfReceipt", "Verarbeitungsdetails der Beleg ID");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_processing_details.html", "ProcessingDetailsOfReceipt", "Processing details of receipt ID");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_processing_details.html", "ProcessingDetailsOfReceipt", "Verarbeitungsdetails der Beleg ID");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_processing_details.html", "StartingStatus", "Startstatus");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_processing_details.html", "StartingStatus", "Starting status");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_processing_details.html", "StartingStatus", "Startstatus");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_processing_details.html", "Verificator", "Verifizierer");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_processing_details.html", "Verificator", "Verificator");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_processing_details.html", "Verificator", "Verifizierer");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_processing_details.html", "OpeningTime", "Zeitpunkt der Eröffnung");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_processing_details.html", "OpeningTime", "Time of opening");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_processing_details.html", "OpeningTime", "Zeitpunkt der Eröffnung");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_processing_details.html", "SaveTime", "Zeit zum Sparen");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_processing_details.html", "SaveTime", "Time of saving");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_processing_details.html", "SaveTime", "Zeit zum Sparen");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_processing_details.html", "ElepcedTime", "Verstrichene Zeit");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_processing_details.html", "ElepcedTime", "Elepced time");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_processing_details.html", "ElepcedTime", "Verstrichene Zeit");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_processing_details.html", "SavedStatus", "Status gespeichert");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_processing_details.html", "SavedStatus", "Status saved");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_processing_details.html", "SavedStatus", "Status gespeichert");
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
