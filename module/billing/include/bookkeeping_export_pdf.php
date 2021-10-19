<?php

require_once(dirname(__FILE__) . "/../../../include/init.php");

class BookkeepingExportPDF extends \Mpdf\Mpdf
{
    var $module;
    var $css;

    public function __construct($module, $data)
    {
        parent::__construct([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font_size' => 12,
            'default_font' => 'dejavusans',
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 60,
            'margin_bottom' => 60,
            'margin_header' => 0,
            'tempDir' => BOOKKEEPING_EXPORT_TMP_DIR,
        ]);
        $this->PDFA = false;
        $this->PDFAauto = true;
        $this->module = $module;

        $this->css = file_get_contents(PROJECT_DIR . "module/" . $this->module . "/template/bookkeeping_export_pdf_style.css");
        $this->WriteHTML($this->css, 1);

        $this->SetAutoPageBreak(true, 45);
        $this->AddPage();
        $this->SetTopMargin(78);
        $page = new PopupPage($this->module);
        $content = $page->Load("bookkeeping_export_pdf_header.html");
        $content->SetVar("Module", $this->module);
        $content->LoadFromArray($data);
        $html = $page->Grab($content);
        $this->WriteHTML($html);

        $page = new PopupPage($this->module);
        $content = $page->Load("bookkeeping_export_pdf_footer.html");
        $content->SetVar("Module", $this->module);
        $content->LoadFromArray($data);
        $html = $page->Grab($content);
        $this->SetHTMLFooter($html, null);
    }
}
