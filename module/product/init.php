<?php

define("PRODUCT_IMAGE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/product/");
define("PRODUCT_GROUP_IMAGE", "50x50|1|admin,100x100|0|full,500x500|1|api");
define("RECEIPT_TYPE_IMAGE", "50x50|1|admin,100x100|0|full,500x500|1|api");

define("CONTAINER__PRODUCT", "");


define("CONTAINER__RECEIPT__AD", "");
define("CONTAINER__RECEIPT__BASE", "");
define("CONTAINER__RECEIPT__BENEFIT", "");
define("CONTAINER__RECEIPT__BONUS", "_test");
define("CONTAINER__RECEIPT__BONUS_VOUCHER", "_test");
define("CONTAINER__RECEIPT__CHILD_CARE", "");
define("CONTAINER__RECEIPT__FOOD", "");
define("CONTAINER__RECEIPT__GIFT", "");
define("CONTAINER__RECEIPT__GIVVE", "");
define("CONTAINER__RECEIPT__INTERNET", "");
define("CONTAINER__RECEIPT__MOBILE", "");
define("CONTAINER__RECEIPT__RECREATION", "");
define("CONTAINER__RECEIPT__STORED_DATA", "");
define("CONTAINER__RECEIPT__TRANSPORT", "");
define("CONTAINER__RECEIPT__TRAVEL", "");
define("CONTAINER__RECEIPT__BENEFIT_VOUCHER", "");
define("CONTAINER__RECEIPT__FOOD_VOUCHER", "");
define("CONTAINER__RECEIPT__GIFT_VOUCHER", "");
define("CONTAINER__RECEIPT__CORPORATE_HEALTH_MANAGEMENT", "");

//never touch these contants!
define("OPTION_TYPE_INT", "int");
define("OPTION_TYPE_FLOAT", "float");
define("OPTION_TYPE_STRING", "string");
define("OPTION_TYPE_CURRENCY", "currency");
define("OPTION_TYPE_FLAG", "flag");

define("OPTION_LEVEL_GLOBAL", "global");
define("OPTION_LEVEL_COMPANY_UNIT", "company_unit");
define("OPTION_LEVEL_EMPLOYEE", "employee");

//format is {entity}__{productgroup}[__{product}][__{option}]
define("PRODUCT_GROUP__BASE", "base");
define("PRODUCT__BASE__MAIN", "base__main");
define("PRODUCT__BASE__INTERRUPTION", "base__interruption");

define("OPTION__BASE__MAIN__MONTHLY_PRICE", "base__main__monthly_price");
define("OPTION__BASE__MAIN__MONTHLY_DISCOUNT", "base__main__monthly_discount");
define("OPTION__BASE__MAIN__IMPLEMENTATION_PRICE", "base__main__implementation_price");
define("OPTION__BASE__MAIN__IMPLEMENTATION_DISCOUNT", "base__main__implementation_discount");
define("OPTION__BASE__FORCE_APPROVAL", "base__force_approval");
define("OPTION__BASE__MAIN__DEACTIVATION_REASON", "base__main__deactivation_reason");
define("OPTION__BASE__MAIN__UPLOAD_PICTURES_FROM_GALLERY", "base__main__upload_pictures_from_gallery");

define("OPTION__BASE__INTERRUPTION__MONTHLY_PRICE", "base__interruption__monthly_price");
define("OPTION__BASE__INTERRUPTION__MONTHLY_DISCOUNT", "base__interruption__monthly_discount");
define("OPTION__BASE__INTERRUPTION__IMPLEMENTATION_PRICE", "base__interruption__implementation_price");
define("OPTION__BASE__INTERRUPTION__IMPLEMENTATION_DISCOUNT", "base__interruption__implementation_discount");

define("PRODUCT_GROUP__FOOD", "food");

define("PRODUCT__FOOD__MAIN", "food__main");
define("PRODUCT__FOOD__PLAUSIBILITY", "food__document_plausibility");
define("PRODUCT__FOOD__LUMP_SUM_TAX_EXAMINATION", "food__lump_sum_tax_examination");
define("PRODUCT__FOOD__WEEKLY_SHOPPING", "food__weekly_shopping");
define("PRODUCT__FOOD__CANTEEN", "food__canteen");
define("PRODUCT__FOOD__ADVANCED_SECURITY", "food__advanced_security");

define("OPTION__FOOD__MAIN__MONTHLY_PRICE", "food__main__monthly_price");
define("OPTION__FOOD__MAIN__MONTHLY_DISCOUNT", "food__main__monthly_discount");
define("OPTION__FOOD__MAIN__IMPLEMENTATION_PRICE", "food__main__implementation_price");
define("OPTION__FOOD__MAIN__IMPLEMENTATION_DISCOUNT", "food__main__implementation_discount");
define("OPTION__FOOD__MAIN__SALARY_OPTION", "food__main__salary_option");
define("OPTION__FOOD__MAIN__UNITS_PER_WEEK", "food__main__units_per_week");
define("OPTION__FOOD__MAIN__UNITS_PER_MONTH", "food__main__units_per_month");
define("OPTION__FOOD__MAIN__UNITS_FOR_TRANSFER", "food__main__units_per_week_transfer");
define("OPTION__FOOD__MAIN__MEAL_VALUE", "food__main__meal_value");
define("OPTION__FOOD__MAIN__EMPLOYER_MEAL_GRANT", "food__main__employer_meal_grant");
define("OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT", "food__main__employee_meal_grant");
define("OPTION__FOOD__MAIN__AUTO_ADOPTION", "food__main__auto_adoption");
define("OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY", "food__main__employee_meal_grant_mandatory");
define("OPTION__FOOD__MAIN__IMPORTANT_INFO", "food__main__important_info");
define("OPTION__FOOD__MAIN__INTERNAL_VERIFICATION_INFO", "food__main__internal_verification_info");
define("OPTION__FOOD__MAIN__FLEX_OPTION", "food__main__flex_option");
define("OPTION__FOOD__MAIN__FLEX_UNIT_PRICE", "food__main__flex_unit_price");
define("OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE", "food__main__flex__unit_percentage");
define("OPTION__FOOD__MAIN__FLEX_FREE_UNITS", "food__main__flex_free_units");

