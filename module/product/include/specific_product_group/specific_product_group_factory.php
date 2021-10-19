<?php

class SpecificProductGroupFactory
{

    /**
     * Creates object of class matching the product group
     *
     * @param int $groupID
     *
     * @return AbstractSpecificProductGroup|NULL
     */
    public static function Create($groupID)
    {
        $productGroup = new ProductGroup("product");
        if ($productGroup->LoadByID($groupID)) {
            return self::CreateByCode($productGroup->GetProperty("code"));
        }

        return null;
    }

    /**
     * Creates object of class matching the product group
     *
     * @param string $groupCode
     *
     * @return AbstractSpecificProductGroup|NULL
     */
    public static function CreateByCode($groupCode)
    {
        switch ($groupCode) {
            case PRODUCT_GROUP__BASE:
                return new SpecificProductGroupBase();
            case PRODUCT_GROUP__FOOD:
                return new SpecificProductGroupFood();
            case PRODUCT_GROUP__FOOD_VOUCHER:
                return new SpecificProductGroupFoodVoucher();
            case PRODUCT_GROUP__RECREATION:
                return new SpecificProductGroupRecreation();
            case PRODUCT_GROUP__INTERNET:
                return new SpecificProductGroupInternet();
            case PRODUCT_GROUP__AD:
                return new SpecificProductGroupAd();
            case PRODUCT_GROUP__BENEFIT:
                return new SpecificProductGroupBenefit();
            case PRODUCT_GROUP__BENEFIT_VOUCHER:
                return new SpecificProductGroupBenefitVoucher();
            case PRODUCT_GROUP__GIFT:
                return new SpecificProductGroupGift();
            case PRODUCT_GROUP__GIFT_VOUCHER:
                return new SpecificProductGroupGiftVoucher();
            case PRODUCT_GROUP__MOBILE:
                return new SpecificProductGroupMobile();
            case PRODUCT_GROUP__BONUS:
                return new SpecificProductGroupBonus();
            case PRODUCT_GROUP__BONUS_VOUCHER:
                return new SpecificProductGroupBonusVoucher();
            case PRODUCT_GROUP__TRANSPORT:
                return new SpecificProductGroupTransport();
            case PRODUCT_GROUP__CHILD_CARE:
                return new SpecificProductGroupChildCare();
            case PRODUCT_GROUP__TRAVEL:
                return new SpecificProductGroupTravel();
            case PRODUCT_GROUP__GIVVE:
                return new SpecificProductGroupGivve();
            case PRODUCT_GROUP__STORED_DATA:
                return new SpecificProductGroupStoredData();
            case PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT:
                return new SpecificProductGroupCorporateHealthManagement();
            default:
                return null;
        }
    }
}
