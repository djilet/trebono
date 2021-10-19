<?php

class GivveTransactionImport extends LocalObject
{

    private $module;

    protected $headerVersion = 'Accept-Version: v2';
    protected $headerContentType = 'Content-Type: application/json';

    protected $base_url = 'https://www.givve.com/api';

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of contact properties to be loaded instantly
     */
    public function GivveTransactionImport($module, $data = array())
    {
        parent::LocalObject($data);
        $this->module = $module;
    }

    /**
     * Returns access and refresh tokens acquired from givve
     *
     * @param $data string Body for API request
     *
     * @return mixed
     */
    public function GetAccessToken($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $this->headerVersion,
            $this->headerContentType
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $this->base_url . "/authorizations");

        $output = curl_exec($ch);
        curl_close($ch);

        return json_decode($output, true);
    }

    /**
     * Returns list of vouchers
     *
     * @param $accessToken string Customer identifier, required to gain access to givve
     *
     * @return mixed
     */
    public function GetVoucherList($accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $this->headerVersion,
            $this->headerContentType,
            'Authorization: Bearer ' . $accessToken
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_URL, $this->base_url . "/vouchers");

        $output = curl_exec($ch);
        curl_close($ch);

        return json_decode($output, true);
    }

    /**
     * Returns separate voucher
     *
     * @param $voucherID string Voucher identifier
     * @param $accessToken string Customer identifier, required to gain access to givve
     *
     * @return mixed
     */
    public function GetVoucher($voucherID, $accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $this->headerVersion,
            $this->headerContentType,
            'Authorization: Bearer ' . $accessToken
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_URL, $this->base_url . "/vouchers/" . $voucherID);

        $output = curl_exec($ch);
        curl_close($ch);

        return json_decode($output, true);
    }

    /**
     * Returns list of transactions by given voucher ID
     *
     * @param $voucherID string Voucher identifier
     * @param $accessToken string Customer identifier, required to gain access to givve
     *
     * @return mixed
     */
    public function GetTransactionList($voucherID, $accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $this->headerVersion,
            $this->headerContentType,
            'Authorization: Bearer ' . $accessToken
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt(
            $ch,
            CURLOPT_URL,
            $this->base_url . "/vouchers/" . $voucherID . "/transactions?sort=-booked_at&filter[created_at][\$gte]=" . date(
                "Y-m-d",
                strtotime("-" . intval(Config::GetConfigValue("givve_transactions_month_limit")) . " month", time())
            )
        );

        $output = curl_exec($ch);
        curl_close($ch);

        return json_decode($output, true);
    }
}
