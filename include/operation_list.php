<?php

class OperationList extends LocalObjectList
{
    var $module;
    var $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    function __construct($data = array())
    {
        parent::LocalObjectList($data);

        $this->SetSortOrderFields(array(
            "date_asc" => "date ASC",
            "date_desc" => "date DESC",
            "user_id_asc" => "date DESC, user_id ASC",
            "user_id_desc" => "date DESC, user_id DESC",
            "date_no_time_asc" => "date DESC, date_no_time ASC",
            "date_no_time_desc" => "date DESC, date_no_time DESC"
        ));
        $this->SetOrderBy("date_desc");
        $this->SetItemsOnPage(20);
    }

    /**
     * Loads operation list using filter params
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>ItemsOnPage</u> - int - size of page when paging is used</li>
     *      <li><u>FilterDateRange</u> - string - property for "date" field filtration. format is "{any_date_format_from} - {any_date_format_to}"</li>
     *        <li><u>FilterUser</u> - string - property for user name filtration</li>
     *      <li><u>FilterSection</u> - string - property for section name filtration</li>
     *        </ul>
     * @param bool $fullList If is set to true, then all objects will be loaded at once without paging
     */
    public function LoadOperationList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $groupMap = array(
            "user_id" => array(
                "Key" => "user_id",
                "OrderByKey" => "user_id_desc",
                "GroupTitle" => "user_name",
                "TranslationKey" => "logging-group-user"
            ),
            "date" => array(
                "Key" => "date_no_time",
                "OrderByKey" => "date_no_time_desc",
                "GroupTitle" => "date_no_time",
                "TranslationKey" => "logging-group-date"
            )
        );

        if ($request->GetProperty("FilterGroupBy")) {
            $this->SetOrderBy($groupMap[$request->GetProperty("FilterGroupBy")]["OrderByKey"]);
        } elseif ($request->GetProperty($this->GetOrderByParam())) {
            $this->SetOrderBy($request->GetProperty($this->GetOrderByParam()));
        }

        $where = array();

