<?php


use Phinx\Migration\AbstractMigration;

class CreateAppVersionHistory extends AbstractMigration
{
    public function up()
    {
        $this->table("app_version_history", ["id" => "value_id"])
            ->addColumn("app_version_id", "integer", ["null" => false])
            ->addColumn("property_name", "string", ["null" => false, "length" => 255])
            ->addColumn("value", "string", ["null" => false, "length" => 255])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->save();

        $stmt = GetStatement(DB_MAIN);
        $query = "ALTER TABLE app_version ADD COLUMN created timestamp NOT NULL DEFAULT ".Connection::GetSQLString(GetCurrentDateTime()).",
                    ADD COLUMN created_by integer NOT NULL DEFAULT ".SERVICE_USER_ID.",
                    ADD COLUMN archive flag NOT NULL DEFAULT 'N'";
        $stmt->Execute($query);
    }

    public function down()
    {
        $this->execute("DROP TABLE app_version_history");

        $stmt = GetStatement(DB_MAIN);
        $query = "ALTER TABLE app_version DROP COLUMN created, DROP COLUMN created_by, DROP COLUMN archive";
        $stmt->Execute($query);
    }
}