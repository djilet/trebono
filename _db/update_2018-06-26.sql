ALTER TABLE "public"."product_group" RENAME COLUMN "GroupID" TO "group_id";
ALTER TABLE "public"."product_group" RENAME COLUMN "Title" TO "title";
ALTER TABLE "public"."product_group" RENAME COLUMN "Created" TO "created";
ALTER TABLE "public"."product" RENAME COLUMN "ProductID" TO "product_id";
ALTER TABLE "public"."product" RENAME COLUMN "GroupID" TO "group_id";
ALTER TABLE "public"."product" RENAME COLUMN "Title" TO "title";
ALTER TABLE "public"."product" RENAME COLUMN "Created" TO "created";
ALTER TABLE "public"."product_group" ADD COLUMN "code" character varying(255) NOT NULL;
ALTER TABLE "public"."product" ADD COLUMN "code" character varying(255) NOT NULL;
CREATE UNIQUE INDEX "product_code_key" ON "public"."product" USING BTREE ("code");
CREATE UNIQUE INDEX "product_group_code_key" ON "public"."product_group" USING BTREE ("code");
ALTER TABLE "public"."option_key" RENAME TO "option";
ALTER TABLE "public"."option" RENAME COLUMN "KeyID" TO "option_id";
ALTER TABLE "public"."option" RENAME COLUMN "Type" TO "type";
ALTER TABLE "public"."option" RENAME COLUMN "Name" TO "code";
ALTER TABLE "public"."option" RENAME COLUMN "Title" TO "title";
ALTER TABLE "public"."company_unit_option" RENAME TO "company_unit_option_value";
ALTER TABLE "public"."company_unit_option_value" RENAME COLUMN "option_id" TO "value_id";
ALTER TABLE "public"."company_unit_option_value" RENAME COLUMN "key_id" TO "option_id";
ALTER TABLE "public"."company_unit_option_value" ADD COLUMN "user_id" integer;
ALTER TABLE "public"."company_unit_option_value" ALTER COLUMN "user_id" SET NOT NULL;
ALTER TABLE "public"."employee_option" RENAME TO "employee_option_value";
ALTER TABLE "public"."employee_option_value" RENAME COLUMN "OptionID" TO "value_id";
ALTER TABLE "public"."employee_option_value" RENAME COLUMN "EmployeeID" TO "employee_id";
ALTER TABLE "public"."employee_option_value" RENAME COLUMN "KeyID" TO "option_id";
ALTER TABLE "public"."employee_option_value" RENAME COLUMN "Value" TO "value";
ALTER TABLE "public"."employee_option_value" RENAME COLUMN "Created" TO "created";
ALTER TABLE "public"."employee_option_value" ADD COLUMN "user_id" integer NOT NULL;
ALTER TABLE "public"."contract_option" RENAME TO "option_value";
ALTER TABLE "public"."option_value" RENAME COLUMN "OptionID" TO "value_id";
ALTER TABLE "public"."option_value" DROP COLUMN "ContractID";
ALTER TABLE "public"."option_value" RENAME COLUMN "KeyID" TO "option_id";
ALTER TABLE "public"."option_value" RENAME COLUMN "Value" TO "value";
ALTER TABLE "public"."option_value" RENAME COLUMN "Created" TO "created";
ALTER TABLE "public"."option_value" ADD COLUMN "user_id" integer NOT NULL;
ALTER TABLE "public"."option" ADD COLUMN "sort_order" integer NOT NULL;
ALTER TABLE "public"."option" ADD COLUMN "product_id" integer NOT NULL;
ALTER TABLE "public"."option" ADD COLUMN "group_id" integer NOT NULL;
CREATE INDEX "option_key_group_id_key" ON "public"."option" USING BTREE ("group_id");
CREATE TYPE flag AS ENUM ('Y', 'N');
ALTER TABLE "public"."option" ADD COLUMN "in_global" flag NOT NULL;
ALTER TABLE "public"."option" ADD COLUMN "in_company_unit" flag NOT NULL;
ALTER TABLE "public"."option" ADD COLUMN "in_employee" flag NOT NULL;
ALTER TABLE "public"."option" RENAME COLUMN "in_global" TO "level_global";
ALTER TABLE "public"."option" RENAME COLUMN "in_company_unit" TO "level_company_unit";
ALTER TABLE "public"."option" RENAME COLUMN "in_employee" TO "level_employee";
CREATE TABLE "public"."option_group" ("group_id" integer, "code" character varying(255) UNIQUE, "title" character varying(255), "sort_order" integer, PRIMARY KEY ("group_id")) WITHOUT OIDS;
CREATE SEQUENCE "public"."option_group_GroupID_seq" INCREMENT 1;
ALTER TABLE "public"."option_group" ALTER COLUMN "group_id" SET DEFAULT nextval('"option_group_GroupID_seq"'::regclass);
ALTER TABLE "public"."contract" RENAME COLUMN "ContractID" TO "contract_id";
ALTER TABLE "public"."contract" RENAME COLUMN "CompanyID" TO "company_unit_id";
ALTER TABLE "public"."contract" RENAME COLUMN "ProductID" TO "product_id";
ALTER TABLE "public"."contract" ADD FOREIGN KEY ("company_unit_id") REFERENCES "public"."company_unit"("company_unit_id") ;
ALTER TABLE "public"."contract" DROP CONSTRAINT "contract_fk0";
ALTER TABLE "public"."contract" DROP COLUMN "PartnerID";
ALTER TABLE "public"."contract" RENAME COLUMN "Created" TO "created";
ALTER TABLE "public"."contract" ADD COLUMN "start_date" date NOT NULL;
ALTER TABLE "public"."contract" ADD COLUMN "end_date" date NOT NULL;
ALTER TABLE "public"."contract" RENAME TO "company_unit_contract";
ALTER TABLE "public"."company_unit_contract" ALTER COLUMN "end_date" DROP NOT NULL;
CREATE TYPE option_type AS ENUM ('int', 'float', 'string', 'currency', 'flag');
ALTER TABLE "public"."option" ALTER COLUMN "type" TYPE option_type USING NULL;

