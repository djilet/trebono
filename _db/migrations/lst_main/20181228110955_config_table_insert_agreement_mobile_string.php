<?php


use Phinx\Migration\AbstractMigration;

class ConfigTableInsertAgreementMobileString extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
            VALUES ( 
                'mobile_app_employment_agreement_confirm_button',
                'Ichtimme den Bedingungen zu',
                'm_mobile_app',
                'plain',
                '2018-12-28 00:00:00'
            )"
        );
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code = 'mobile_app_employment_agreement_confirm_button'");
    }
}
