<?php

class TripList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function TripList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "created_asc" => "t.created ASC",
            "created_desc" => "t.created DESC",
            "trip_id_asc" => "t.trip_id ASC",
            "trip_id_desc" => "t.trip_id ASC",
        ));
        $this->SetOrderBy("created_desc");
        $this->SetItemsOnPage(0);
    }

    /**
     * Loads trip list for api filtered by employee id
     *
     * @param int $employeeID - employee_id
     */
    public function LoadTripListListForApi($employeeID)
    {
        $where = array();
        $where[] = "t.employee_id=" . intval($employeeID);

        $currentDate = GetCurrentDate();
        $dateFrom = date("Y-m-d", strtotime($currentDate . " - 12 weeks"));
        $where[] = "(t.created>=" . Connection::GetSQLDate($dateFrom) . " OR t.finished_by_employee='N')";

        $dateFromReceipts = date("Y-m-01", strtotime($currentDate . " -1 month"));
        $dateToReceipts = date("Y-m-t", strtotime($currentDate));

        $query = "SELECT t.trip_id, t.employee_id, t.finished_by_employee, t.trip_name, t.purpose, t.start_date, t.end_date, SUM(CASE c.read_by_employee WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_employee 
                    FROM trip AS t
                        LEFT JOIN receipt AS r ON r.trip_id=t.trip_id 
                            AND r.employee_id=" . intval($employeeID) . " 
                            AND DATE(r.created) >= " . Connection::GetSQLDate($dateFromReceipts) . "
                            AND DATE(r.created) <= " . Connection::GetSQLDate($dateToReceipts) . "
					   LEFT JOIN receipt_comment AS c ON c.receipt_id=r.receipt_id 
                    " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . "
                    GROUP BY t.trip_id";

        $this->LoadFromSQL($query);

        $receiptList = new ReceiptList("receipt");

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $receiptList->LoadReceiptListForApi($employeeID, false, false, $this->_items[$i]["trip_id"]);

            $this->_items[$i]["receipt_count"] = count($receiptList->GetItems());

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

            $this->_items[$i]["finished_by_admin"] = !$finishedByAdmin ? 'N' : 'Y';

            $this->_items[$i]['number_necessary_actions_employee'] = ReceiptList::GetNumberNecessaryActionsByEmployee(
                false,
                $employeeID,
                $this->_items[$i]["trip_id"]
            );
        }
    }
}
