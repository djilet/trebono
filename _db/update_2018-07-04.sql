UPDATE "public"."option" SET "code"='food__main__employee_meal_grant_mandatory' WHERE "code"='food__main__employer_meal_grant_mandatory';
ALTER TABLE "public"."receipt" RENAME COLUMN "units_approved" TO "amount_approved";
ALTER TABLE "public"."receipt" ALTER COLUMN "amount_approved" TYPE numeric(10,2);