define("OPTION__FOOD__DOCUMENT_PLAUSIBILITY__MONTHLY_PRICE", "food__document_plausibility__monthly_price");
define("OPTION__FOOD__DOCUMENT_PLAUSIBILITY__MONTHLY_DISCOUNT", "food__document_plausibility__monthly_discount");
define(
    "OPTION__FOOD__DOCUMENT_PLAUSIBILITY__IMPLEMENTATION_PRICE",
    "food__document_plausibility__implementation_price"
);
define(
    "OPTION__FOOD__DOCUMENT_PLAUSIBILITY__IMPLEMENTATION_DISCOUNT",
    "food__document_plausibility__implementation_discount"
);

define("OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__MONTHLY_PRICE", "food__lump_sum_tax_examination__monthly_price");
define("OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__MONTHLY_DISCOUNT", "food__lump_sum_tax_examination__monthly_discount");
define(
    "OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__IMPLEMENTATION_PRICE",
    "food__lump_sum_tax_examination__implementation_price"
);
define(
    "OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__IMPLEMENTATION_DISCOUNT",
    "food__lump_sum_tax_examination__implementation_discount"
);

define("OPTION__FOOD__WEEKLY_SHOPPING__MONTHLY_PRICE", "food__weekly_shopping__monthly_price");
define("OPTION__FOOD__WEEKLY_SHOPPING__MONTHLY_DISCOUNT", "food__weekly_shopping__monthly_discount");
define("OPTION__FOOD__WEEKLY_SHOPPING__IMPLEMENTATION_PRICE", "food__weekly_shopping__implementation_price");
define("OPTION__FOOD__WEEKLY_SHOPPING__IMPLEMENTATION_DISCOUNT", "food__weekly_shopping__implementation_discount");
define("OPTION__FOOD__WEEKLY_SHOPPING__RECEIPT_PERIOD", "food__weekly_shopping__receipt_period");

define("OPTION__FOOD__CANTEEN__MONTHLY_PRICE", "food__canteen__monthly_price");
define("OPTION__FOOD__CANTEEN__MONTHLY_DISCOUNT", "food__canteen__monthly_discount");
define("OPTION__FOOD__CANTEEN__IMPLEMENTATION_PRICE", "food__canteen__implementation_price");
define("OPTION__FOOD__CANTEEN__IMPLEMENTATION_DISCOUNT", "food__canteen__implementation_discount");

