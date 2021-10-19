<?php

class PartnerList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function PartnerList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "title_asc" => "u.title ASC",
            "title_desc" => "u.title DESC",
        ));
        $this->SetOrderBy("title_asc");
    }

    /**
     * Loads available partner list.
     * Using filter to select available partners only.
     */
    public function LoadPartnerList($request, $fullList = false, $forAdmin = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $where = array();

        //check for permissions
        $user = new User();
        $user->LoadBySession();
        if (!$user->Validate(array("root")) && !$user->Validate(array("partner")) && $forAdmin === false) {
            $partnerIDs = $user->GetPermissionLinkIDs("partner");
            if (count($partnerIDs) <= 0) {
                return;
            }

            $where[] = "u.\"PartnerID\" IN(" . implode(", ", $partnerIDs) . ")";
        }

        if ($request->GetProperty("FilterTitle")) {
            $where[] = "u.title  ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterTitle"));
        }
        if (!$request->IsPropertySet("FilterArchive")) {
            $where[] = "u.archive='N'";
        } elseif ($request->GetProperty("FilterArchive")) {
            $where[] = "u.archive=" . $request->GetPropertyForSQL("FilterArchive");
        }

        $query = "SELECT u.\"PartnerID\" as partner_id, u.*  
					FROM partner AS u 					   
					" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $this->SetCurrentPage();
        $this->LoadFromSQL($query);
    }


    /**
     * Returns all partner_id's
     *
     * @return array of partner_id's
     */
    public static function GetAllPartnerIDs()
    {
        $stmt = GetStatement();
        $query = "SELECT \"PartnerID\" AS partner_id FROM partner";

        return array_keys($stmt->FetchIndexedList($query));
    }

    /**
     * NOT Removes partner from database by provided ids.
     * Just make the inactive.
     *
     * @param array $ids array of partner_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE partner SET archive='Y' WHERE \"PartnerID\" IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        if ($stmt->Execute($query)) {
            if ($stmt->GetAffectedRows() > 0) {
                $this->AddMessage("object-disactivated", $this->module, array("Count" => $stmt->GetAffectedRows()));
            }
        } else {
            $this->AddError("sql-error-removing");
        }
    }

    /**
     * Revert operation of Remove partner by provided ids.
     *
     * @param array $ids array of partner_id's
     */
    public function Activate($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE partner SET archive='N' WHERE \"PartnerID\" IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        if ($stmt->Execute($query)) {
            if ($stmt->GetAffectedRows() > 0) {
                $this->AddMessage("object-activated", $this->module, array("Count" => $stmt->GetAffectedRows()));
            }
        } else {
            $this->AddError("sql-error-activating");
        }
    }
}
