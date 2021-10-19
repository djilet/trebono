<?php

use Phinx\Migration\AbstractMigration;

class AttachPermissionsToGroups extends AbstractMigration
{
    public function up()
    {
        $data = $this->getData();
        $groupSort = 1;
        $permissionSort = 1;
        $groupBuilder = $this->getQueryBuilder()
            ->insert(["group_id", "code", "sort_order"])->into("permission_group");
        $permissionBuilder = $this->getQueryBuilder()
            ->insert(["group_id", "name", "title", "sort_order"])->into("permission");
        foreach ($data as $groupCode => $permissions) {
            $groupBuilder->values([
                "group_id" => $groupSort,
                "code" => $groupCode,
                "sort_order" => $groupSort,
            ]);
            foreach ($permissions as $permission) {
                $permissionBuilder->values([
                    "group_id" => $groupSort,
                    "name" => $permission["name"],
                    "title" => $permission["title"],
                    "sort_order" => $permissionSort,
                ]);
                $permissionSort++;
            }
            $groupSort++;
        }
        $groupBuilder->execute();

        $sql = preg_replace(
            '/(.*)(RETURNING.*)/',
            '$1 ON CONFLICT (name) DO UPDATE SET (sort_order, group_id) = (EXCLUDED.sort_order, EXCLUDED.group_id) $2',
            $permissionBuilder->sql()
        );
        $statement = $permissionBuilder->getConnection()->prepare($sql);
        $permissionBuilder->getValueBinder()->attachTo($statement);
        $statement->execute();
    }

    public function down()
    {
        $this->getQueryBuilder()->update("permission")->set("group_id", null)->execute();
        $this->getQueryBuilder()->delete("permission_group")->execute();
    }

    private function getData(): array
    {
        return [
            "internal-roles" => [
                [
                    "name" => "root",
                    "title" => "Cloud Administrator (CA)",
                ],
                [
                    "name" => "support",
                    "title" => "Support Manager",
                ],
                [
                    "name" => "webapi",
                    "title" => "Web Administrator",
                ],
                [
                    "name" => "partner",
                    "title" => "Partner",
                ],
            ],
            "internal-travel-receipt-mgt-service-roles" => [
                [
                    "name" => "receipt",
                    "title" => "Receipt Processing",
                ],
                [
                    "name" => "service",
                    "title" => "Allowed Services for Receipt Processing",
                ],
            ],
            "customer-roles" => [
                [
                    "name" => "company_unit",
                    "title" => "Company Admin (CoA)",
                ],
                [
                    "name" => "employee",
                    "title" => "Employee Admin",
                ],
                [
                    "name" => "security_settings",
                    "title" => "Security Setting Admin (SSA)",
                ],
                [
                    "name" => "tax_auditor",
                    "title" => "Tax Auditor",
                ],
                [
                    "name" => "invoice",
                    "title" => "Invoice Receiver",
                ],
                [
                    "name" => "payroll",
                    "title" => "Payroll Receiver",
                ],
                [
                    "name" => "bookkeeping_export",
                    "title" => "Travel Cost Export Receiver",
                ],
                [
                    "name" => "stored_data",
                    "title" => "Data Export Receiver",
                ],
                [
                    "name" => "employee_view",
                    "title" => "Employee",
                ],
                [
                    "name" => "api",
                    "title" => "Mobile User",
                ],
                [
                    "name" => "web_shop",
                    "title" => "Web Shop User",
                ],
            ],
        ];
    }
}
