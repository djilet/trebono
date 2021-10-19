<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class WebApiFields extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit_history")
            ->addColumn("created_from", "text", ["null" => false, "default" => "admin"])
            ->save();

        $this->table("company_unit_contract")
            ->addColumn("start_from", "text", ["null" => false, "default" => "admin"])
            ->addColumn("end_from", "text", ["null" => false, "default" => "admin"])
            ->save();

        $this->table("contact_history")
            ->addColumn("created_from", "text", ["null" => false, "default" => "admin"])
            ->save();

        $this->table("employee_history")
            ->addColumn("created_from", "text", ["null" => false, "default" => "admin"])
            ->save();

        $this->table("user_history")
            ->addColumn("created_from", "text", ["null" => false, "default" => "admin"])
            ->save();

        $this->table("user_permission_history")
            ->addColumn("created_from", "text", ["null" => false, "default" => "admin"])
            ->save();

        $this->table("employee_contract")
            ->addColumn("start_from", "text", ["null" => false, "default" => "admin"])
            ->addColumn("end_from", "text", ["null" => false, "default" => "admin"])
            ->save();

        $this->table("option_value_history")
            ->addColumn("created_from", "text", ["null" => false, "default" => "admin"])
            ->save();
    }

    public function down()
    {
        $this->table("company_unit_history")
            ->removeColumn("created_from")
            ->save();

        $this->table("company_unit_contract")
            ->removeColumn("start_from")
            ->removeColumn("end_from")
            ->save();

        $this->table("contact_history")
            ->removeColumn("created_from")
            ->save();

        $this->table("employee_history")
            ->removeColumn("created_from")
            ->save();

        $this->table("user_history")
            ->removeColumn("created_from")
            ->save();

        $this->table("user_permission_history")
            ->removeColumn("created_from")
            ->save();

        $this->table("employee_contract")
            ->removeColumn("start_from")
            ->removeColumn("end_from")
            ->save();

        $this->table("option_value_history")
            ->removeColumn("created_from")
            ->save();
    }
}
