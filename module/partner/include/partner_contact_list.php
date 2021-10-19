<?php

class PartnerContactList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function PartnerContactList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "type_asc" => "	CASE
								WHEN contact_type='management' THEN 1
								WHEN contact_type='hr' THEN 2
								WHEN contact_type='other' THEN 3
							END, contact_id ASC",
        ));
        $this->SetOrderBy("type_asc");
    }

    /**
     * Loads partner's contacts
     *
     * @param int $partnerID PartnerID of partner which contacts should be loaded
     */
    public function LoadContactList($partnerID)
    {
        $where = array();
        $where[] = "c.partner_id=" . intval($partnerID);

        $query = "SELECT c.partner_contact_id AS contact_id, c.partner_id, c.created, c.contact_type, 
						c.position, c.department, c.first_name, c.last_name, 
						c.email, c.phone, c.phone_job, c.comment, 
						c.salutation 
					FROM partner_contact AS c "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["contact_type_title"] = GetTranslation(
                "contact-type-" . $this->_items[$i]["contact_type"],
                $this->module
            );
        }
    }

    /**
     * Removes partner's contacts from database by provided ids.
     *
     * @param array $ids array of contact_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement(DB_PERSONAL);

        $query = "DELETE FROM partner_contact WHERE partner_contact_id IN (" . implode(
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
