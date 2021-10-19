<?php

class AppVersionList extends LocalObjectList
{
    /**
     * Constructor
     */
    public function AppVersionList()
    {
        $this->SetSortOrderFields(array(
            "app_version_asc" => "client ASC, string_to_array(app_version, '.')::int[] ASC",
            "app_version_desc" => "client ASC, string_to_array(app_version, '.')::int[] DESC",
        ));
        $this->SetOrderBy("app_version_asc");
    }

    /**
     * Loads app version list
     */
    public function LoadAppVersionList()
    {
        $groupMap = array(
            "client" => array(
                "Key" => "client",
                "OrderByKey" => "client ASC, string_to_array(app_version, '.')::int[] ASC",
                "GroupTitle" => "client",
                "TranslationKey" => "app_version-group-client"
            )
        );
        $groupBy = "client";

        $query = "SELECT app_version_id, app_version, client, critical, created, created_by FROM app_version WHERE archive='N'";
        $this->LoadFromSQL($query);

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["GroupList"] = array();
            if ($i == 0 || $this->_items[$i][$groupMap[$groupBy]["Key"]] != $this->_items[$i - 1][$groupBy]) {
                $this->_items[$i]["GroupList"][] = array(
                    "group" => 1,
                    "group_title" => $this->_items[$i][$groupMap[$groupBy]["GroupTitle"]],
                    "group_translation" => GetTranslation($groupMap[$groupBy]["TranslationKey"])
                );
            }
            $this->_items[$i]["user_name"] = User::GetNameByID($this->_items[$i]["created_by"]);
        }
    }

    /**
     * Sends to archive app versions by provided ids.
     *
     * @param array $ids array of app_version_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();
        $query = "UPDATE app_version SET archive='Y' WHERE app_version_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $values = array();
        foreach ($ids as $id) {
            $values[] = "(" . $id . ",'archive','Y','" . GetCurrentDateTime() . "'," . $GLOBALS['user']->GetIntProperty("user_id") . ")";
        }

        if ($stmt->Execute($query)) {
            //Save history
            $stmt1 = GetStatement(DB_CONTROL);
            $query = "INSERT INTO app_version_history (app_version_id, property_name, value, created, user_id) VALUES" . implode(
                ",",
                $values
            );
            $stmt1->Execute($query);

            if ($stmt->GetAffectedRows() > 0) {
                $this->AddMessage("object-removed", null, array("Count" => $stmt->GetAffectedRows()));
            }
        } else {
            $this->AddError("sql-error");
        }
    }
}
