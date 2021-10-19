ALTER TABLE "public"."product_group" ADD COLUMN "sort_order" integer NOT NULL DEFAULT 0;
ALTER TABLE "public"."product_group" ALTER COLUMN "sort_order" DROP DEFAULT;
UPDATE "public"."product_group" SET "sort_order"='1' WHERE "group_id"='1';
INSERT INTO "public"."product_group" ("group_id","title","created","code","sort_order")
						VALUES (nextval('"product_group_GroupID_seq"'::regclass),'Benefit Service',NOW(),'benefit','2');
INSERT INTO "public"."product_group" ("group_id","title","created","code","sort_order")
						VALUES (nextval('"product_group_GroupID_seq"'::regclass),'Internet Service',NOW(),'internet','3');
INSERT INTO "public"."product_group" ("group_id","title","created","code","sort_order")
						VALUES (nextval('"product_group_GroupID_seq"'::regclass),'Advertisement Service',NOW(),'ad','4');
INSERT INTO "public"."product_group" ("group_id","title","created","code","sort_order")
						VALUES (nextval('"product_group_GroupID_seq"'::regclass),'Recreation Service',NOW(),'recreation','5');
INSERT INTO "public"."product_group" ("group_id","title","created","code","sort_order")
						VALUES (nextval('"product_group_GroupID_seq"'::regclass),'Mobile Service',NOW(),'mobile','6');
INSERT INTO "public"."product_group" ("group_id","title","created","code","sort_order")
						VALUES (nextval('"product_group_GroupID_seq"'::regclass),'Gifts',NOW(),'gift','7');
UPDATE "public"."product_group" SET "title"='Food Service' WHERE "group_id"='1';

ALTER TABLE "public"."receipt" ADD COLUMN "group_id" integer NOT NULL DEFAULT 0;
UPDATE "public"."receipt" SET group_id='1';