<?php

class ProductList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ProductList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "created_asc" => "p.created ASC",
            "created_desc" => "p.created DESC",
        ));
        $this->SetOrderBy("created_asc");
    }

    /**
     * Loads product list for admin panel filtered by product group id
     *
     * @param int $groupID - group_id products should be filtered by
     */
    public function LoadProductListForAdmin($groupID)
    {
        $query = "SELECT p.product_id, p.group_id, p.created, p.title, p.code, p.inheritable FROM product AS p WHERE p.group_id=" . intval($groupID);
        $this->SetSortOrderFields(["product_id"]);
        $this->SetOrderBy("product_id");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["title_translation"] = GetTranslation(
                "product-" . $this->_items[$i]["code"],
                $this->module
            );
        }
    }

    /**
     * Returns fill list of existing products
     *
     * @return array
     */
    public static function GetFullProductListForWebApi()
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT code FROM product";
        $productList = $stmt->FetchList($query);

        $codeList = array();
        foreach ($productList as $product) {
            $codeList[] = array(
                "Title" => GetTranslation("product-" . $product["code"], "product"),
                "Code" => $product["code"]
            );
        }

        return $codeList;
    }

    /**
     * Returns array with codes of new gen products with vouchers
     *
     * @param bool $newGen
     */
    public static function GetVoucherProductList($newGen = false)
    {
        if ($newGen) {
            $voucherList = [
                PRODUCT__BENEFIT_VOUCHER__MAIN,
                PRODUCT__FOOD_VOUCHER__MAIN,
                PRODUCT__GIFT_VOUCHER__MAIN,
                PRODUCT__BONUS_VOUCHER__MAIN,
            ];
        } else {
            $voucherList = [
                PRODUCT__BENEFIT_VOUCHER__MAIN,
                PRODUCT__FOOD_VOUCHER__MAIN,
                PRODUCT__BONUS_VOUCHER__MAIN,
                PRODUCT__GIFT_VOUCHER__MAIN,
                PRODUCT__BONUS__MAIN,
                PRODUCT__GIFT__MAIN,
            ];
        }

        $user = new User();
        $user->LoadBySession();
        $hideFromAdmin = [
            PRODUCT__BONUS__MAIN,
            PRODUCT__GIFT__MAIN,
            PRODUCT__BENEFIT__MAIN,
            PRODUCT__BONUS_VOUCHER__MAIN,
        ];
        if (!$user->Validate(array("root"))) {
            $where[] = "p.code NOT IN (" . implode(", ", Connection::GetSQLArray($hideFromAdmin)) . ")";
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT p.product_id, p.group_id, p.created, p.title, p.code, p.inheritable FROM product AS p WHERE p.code IN (" . implode(
            ", ",
            Connection::GetSQLArray($voucherList)
        ) . ")";
        $result = $stmt->FetchList($query);

        return is_array($result) ? $result : [];
    }
}
