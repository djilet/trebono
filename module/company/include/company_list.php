<?php

class CompanyList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function CompanyList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "title_asc" => "title ASC",
            "title_desc" => "title DESC",
        ));
        $this->SetOrderBy("title_asc");
        $this->SetItemsOnPage(20);
    }

    /**
     * Loads company list using acl to select available companies only.
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>ItemsOnPage</u> - int - size of page when paging is used</li>
     *        <li><u>FilterTitle</u> - string - property for root company_unit title filtration</li>
     *        </ul>
     * @param bool $fullList If is set to true, then all objects will be loaded at once without paging
     */
    public function LoadCompanyList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $where = array();
        if ($request->GetProperty("FilterTitle")) {
            $where[] = Connection::GetSQLDecryption("ua.title") . " ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterTitle"));
        }
        if (!$request->IsPropertySet("FilterArchive")) {
            $where[] = "u.archive='N'";
        } elseif ($request->GetProperty("FilterArchive")) {
            $where[] = "u.archive=" . $request->GetPropertyForSQL("FilterArchive");
        }

        if ($request->GetProperty("FilterActiveModule")) {
            $contractList = new ContractList("product");
            $contractList->LoadActiveContractListByProductID(
                OPTION_LEVEL_COMPANY_UNIT,
                $request->GetProperty("FilterActiveModule")
            );
            if ($contractList->GetCountItems() > 0) {
                $where[] = "u.company_unit_id IN(" . implode(
                    ", ",
                    array_column($contractList->GetItems(), "company_unit_id")
                ) . ")";
            } else {
                $where[] = "u.company_unit_id IN(NULL)";
            }
        }

        $user = new User();
        $user->LoadBySession();
        if (!$user->Validate(array("company_unit", "contract"), "or")) {
            $companyUnitIDs = array_merge(
                $user->GetPermissionLinkIDs("company_unit"),
                $user->GetPermissionLinkIDs("contract")
            );
            if (count($companyUnitIDs) <= 0) {
                return;
            }

            $where[] = "ua.company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
        }

        $query = "SELECT c.company_id, MAX(" . Connection::GetSQLDecryption("u.title") . ") AS title 
					FROM company AS c 
						LEFT JOIN company_unit AS u ON u.company_id=c.company_id AND u.parent_unit_id IS NULL 
						LEFT JOIN company_unit AS ua ON ua.company_id=c.company_id 
					" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . " 
					GROUP BY c.company_id";

        $this->SetCurrentPage();
        $this->LoadFromSQL($query);
    }

    /**
     * Removes companies from database by provided ids.
     *
     * @param array $ids array of company_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "DELETE FROM company WHERE company_id IN (" . implode(", ", Connection::GetSQLArray($ids)) . ")";
        if ($stmt->Execute($query)) {
            $this->AddMessage("object-removed", $this->module, array("Count" => $stmt->GetAffectedRows()));
        } else {
            $this->AddError("sql-error-removing");
        }
    }
}
