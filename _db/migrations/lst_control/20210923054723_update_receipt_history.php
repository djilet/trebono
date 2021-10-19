<?php

use Phinx\Migration\AbstractMigration;

class UpdateReceiptHistory extends AbstractMigration
{
    public function up()
    {
        if (!$this->IsReleaseEnvironment()) {
            return;
        }

        $this->execute("DELETE FROM receipt_history WHERE value_id = 1156408");
        $this->execute("INSERT INTO receipt_history 
            (value_id, receipt_id, property_name, value, created, user_id) 
        VALUES (1156408, 109697, 'status', 'approved', '2021-07-08 09:27:14', 1735)");
    }

    public function down()
    {
        if (!$this->IsReleaseEnvironment()) {
            return;
        }

        $this->execute("DELETE FROM receipt_history WHERE value_id = 1156408");
        $this->execute("INSERT INTO receipt_history 
            (value_id, receipt_id, property_name, value, created, user_id) 
        VALUES (1156408, 109697, 'status', 'approved', '2021-04-20 09:27:14', 1735)");
    }

    public function IsReleaseEnvironment()
    {
        //k8 environment
        if (getenv("APP_ENV") == "trebono") {
            return true;
        }

        //cloudfoundry environment
        if ($application = getenv("VCAP_APPLICATION")) {
            $application = json_decode($application, true);
            if (isset($application["space_name"]) && $application["space_name"] == "lst-release") {
                return true;
            }
        }

        //fallback server
        return !IsLocalEnvironment() && GetFromConfig("Environment", "env") == "production";
    }
}