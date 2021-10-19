<?php


use Phinx\Migration\AbstractMigration;

class CreateAgreementsHistoryTable extends AbstractMigration
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
        $this->table('agreements_history', ["id" => false, 'primary_key' => ["agreement_id", "version"]])
            ->addColumn("agreement_id", "integer", ["null" => false])
            ->addColumn("version", "integer", ["null" => false])
            ->addColumn("content", "text", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("created_at", "timestamp", ["null" => false])
            ->create();
    }

    public function down()
    {
        $this->table("agreements_history")->drop()->save();
    }
}
