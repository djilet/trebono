<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CompanyUnitDocuments extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "TabDocuments", "Documents");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "TabDocuments", "Unterlagen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "TabDocuments", "Unterlagen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "Contracts", "Contracts");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "Contracts", "Verträge");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "Contracts", "Verträge");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BusinessTerms", "Business terms");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BusinessTerms", "Geschäftsbedingungen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BusinessTerms", "Geschäftsbedingungen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "ContractLimitReached", "You can only upload 10 contracts maximum");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "ContractLimitReached", "Sie können maximal 10 Verträge hochladen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "ContractLimitReached", "Sie können maximal 10 Verträge hochladen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "UploadContractFile", "Upload contract");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "UploadContractFile", "Vertrag hochladen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "UploadContractFile", "Vertrag hochladen");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-business_terms_1", "Business terms document 1");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-business_terms_1", "Nicht belegt 1");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-business_terms_1", "Nicht belegt 1");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-business_terms_2", "Business terms document 2");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-business_terms_2", "Nicht belegt 2");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-business_terms_2", "Nicht belegt 2");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-business_terms_3", "Business terms document 3");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-business_terms_3", "Nicht belegt 3");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-business_terms_3", "Nicht belegt 3");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-business_terms_4", "Business terms document 4");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-business_terms_4", "Nicht belegt 4");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-business_terms_4", "Nicht belegt 4");
    }

    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated, sort_order)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'business_terms_1',
							'',
							'x_app_license'::character varying,
							'file'::character varying,
							NOW(),
							5
						),
						(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'business_terms_2',
							'',
							'x_app_license'::character varying,
							'file'::character varying,
							NOW(),
							6
						),
						(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'business_terms_3',
							'',
							'x_app_license'::character varying,
							'file'::character varying,
							NOW(),
							7
						)
						,(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'business_terms_4',
							'',
							'x_app_license'::character varying,
							'file'::character varying,
							NOW(),
							8
						)");

        $this->table("company_unit_document", ["id" => "document_id"])
            ->addColumn("company_unit_id", "integer", ["null" => false])
            ->addColumn("title", "text", ["null" => false])
            ->addColumn("value", "text", ["null" => true])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='business_terms_1' OR code='business_terms_2' OR code='business_terms_3' OR code='business_terms_4'");
        $this->dropTable("company_unit_document");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
