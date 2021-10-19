<?php 
declare(strict_types=1);

require_once(dirname(__FILE__)."/../../include/init.php");
es_include("localobject.php");

use PHPUnit\Framework\TestCase;

final class InitDatabase extends TestCase
{
    public function testConstructor()
    {
        //user permissions
        $stmt = GetStatement(DB_PERSONAL);
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(1,'root','Administrator',NULL)"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(2,'company_unit','Company administrator','company_unit')"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(3,'employee','Employee administrator','company_unit')"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(4,'api','Mobile user',NULL)"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(5,'receipt','Receipt processing','company_unit')"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(6,'support','Support manager',NULL)"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(7,'invoice','Invoice receiver','company_unit')"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(8,'payroll','Payroll receiver','company_unit')"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(9,'tax_auditor','Tax auditor','company_unit')"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(10,'service','Service administrator','product_group')"));
        $this->assertTrue($stmt->Execute("INSERT INTO permission(permission_id,name,title,link_to) VALUES(11,'webapi','Web administrator',NULL)"));

        $count = $stmt->FetchField("SELECT COUNT(*) FROM permission");
        $this->assertEquals($count, 11);

        $stmt = GetStatement(DB_MAIN);
        //product group
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(8,'Base Module', '2018-08-02 12:08:34.140202', 'base',1 ,'N','Y','N','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(4,  'Advertisement Service','2018-07-03 05:39:19.088632', 'ad', 5  ,'Y','N',  'zbqrdxpw0w.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(6,  'Mobile Service','2018-07-03 05:39:19.088632', 'mobile', 7  ,'Y','Y',  'pph7a6zq3g.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(1,  'Food Service','2018-06-26 13:31:41.913544', 'food',2  ,'Y','Y',  '2atqyg09ha.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(2,  'Benefit Service','2018-07-03 05:39:19.088632', 'benefit', 3  ,'Y','Y',  'a8qrgjdqkg.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(3,  'Internet Service','2018-07-03 05:39:19.088632', 'internet',4  ,'Y','Y',  'ilmru71ods.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(5,  'Recreation Service','2018-07-03 05:39:19.088632', 'recreation', 6  ,'Y','N',  'h7yv0vgmtq.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(7,  'Gifts','2018-07-03 05:39:19.088632', 'gift',8  ,'Y','Y',  'fidqqr8lkk.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(9,  'Bonus Service','2018-12-21 12:39:36.032908', 'bonus',  9  ,'Y','Y',  'e2782xjla8.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(10, 'Public Transportation Service','2019-01-22 11:52:43.6488','transport',  10 ,'Y','Y',  'ffy8in0afg.png', '{\"Width\":0,\"Height\":0}','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(43, 'Child Care Service','2019-03-13 17:40:06.741521', 'child_care', 11 ,'Y','N','N','N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(47, 'Givve Service','2019-03-19 14:04:46.756598', 'givve',  13 ,'Y','Y','N','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group (group_id, title, created, code, sort_order, receipts, need_check_image, product_group_image, product_group_image_config, multiple_receipt_file)
                  VALUES(44, 'Travel Management Service', '2019-03-13 17:40:06.805321', 'travel', 12 ,'Y','N','N','N','Y')"));

        //product
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(2,1,'Plausibility check document','2018-06-26 13:31:41.913544','food__document_plausibility','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(3,1,'Examination lump-sum tax', '2018-06-26 13:31:41.913544','food__lump_sum_tax_examination','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(4,1,'Weekly shopping', '2018-06-26 13:31:41.913544','food__weekly_shopping',  'N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(5,1,'Canteen use','2018-06-26 13:31:41.913544','food__canteen',  'N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(6,8,'Base Module','2018-08-02 12:31:34.140202','base__main','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(1,1,'Main service','2018-06-26 13:31:41.913544','food__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(8,7,'Gift','2018-08-03 09:12:34','gift__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(9,3,'Internet', '2018-08-03 15:11:34','internet__main', 'Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(10,4,'Advertisement','2018-08-03 15:11:34','ad__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(13,5,'Recreation Service','2018-08-06 16:46:34','recreation__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(14,6,'Mobile',  '2018-08-07 12:44:34','mobile__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(15,2,'Benefit', '2018-08-07 15:01:34','benefit__main',  'Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(16,9,'Bonus','2018-12-21 12:39:36.032908','bonus__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(17,10,'Public Transportation','2019-01-22 11:52:43.6488', 'transport__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(18,1,'Food Service Advanced Security',  '2019-01-30 12:37:35.024319','food__advanced_security','N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(19,2,'Benefit Service Advanced Security','2019-01-30 12:37:35.024319','benefit__advanced_security','N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(20,3,'Internet Service Advanced Security','2019-01-30 12:37:35.024319','internet__advanced_security','N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(21,4,'Advertisement Service Advanced Security', '2019-01-30 12:37:35.024319','ad__advanced_security',  'N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(22,5,'Recreation Service Advanced Security','2019-01-30 12:37:35.024319','recreation__advanced_security',  'N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(23,6,'Mobile Service Advanced Security', '2019-01-30 12:37:35.024319','mobile__advanced_security','N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(24,7,'Gifts Advanced Security', '2019-01-30 12:37:35.024319','gift__advanced_security','N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(25,9,'Bonus Advanced Security', '2019-01-30 12:37:35.024319','bonus__advanced_security','N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(26,10,'Public Transportation Advanced Security', '2019-01-30 12:37:35.024319','transport__advanced_security','N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(51,43,'Child Care','2019-03-13 17:40:06.741521','child_care__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(52,43,'Child Care','2019-03-13 17:40:06.741521','child_care__advanced_security',  'N','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(53,44,'Travel Management','2019-03-13 17:40:06.805321','travel__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(57,47,'Givve','2019-03-19 14:04:46.756598','givve__main','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product (product_id, group_id, title, created, code, base_for_api, inheritable)
                  VALUES(61,44,'Travel Management','2019-04-23 14:10:00.644488','travel__advanced_security','N','Y')"));

        //receipt_type
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(1,'shop'   ,'N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(2,'restaurant','N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(3,'ticket' ,'N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(4,'accommodation'  ,'N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(5,'hospitality','N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(6,'parking','N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(7,'meal'   ,'N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(8,'other'  ,'N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(9,'doc','N','N','2019-05-14 12:25:09',-2,'N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO receipt_type (receipt_type_id, code, receipt_type_image, receipt_type_image_config, created, created_by, archive)
                  VALUES(10,'confirm','N','N','2019-05-14 12:25:09',-2,'N')"));

        //product_group_2_receipt_type
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(1,1,'shop')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(2,1,'restaurant')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(3,44,'ticket')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(4,44,'accommodation')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(5,44,'hospitality')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(6,44,'parking')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(7,44,'meal')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(8,44,'other')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(9,5,'doc')"));
        $this->assertTrue($stmt->Execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, group_id, code)
                  VALUES(10,5,'confirm')"));

        //option group
        $this->assertTrue($stmt->Execute("INSERT INTO option_group (group_id, code, title, sort_order) VALUES(1,'basic','Basic info',1)"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_group (group_id, code, title, sort_order) VALUES(2,'limits_for_units','Limits for units',2)"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_group (group_id, code, title, sort_order) VALUES(3,'special_values','Special values',3)"));

        $count = $stmt->FetchField("SELECT COUNT(*) FROM option_group");
        $this->assertEquals($count, 3);

        //option
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
                VALUES(54,'currency','recreation__main__monthly_price','Monthly service price',1,13,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
                VALUES(163,'currency','child_care__advanced_security__monthly_price','Monthly service price',1,52,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
                VALUES(185,'currency','givve__main__monthly_price', 'Monthly service price',1,57,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
                VALUES(192,'currency','travel__main__monthly_price','Monthly service price',1,53,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(14,'currency','food__document_plausibility__monthly_price','Monthly service price',1,2,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(18,'currency','food__lump_sum_tax_examination__monthly_price','Monthly service price',1,3,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(195,'int','recreation__main__max_doc_receipt_file_count','Max. number of documentation photos',4,13,3,'Y','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(196,'int','gift__main__amount_per_voucher','Max amount per voucher',1,8,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(197,'int','gift__main__qty_per_year','Max qty of vouchers per year',2,8,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(198,'int','mobile__main__payment_month_qty','Number of payment month',4,14,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(199,'int','internet__main__payment_month_qty','Number of payment month',4,9,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(20,'currency','food__lump_sum_tax_examination__implementation_price','Implementation fee',3,3,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(24,'currency','food__weekly_shopping__implementation_price','Implementation fee',3,4,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(28,'currency','food__canteen__implementation_price','Implementation fee',3,5,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(21,'int','food__lump_sum_tax_examination__implementation_discount','Discount for implem. fee',4,3,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(10,'currency','food__main__employer_meal_grant','Employer meal grant',2,1,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(11,'currency','food__main__employee_meal_grant','Employee meal grant',3,1,3,'Y','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(23,'int','food__weekly_shopping__monthly_discount','Discount for weekly shopping',2,4,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(25,'int','food__weekly_shopping__implementation_discount','Discount for implem. fee',4,4,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(27,'int','food__canteen__monthly_discount','Discount for canteen',2,5,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(81,'currency','bonus__main__monthly_discount','Discount for bonus service',2,16,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(87,'currency','transport__main__monthly_discount','Discount for public transportation service',2,17,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(82,'currency','bonus__main__implementation_price','Implementation fee',3,16,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(88,'currency','transport__main__implementation_price','Implementation fee',3,17,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(93,'currency','food__advanced_security__implementation_price','Implementation fee',3,18,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(224,'string','ad__main__receipt_option','Advertisement Service Receipt Option',6,10,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(83,'currency','bonus__main__implementation_discount','Discount for implem. fee',4,16,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(89,'currency','transport__main__implementation_discount','Discount for implem. fee',4,17,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(92,'currency','food__advanced_security__monthly_discount','Discount for food advanced security service',1,18,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(194,'int','ad__main__payment_month_qty','Number of payment month',4,10,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(162,'string','child_care__main__salary_option','Salary option',5,51,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(161,'currency','child_care__main__implementation_discount','Discount for implem. fee',4,51,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(7,'int','food__main__units_per_month','Units per month',2,1,2,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(164,'currency','child_care__advanced_security__monthly_discount','Discount for child care advanced security service',1,52,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(186,'currency','givve__main__monthly_discount','Discount for givve service',2,57,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(166,'currency','child_care__advanced_security__implementation_discount','Discount for implem. fee',4,52,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(193,'currency','travel__main__monthly_discount','Discount for travel service',2,53,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(169,'currency','travel__main__implementation_discount','Discount for implem. fee',4,53,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(8,'int','food__main__units_per_week_transfer','Units fror transfer',3,1,2,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(184,'currency','givve__main__implementation_discount','Discount for implem. fee',4,57,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(9,'currency','food__main__meal_value','Meal value',1,1,3,'Y','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(22,'currency','food__weekly_shopping__monthly_price','Monthly service price',1,4,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(26,'currency','food__canteen__monthly_price','Monthly service price',1,5,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(30,'currency','base__main__monthly_price','Monthly service price',1,6,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(34,'currency','gift__main__monthly_price','Monthly service price',1,8,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(41,'currency','internet__main__monthly_price','Monthly service price',1,9,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(47,'currency','ad__main__monthly_price','Monthly service price',1,10,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(1,'currency','food__main__monthly_price','Monthly service price',1,1,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(64,'currency','mobile__main__monthly_price','Monthly service price',1,14,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(77, 'string','benefit__main__receipt_option','Benefit Service Receipt Option',2,15,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(165,'currency','child_care__advanced_security__implementation_price','Implementation fee',3,52,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(168,'currency','travel__main__implementation_price','Implementation fee',3,53,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(183,'currency','givve__main__implementation_price','Implementation fee',3,57,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(46,'currency','internet__main__employer_grant','Max. Monthly Value (Employer Grant)',1,9,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(52,'currency','ad__main__max_yearly','Max. yearly Value',1,10,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(62,'currency','recreation__main__max_child','Max. Value Child',3,13,3,'Y','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(61,'currency','recreation__main__max_spouse','Max. Value Spouse',2,13,3,'Y','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(60,'currency','recreation__main__max_employee','Max. Value Employee',1,13,3,'Y','N','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(111,'currency','mobile__advanced_security__monthly_price','Monthly service price',1,23,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(115,'currency','gift__advanced_security__monthly_price','Monthly service price',1,24,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(212,'currency','travel__advanced_security__monthly_price','Monthly service price',1,61,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(213,'currency','travel__advanced_security__monthly_discount','Discount for travel management advanced security service',1,61,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(214,'currency','travel__advanced_security__implementation_price','Implementation fee',3,61,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(215,'currency','travel__advanced_security__implementation_discount','Discount for implem. fee',4,61,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(167,'flag','base__force_approval','Receipts force approval',1,6,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(59,'currency','recreation__main__max_value','Max. Value (Employer Grant)',1,13,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(68, 'string', 'mobile__main__salary_option','Salary option',5,14,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(58,'string','recreation__main__salary_option','Salary option',5,13,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(75,'string','benefit__main__salary_option','Salary option',5,15,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(71,'currency','benefit__main__monthly_price','Monthly service price',1,15,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(107,'currency','recreation__advanced_security__monthly_price','Monthly service price',1,22,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(79,'currency','bonus__main__max_yearly','Max. Yearly Value',1,16,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(32,'currency','base__main__implementation_price','Implementation fee',3,6,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(36,'currency','gift__main__implementation_price','Implementation fee',3,8,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(43,'currency','internet__main__implementation_price','Implementation fee',3,9,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(49,'currency','ad__main__implementation_price','Implementation fee',3,10,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(3,'currency','food__main__implementation_price','Implementation fee',3,1,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(16,'currency','food__document_plausibility__implementation_price','Implementation fee',3,2,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(56,'currency','recreation__main__implementation_price','Implementation fee',3,13,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(29,'int','food__canteen__implementation_discount','Discount for implem. fee',4,5,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(33,'int','base__main__implementation_discount','Discount for implem. fee',4,6,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(37,'int','gift__main__implementation_discount','Discount for implem. fee',4,8,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(13,'flag','food__main__employee_meal_grant_mandatory','Employee meal grant mandatory',2,1,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(55,'int','recreation__main__monthly_discount','Discount for recreation service',2,13,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(44,'int','internet__main__implementation_discount','Discount for implem. fee',4,9,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(50,'int','ad__main__implementation_discount','Discount for implem. fee',4,10,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(2,'int','food__main__monthly_discount','Discount for food service',2,1,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(4,'int','food__main__implementation_discount','Discount for implem. fee',4,1,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(15,'int','food__document_plausibility__monthly_discount','Discount for plausability',2,2,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(17,'int','food__document_plausibility__implementation_discount','Discount for implem. fee',4,2,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(19,'int','food__lump_sum_tax_examination__monthly_discount','Discount for examination',2,3,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(57,'int','recreation__main__implementation_discount','Discount for implem. fee',4,13,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(42,'int','internet__main__monthly_discount','Discount for internet service',2,9,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(48,'int','ad__main__monthly_discount','Discount for advertisement service',2,10,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(35,'int','gift__main__monthly_discount','Discount for gift service',2,8,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(31,'int','base__main__monthly_discount','Discount for base module',2,6,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(65,'int','mobile__main__monthly_discount','Discount for mobile service',2,14,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(67,'int','mobile__main__implementation_discount','Discount for implem. fee',4,14,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(72,'int','benefit__main__monthly_discount','Discount for benefit service',2,15,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(74,'int','benefit__main__implementation_discount','Discount for implem. fee',4,15,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(94,'currency','food__advanced_security__implementation_discount','Discount for implem. fee',4,18,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(96,'currency','benefit__advanced_security__monthly_discount','Discount for benefit advanced security service',1,19,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(98,'currency','benefit__advanced_security__implementation_discount','Discount for implem. fee',4,19,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(102,'currency','internet__advanced_security__implementation_discount','Discount for implem. fee',4,20,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(66,'currency','mobile__main__implementation_price','Implementation fee',3,14,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(104,'currency','ad__advanced_security__monthly_discount','Discount for ad advanced security service',1,21,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(106,'currency','ad__advanced_security__implementation_discount','Discount for implem. fee',4,21,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(119,'currency','bonus__advanced_security__monthly_price','Monthly service price',1,25,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(110,'currency','recreation__advanced_security__implementation_discount','Discount for implem. fee',4,22,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(114,'currency','mobile__advanced_security__implementation_discount','Discount for implem. fee',4,23,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(123,'currency','transport__advanced_security__monthly_price','Monthly service price',1,26,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(118,'currency','gift__advanced_security__implementation_discount','Discount for implem. fee',4,24,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(158,'currency','child_care__main__monthly_price','Monthly service price',1,51,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(120,'currency','bonus__advanced_security__monthly_discount','Discount for bonus advanced security service',1,25,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(122,'currency','bonus__advanced_security__implementation_discount','Discount for implem. fee',4,25,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(126,'currency','transport__advanced_security__implementation_discount','Discount for implem. fee',4,26,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(78,'int','food__weekly_shopping__receipt_period','Weekly purchase receipt period (days)',5,4,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(6,'int','food__main__units_per_week','Units per week',1,1,2,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(63, 'string', 'ad__main__payment_month','Payment Month for Yearly Value',3,10,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(5,'string', 'food__main__salary_option','Salary option',5,1,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(53,'currency','ad__main__employer_grant','Max. Monthly Value',2,10,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(69,'currency','mobile__main__employer_grant','Max. Monthly Value (Employer Grant)',1,14,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(38,'string','gift__main__salary_option','Salary option',5,8,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(45,'string','internet__main__salary_option','Salary option',5,9,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(51,'string','ad__main__salary_option','Salary option',5,10,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(159,'currency','child_care__main__monthly_discount','Discount for child care service',2,51,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(116,'currency','gift__advanced_security__monthly_discount','Discount for gift advanced security service',2,24,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(100,'currency','internet__advanced_security__monthly_discount','Discount for internet advanced security service',2,20,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(112,'currency','mobile__advanced_security__monthly_discount','Discount for mobile advanced security service',2,23,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(108,'currency','recreation__advanced_security__monthly_discount','Discount for recreation advanced security service',2,22,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(124,'currency','transport__advanced_security__monthly_discount','Discount for transport advanced security service',2,26,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(73,'currency','benefit__main__implementation_price','Implementation fee',3,15,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(97,'currency','benefit__advanced_security__implementation_price','Implementation fee',3,19,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(76,'currency','benefit__main__employer_grant','Max. Monthly Value (Employer Grant)',1,15,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(101,'currency','internet__advanced_security__implementation_price','Implementation fee',3,20,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(105,'currency','ad__advanced_security__implementation_price','Implementation fee',3,21,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(109,'currency','recreation__advanced_security__implementation_price','Implementation fee',3,22,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(113,'currency','mobile__advanced_security__implementation_price','Implementation fee',3,23,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(117,'currency','gift__advanced_security__implementation_price','Implementation fee',3,24,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(121,'currency','bonus__advanced_security__implementation_price','Implementation fee',3,25,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(125,'currency','transport__advanced_security__implementation_price','Implementation fee',3,26,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(160,'currency','child_care__main__implementation_price','Implementation fee',3,51,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(84, 'string', 'bonus__main__salary_option','Salary option',5,16,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(85,'currency','transport__main__max_monthly','Max. Value Per Month',1,17,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(90, 'string', 'transport__main__salary_option','Salary option',5,17,3,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(157,'currency','child_care__main__max_monthly','Max. Value Per Month',1,51,3,'Y','Y','Y')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(80,'currency','bonus__main__monthly_price','Monthly service price',1,16,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(86,'currency','transport__main__monthly_price','Monthly service price',1,17,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(91,'currency','food__advanced_security__monthly_price','Monthly service price',1,18,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(95,'currency','benefit__advanced_security__monthly_price','Monthly service price',1,19,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(99,'currency','internet__advanced_security__monthly_price','Monthly service price',1,20,1,'Y','Y','N')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option (option_id, type, code, title, sort_order, product_id, group_id, level_global, level_company_unit, level_employee)
        VALUES(103,'currency','ad__advanced_security__monthly_price','Monthly service price',1,21,1,'Y','Y','N')"));

        $stmt = GetStatement(DB_CONTROL);

        //global option values for payroll test
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 52, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 53, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 76, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 79, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 157, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 162, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 11, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 13, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 10, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 9, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 7, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 6, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 8, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 78, 5, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 196, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 197, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 46, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 199, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 69, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 198, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
        $this->assertTrue($stmt->Execute("INSERT INTO option_value_history (value_id, level, entity_id, option_id, value, date_from, user_id, created_from, created)
          VALUES(1, 'global', 0, 85, 2, '2018-10-12 14:31:23', 1, 'admin', '2018-10-12 14:31:23')"));
    }
}
?>