<?php

require_once(dirname(__FILE__) . "/../../include/init.php");

class ApiStatisticsProcessor extends ApiProcessor
{
    private $module;

    /**
     * @var  LocalObject
     */
    private $request;

    /**
     * @var ApiResponse
     */
    private $response;

    /**
     * @var  Employee
     */
    private $employee;


    public function __construct($module)
    {
        $this->module = $module;
    }

    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
        $method = 'get' . implode($path, '') . 'Action_' . $method;

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return false;
    }

    /**
     * Get all receipt statistics
     *
     * @return bool
     */
    private function getAllAction_GET(): bool
    {
        $device = new Device();
        $userID = $device->GetUserID($this->request->GetProperty("auth_device_id"));

        $user = new User();
        if ($user->LoadByID($userID) && $user->Validate(array("api"))) {
            $this->employee = new Employee("company");
            if ($this->employee->LoadByUserID($userID)) {
                $options = [
                    'available_units_month',
                    'available_units_year',
                    'available_month',
                    'available_year',
                    'approved_units_month',
                    'approved_units_year',
                    'approved_month',
                    'approved_year',
                    'approve_proposed_units_month',
                    'approve_proposed_month',
                    'approve_proposed_year',
                ];
                $data = Statistics::GetStatistics(
                    $this->employee,
                    $this->request->GetProperty("monthly_statistics_date"),
                    null,
                    $options,
                    false,
                    true
                );

                $this->response->SetStatus(ApiResponse::STATUS_OK);
                $this->response->SetCode(200);
                $this->response->SetData($data);

                return true;
            }
        }

        $this->response->SetStatus(ApiResponse::STATUS_ERROR);
        $this->response->SetCode(403);

        return true;
    }

    private function GetOptionValue($code)
    {
        $option = new Option('option');
        if ($option->LoadByID(Option::GetOptionIDByCode($code))) {
            $optionValue = Option::GetCurrentValue(
                OPTION_LEVEL_EMPLOYEE,
                $option->GetIntProperty('option_id'),
                $this->employee->GetIntProperty("employee_id")
            );

            if ($optionValue === null) {
                $optionValue = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $option->GetProperty('code'),
                    $this->employee->GetIntProperty("employee_id"),
                    GetCurrentDate()
                );
            }

            return floatval($optionValue);
        }

        return 0;
    }

    private function GetPriceFormat($number)
    {
        return number_format($number, 2, ".", "");
    }
}
