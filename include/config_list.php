<?php

class ConfigList extends LocalObjectList
{
    private $groupMap;
    private $maxValueLength = 60;

    /**
     * Constructor
     */
    public function ConfigList()
    {
        $this->groupMap = array(
            "mobile_app" => ["m_mobile_app", "m_mobile_app_icons"],
            "ocr" => ["b_receipt_shop", "c_receipt_restaurant", "o_ocr_misc"],
            "push" => ["p_push", "r_autodeny"],
            "others" => ["e_export", "misc", "x_app_license", "v_receipt_verification", "email_texts"]
        );
    }

    /**
     * Loads full config list from database
     */
    public function LoadConfigList($section)
    {
        $query = "SELECT config_id, code, group_code, editor, value, sort_order FROM config
                    WHERE group_code IN (" . implode(",", Connection::GetSQLArray($this->groupMap[$section])) . ")
                    ORDER BY group_code, sort_order ASC, code ASC";
        $this->LoadFromSQL($query);

        $this->PrepareBeforeShow();
    }

    /**
     * Prepares config's title translation
     */
    private function PrepareBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            //show values in list
            $noValueShown = ["file", "ckeditor"];
            if (!in_array($this->_items[$i]["editor"], $noValueShown)) {
                $this->_items[$i]["show_value"] = 1;
                if (Config::DoesConfigHaveHistory($this->_items[$i]["code"])) {
                    $this->_items[$i]["history"] = 1;
                    $this->_items[$i]["value"] = Config::GetConfigValueByDate(
                        $this->_items[$i]["code"],
                        GetCurrentDate()
                    );
                }
                if ($this->_items[$i]["editor"] == "field-float") {
                    $this->_items[$i]["value"] = GetPriceFormat($this->_items[$i]["value"]);
                }
                if (strlen($this->_items[$i]["value"]) > $this->maxValueLength) {
                    $this->_items[$i]["value"] = chopString($this->_items[$i]["value"], $this->maxValueLength);
                }
            } else {
                $this->_items[$i]["show_value"] = 0;
            }

            //show group title
            if ($this->_items[$i]["group_code"] == "o_option") {
                $optionTypes = array("__monthly_price", "__implementation_price");
                $product = str_replace($optionTypes, "", $this->_items[$i]["code"]);
                $this->_items[$i]["title_translation"] = GetTranslation(
                    "product-" . $product,
                    "product"
                ) . " " . GetTranslation("option-" . $this->_items[$i]["code"], "product");
            } else {
                $this->_items[$i]["title_translation"] = GetTranslation("config-" . $this->_items[$i]["code"]);
            }
            $this->_items[$i]["group_title_translation"] =
                GetTranslation("config-group-" . $this->_items[$i]["group_code"]);

            if ($i == 0 || $this->_items[$i]["group_code"] != $this->_items[$i - 1]["group_code"]) {
                $this->_items[$i]["show_group"] = 1;
            }

            //set editor_type
            $this->_items[$i]["editor_type"] = explode("-", $this->_items[$i]["editor"])[0];
        }
    }
}
