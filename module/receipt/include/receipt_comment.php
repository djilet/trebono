<?php

class ReceiptComment extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of comment properties to be loaded instantly
     */
    public function ReceiptComment($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Creates receipt comment. Object must be loaded from request before the method will be called.
     * Required properties are: receipt_id, user_id, content
     *
     * @return bool true if file is created successfully or false on failure
     */
    public function Create()
    {
        if (!$this->ValidateCreate() || !$this->SaveCommentFile()) {
            return false;
        }

        $stmt = GetStatement();
        $query = "INSERT INTO receipt_comment (receipt_id, user_id, created, content, comment_file, read_by_admin) VALUES ( 
					" . $this->GetIntProperty("receipt_id") . ",
					" . $this->GetIntProperty("user_id") . ",
					" . Connection::GetSQLString(GetCurrentDateTime()) . ",
					" . $this->GetPropertyForSQL("content") . ",
					" . $this->GetPropertyForSQL("comment_file") . ",
                    " . $this->GetPropertyForSQL("read_by_admin") . ")
				RETURNING comment_id";
        if ($stmt->Execute($query)) {
            $this->SetProperty("comment_id", $stmt->GetLastInsertID());
            Receipt::Touch($this->GetProperty("receipt_id"));

            return true;
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Validates input data when trying to create receipt comment
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    private function ValidateCreate()
    {
        if (!$this->ValidateNotEmpty("content")) {
            $this->AddError("receipt-comment-content-empty", $this->module);
        }

        if ($this->GetProperty("read_by_admin") != "Y") {
            $this->SetProperty("read_by_admin", "N");
        }

        return !$this->HasErrors();
    }

    /**
     * Tries to upload comment file and sets comment_file property if file is uploaded correctly
     *
     * @param string $savedImage previously uploaded filename
     *
     * @return bool false if error is occured during new image uploading or true if its uploaded successfully or no new image provided
     */
    private function SaveCommentFile()
    {
        $receipt = new Receipt("receipt");
        $receipt->LoadByID($this->GetProperty("receipt_id"));

        $specificProductGrpup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        $container = $specificProductGrpup->GetContainer();

        $fileStorage = GetFileStorage($container);

        $commentFile = $fileStorage->Upload("comment_file", RECEIPT_IMAGE_DIR . "comment/", false, null);
        if ($commentFile) {
            $this->SetProperty("comment_file", $commentFile["FileName"]);
        }

        $this->AppendErrorsFromObject($fileStorage);

        return !$fileStorage->HasErrors();
    }
}
