<?php

    use Phinx\Migration\AbstractMigration;
    
    class BillableItemsLanguage extends AbstractMigration
    {
        private $langVarList = array();
    
        public function init()
        {
            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillQuantity", "Quantity");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillQuantity", "Menge");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillQuantity", "Menge");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillDiscount", "Discount");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillDiscount", "Rabatt");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillDiscount", "Rabatt");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillAmount", "Amount");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillAmount", "Preis");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillAmount", "Preis");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillEndDate", "Date end");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillEndDate", "Gültig bis");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillEndDate", "Gültig bis");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillStartDate", "Date start");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillStartDate", "Datum Beginn");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillStartDate", "Datum Beginn");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "AddNewBill", "Add new bill");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "AddNewBill", "Neue Rechnung hinzufügen");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "AddNewBill", "Neue Rechnung hinzufügen");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "EditBill", "Edit bill");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "EditBill", "rechnung bearbeiten");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "EditBill", "rechnung bearbeiten");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillPeriod", "Period");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillPeriod", "Für Periode");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillPeriod", "Für Periode");

            $this->langVarList[] = new LangVar("en", "template", "core", "common", "BillCreated", "Created");
            $this->langVarList[] = new LangVar("de", "template", "core", "common", "BillCreated", "Erstellt am");
            $this->langVarList[] = new LangVar("tr", "template", "core", "common", "BillCreated", "Erstellt am");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "CreatedBillUser", "Created user");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "CreatedBillUser", "Erstellt von");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "CreatedBillUser", "Erstellt von");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillCreatedUser", "Created user");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillCreatedUser", "Erstellt von");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillCreatedUser", "Erstellt von");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillItemName", "Item name");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillItemName", "Elementnamen");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillItemName", "Elementnamen");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillTotal", "Total");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillTotal", "Insgesamt");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillTotal", "Insgesamt");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillRemove", "Remove");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillRemove", "Entfernen");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillRemove", "Entfernen");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillableRecords", "Billable records");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillableRecords", "abrechenbare Datensätze");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillableRecords", "abrechenbare Datensätze");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "SBI", "Standard billing items");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "SBI", "Standard abrechnungsposten");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "SBI", "Standard abrechnungsposten");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillPrice", "Price");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillPrice", "Preis");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillPrice", "Preis");

            $this->langVarList[] = new LangVar("en", "php", "company", "common", "add-new-bill", "Add new bill");
            $this->langVarList[] = new LangVar("de", "php", "company", "common", "add-new-bill", "Neue Rechnung hinzufügen");
            $this->langVarList[] = new LangVar("tr", "php", "company", "common", "add-new-bill", "Neue Rechnung hinzufügen");

            $this->langVarList[] = new LangVar("en", "php", "company", "common", "edit-bill", "Edit bill");
            $this->langVarList[] = new LangVar("de", "php", "company", "common", "edit-bill", "rechnung bearbeiten");
            $this->langVarList[] = new LangVar("tr", "php", "company", "common", "edit-bill", "rechnung bearbeiten");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillError", "Please, fill in all fields correctly");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillError", "Bitte füllen Sie alle Felder korrekt aus");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillError", "Bitte füllen Sie alle Felder korrekt aus");

            $this->langVarList[] = new LangVar("en", "template", "company", "common", "BillEdit", "Saved");
            $this->langVarList[] = new LangVar("de", "template", "company", "common", "BillEdit", "Spielstand");
            $this->langVarList[] = new LangVar("tr", "template", "company", "common", "BillEdit", "Spielstand");
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
    
