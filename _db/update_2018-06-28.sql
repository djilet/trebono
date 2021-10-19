CREATE TABLE "public"."employee_contract" ("contract_id" integer, "employee_id" integer NOT NULL, "product_id" integer NOT NULL, "created" timestamp without time zone NOT NULL, "start_date" date NOT NULL, "end_date" date, PRIMARY KEY ("contract_id")) WITHOUT OIDS;
ALTER SEQUENCE "public"."contract_ContractID_seq" RENAME TO "company_unit_contract_ContractID_seq";
CREATE SEQUENCE "public"."employee_contract_ContractID_seq" INCREMENT 1;
ALTER TABLE "public"."employee_contract" ALTER COLUMN "contract_id" SET DEFAULT nextval('"employee_contract_ContractID_seq"'::regclass);
CREATE INDEX "employee_contract_employee_id_key" ON "public"."employee_contract" USING BTREE ("employee_id");
CREATE INDEX "employee_contract_product_id_key" ON "public"."employee_contract" USING BTREE ("product_id");
CREATE SEQUENCE "public"."config_ConfigID_seq" INCREMENT 1 START 1;
CREATE TABLE "public"."config" ("config_id" integer DEFAULT nextval('"config_ConfigID_seq"'::regclass), "code" character varying(255) NOT NULL, "value" text NOT NULL, PRIMARY KEY ("config_id")) WITHOUT OIDS;
CREATE UNIQUE INDEX "config_code_key" ON "public"."config" USING BTREE ("code");
INSERT INTO "public"."config" ("config_id","code","value")
						VALUES (nextval('"config_ConfigID_seq"'::regclass),'vat_exception_7','');
INSERT INTO "public"."config" ("config_id","code","value")
						VALUES (nextval('"config_ConfigID_seq"'::regclass),'vat_exception_19','');