<?php

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../include/init.php");
require_once(dirname(__FILE__) . "/init.php");

$module = "statistics";
$moduleURL = "module.php?load=" . $module;

$result = array();

$user = new User();
if (!$user->LoadBySession()) {
    $result["SessionExpired"] = GetTranslation("your-session-expired");
    exit();
} else {
    $request = new LocalObject(array_merge($_GET, $_POST));

    switch ($request->GetProperty("Action")) {
        case "GetStatisticsHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_statistics.html");

            $employee = new Employee("company");
            $employee->LoadByID($request->GetProperty("employee_id"));

            if ($request->GetProperty("monthly_statistics_date")) {
                $request->SetProperty(
                    "monthly_statistics_date",
                    "01." . $request->GetProperty("monthly_statistics_date")
                );
            }

            $paramList = [
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
                $employee,
                $request->GetProperty("monthly_statistics_date"),
                null,
                $paramList,
                true
            );
            if (isset($data["product_groups"])) {
                $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true);
                foreach ($data["product_groups"] as $key => $productGroup) {
                    $data["product_groups"][$key] = array_merge(
                        $data["product_groups"][$key],
                        $data["product_groups"][$key]["stats"]
                    );
                    unset($data["product_groups"][$key]["stats"]);

                    if (
                        !in_array($productGroup["code"], array_column($voucherProductGroupList, "code")) ||
                        $request->GetProperty("is_employee_admin") != "Y" ||
                        $request->GetProperty("is_employee_self") == "Y"
                    ) {
                        continue;
                    }

                    $data["product_groups"][$key]["hide_approved"] = true;
                    $data["total_month"] -= $productGroup["approved_month"];
                    $data["total_year"] -= $productGroup["approved_year"];
                }
            }
            $content->LoadFromArray($data);

            $result["HTML"] = $popupPage->Grab($content);
            break;
    }
}

echo json_encode($result);
