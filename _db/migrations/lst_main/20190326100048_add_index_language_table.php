<?php


use Phinx\Migration\AbstractMigration;

class AddIndexLanguageTable extends AbstractMigration
{
    public function up()
    {
        $this->table('language_variable')
         ->addIndex(['tag_name', 'type', 'module', 'template', 'language_code'], ['unique' => true])
         ->changeColumn("template", "string", ["null" => true, "length" => 60])
         ->save();
    }
    
    public function down()
    {
        $this->table('language_variable')
        ->removeIndex(['tag_name', 'type', 'module', 'template', 'language_code'], ['unique' => true])
        ->changeColumn("template", "string", ["null" => true, "length" => 30])
        ->save();
    }
}
