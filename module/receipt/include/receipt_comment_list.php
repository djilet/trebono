<?php

class ReceiptCommentList extends LocalObjectList
{
    private $module;
    private $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ReceiptCommentList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "created_asc" => "c.created ASC",
            "created_desc" => "c.created DESC",
        ));
        $this->SetOrderBy("created_desc");

        $this->params = array();
        $this->params["comment_file"] = LoadImageConfig("comment_file", $this->module, RECEIPT_COMMENT_FILE);
        $this->params["user_image"] = LoadImageConfig("user_image", "user", GetFromConfig("UserImage"));
    }

    /**
     * Loads receipt comment list for admin panel
     *
     * @param int $receiptID receipt_id comment list to be filtered by
     */
    public function LoadCommentListForAdmin($receiptID)
    {
        $where = array();
        $where[] = "c.receipt_id=" . intval($receiptID);
        $where[] = "c.archive='N'";

        $query = "SELECT c.comment_id, c.receipt_id, c.user_id, c.created, c.content, c.comment_file 
					FROM receipt_comment AS c 
					" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow($receiptID);
    }

    public function MarkAsReadByAdmin()
    {
        $user = new User();
        $user->LoadBySession();

        if ($user->GetProperty("email") == "root@3kglobaltrading.com" || $this->GetCountItems() <= 0) {
            return;
        }

        $commentIDs = array_column($this->GetItems(), "comment_id");

        $stmt = GetStatement();
        $query = "UPDATE receipt_comment SET read_by_admin='Y' WHERE comment_id IN(" . implode(
            ", ",
            $commentIDs
        ) . ")";
        $stmt->Execute($query);
    }

    /**
     * Loads receipt comment list for mobile application
     *
     * @param int $receiptID receipt_id comment list to be filtered by
     */
    public function LoadCommentListForApi($receiptID)
    {
        $where = array();
        $where[] = "c.receipt_id=" . intval($receiptID);
        $where[] = "c.archive='N'";

        $query = "SELECT c.comment_id, c.receipt_id, c.user_id, c.created, c.content, c.comment_file 
					FROM receipt_comment AS c 
					" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->SetOrderBy("created_asc");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow($receiptID);
        $this->MarkAsReadByEmployee();
    }

    private function MarkAsReadByEmployee()
    {
        if ($this->GetCountItems() <= 0) {
            return;
        }

        $commentIDs = array_column($this->GetItems(), "comment_id");

        $stmt = GetStatement();
        $query = "UPDATE receipt_comment SET read_by_employee='Y' WHERE comment_id IN(" . implode(
            ", ",
            $commentIDs
        ) . ")";
        $stmt->Execute($query);
    }

    /**
     * Puts additional comment fields which cannot be loaded by main sql query
     *
     * @param int $receiptID receipt_id comment list was filtered by
     */
    private function PrepareContentBeforeShow($receiptID)
    {
        if ($this->GetCountItems() <= 0) {
            return;
        }

        $receipt = new Receipt($this->module);
        $receipt->LoadByID($receiptID);

        $specificProductGrpup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        $container = $specificProductGrpup->GetContainer();

        $employee = new Employee("company");
        $employee->LoadByID($receipt->GetProperty("employee_id"));

        //fetch users of loaded comments
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT user_id, user_image, user_image_config, 
                        " . Connection::GetSQLDecryption("first_name") . " AS first_name,
                        " . Connection::GetSQLDecryption("last_name") . " AS last_name
                    FROM user_info 
                    WHERE user_id IN(" . implode(", ", array_column($this->GetItems(), "user_id")) . ")";
        $userMap = $stmt->FetchAssocIndexedList($query, "user_id");

        //prepare image path and append user info
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["created_ago"] = GetAgoString($this->_items[$i]["created"]);
            $this->_items[$i]["is_owner_comment"] = $this->_items[$i]["user_id"] == $employee->GetProperty("user_id")
                ? "Y"
                : "N";
            $this->_items[$i]["is_comment_file_image"] = preg_match(
                '/(\.jpg|\.jpeg|\.gif|\.png|\.bmp)$/i',
                $this->_items[$i]["comment_file"]
            ) ? "Y" : "N";

            if (isset($userMap[$this->_items[$i]["user_id"]])) {
                $this->_items[$i] = array_merge($this->_items[$i], $userMap[$this->_items[$i]["user_id"]]);
            }

            PrepareImagePath(
                $this->_items[$i],
                "comment",
                $this->params["comment_file"],
                $container,
                "comment/",
                "_file"
            );
            PrepareImagePath($this->_items[$i], "user", $this->params["user_image"], CONTAINER__CORE, "", "_image");
            PrepareDownloadPath($this->_items[$i], "comment_file", RECEIPT_IMAGE_DIR . "comment/", $container);
        }
    }

    /**
     * Marks receipt comments as "archive" by provided ids
     *
     * @param array $ids array of comment_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();
        $receiptIDs = array_keys($stmt->FetchIndexedList("SELECT DISTINCT(receipt_id) FROM receipt_comment WHERE comment_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")"));
        if (count($receiptIDs) > 0) {
            foreach ($receiptIDs as $receiptID) {
                Receipt::Touch($receiptID);
            }
        }

        $query = "UPDATE receipt_comment SET archive='Y' WHERE comment_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        $this->AddMessage("object-removed", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }
}
