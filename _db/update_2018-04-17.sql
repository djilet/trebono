ALTER TABLE company RENAME COLUMN "CompanyID" TO "company_id";
ALTER TABLE company RENAME COLUMN "Title" to "title";
ALTER TABLE company RENAME COLUMN "Created" to "created";
ALTER TABLE company RENAME COLUMN "Colorscheme" to "colorscheme";
ALTER TABLE company RENAME COLUMN "Logo" to "logo";
ALTER TABLE company RENAME COLUMN "Address" to "address";

ALTER TABLE company_contact RENAME COLUMN "ContactID" to "contact_id";
ALTER TABLE company_contact RENAME COLUMN "CompanyID" to "company_id";
ALTER TABLE company_contact RENAME COLUMN "Name" to "name";
ALTER TABLE company_contact RENAME COLUMN "Position" to "position";
ALTER TABLE company_contact RENAME COLUMN "Created" to "created";

ALTER TABLE company_unit RENAME COLUMN "CompanyUnitID" to "company_unit_id";
ALTER TABLE company_unit RENAME COLUMN "CompanyID" to "company_id";
ALTER TABLE company_unit RENAME COLUMN "Title" to "title";
ALTER TABLE company_unit RENAME COLUMN "ParentUnitID" to "parent_unit_id";
ALTER TABLE company_unit RENAME COLUMN "Created" to "created";

ALTER TABLE company_unit_option RENAME COLUMN "OptionID" to "option_id";
ALTER TABLE company_unit_option RENAME COLUMN "CompanyUnitID" to "company_unit_id";
ALTER TABLE company_unit_option RENAME COLUMN "KeyID" to "key_id";
ALTER TABLE company_unit_option RENAME COLUMN "Value" to "value";
ALTER TABLE company_unit_option RENAME COLUMN "Created" to "created";

ALTER TABLE "public"."company_unit" ALTER COLUMN "parent_unit_id" DROP NOT NULL;
ALTER TABLE "public"."company" ADD COLUMN "company_image" character varying(255);
ALTER TABLE "public"."company" ADD COLUMN "company_image_config" text;
ALTER TABLE "public"."company" DROP COLUMN "logo";
ALTER TABLE "public"."company" ADD COLUMN "phone" character varying(255);
ALTER TABLE "public"."company" ADD COLUMN "email" character varying(255);