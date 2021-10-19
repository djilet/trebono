<?php

class Trip extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of product properties to be loaded instantly
     */
    public function Trip($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads trip by its trip_id
     *
     * @param int $id trip_id
     *
     * @return bool true if loaded successfully or else otherwise
     */
    public function LoadByID($id)
    {
        $query = "SELECT trip_id, employee_id, created, finished_by_employee, trip_name, purpose, start_date, end_date
					FROM trip
					WHERE trip_id=" . intval($id);
        $this->LoadFromSQL($query);

        if (!$this->GetProperty("trip_id")) {
            return false;
        }

        $receiptList = new ReceiptList("receipt");
        $receiptList->LoadReceiptListForApi(
            $this->GetProperty("employee_id"),
            false,
            false,
            $this->GetProperty("trip_id")
        );

        $this->SetProperty("receipt_count", count($receiptList->GetItems()));

        $bookedList = array_column($receiptList->GetItems(), "booked");
        $bookedKeys = array_keys($bookedList, "Y");
        $statusList = array_column($receiptList->GetItems(), "status");
        $deniedKeys = array_keys($statusList, "denied");
        $allKeys = array_keys($bookedList);

        $finishedByAdmin = true;
        foreach ($allKeys as $key) {
            if (!in_array($key, $bookedKeys) && !in_array($key, $deniedKeys)) {
                $finishedByAdmin = false;
                break;
            }
        }

        if (!$finishedByAdmin) {
            $this->SetProperty("finished_by_admin", "N");
        } else {
            $this->SetProperty("finished_by_admin", "Y");
        }

        return true;
    }

    /**
     * Creates trip from mobile application. Object must be loaded from request before the method will be called.
     * Required properties are: employee_id
     *
     * @return bool true if trip is created successfully or false on failure
     */
    public function Create()
    {
        if (!$this->ValidateCreate()) {
            return false;
        }

        $stmt = GetStatement();
        $query = "INSERT INTO trip (employee_id, created, trip_name, purpose, start_date, end_date) VALUES (
						" . $this->GetIntProperty("employee_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
                        " . $this->GetPropertyForSQL("trip_name") . ",
                        " . $this->GetPropertyForSQL("purpose") . ",
                        " . $this->GetPropertyForSQL("start_date") . ",
                        " . $this->GetPropertyForSQL("end_date") . ")
					RETURNING trip_id";
        if ($stmt->Execute($query)) {
            $this->SetProperty("trip_id", $stmt->GetLastInsertID());

            return true;
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Set finished=Y to trip by by provided trip_id
     *
     * @param int $tripID trip_id
     *
     * @return bool true if trip is updated successfully or false on failure
     */
    public static function FinishByEmployee($tripID)
    {
        $stmt = GetStatement();
        $query = "UPDATE trip SET finished_by_employee='Y' WHERE trip_id=" . intval($tripID);

        return $stmt->Execute($query);
    }

    /**
     * Validates input data on create trip
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    public function ValidateCreate()
    {
        if (!$this->IsPropertySet("trip_name")) {
            $this->AddError("trip_name-empty", $this->module);
        }

        return !$this->HasErrors();
    }
}
