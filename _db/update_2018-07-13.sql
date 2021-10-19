ALTER TABLE "public"."invoice" RENAME COLUMN "InvoiceID" TO "invoice_id";
ALTER TABLE "public"."invoice" DROP COLUMN "ContractID";
ALTER TABLE "public"."invoice" DROP COLUMN "Title";
ALTER TABLE "public"."invoice" DROP COLUMN "PeriodFrom";
ALTER TABLE "public"."invoice" DROP COLUMN "PeriodTo";
ALTER TABLE "public"."invoice" DROP COLUMN "PayDate";
ALTER TABLE "public"."invoice" ADD COLUMN "company_unit_id" integer NOT NULL;
ALTER TABLE "public"."invoice" RENAME COLUMN "Created" TO "created";
ALTER TABLE "public"."invoice" RENAME COLUMN "Status" TO "status";
ALTER TABLE "public"."invoice" DROP COLUMN "created";
ALTER TABLE "public"."invoice" DROP COLUMN "status";
ALTER TABLE "public"."invoice" ADD COLUMN "created" timestamp without time zone NOT NULL;
ALTER TABLE "public"."invoice" ADD COLUMN "date_from" date NOT NULL;
ALTER TABLE "public"."invoice" ADD COLUMN "date_to" date NOT NULL;
ALTER TABLE "public"."invoice" ADD COLUMN "status" character varying(255) NOT NULL;
CREATE SEQUENCE "public"."invoice_line_InvoiceLineID_seq" INCREMENT 1 START 1;
CREATE TABLE "public"."invoice_line" ("invoice_line_id" integer DEFAULT nextval('"invoice_line_InvoiceLineID_seq"'::regclass), "invoice_id" integer NOT NULL, "product_id" integer NOT NULL, "type" character varying(255) NOT NULL, "cost" numeric(10,2) NOT NULL, PRIMARY KEY ("invoice_line_id")) WITHOUT OIDS;
ALTER TABLE "public"."invoice_line" ADD COLUMN "quantity" integer NOT NULL;
ALTER TABLE "public"."company_unit" ADD COLUMN "customer_guid" integer;
CREATE UNIQUE INDEX "company_unit_customer_guid_key" ON "public"."company_unit" USING BTREE ("customer_guid");
ALTER TABLE "public"."company_unit" ALTER COLUMN "customer_guid" TYPE character varying(255);
ALTER TABLE "public"."invoice" ADD COLUMN "invoice_guid" character varying(255);