INSERT INTO "public"."product_group" ("group_id","title","created","code")
						VALUES (nextval('"product_group_GroupID_seq"'::regclass),'Food service',NOW(),'food');
INSERT INTO "public"."product" ("product_id","group_id","title","created","code")
						VALUES (nextval('"product_ProductID_seq"'::regclass),'1','Main service',NOW(),'food__main');
INSERT INTO "public"."product" ("product_id","group_id","title","created","code")
						VALUES (nextval('"product_ProductID_seq"'::regclass),'1','Plausibility check document',NOW(),'food__document_plausibility');
INSERT INTO "public"."product" ("product_id","group_id","title","created","code")
						VALUES (nextval('"product_ProductID_seq"'::regclass),'1','Examination lump-sum tax',NOW(),'food__lump_sum_tax_examination');
INSERT INTO "public"."product" ("product_id","group_id","title","created","code")
						VALUES (nextval('"product_ProductID_seq"'::regclass),'1','Weekly shopping','NOW()','food__weekly_shopping');
INSERT INTO "public"."product" ("product_id","group_id","title","created","code")
						VALUES (nextval('"product_ProductID_seq"'::regclass),'1','Canteen use',NOW(),'food__canteen');

INSERT INTO "public"."option_group" ("group_id","code","title","sort_order")
						VALUES (nextval('"option_group_GroupID_seq"'::regclass),'basic','Basic info','1');
INSERT INTO "public"."option_group" ("group_id","code","title","sort_order")
						VALUES (nextval('"option_group_GroupID_seq"'::regclass),'limits_for_units','Limits for units','2');
INSERT INTO "public"."option_group" ("group_id","code","title","sort_order")
						VALUES (nextval('"option_group_GroupID_seq"'::regclass),'special_values','Special values','3');

INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__main__monthly_price','Monthly service price','1','1','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__main__montly_discount','Discount for food service','2','1','1','N','Y','Y');						
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__main__implementation_price','Implementation fee','3','1','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__main__implementation_discount','Discount for implem. fee','4','1','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'string','food__main__salary_option','Salary option','5','1','1','N','Y','Y');						
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'int','food__main__units_per_week','Units per week','1','1','2','Y','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'int','food__main__units_per_month','Units per month','2','1','2','Y','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'int','food__main__units_per_week_transfer','Units fror transfer','3','1','2','Y','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__main__meal_value','Meal value','1','1','3','Y','N','N');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__main__employer_meal_grant','Employer meal grant','2','1','3','Y','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__main__employee_meal_grant','Employee meal grant','3','1','3','Y','N','N');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'flag','food__main__auto_adoption','Aut. adoption','1','1','3','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'flag','food__main__employer_meal_grant_mandatory','Employee meal grant mandatory','2','1','3','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__document_plausibility__monthly_price','Montly service price','1','2','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__document_plausibility__monthly_discount','Discount for plausability','2','2','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__document_plausibility__implementation_price','Implementation fee','3','2','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__document_plausibility__implementation_discount','Discount for implem. fee','4','2','1','N','Y','Y');						
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__lump_sum_tax_examination__monthly_price','Montly service price','1','3','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__lump_sum_tax_examination__monthly_discount','Discount for examination','2','3','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__lump_sum_tax_examination__implementation_price','Implementation fee','3','3','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__lump_sum_tax_examination__implementation_discount','Discount for implem. fee','4','3','1','N','Y','Y');						
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__weekly_shopping__monthly_price','Montly service price','1','4','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__weekly_shopping__monthly_discount','Discount for weekly shopping','2','4','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__weekly_shopping__implementation_price','Implementation fee','3','4','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__weekly_shopping__implementation_discount','Discount for implem. fee','4','4','1','N','Y','Y');						
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__canteen__monthly_price','Montly service price','1','5','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__canteen__monthly_discount','Discount for canteen','2','5','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'currency','food__canteen__implementation_price','Implementation fee','3','5','1','N','Y','Y');
INSERT INTO "public"."option" ("option_id","type","code","title","sort_order","product_id","group_id","level_global","level_company_unit","level_employee")
						VALUES (nextval('"option_key_KeyID_seq"'::regclass),'float','food__canteen__implementation_discount','Discount for implem. fee','4','5','1','N','Y','Y');