<?php

class Product extends LocalObject
{
    private $module;
    private static $codeToProductIDMap;
    private static $productIDToCodeMap;

    /**
     * Constructor
     * @param string $module Name of context module
     * @param array $data Array of product properties to be loaded instantly
     */
    public function Product($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads product by its product_id
     * @param int $id product_id
     * @return boolean true if loaded successfully or else otherwise
     */
    public function LoadByID($id)
    {
        $query = "SELECT product_id, group_id, created, code, title 
					FROM product
					WHERE product_id=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("product_id")) {
            $this->PrepareContentBeforeShow();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        $this->SetProperty("title_translation", GetTranslation("product-" . $this->GetProperty("code"), $this->module));
    }

    /**
     * Loads code->product_id and product_id->code maps from database to static field
     */
    private static function LoadProductCodeMaps()
    {
        self::$codeToProductIDMap = array();

        $stmt = GetStatement();
        $query = "SELECT product_id, code FROM product ORDER BY product_id ASC";
        $productList = $stmt->FetchAssocIndexedList($query, "code");

        foreach ($productList as $code => $product) {
            self::$codeToProductIDMap[$code] = $product["product_id"];
        }

        self::$productIDToCodeMap = array_flip(self::$codeToProductIDMap);
    }

    /**
     * Returns code->product_id map
     * @return array
     */
    private static function GetCodeToProductIDMap()
    {
        if (self::$codeToProductIDMap === null) {
            self::LoadProductCodeMaps();
        }
        return self::$codeToProductIDMap;
    }

    /**
     * Returns product_id->code map
     * @return array
     */
    private static function GetProductIDToCodeMap()
    {
        if (self::$productIDToCodeMap === null) {
            self::LoadProductCodeMaps();
        }
        return self::$productIDToCodeMap;
    }

    /**
     * Returns product_id by its unique code
     * @param string $code
     * @return NULL|int product_id
     */
    public static function GetProductIDByCode($code)
    {
        $map = self::GetCodeToProductIDMap();
        return isset($map[$code]) ? $map[$code] : null;
    }

    /**
     * Returns product code by its product_id
     * @param int $product_id
     * @return NULL|string code
     */
    public static function GetProductCodeByID($productID)
    {
        $map = self::GetProductIDToCodeMap();
        return isset($map[$productID]) ? $map[$productID] : null;
    }

    /**
     * Returns product code by its product_id
     * @param int $product_id
     * @return NULL|string code
     */
    public static function IsProductInheritable($productID)
    {
        $stmt = GetStatement();
        $query = "SELECT inheritable FROM product WHERE product_id=" . intval($productID);
        $inheritable = $stmt->FetchField($query);
        if ($inheritable == "Y") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return whenever flex option is enabled in service
     * @param string $productCode code
     * @param int $employeeID employee_id
     * @param string $date
     * @return bool
     */
    public static function IsFlexOptionOn($productCode, $employeeID, $date)
    {
        if(isset(OPTIONS_FLEX_OPTION[$productCode]))
        {
            $optionCode = OPTIONS_FLEX_OPTION[$productCode];
            $value = Option::GetInheritableOptionValue(OPTION_LEVEL_EMPLOYEE, $optionCode, $employeeID, $date);
            if($value == "Y")
                return true;
        }
        elseif(isset(OPTIONS_VOUCHER_FLEX_OPTION[$productCode]))
        {
            $optionCode = OPTIONS_VOUCHER_FLEX_OPTION[$productCode];
            $value = Option::GetInheritableOptionValue(OPTION_LEVEL_EMPLOYEE, $optionCode, $employeeID, $date);
            if($value == "N")
                return true;
        }

        return false;
    }
}