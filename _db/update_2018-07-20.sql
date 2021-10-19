ALTER TABLE "public"."company_unit_contract" ADD COLUMN "start_user_id" integer NOT NULL DEFAULT 0;
ALTER TABLE "public"."company_unit_contract" ADD COLUMN "end_user_id" integer;
UPDATE company_unit_contract SET start_user_id=1;
UPDATE company_unit_contract SET end_user_id=1 WHERE end_date IS NOT NULL;
ALTER TABLE "public"."company_unit_contract" ALTER COLUMN "start_user_id" DROP DEFAULT;

ALTER TABLE "public"."employee_contract" ADD COLUMN "start_user_id" integer NOT NULL DEFAULT 0;
ALTER TABLE "public"."employee_contract" ADD COLUMN "end_user_id" integer;
UPDATE employee_contract SET start_user_id=1;
UPDATE employee_contract SET end_user_id=1 WHERE end_date IS NOT NULL;
ALTER TABLE "public"."employee_contract" ALTER COLUMN "start_user_id" DROP DEFAULT;