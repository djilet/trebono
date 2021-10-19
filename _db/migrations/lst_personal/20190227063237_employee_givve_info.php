<?php


use Phinx\Migration\AbstractMigration;

class EmployeeGivveInfo extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $this->table("employee")
            ->addColumn("givve_login", "string", ["length" => 255, "null" => true])
            ->addColumn("givve_password", "string", ["length" => 255, "null" => true])
            ->addColumn("givve_access_token", "string", ["length" => 255, "null" => true])
            ->addColumn("givve_refresh_token", "string", ["length" => 255, "null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->removeColumn("givve_login")
            ->removeColumn("givve_password")
            ->removeColumn("givve_access_token")
            ->removeColumn("givve_refresh_token")
            ->save();
    }
}