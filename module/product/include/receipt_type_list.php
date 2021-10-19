<?php

class ReceiptTypeList extends LocalObjectList
{
    private $module;
    private $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ReceiptTypeList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "receipt_type_id_asc" => "rt.receipt_type_id ASC",
            "receipt_type_id_desc" => "rt.receipt_type_id DESC",
        ));
        $this->SetOrderBy("receipt_type_id_asc");

        $this->params = array();
        $this->params["receipt_type"] = LoadImageConfig("receipt_type_image", $this->module, RECEIPT_TYPE_IMAGE);
    }

    /**
     * Loads full receipt type list for admin panel
     */
    public function LoadReceiptTypeListForAdmin($excludeArchive = true)
    {
        $where = array();
        if ($excludeArchive) {
            $where[] = "rt.archive='N'";
        }

        $query = "SELECT rt.receipt_type_id, rt.code, rt.created, rt.created_by, rt.archive, rt.receipt_type_image, rt.receipt_type_image_config
					FROM receipt_type AS rt"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Loads full receipt type list for admin panel
     */
    public function LoadReceiptTypeListForProductGroup($groupID)
    {
        $where = array();
        $where[] = "pr.group_id=" . intval($groupID);
        $where[] = "rt.archive='N'";

        $query = "SELECT rt.receipt_type_id, rt.code, rt.created, rt.created_by, rt.archive, rt.receipt_type_image, rt.receipt_type_image_config
				FROM receipt_type AS rt
                    JOIN product_group_2_receipt_type pr ON pr.code=rt.code"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        $stmt = GetStatement(DB_MAIN);
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $query = "SELECT * FROM product_group_2_receipt_type pr JOIN product_group AS g ON g.group_id=pr.group_id WHERE pr.code=" . Connection::GetSQLString($this->_items[$i]["code"]);
            if ($productGroupList = $stmt->FetchList($query)) {
                foreach ($productGroupList as $key => $productGroup) {
                    $productGroupList[$key]["product_group_title_translation"] = GetTranslation(
                        "product-group-" . $productGroup["code"],
                        $this->module
                    );
                }
            }
            $this->_items[$i]["ReceiptTypeProductGroupList"] = $productGroupList;

            $this->_items[$i]["title_translation"] = GetTranslation(
                "receipt-type-" . $this->_items[$i]["code"],
                $this->module
            );
            $this->_items[$i]["user_name"] = User::GetNameByID($this->_items[$i]["created_by"]);

            foreach ($this->params as $k => $v) {
                PrepareImagePath($this->_items[$i], $k, $v, CONTAINER__PRODUCT, "receipt_type/");
            }
        }
    }

    /**
     * NOT Removes receipt_types from database by provided ids.
     * Just make them inactive.
     *
     * @param array $ids array of receipt_type_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE receipt_type SET archive='Y' WHERE receipt_type_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        $this->AddMessage("object-disactivated", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }

    /**
     * Revert the operation of Remove receipt_types from database by provided ids.
     *
     * @param array $ids array of receipt_type_id's
     */
    public function Activate($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE receipt_type SET archive='N' WHERE receipt_type_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        $this->AddMessage("object-activated", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }
}
