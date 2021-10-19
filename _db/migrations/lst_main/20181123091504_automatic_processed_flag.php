<?php


use Phinx\Migration\AbstractMigration;

class AutomaticProcessedFlag extends AbstractMigration
{
   public function up(){
       $this->execute("ALTER TABLE receipt ADD COLUMN automatic_processed flag NOT NULL DEFAULT 'N'");
   }

   public function down(){
       $this->execute("ALTER TABLE receipt DROP COLUMN automatic_processed");
   }
}
