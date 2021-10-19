<?php


use Phinx\Migration\AbstractMigration;

class CreateTextReceiptApprovedLessThanUnit extends AbstractMigration
{
    public function up(){
        $query = "INSERT INTO config (code, value, group_code, editor, updated)
                    VALUES('message_for_approved_less_than_unit', 'Guten Tag %salutation% %first_name% %last_name%, bitte beachten Sie, dass der Beleg nicht den vollen Wert einer Essensmarke enthält. Sie können noch einen Beleg desselben Datums einreichen, um diese Essensmarke zu vervollständigen. Wenn sie diesen Beleg nicht nutzen möchten oder später durch einen anderen Beleg ersetzen möchten, bestätigen Sie ihn bitte nicht. Viele Grüße, Ihr FINEasy Team.',
                           'p_push', 'plain', '".GetCurrentDateTime()."')";
        
        $this->execute($query);
    }
    
    public function down(){
        $query = "DELETE FROM config WHERE code IN ('message_for_approved_less_than_unit')";
        $this->execute($query);
    }
}