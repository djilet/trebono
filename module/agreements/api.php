<?php

require_once(dirname(__FILE__) . "/../../include/init.php");
require_once(dirname(__FILE__) . "/include/contract.php");


class ApiAgreementsProcessor extends ApiProcessor
{
    /**
     * @var  string
     */
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

    /**
     * AgreementApiProcessor constructor.
     *
     * @param string $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Required data check
     *
     * @return bool
     */
    public function ValidateRequest()
    {
        $device = new Device();
        $userID = $device->GetUserID($this->request->GetProperty("auth_device_id"));

        $user = new User();
        if (!$user->LoadByID($userID) || !$user->Validate(["api"])) {
            return false;
        }

        $this->employee = new Employee("company");

        return $this->employee->LoadByUserID($userID);
    }

    public function Process($method, $path, LocalObject $request, ApiResponse $response)
    {
        $this->request = $request;
        $this->response = $response;

        if (!$this->ValidateRequest()) {
            $this->response->SetStatus(ApiResponse::STATUS_ERROR);
            $this->response->SetCode(403);

            return false;
        }

        $pathCount = count($path);

        if ($method == "GET") {
            if ($pathCount == 2 && $path[0] == "group_id") {
                $this->LoadAgreementByGroupID(intval($path[1]));

                return true;
            }

            if ($pathCount == 3 && $path[0] == "group_id" && $path[2] == "check") {
                $this->CheckNeedIsAcceptAgreementByGroupID(intval($path[1]));

                return true;
            }
        }


        if ($method == "POST") {
            if ($pathCount == 1 && $path[0] > 0) {
                $this->AcceptedAgreementContract(intval($path[0]));

                return true;
            }
        }

        $this->response->SetStatus(ApiResponse::STATUS_ERROR);
        $this->response->SetCode(404);

        return false;
    }

    /**
     * User Agreement load
     *
     * @param int $groupID Service group id
     */
    protected function LoadAgreementByGroupID($groupID)
    {
        $contract = new AgreementsContract($this->module);
        $contract->LoadForApi($groupID, $this->employee);

        $this->response->SetStatus(ApiResponse::STATUS_OK);
        $this->response->SetData($contract->GetProperties());
        $this->response->SetCode(200);
    }

    /**
     * User accepts user agreement
     *
     * @param $agreementID
     */
    protected function AcceptedAgreementContract($agreementID)
    {
        $versionID = $this->request->IsPropertySet("version_id") ? $this->request->GetProperty("version_id") : 0;
        $contract = new AgreementsContract($this->module);
        $result = $contract->UserAcceptedTheAgreement(
            $agreementID,
            $this->employee,
            $this->request->GetProperty("auth_device_id"),
            $versionID
        );

        if ($result) {
            $this->response->SetStatus(ApiResponse::STATUS_OK);
            $this->response->SetCode(200);
        } else {
            $this->response->SetStatus(ApiResponse::STATUS_ERROR);
            $this->response->SetCode(403);
        }
    }

    /**
     * Checks whether to accept an employment agreement
     *
     * @param $groupId
     */
    protected function CheckNeedIsAcceptAgreementByGroupID($groupId)
    {
        $contract = new AgreementsContract($this->module);
        $result = $contract->IsAgreementMustBeAccepted(
            $groupId,
            $this->employee->GetIntProperty("company_unit_id"),
            $this->employee->GetIntProperty("employee_id")
        );

        $this->response->SetStatus(ApiResponse::STATUS_OK);
        $this->response->SetData([
            "exist_new_agreement" => $result,
        ]);
        $this->response->SetCode(200);
    }
}
