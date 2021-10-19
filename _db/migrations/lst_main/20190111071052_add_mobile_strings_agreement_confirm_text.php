<?php


use Phinx\Migration\AbstractMigration;

class AddMobileStringsAgreementConfirmText extends AbstractMigration
{
    private $code = 'mobile_app_employment_agreement_text_after_confirm';
    
    public function up()
    {
        $row = [
            'code'    => $this->code,
            'value'  => 'Vielen Dank für Ihre Bestätigung der Ergänzenden Vereinbarung. Ihre Personalbereich hat nun eine E-Mail mit einem PDF Ihrer Bestätigung bekommen und wird Ihnen in den nächsten Tagen ein unterschriebenes Exemplar zusenden. Wir wünschen Ihn nun viel Spass und Erfolg mit unserem neuen digitalen Service. Ihr FIN-easy Team',
            'group_code'  => 'm_mobile_app',
            'editor'  => 'plain',
            'updated'  => '2019-01-11 00:00:00',
        ];

        $table = $this->table('config');
        $table->insert($row);
        $table->saveData();
    }

    public function down()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->delete('config')
            ->where(['code' => $this->code])
            ->execute();
    }
}
