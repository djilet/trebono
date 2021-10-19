<?php

class BillableItem extends LocalObject
{
    /**
     * change archive to N in billable_items
     *
     * @param int billable item's id
     * 
     */
    public function changeArchiveToN($itemId)
    {
        $stmt = GetStatement(DB_MAIN);

            $query = "UPDATE billable_item SET archive='N'
                            WHERE item_id=" . Connection::GetSQLString($itemId);
                $result = $stmt->Execute($query);

            return ($result == 1) ? true : false;
    }


    /**
     * change archive to Y in billable_items
     *
     * @param int billable item's id
     * 
     */
    public function changeArchiveToY($itemId)
    {
        $stmt = GetStatement(DB_MAIN);

            $query = "UPDATE billable_item SET archive='Y'
                            WHERE item_id=" . Connection::GetSQLString($itemId);
                $result = $stmt->Execute($query);

            return ($result == 1) ? true : false;
    }


    /**
     * delete billable item
     *
     * @param int billable item's id
     * 
     */
    public function deleteBillableItem($itemId)
    {
        $stmt = GetStatement(DB_MAIN);

            $query = "DELETE FROM billable_item WHERE item_id=" . Connection::GetSQLString($itemId);

                $result = $stmt->Execute($query);

            return ($result == 1) ? true : false;
    }



    /**
     * add billable item
     *
     * @param array new bill data
     * 
     */
    public function addBillableItem($billData)
    {

        $employeeId = Connection::GetSQLString($billData['user_id']);
        $dateStart = Connection::GetSQLString($billData['date_start']);
        $dateEnd = Connection::GetSQLString($billData['date_end']);
        $companyId = Connection::GetSQLString($billData['company_unit_id']);
        $price = Connection::GetSQLString($billData['amount']);
        $itemName = Connection::GetSQLString($billData['reason']);
        $discount = Connection::GetSQLString($billData['discount']);
        $quantity = Connection::GetSQLString($billData['quantity']);

        $stmt = GetStatement(DB_MAIN);
            $query = "INSERT INTO billable_item (date_start, date_end, created, created_user, company_unit_id, price,
                                                item_name, discount, quantity, updated_at, archive, invoice_id)
                                                VALUES (
                                                    $dateStart,
                                                    $dateEnd,
                                                    NOW(),
                                                    $employeeId,
                                                    $companyId,
                                                    $price,
                                                    $itemName,
                                                    $discount,
                                                    $quantity,
                                                    NOW(),
                                                    'N',
                                                    NULL
                                                )";

            $result = $stmt->Execute($query);

        return $result;
    }



    /**
     * edit billable item
     *
     * @param array new bill data
     * 
     */
    public function editBillableItem($billData)
    {
        $itemId = Connection::GetSQLString($billData['billable_item_id']);

        $dateStart = Connection::GetSQLString($billData['date_start']);
        $dateEnd = Connection::GetSQLString($billData['date_end']);
        $price = Connection::GetSQLString($billData['amount']);
        $itemName = Connection::GetSQLString($billData['reason']);
        $discount = Connection::GetSQLString($billData['discount']);
        $quantity = Connection::GetSQLString($billData['quantity']);

        $stmt = GetStatement(DB_MAIN);

            $query = "UPDATE billable_item SET 
                                            date_start=$dateStart,
                                            date_end=$dateEnd,
                                            price=$price,
                                            item_name=$itemName,
                                            discount=$discount,
                                            quantity=$quantity,
                                            updated_at=NOW()
                    WHERE item_id=$itemId
                ";

                $result = $stmt->Execute($query);

        return $result;
    }



    /**
     * get billable data
     * 
     * @param int itemId 
     */
    public function getBillData($itemId)
    {
        $stmt = GetStatement(DB_MAIN);

            $query = "SELECT * FROM billable_item WHERE item_id=" . Connection::GetSQLString($itemId);

                $result = $stmt->FetchRow($query);

        $result['date_start'] = implode(".", array_reverse(explode("-",($result['date_start']) ) ) );
        $result['date_end'] = implode(".", array_reverse(explode("-",($result['date_end']) ) ) );

        $result['price'] = GetPriceFormat($result['price']);

        $createdDate = explode(" ", $result['created']);
            $createdDate[0] = implode(".", array_reverse(explode("-",($createdDate[0]) ) ) );
            $createdDate[1] = substr($createdDate[1], 0 , -10);
        $result['created'] = implode(" ", $createdDate);

            return $result;
    }



    /**
     * get billable reasons from DB (misc)
     */
    public function getBillalbleReasons()
    {
        $stmt = GetStatement(DB_MAIN);

            $query = "SELECT value FROM config WHERE code='create_standard_billing_items'";

                $result = explode("\n", $stmt->FetchRow($query)['value']);


        $newResult = [];
        foreach ($result as $key => &$value) {
            $array = explode(';', $value);
                $newResult[] = ['name' => $array[0], 'value' => $array[1]];
        }

        return $newResult;
    }



    /**
     * check bill by id
     * 
     * @param int item's id 
     * 
     * @param int company's id
     */
    public function checkBillById($itemId, $companyId)
    {
        $stmt = GetStatement(DB_MAIN);

            $query = "SELECT * FROM billable_item WHERE item_id=" . Connection::GetSQLString($itemId) . " 
                        AND company_unit_id=" . Connection::GetSQLString($companyId);

            $result = $stmt->FetchRow($query);

        return $result;
    }



}