<?php

class ReceiptFileList extends LocalObjectList
{
    private $module;
    private $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ReceiptFileList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "created_asc" => "f.created ASC",
            "created_desc" => "f.created DESC",
        ));
        $this->SetOrderBy("created_asc");

        $this->params = array();
        $this->params["file"] = LoadImageConfig("file_image", $this->module, RECEIPT_RECEIPT_IMAGE);
    }

    /**
     * Loads receipt file list
     *
     * @param int $receiptID receipt_id file list to be filtered by
     */
    public function LoadFileList($receiptID)
    {
        $where = array();
        $where[] = "f.receipt_id=" . intval($receiptID);

        $query = "SELECT f.receipt_file_id, f.receipt_id, f.file_image, f.created, f.hash , f.signature_file, f.signature_report_file
					FROM receipt_file AS f 
					" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow($receiptID);
    }

    /**
     * Prepares file image paths and urls
     */
    private function PrepareContentBeforeShow($receiptID)
    {
        $receipt = new Receipt($this->module);
        $receipt->LoadByID($receiptID);

        $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        $container = $specificProductGroup ? $specificProductGroup->GetContainer() : "";

        for ($i = 0; $i < count($this->_items); $i++) {
            foreach ($this->params as $k => $v) {
                PrepareImagePath($this->_items[$i], $k, $v, $container, "file/");
            }
        }
    }

    /**
     * Removes receipt files from database by provided ids. Also removes their pictures from file system.
     *
     * @param array $ids array of receipt_file_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $receiptFileList = $stmt->FetchList("SELECT receipt_file_id, file_image, signature_file, signature_report_file, receipt_id FROM receipt_file WHERE receipt_file_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")");
        foreach ($receiptFileList as $receiptFile) {
            $receipt = new Receipt("receipt");
            $receipt->LoadByID($receiptFile["receipt_id"]);

            $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
            $container = $specificProductGroup ? $specificProductGroup->GetContainer() : "";

            $fileStorage = GetFileStorage($container);

            $fileStorage->Remove(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["file_image"]);
            if ($receiptFile["signature_file"]) {
                $fileStorage->Remove(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_file"]);
            }
            if (!$receiptFile["signature_report_file"]) {
                continue;
            }

            $fileStorage->Remove(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_report_file"]);
        }

        $receiptIDs = array_keys($stmt->FetchIndexedList("SELECT DISTINCT(receipt_id) FROM receipt_file WHERE receipt_file_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")"));
        if (count($receiptIDs) > 0) {
            foreach ($receiptIDs as $receiptID) {
                Receipt::Touch($receiptID);
            }
        }

        $query = "DELETE FROM receipt_file WHERE receipt_file_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() > 0) {
            $this->AddMessage("object-removed", $this->module, array("Count" => $stmt->GetAffectedRows()));
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = "DELETE FROM receipt_file_log WHERE receipt_file_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);
    }
}