        //process filter params
        if ($request->GetProperty("FilterDateRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterDateRange"));
            $where[] = "date >= " . Connection::GetSQLDateTime($from);
            $where[] = "date <= " . Connection::GetSQLDateTime($to);
        }

        if ($request->GetProperty("date_from") && $request->GetProperty("date_to")) {
            $where[] = "date >= " . Connection::GetSQLDateTime($request->GetProperty("date_from"));
            $where[] = "date <= " . Connection::GetSQLDateTime($request->GetProperty("date_to"));
        }

        if ($request->GetProperty("FilterUser")) {
            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT user_id FROM user_info
						WHERE CONCAT(" . Connection::GetSQLDecryption("first_name") . ", ' ', " . Connection::GetSQLDecryption("last_name") . ") ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterUser"));
            $userIDs = array_keys($stmt->FetchIndexedList($query));
            $where[] = !empty($userIDs) ? "user_id IN(" . implode(", ", $userIDs) . ")" : "";
        }

        if ($request->GetProperty("FilterSection")) {
            $where[] = "section=" . $request->GetPropertyForSQL("FilterSection");
        }

        if ($request->GetProperty("codes")) {
            if ($request->GetProperty("object_id")) {
                $where[] = "object_id=" . $request->GetPropertyForSQL("object_id");
            }

            $where[] = "code IN ('" . implode("','", $request->GetProperty("codes")) . "')";
        }

        $query = "SELECT operation_id, date, user_id, ip, link, section, code, object_id,
                        CAST(date AS DATE) as date_no_time
                    FROM operation
                    " . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");

        $this->SetCurrentPage();
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));
        $this->PrepareContentBeforeShow();

        if (!$request->GetProperty("FilterGroupBy")) {
            return;
        }

        $groupByID = 0;
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["GroupList"] = array();
            $addChildGroup = false;
            if (
                $i == 0 || $addChildGroup
                || $this->_items[$i][$groupMap[$request->GetProperty("FilterGroupBy")]["Key"]]
                    != $this->_items[$i - 1][$groupMap[$request->GetProperty("FilterGroupBy")]["Key"]]
            ) {
                $count = 1;
                for ($j = $i + 1; $j < $this->GetCountItems(); $j++) {
                    if (
                        $this->_items[$j][$groupMap[$request->GetProperty("FilterGroupBy")]["Key"]]
                            != $this->_items[$j - 1][$groupMap[$request->GetProperty("FilterGroupBy")]["Key"]]
                    ) {
                        break;
                    }

                    $count++;
                }

                $groupByID++;
                $this->_items[$i]["GroupList"][] = array(
                    "group" => 1,
                    "group_title" => $this->_items[$i][$groupMap[$request->GetProperty("FilterGroupBy")]["GroupTitle"]],
                    "group_translation" => GetTranslation(
                        $groupMap[$request->GetProperty("FilterGroupBy")]["TranslationKey"],
                        $this->module
                    ),
                    "group_by_id" => $groupByID,
                    "group_count" => $count
                );
            }
            $this->_items[$i]["group_by_id"] = $groupByID;
        }
    }

    public function LoadCronOperationList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        if ($request->GetProperty($this->GetOrderByParam())) {
            $this->SetOrderBy($request->GetProperty($this->GetOrderByParam()));
        }

        $where = array();

        //process filter params
        if ($request->GetProperty("FilterDateRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterDateRange"));
            $where[] = "date >= " . Connection::GetSQLDateTime($from);
            $where[] = "date <= " . Connection::GetSQLDateTime($to);
        }

        if ($request->GetProperty("Section")) {
            $where[] = "type=" . $request->GetPropertyForSQL("Section");
        }

        $query = "SELECT operation_id, date, description, is_successful, error_message, status, status_updated, used_ids
                    FROM operation_cron
                    " . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
        $this->SetCurrentPage();
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["used_ids"] = json_decode($this->_items[$i]["used_ids"], true);
        }
    }

    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["code_translation"] = GetTranslation("operation-" . $this->_items[$i]["code"]);
            $this->_items[$i]["section_translation"]
                = GetTranslation("operation-section-" . $this->_items[$i]["section"]);

            $this->_items[$i]["user_name"] = User::GetNameByID($this->_items[$i]["user_id"]);

            switch ($this->_items[$i]["section"]) {
                case "receipt":
                    if (
                        $this->_items[$i]["code"] == "receipt_line_id"
                        || $this->_items[$i]["code"] == "receipt_line_id_save"
                    ) {
                        $this->_items[$i]["object_description"]
                            = ReceiptLine::GetTitleByID($this->_items[$i]["object_id"]);
                    } elseif (
                        $this->_items[$i]["code"] != "receipt_list"
                        && $this->_items[$i]["code"] != "receipt_delete"
                    ) {
                        $this->_items[$i]["object_description"] = $this->_items[$i]["object_id"];
                    }
                    break;
                case "company":
                    if ($this->_items[$i]["code"] == "contact_id" || $this->_items[$i]["code"] == "contact_id_save") {
                        $this->_items[$i]["object_description"] = Contact::GetNameByID($this->_items[$i]["object_id"]);
                    } elseif ($this->_items[$i]["code"] == "voucher-generate-and-send") {
                        $this->_items[$i]["object_description"] = $this->_items[$i]["object_id"];
                    } elseif (
                        $this->_items[$i]["code"] != "company_list"
                        && $this->_items[$i]["code"] != "company_delete"
                    ) {
                        $this->_items[$i]["object_description"]
                            = CompanyUnit::GetTitleByID($this->_items[$i]["object_id"]);
                    }
                    break;
                case "employee":
                    if (
                        $this->_items[$i]["code"] != "employee_list"
                        && ($objectId = ($this->_items[$i]["object_id"] ?? null))
                    ) {
                        $this->_items[$i]["object_description"] = Employee::GetNameByID($objectId);
                    }
                    break;
                case "config":
                    $this->_items[$i]["object_description"] = Config::GetTypeByID($this->_items[$i]["object_id"]);
                    break;
                case "user":
                    if ($this->_items[$i]["code"] != "user_list" && $this->_items[$i]["code"] != "user_delete") {
                        $this->_items[$i]["object_description"] = User::GetNameByID($this->_items[$i]["object_id"]);
                    }
                    break;
                case "billing":
                    if ($this->_items[$i]["code"] == "payroll_id" || $this->_items[$i]["code"] == "invoice_id") {
                        $this->_items[$i]["object_description"] = $this->_items[$i]["object_id"];
                    }
                    break;
                case "agreements":
                    if ($this->_items[$i]["code"] != "agreement_list") {
                        $this->_items[$i]["object_description"] = $this->_items[$i]["object_id"];
                    }
                    break;
                default;
            }
        }
    }

    public function GetOperationListForDashboard($dateFrom, $dateTo)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT DISTINCT ON (object_id) object_id, user_id, MIN(date) as date FROM operation
                    WHERE code = 'receipt_id_update' AND
                        date >= " . Connection::GetSQLDateTime($dateFrom) . " AND
                        date <= " . Connection::GetSQLDateTime($dateTo) . "
                    GROUP BY object_id, user_id";
        $saveOperations = $stmt->FetchList($query);

        $operationList = array();
        foreach ($saveOperations as $saveOperation) {
            $query = "SELECT code, date FROM operation
                    WHERE user_id = " . $saveOperation["user_id"] . " AND
                        object_id = " . $saveOperation["object_id"] . " AND
                        date < " . Connection::GetSQLDateTime($saveOperation["date"]);
            $openOperation = $stmt->FetchList($query);

            if (count($openOperation) != 1 || $openOperation[0]["code"] != "receipt_id") {
                continue;
            }

            $operationList[] = array(
                "user_id" => $saveOperation["user_id"],
                "object_id" => $saveOperation["object_id"],
                "opening_time" => $openOperation[0]["date"],
                "save_time" => $saveOperation["date"],
            );
        }

        return $operationList;
    }
}
