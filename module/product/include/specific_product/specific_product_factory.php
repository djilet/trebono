<?php

class SpecificProductFactory
{

    /**
     * Creates object of class matching the product code
     * @param string $code
     * @return AbstractSpecificProduct|NULL
     */
    public static function Create($code)
    {
        switch ($code) {
            case PRODUCT__BASE__MAIN:
                return new SpecificProductBaseMain();
            case PRODUCT__BASE__INTERRUPTION:
                return new SpecificProductBaseInterruption();
            case PRODUCT__FOOD__MAIN:
                return new SpecificProductFoodMain();
            case PRODUCT__FOOD__PLAUSIBILITY:
                return new SpecificProductFoodDocumentPlausibility();
            case PRODUCT__FOOD__LUMP_SUM_TAX_EXAMINATION:
                return new SpecificProductFoodLumpSumTaxExamination();
            case PRODUCT__FOOD__WEEKLY_SHOPPING:
                return new SpecificProductFoodWeeklyShopping();
            case PRODUCT__FOOD__CANTEEN:
                return new SpecificProductFoodCanteen();
            case PRODUCT__FOOD_VOUCHER__MAIN:
                return new SpecificProductFoodVoucherMain();
            case PRODUCT__BENEFIT__MAIN:
                return new SpecificProductBenefitMain();
            case PRODUCT__BENEFIT_VOUCHER__MAIN:
                return new SpecificProductBenefitVoucherMain();
            case PRODUCT__INTERNET__MAIN:
                return new SpecificProductInternetMain();
            case PRODUCT__AD__MAIN:
                return new SpecificProductAdMain();
            case PRODUCT__RECREATION__MAIN:
                return new SpecificProductRecreationMain();
            case PRODUCT__MOBILE__MAIN:
                return new SpecificProductMobileMain();
            case PRODUCT__GIFT__MAIN:
                return new SpecificProductGiftMain();
            case PRODUCT__GIFT_VOUCHER__MAIN:
                return new SpecificProductGiftVoucherMain();
			case PRODUCT__BONUS__MAIN:
			    return new SpecificProductBonusMain();
            case PRODUCT__BONUS_VOUCHER__MAIN:
                return new SpecificProductBonusVoucherMain();
			case PRODUCT__TRANSPORT__MAIN:
			    return new SpecificProductTransportMain();
			case PRODUCT__CHILD_CARE__MAIN:
				return new SpecificProductChildCareMain();
            case PRODUCT__TRAVEL__MAIN:
                return new SpecificProductTravelMain();
            case PRODUCT__GIVVE__MAIN:
                return new SpecificProductGivveMain();
            case PRODUCT__STORED_DATA__MAIN:
                return new SpecificProductStoredDataMain();
            case PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN:
                return new SpecificProductCorporateHealthManagementMain();


            case PRODUCT__FOOD__ADVANCED_SECURITY:
                return new SpecificProductFoodAdvancedSecurity();
            case PRODUCT__FOOD_VOUCHER__ADVANCED_SECURITY:
                return new SpecificProductFoodVoucherAdvancedSecurity();
            case PRODUCT__BENEFIT__ADVANCED_SECURITY:
                return new SpecificProductBenefitAdvancedSecurity();
            case PRODUCT__BENEFIT_VOUCHER__ADVANCED_SECURITY:
                return new SpecificProductBenefitVoucherAdvancedSecurity();
            case PRODUCT__INTERNET__ADVANCED_SECURITY:
                return new SpecificProductInternetAdvancedSecurity();
            case PRODUCT__AD__ADVANCED_SECURITY:
                return new SpecificProductAdAdvancedSecurity();
            case PRODUCT__RECREATION__ADVANCED_SECURITY:
                return new SpecificProductRecreationAdvancedSecurity();
            case PRODUCT__MOBILE__ADVANCED_SECURITY:
                return new SpecificProductMobileAdvancedSecurity();
            case PRODUCT__GIFT__ADVANCED_SECURITY:
                return new SpecificProductGiftAdvancedSecurity();
            case PRODUCT__BONUS__ADVANCED_SECURITY:
                return new SpecificProductBonusAdvancedSecurity();
            case PRODUCT__TRANSPORT__ADVANCED_SECURITY:
                return new SpecificProductTransportAdvancedSecurity();
            case PRODUCT__CHILD_CARE__ADVANCED_SECURITY:
                return new SpecificProductChildCareAdvancedSecurity();
            case PRODUCT__TRAVEL__ADVANCED_SECURITY:
                return new SpecificProductTravelAdvancedSecurity();
            case PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY:
                return new SpecificProductCorporateHealthManagementAdvancedSecurity();
        }

        return null;
    }
}