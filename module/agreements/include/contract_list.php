<?php

class AgreementsContractList extends LocalObjectList
{
    /**
     * Load services with user agreement information
     *
     * @param int $organizationId Company unit id
     */
    public function LoadAll($organizationId)
    {
        $organizationId = intval($organizationId);
        if ($organizationId <= 0) {
            $this->_items = [];

            return;
        }

        $sql = "SELECT p.group_id, p.title, p.code, a.agreement_id, a.version, a.updated_at
            FROM product_group AS p
                LEFT JOIN agreements AS a ON p.group_id=a.group_id AND a.organization_id=" . $organizationId . "
            WHERE p.receipts='Y'
            ORDER BY p.sort_order ASC";
        $this->LoadFromSQL($sql);
        $this->prepare();
    }

    protected function prepare()
    {
        foreach ($this->_items as $key => $item) {
            $this->_items[$key]["title_translation"] = GetTranslation("product-group-" . $item["code"], "product");
        }
    }
}
