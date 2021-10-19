<?php


use Phinx\Migration\AbstractMigration;

class CreateAgreementsTable extends AbstractMigration
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
        $this->table("agreements", ["id" => "agreement_id"])
            ->addColumn("group_id", "integer", ["null" => false])
            ->addColumn("organization_id", "integer", ["null" => false])
            ->addColumn("content", "text", ["null" => false])
            ->addColumn("version", "integer", ["null" => false, 'default' => 1])
            ->addColumn("updated_at", "timestamp", ["null" => false])
            ->addIndex('group_id', ['unique' => false])
            ->addIndex('organization_id', ['unique' => false])
            ->create();
        
        $this->table("agreements_employee", ["id" => false, 'primary_key' => ["agreement_id", "employee_id", "device_id"]])
            ->addColumn("agreement_id", "integer", ["null" => false])
            ->addColumn("employee_id", "integer", ["null" => false])
            ->addColumn("device_id", "string", ["length" => 50, "null" => false])
            ->addColumn("version", "integer", ["null" => false])
            ->addColumn("device_info", "string", ["length" => 255, "null" => false])
            ->addColumn("updated_at", "timestamp", ["null" => false])
            ->create();
    }


    public function down()
    {
        $this->table("agreements")->drop()->save();
        $this->table("agreements_employee")->drop()->save();
    }
}