define("OPTION__FOOD__ADVANCED_SECURITY__MONTHLY_PRICE", "food__advanced_security__monthly_price");
define("OPTION__FOOD__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "food__advanced_security__monthly_discount");
define("OPTION__FOOD__ADVANCED_SECURITY__IMPLEMENTATION_PRICE", "food__advanced_security__implementation_price");
define("OPTION__FOOD__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT", "food__advanced_security__implementation_discount");

define("PRODUCT_GROUP__FOOD_VOUCHER", "food_voucher");
define("PRODUCT__FOOD_VOUCHER__MAIN", "food_voucher__main");
define("PRODUCT__FOOD_VOUCHER__ADVANCED_SECURITY", "food_voucher__advanced_security");

define("OPTION__FOOD_VOUCHER__MAIN__MONTHLY_PRICE", "food_voucher__main__monthly_price");
define("OPTION__FOOD_VOUCHER__MAIN__MONTHLY_DISCOUNT", "food_voucher__main__monthly_discount");
define("OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_PRICE", "food_voucher__main__implementation_price");
define("OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT", "food_voucher__main__implementation_discount");
define("OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION", "food_voucher__main__salary_option");
define("OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK", "food_voucher__main__units_per_week");
define("OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH", "food_voucher__main__units_per_month");
define("OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER", "food_voucher__main__units_per_week_transfer");
define("OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE", "food_voucher__main__meal_value");
define("OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT", "food_voucher__main__employer_meal_grant");
define("OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT", "food_voucher__main__employee_meal_grant");
define("OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION", "food_voucher__main__auto_adoption");
define(
    "OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY",
    "food_voucher__main__employee_meal_grant_mandatory"
);
define("OPTION__FOOD_VOUCHER__MAIN__PAYROLL_EXPORT", "food_voucher__main__payroll_export");
define("OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION", "food_voucher__main__auto_generation");
define("OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO", "food_voucher__main__important_info");
define("OPTION__FOOD_VOUCHER__MAIN__INTERNAL_VERIFICATION_INFO", "food_voucher__main__internal_verification_info");

define("OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__MONTHLY_PRICE", "food_voucher__advanced_security__monthly_price");
define(
    "OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__MONTHLY_DISCOUNT",
    "food_voucher__advanced_security__monthly_discount"
);
define(
    "OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_PRICE",
    "food_voucher__advanced_security__implementation_price"
);
define(
    "OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "food_voucher__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__BENEFIT", "benefit");
define("PRODUCT__BENEFIT__MAIN", "benefit__main");
define("PRODUCT__BENEFIT__ADVANCED_SECURITY", "benefit__advanced_security");

define("OPTION__BENEFIT__MAIN__MONTHLY_PRICE", "benefit__main__monthly_price");
define("OPTION__BENEFIT__MAIN__MONTHLY_DISCOUNT", "benefit__main__monthly_discount");
define("OPTION__BENEFIT__MAIN__IMPLEMENTATION_PRICE", "benefit__main__implementation_price");
define("OPTION__BENEFIT__MAIN__IMPLEMENTATION_DISCOUNT", "benefit__main__implementation_discount");
define("OPTION__BENEFIT__MAIN__SALARY_OPTION", "benefit__main__salary_option");
define("OPTION__BENEFIT__MAIN__EMPLOYER_GRANT", "benefit__main__employer_grant");
define("OPTION__BENEFIT__MAIN__RECEIPT_OPTION", "benefit__main__receipt_option");
define("OPTION__BENEFIT__MAIN__INTERNAL_VERIFICATION_INFO", "benefit__main__internal_verification_info");

define("OPTION__BENEFIT__ADVANCED_SECURITY__MONTHLY_PRICE", "benefit__advanced_security__monthly_price");
define("OPTION__BENEFIT__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "benefit__advanced_security__monthly_discount");
define("OPTION__BENEFIT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE", "benefit__advanced_security__implementation_price");
define(
    "OPTION__BENEFIT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "benefit__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__BENEFIT_VOUCHER", "benefit_voucher");
define("PRODUCT__BENEFIT_VOUCHER__MAIN", "benefit_voucher__main");
define("PRODUCT__BENEFIT_VOUCHER__ADVANCED_SECURITY", "benefit_voucher__advanced_security");

define("OPTION__BENEFIT_VOUCHER__MAIN__MONTHLY_PRICE", "benefit_voucher__main__monthly_price");
define("OPTION__BENEFIT_VOUCHER__MAIN__MONTHLY_DISCOUNT", "benefit_voucher__main__monthly_discount");
define("OPTION__BENEFIT_VOUCHER__MAIN__IMPLEMENTATION_PRICE", "benefit_voucher__main__implementation_price");
define("OPTION__BENEFIT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT", "benefit_voucher__main__implementation_discount");
define("OPTION__BENEFIT_VOUCHER__MAIN__SALARY_OPTION", "benefit_voucher__main__salary_option");
define("OPTION__BENEFIT_VOUCHER__MAIN__MAX_MONTHLY", "benefit_voucher__main__employer_grant");
define("OPTION__BENEFIT_VOUCHER__MAIN__PAYROLL_EXPORT", "benefit__main__payroll_export");
define("OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED", "benefit__main__payment_approved_by_customer");
define("OPTION__BENEFIT_VOUCHER__MAIN__SHORT_TEXT_FOR_PDF", "benefit_voucher__main__short_text_for_PDF");
define("OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF", "benefit_voucher__main__long_text_for_PDF");
define("OPTION__BENEFIT_VOUCHER__MAIN__AUTO_GENERATION", "benefit_voucher__main__auto_generation");
define("OPTION__BENEFIT_VOUCHER__MAIN__INTERNAL_VERIFICATION_INFO",
    "benefit_voucher__main__internal_verification_info");
define("OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON", "benefit_voucher__main__default_reason");
define("OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO", "benefit_voucher__main__default_reason_scenario");
define("OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE", "benefit_voucher__main__flex_unit_price");
define("OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE", "benefit_voucher__main__flex__unit_percentage");

define(
    "OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__MONTHLY_PRICE",
    "benefit_voucher__advanced_security__monthly_price"
);
define(
    "OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__MONTHLY_DISCOUNT",
    "benefit_voucher__advanced_security__monthly_discount"
);
define(
    "OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_PRICE",
    "benefit_voucher__advanced_security__implementation_price"
);
define(
    "OPTION__BENEFIT_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "benefit_voucher__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__INTERNET", "internet");
define("PRODUCT__INTERNET__MAIN", "internet__main");
define("PRODUCT__INTERNET__ADVANCED_SECURITY", "internet__advanced_security");

define("OPTION__INTERNET__MAIN__MONTHLY_PRICE", "internet__main__monthly_price");
define("OPTION__INTERNET__MAIN__MONTHLY_DISCOUNT", "internet__main__monthly_discount");
define("OPTION__INTERNET__MAIN__IMPLEMENTATION_PRICE", "internet__main__implementation_price");
define("OPTION__INTERNET__MAIN__IMPLEMENTATION_DISCOUNT", "internet__main__implementation_discount");
define("OPTION__INTERNET__MAIN__SALARY_OPTION", "internet__main__salary_option");
define("OPTION__INTERNET__MAIN__EMPLOYER_GRANT", "internet__main__employer_grant");
define("OPTION__INTERNET__MAIN__PAYMENT_MONTH_QTY", "internet__main__payment_month_qty");
define("OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION", "internet__main__contractual_information");
define("OPTION__INTERNET__MAIN__INTERNAL_VERIFICATION_INFO", "internet__main__internal_verification_info");

define("OPTION__INTERNET__ADVANCED_SECURITY__MONTHLY_PRICE", "internet__advanced_security__monthly_price");
define("OPTION__INTERNET__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "internet__advanced_security__monthly_discount");
define(
    "OPTION__INTERNET__ADVANCED_SECURITY__IMPLEMENTATION_PRICE",
    "internet__advanced_security__implementation_price"
);
define(
    "OPTION__INTERNET__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "internet__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__AD", "ad");
define("PRODUCT__AD__MAIN", "ad__main");
define("PRODUCT__AD__ADVANCED_SECURITY", "ad__advanced_security");

define("OPTION__AD__MAIN__MONTHLY_PRICE", "ad__main__monthly_price");
define("OPTION__AD__MAIN__MONTHLY_DISCOUNT", "ad__main__monthly_discount");
define("OPTION__AD__MAIN__IMPLEMENTATION_PRICE", "ad__main__implementation_price");
define("OPTION__AD__MAIN__IMPLEMENTATION_DISCOUNT", "ad__main__implementation_discount");
define("OPTION__AD__MAIN__SALARY_OPTION", "ad__main__salary_option");
define("OPTION__AD__MAIN__MAX_YEARLY", "ad__main__max_yearly");
define("OPTION__AD__MAIN__MAX_MONTHLY", "ad__main__employer_grant");
define("OPTION__AD__MAIN__PAYMENT_MONTH", "ad__main__payment_month");
define("OPTION__AD__MAIN__PAYMENT_MONTH_QTY", "ad__main__payment_month_qty");
define("OPTION__AD__MAIN__RECEIPT_OPTION", "ad__main__receipt_option");
define("OPTION__AD__MAIN__INTERNAL_VERIFICATION_INFO", "ad__main__internal_verification_info");

define("OPTION__AD__ADVANCED_SECURITY__MONTHLY_PRICE", "ad__advanced_security__monthly_price");
define("OPTION__AD__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "ad__advanced_security__monthly_discount");
define("OPTION__AD__ADVANCED_SECURITY__IMPLEMENTATION_PRICE", "ad__advanced_security__implementation_price");
define("OPTION__AD__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT", "ad__advanced_security__implementation_discount");

define("PRODUCT_GROUP__RECREATION", "recreation");
define("PRODUCT__RECREATION__MAIN", "recreation__main");
define("PRODUCT__RECREATION__ADVANCED_SECURITY", "recreation__advanced_security");

define("OPTION__RECREATION__MAIN__MONTHLY_PRICE", "recreation__main__monthly_price");
define("OPTION__RECREATION__MAIN__MONTHLY_DISCOUNT", "recreation__main__monthly_discount");
define("OPTION__RECREATION__MAIN__IMPLEMENTATION_PRICE", "recreation__main__implementation_price");
define("OPTION__RECREATION__MAIN__IMPLEMENTATION_DISCOUNT", "recreation__main__implementation_discount");
define("OPTION__RECREATION__MAIN__SALARY_OPTION", "recreation__main__salary_option");
define("OPTION__RECREATION__MAIN__MAX_VALUE", "recreation__main__max_value");
define("OPTION__RECREATION__MAIN__MAX_EMPLOYEE", "recreation__main__max_employee");
define("OPTION__RECREATION__MAIN__MAX_SPOUSE", "recreation__main__max_spouse");
define("OPTION__RECREATION__MAIN__MAX_CHILD", "recreation__main__max_child");
define("OPTION__RECREATION__MAX_DOC_RECEIPT_FILE_COUNT", "recreation__main__max_doc_receipt_file_count");
define("OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE", "recreation__main__confirmation_with_picture");
define("OPTION__RECREATION__MAIN__CONFIRMATION_MESSAGE", "recreation__main__confirmation_message");
define(
    "OPTION__RECREATION__MAIN__CONFIRMATION_TRANSACTION_MESSAGE",
    "recreation__main__confirmation_transaction_message"
);
define("OPTION__RECREATION__MAIN__LIMIT_MESSAGE", "recreation__main__limit_message");
define("OPTION__RECREATION__MAIN__INTERNAL_VERIFICATION_INFO", "recreation__main__internal_verification_info");

define("OPTION__RECREATION__ADVANCED_SECURITY__MONTHLY_PRICE", "recreation__advanced_security__monthly_price");
define("OPTION__RECREATION__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "recreation__advanced_security__monthly_discount");
define(
    "OPTION__RECREATION__ADVANCED_SECURITY__IMPLEMENTATION_PRICE",
    "recreation__advanced_security__implementation_price"
);
define(
    "OPTION__RECREATION__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "recreation__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__MOBILE", "mobile");
define("PRODUCT__MOBILE__MAIN", "mobile__main");
define("PRODUCT__MOBILE__ADVANCED_SECURITY", "mobile__advanced_security");

define("OPTION__MOBILE__MAIN__MONTHLY_PRICE", "mobile__main__monthly_price");
define("OPTION__MOBILE__MAIN__MONTHLY_DISCOUNT", "mobile__main__monthly_discount");
define("OPTION__MOBILE__MAIN__IMPLEMENTATION_PRICE", "mobile__main__implementation_price");
define("OPTION__MOBILE__MAIN__IMPLEMENTATION_DISCOUNT", "mobile__main__implementation_discount");
define("OPTION__MOBILE__MAIN__SALARY_OPTION", "mobile__main__salary_option");
define("OPTION__MOBILE__MAIN__EMPLOYER_GRANT", "mobile__main__employer_grant");
define("OPTION__MOBILE__MAIN__AGE_DEDUCTION", "mobile__main__age_deduction");
define("OPTION__MOBILE__MAIN__PAYMENT_MONTH_QTY", "mobile__main__payment_month_qty");
define("OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION", "mobile__main__contractual_information");
define("OPTION__MOBILE__MAIN__MOBILE_MODEL", "mobile__main__mobile_model");
define("OPTION__MOBILE__MAIN__MOBILE_NUMBER", "mobile__main__mobile_number");
define("OPTION__MOBILE__MAIN__INTERNAL_VERIFICATION_INFO", "mobile__main__internal_verification_info");

define("OPTION__MOBILE__ADVANCED_SECURITY__MONTHLY_PRICE", "mobile__advanced_security__monthly_price");
define("OPTION__MOBILE__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "mobile__advanced_security__monthly_discount");
define("OPTION__MOBILE__ADVANCED_SECURITY__IMPLEMENTATION_PRICE", "mobile__advanced_security__implementation_price");
define(
    "OPTION__MOBILE__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "mobile__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__GIFT", "gift");
define("PRODUCT__GIFT__MAIN", "gift__main");
define("PRODUCT__GIFT__ADVANCED_SECURITY", "gift__advanced_security");

define("OPTION__GIFT__MAIN__MONTHLY_PRICE", "gift__main__monthly_price");
define("OPTION__GIFT__MAIN__MONTHLY_DISCOUNT", "gift__main__monthly_discount");
define("OPTION__GIFT__MAIN__IMPLEMENTATION_PRICE", "gift__main__implementation_price");
define("OPTION__GIFT__MAIN__IMPLEMENTATION_DISCOUNT", "gift__main__implementation_discount");
define("OPTION__GIFT__MAIN__SALARY_OPTION", "gift__main__salary_option");
define("OPTION__GIFT__MAIN__AMOUNT_PER_VOUCHER", "gift__main__amount_per_voucher");
define("OPTION__GIFT__MAIN__QTY_PER_YEAR", "gift__main__qty_per_year");
define("OPTION__GIFT__MAIN__INTERNAL_VERIFICATION_INFO", "gift__main__internal_verification_info");

define("OPTION__GIFT__ADVANCED_SECURITY__MONTHLY_PRICE", "gift__advanced_security__monthly_price");
define("OPTION__GIFT__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "gift__advanced_security__monthly_discount");
define("OPTION__GIFT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE", "gift__advanced_security__implementation_price");
define("OPTION__GIFT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT", "gift__advanced_security__implementation_discount");

define("PRODUCT_GROUP__GIFT_VOUCHER", "gift_voucher");
define("PRODUCT__GIFT_VOUCHER__MAIN", "gift_voucher__main");
define("PRODUCT__GIFT_VOUCHER__ADVANCED_SECURITY", "gift_voucher__advanced_security");

define("OPTION__GIFT_VOUCHER__MAIN__MONTHLY_PRICE", "gift_voucher__main__monthly_price");
define("OPTION__GIFT_VOUCHER__MAIN__MONTHLY_DISCOUNT", "gift_voucher__main__monthly_discount");
define("OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_PRICE", "gift_voucher__main__implementation_price");
define("OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT", "gift_voucher__main__implementation_discount");
define("OPTION__GIFT_VOUCHER__MAIN__SALARY_OPTION", "gift_voucher__main__salary_option");
define("OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER", "gift_voucher__main__amount_per_voucher");
define("OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR", "gift_voucher__main__qty_per_year");
define("OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT", "gift_voucher__main__payroll_export");
define("OPTION__GIFT_VOUCHER__MAIN__INTERNAL_VERIFICATION_INFO", "gift_voucher__main__internal_verification_info");
define("OPTION__GIFT_VOUCHER__MAIN__FLEX_OPTION", "gift_voucher__main__flex_option");
define("OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PRICE", "gift_voucher__main__flex_unit_price");
define("OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE", "gift_voucher__main__flex__unit_percentage");

define("PRODUCT_GROUP__BONUS", "bonus");
define("PRODUCT__BONUS__MAIN", "bonus__main");
define("PRODUCT__BONUS__ADVANCED_SECURITY", "bonus__advanced_security");

define("OPTION__BONUS__MAIN__MONTHLY_PRICE", "bonus__main__monthly_price");
define("OPTION__BONUS__MAIN__MONTHLY_DISCOUNT", "bonus__main__monthly_discount");
define("OPTION__BONUS__MAIN__IMPLEMENTATION_PRICE", "bonus__main__implementation_price");
define("OPTION__BONUS__MAIN__IMPLEMENTATION_DISCOUNT", "bonus__main__implementation_discount");
define("OPTION__BONUS__MAIN__SALARY_OPTION", "bonus__main__salary_option");
define("OPTION__BONUS__MAIN__AMOUNT_PER_YEAR", "bonus__main__max_yearly");
define("OPTION__BONUS__MAIN__INTERNAL_VERIFICATION_INFO", "bonus__main__internal_verification_info");

define("OPTION__BONUS__ADVANCED_SECURITY__MONTHLY_PRICE", "bonus__advanced_security__monthly_price");
define("OPTION__BONUS__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "bonus__advanced_security__monthly_discount");
define("OPTION__BONUS__ADVANCED_SECURITY__IMPLEMENTATION_PRICE", "bonus__advanced_security__implementation_price");
define(
    "OPTION__BONUS__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "bonus__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__BONUS_VOUCHER", "bonus_voucher");
define("PRODUCT__BONUS_VOUCHER__MAIN", "bonus_voucher__main");

define("OPTION__BONUS_VOUCHER__MAIN__MONTHLY_PRICE", "bonus_voucher__main__monthly_price");
define("OPTION__BONUS_VOUCHER__MAIN__MONTHLY_DISCOUNT", "bonus_voucher__main__monthly_discount");
define("OPTION__BONUS_VOUCHER__MAIN__IMPLEMENTATION_PRICE", "bonus_voucher__main__implementation_price");
define("OPTION__BONUS_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT", "bonus_voucher__main__implementation_discount");
define("OPTION__BONUS_VOUCHER__MAIN__SALARY_OPTION", "bonus_voucher__main__salary_option");
define("OPTION__BONUS_VOUCHER__MAIN__AMOUNT_PER_YEAR", "bonus_voucher__main__max_yearly");
define("OPTION__BONUS_VOUCHER__MAIN__INTERNAL_VERIFICATION_INFO", "bonus_voucher__main__internal_verification_info");
define("OPTION__BONUS_VOUCHER__MAIN__PAYROLL_EXPORT", "bonus_voucher__main__payroll_export");
define("OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON", "bonus_voucher__main__default_reason");
define("OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO", "bonus_voucher__main__default_reason_scenario");
define("OPTION__BONUS_VOUCHER__MAIN__FLEX_OPTION", "bonus_voucher__main__flex_option");
define("OPTION__BONUS_VOUCHER__MAIN__FLEX_UNIT_PRICE", "bonus_voucher__main__flex_unit_price");
define("OPTION__BONUS_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE", "bonus_voucher__main__flex__unit_percentage");

define("PRODUCT_GROUP__TRANSPORT", "transport");
define("PRODUCT__TRANSPORT__MAIN", "transport__main");
define("PRODUCT__TRANSPORT__ADVANCED_SECURITY", "transport__advanced_security");

define("OPTION__TRANSPORT__MAIN__MONTHLY_PRICE", "transport__main__monthly_price");
define("OPTION__TRANSPORT__MAIN__MONTHLY_DISCOUNT", "transport__main__monthly_discount");
define("OPTION__TRANSPORT__MAIN__IMPLEMENTATION_PRICE", "transport__main__implementation_price");
define("OPTION__TRANSPORT__MAIN__IMPLEMENTATION_DISCOUNT", "transport__main__implementation_discount");
define("OPTION__TRANSPORT__MAIN__SALARY_OPTION", "transport__main__salary_option");
define("OPTION__TRANSPORT__MAIN__AMOUNT_PER_MONTH", "transport__main__max_monthly");
define("OPTION__TRANSPORT__MAIN__INTERNAL_VERIFICATION_INFO", "transport__main__internal_verification_info");

define("OPTION__TRANSPORT__ADVANCED_SECURITY__MONTHLY_PRICE", "transport__advanced_security__monthly_price");
define("OPTION__TRANSPORT__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "transport__advanced_security__monthly_discount");
define(
    "OPTION__TRANSPORT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE",
    "transport__advanced_security__implementation_price"
);
define(
    "OPTION__TRANSPORT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "transport__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__CHILD_CARE", "child_care");
define("PRODUCT__CHILD_CARE__MAIN", "child_care__main");
define("PRODUCT__CHILD_CARE__ADVANCED_SECURITY", "child_care__advanced_security");

define("OPTION__CHILD_CARE__MAIN__MONTHLY_PRICE", "child_care__main__monthly_price");
define("OPTION__CHILD_CARE__MAIN__MONTHLY_DISCOUNT", "child_care__main__monthly_discount");
define("OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_PRICE", "child_care__main__implementation_price");
define("OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_DISCOUNT", "child_care__main__implementation_discount");
define("OPTION__CHILD_CARE__MAIN__SALARY_OPTION", "child_care__main__salary_option");
define("OPTION__CHILD_CARE__MAIN__MAX_MONTHLY", "child_care__main__max_monthly");
define("OPTION__CHILD_CARE__MAIN__INTERNAL_VERIFICATION_INFO", "child_care__main__internal_verification_info");

define("OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_PRICE", "child_care__advanced_security__monthly_price");
define("OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "child_care__advanced_security__monthly_discount");
define(
    "OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_PRICE",
    "child_care__advanced_security__implementation_price"
);
define(
    "OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "child_care__advanced_security__implementation_discount"
);


define("PRODUCT_GROUP__TRAVEL", "travel");
define("PRODUCT__TRAVEL__MAIN", "travel__main");
define("PRODUCT__TRAVEL__ADVANCED_SECURITY", "travel__advanced_security");

define("OPTION__TRAVEL__MAIN__IMPLEMENTATION_PRICE", "travel__main__implementation_price");
define("OPTION__TRAVEL__MAIN__IMPLEMENTATION_DISCOUNT", "travel__main__implementation_discount");
define("OPTION__TRAVEL__MAIN__MONTHLY_PRICE", "travel__main__monthly_price");
define("OPTION__TRAVEL__MAIN__MONTHLY_DISCOUNT", "travel__main__monthly_discount");
define("OPTION__TRAVEL__MAIN__SALARY_OPTION", "travel__main__salary_option");
define("OPTION__TRAVEL__MAIN__AMOUNT_PER_YEAR", "travel__main__max_yearly");
define("OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH", "travel__main__max_monthly");
define("OPTION__TRAVEL__MAIN__CREDITOR_BOOKING", "travel__main__creditor_booking");
define("OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE", "travel__main__fixed_daily_allowance");
define("OPTION__TRAVEL__MAIN__HOURS_UNDER", "travel__main__hours_under");
define("OPTION__TRAVEL__MAIN__HOURS_OVER", "travel__main__hours_over");
define("OPTION__TRAVEL__MAIN__INTERNAL_VERIFICATION_INFO", "travel__main__internal_verification_info");

define("OPTION__TRAVEL__ADVANCED_SECURITY__MONTHLY_PRICE", "travel__advanced_security__monthly_price");
define("OPTION__TRAVEL__ADVANCED_SECURITY__MONTHLY_DISCOUNT", "travel__advanced_security__monthly_discount");
define("OPTION__TRAVEL__ADVANCED_SECURITY__IMPLEMENTATION_PRICE", "travel__advanced_security__implementation_price");
define(
    "OPTION__TRAVEL__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "travel__advanced_security__implementation_discount"
);

define("PRODUCT_GROUP__GIVVE", "givve");
define("PRODUCT__GIVVE__MAIN", "givve__main");

define("OPTION__GIVVE__MAIN__IMPLEMENTATION_PRICE", "givve__main__implementation_price");
define("OPTION__GIVVE__MAIN__IMPLEMENTATION_DISCOUNT", "givve__main__implementation_discount");
define("OPTION__GIVVE__MAIN__MONTHLY_PRICE", "givve__main__monthly_price");
define("OPTION__GIVVE__MAIN__MONTHLY_DISCOUNT", "givve__main__monthly_discount");

define("PRODUCT_GROUP__STORED_DATA", "stored_data");
define("PRODUCT__STORED_DATA__MAIN", "stored_data__main");

define("OPTION__STORED_DATA__MAIN__IMPLEMENTATION_PRICE", "stored_data__main__implementation_price");
define("OPTION__STORED_DATA__MAIN__IMPLEMENTATION_DISCOUNT", "stored_data__main__implementation_discount");
define("OPTION__STORED_DATA__MAIN__MONTHLY_PRICE", "stored_data__main__monthly_price");
define("OPTION__STORED_DATA__MAIN__QUARTERLY_PRICE", "stored_data__main__quarterly_price");
define("OPTION__STORED_DATA__MAIN__YEARLY_PRICE", "stored_data__main__yearly_price");
define("OPTION__STORED_DATA__MAIN__MONTHLY_DISCOUNT", "stored_data__main__monthly_discount");
define("OPTION__STORED_DATA__MAIN__EMPLOYEES", "stored_data__main__employees");
define("OPTION__STORED_DATA__MAIN__SERVICES", "stored_data__main__services");
define("OPTION__STORED_DATA__MAIN__FREQUENCY", "stored_data__main__frequency");
define("OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES", "stored_data__main__individual_files");

define("PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT", "corporate_health_management");
define("PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN", "corporate_health_management__main");
define("PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY", "corporate_health_management__advanced_security");

define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_PRICE",
    "corporate_health_management__main__implementation_price"
);
define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_DISCOUNT",
    "corporate_health_management__main__implementation_discount"
);
define("OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_PRICE", "corporate_health_management__main__monthly_price");
define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_DISCOUNT",
    "corporate_health_management__main__monthly_discount"
);
define("OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION", "corporate_health_management__main__salary_option");
define("OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY", "corporate_health_management__main__max_monthly");
define("OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY", "corporate_health_management__main__max_yearly");
define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT",
    "corporate_health_management__main__payroll_export"
);
define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__INTERNAL_VERIFICATION_INFO",
    "corporate_health_management__main__internal_verification_info"
);

define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_PRICE",
    "corporate_health_management__advanced_security__monthly_price"
);
define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__MONTHLY_DISCOUNT",
    "corporate_health_management__advanced_security__monthly_discount"
);
define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_PRICE",
    "corporate_health_management__advanced_security__implementation_price"
);
define(
    "OPTION__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT",
    "corporate_health_management__advanced_security__implementation_discount"
);

//main product list
define("PRODUCT_GROUP__MAIN_PRODUCT", [
    PRODUCT_GROUP__BASE => PRODUCT__BASE__MAIN,
    PRODUCT_GROUP__FOOD => PRODUCT__FOOD__MAIN,
    PRODUCT_GROUP__FOOD_VOUCHER => PRODUCT__FOOD_VOUCHER__MAIN,
    PRODUCT_GROUP__BENEFIT => PRODUCT__BENEFIT__MAIN,
    PRODUCT_GROUP__BENEFIT_VOUCHER => PRODUCT__BENEFIT_VOUCHER__MAIN,
    PRODUCT_GROUP__INTERNET => PRODUCT__INTERNET__MAIN,
    PRODUCT_GROUP__AD => PRODUCT__AD__MAIN,
    PRODUCT_GROUP__RECREATION => PRODUCT__RECREATION__MAIN,
    PRODUCT_GROUP__MOBILE => PRODUCT__MOBILE__MAIN,
    PRODUCT_GROUP__GIFT => PRODUCT__GIFT__MAIN,
    PRODUCT_GROUP__GIFT_VOUCHER => PRODUCT__GIFT_VOUCHER__MAIN,
    PRODUCT_GROUP__BONUS_VOUCHER => PRODUCT__BONUS_VOUCHER__MAIN,
    PRODUCT_GROUP__BONUS => PRODUCT__BONUS__MAIN,
    PRODUCT_GROUP__TRANSPORT => PRODUCT__TRANSPORT__MAIN,
    PRODUCT_GROUP__CHILD_CARE => PRODUCT__CHILD_CARE__MAIN,
    PRODUCT_GROUP__TRAVEL => PRODUCT__TRAVEL__MAIN,
    PRODUCT_GROUP__GIVVE => PRODUCT__GIVVE__MAIN,
    PRODUCT_GROUP__STORED_DATA => PRODUCT__STORED_DATA__MAIN,
    PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT => PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN,
]);

//Default values for monthly service price
define("OPTION_DEFAULT_VALUES", [
    OPTION__FOOD__MAIN__MONTHLY_PRICE => 4,
    OPTION__FOOD__DOCUMENT_PLAUSIBILITY__MONTHLY_PRICE => 1,
    OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__MONTHLY_PRICE => 2,
    OPTION__FOOD__WEEKLY_SHOPPING__MONTHLY_PRICE => 0.5,
    OPTION__FOOD__CANTEEN__MONTHLY_PRICE => 1,
    OPTION__BASE__MAIN__MONTHLY_PRICE => 4,
    OPTION__GIFT__MAIN__MONTHLY_PRICE => 1,
    OPTION__INTERNET__MAIN__MONTHLY_PRICE => 2,
    OPTION__AD__MAIN__MONTHLY_PRICE => 1,
    OPTION__RECREATION__MAIN__MONTHLY_PRICE => 1,
    OPTION__MOBILE__MAIN__MONTHLY_PRICE => 2,
    OPTION__BENEFIT__MAIN__MONTHLY_PRICE => 2,
    OPTION__BONUS__MAIN__MONTHLY_PRICE => 2,
    OPTION__FOOD__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__FOOD__DOCUMENT_PLAUSIBILITY__IMPLEMENTATION_PRICE => 10,
    OPTION__FOOD__LUMP_SUM_TAX_EXAMINATION__IMPLEMENTATION_PRICE => 10,
    OPTION__FOOD__WEEKLY_SHOPPING__IMPLEMENTATION_PRICE => 10,
    OPTION__FOOD__CANTEEN__IMPLEMENTATION_PRICE => 10,
    OPTION__BASE__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__GIFT__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__INTERNET__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__AD__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__RECREATION__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__MOBILE__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__BENEFIT__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__BONUS__MAIN__IMPLEMENTATION_PRICE => 10,
    OPTION__TRAVEL__MAIN__CREDITOR_BOOKING => 'N',
    OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE => 'Y',
    OPTION__TRAVEL__MAIN__HOURS_UNDER => 12,
    OPTION__TRAVEL__MAIN__HOURS_OVER => 24,
]);

//Same field for all services
define("OPTIONS_INTERNAL_VERIFICATION_INFO", [
    PRODUCT__FOOD__MAIN => OPTION__FOOD__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__FOOD_VOUCHER__MAIN => OPTION__FOOD_VOUCHER__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__BENEFIT__MAIN => OPTION__BENEFIT__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__BENEFIT_VOUCHER__MAIN => OPTION__BENEFIT_VOUCHER__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__INTERNET__MAIN => OPTION__INTERNET__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__AD__MAIN => OPTION__AD__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__RECREATION__MAIN => OPTION__RECREATION__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__MOBILE__MAIN => OPTION__MOBILE__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__GIFT__MAIN => OPTION__GIFT__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__GIFT_VOUCHER__MAIN => OPTION__GIFT_VOUCHER__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__BONUS__MAIN => OPTION__BONUS__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__BONUS_VOUCHER__MAIN => OPTION__BONUS_VOUCHER__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__TRANSPORT__MAIN => OPTION__TRANSPORT__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__CHILD_CARE__MAIN => OPTION__CHILD_CARE__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__TRAVEL__MAIN => OPTION__TRAVEL__MAIN__INTERNAL_VERIFICATION_INFO,
    PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN => OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__INTERNAL_VERIFICATION_INFO,
]);

define("OPTIONS_FLEX_OPTION", [
    PRODUCT__FOOD__MAIN => OPTION__FOOD__MAIN__FLEX_OPTION,
    PRODUCT__GIFT_VOUCHER__MAIN => OPTION__GIFT_VOUCHER__MAIN__FLEX_OPTION,
    PRODUCT__BONUS_VOUCHER__MAIN => OPTION__BONUS_VOUCHER__MAIN__FLEX_OPTION
]);
define("OPTIONS_VOUCHER_FLEX_OPTION", array(
    PRODUCT__BENEFIT_VOUCHER__MAIN => OPTION__BENEFIT_VOUCHER__MAIN__AUTO_GENERATION
));
define("OPTIONS_SAVING_FIRST_DAY_OF_NEXT_MONTH", array(
    OPTION__FOOD__MAIN__UNITS_PER_MONTH,
    OPTION__FOOD__MAIN__FLEX_OPTION,
    OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH,
    OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION,
    OPTION__BENEFIT__MAIN__EMPLOYER_GRANT,
    OPTION__BENEFIT_VOUCHER__MAIN__MAX_MONTHLY,
    OPTION__BENEFIT_VOUCHER__MAIN__AUTO_GENERATION,
    OPTION__INTERNET__MAIN__EMPLOYER_GRANT,
    OPTION__AD__MAIN__MAX_MONTHLY,
    OPTION__AD__MAIN__MAX_YEARLY,
    OPTION__RECREATION__MAIN__MAX_VALUE,
    OPTION__MOBILE__MAIN__EMPLOYER_GRANT,
    OPTION__TRANSPORT__MAIN__AMOUNT_PER_MONTH,
    OPTION__CHILD_CARE__MAIN__MAX_MONTHLY,
    OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH,
    OPTION__TRAVEL__MAIN__AMOUNT_PER_YEAR,
    OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY,
    OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY,
    OPTION__GIFT__MAIN__QTY_PER_YEAR,
    OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR,
    OPTION__GIFT_VOUCHER__MAIN__FLEX_OPTION,
    OPTION__BONUS__MAIN__AMOUNT_PER_YEAR,
));
define("OPTIONS_SALARY", array(
    OPTION__FOOD__MAIN__SALARY_OPTION,
    OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION,
    OPTION__BENEFIT__MAIN__SALARY_OPTION,
    OPTION__BENEFIT_VOUCHER__MAIN__SALARY_OPTION,
    OPTION__INTERNET__MAIN__SALARY_OPTION,
    OPTION__AD__MAIN__SALARY_OPTION,
    OPTION__RECREATION__MAIN__SALARY_OPTION,
    OPTION__MOBILE__MAIN__SALARY_OPTION,
    OPTION__GIFT__MAIN__SALARY_OPTION,
    OPTION__GIFT_VOUCHER__MAIN__SALARY_OPTION,
    OPTION__BONUS__MAIN__SALARY_OPTION,
    OPTION__BONUS_VOUCHER__MAIN__SALARY_OPTION,
    OPTION__TRANSPORT__MAIN__SALARY_OPTION,
    OPTION__CHILD_CARE__MAIN__SALARY_OPTION,
    OPTION__TRAVEL__MAIN__SALARY_OPTION,
    OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION,
));

define("OPTIONS_VOUCHER_DEFAULT_REASON", [
    PRODUCT_GROUP__BENEFIT_VOUCHER => OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON,
    PRODUCT_GROUP__BONUS_VOUCHER => OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON,
]);
define("OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO", [
    PRODUCT_GROUP__BENEFIT_VOUCHER => OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO,
    PRODUCT_GROUP__BONUS_VOUCHER => OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO
]);

$GLOBALS['moduleConfig']['product'] = array(
    'ColorA' => '#000',
    'ColorI' => '#000',
    'Config' => array()
);
