<?php

require_once(dirname(__FILE__) . "/../../../include/init.php");

class InvoicePDF extends \Mpdf\Mpdf
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
            'margin_left' => 10,
            'margin_right' => 11,
            'margin_top' => 49,
            'margin_bottom' => 60,
            'margin_header' => 4,
            'tempDir' => INVOICE_TMP_DIR,
        ]);
        $this->PDFA = false;
        $this->PDFAauto = true;
        $this->module = $module;
        //this was needed for PDF/A-3 generation
        //$this->pdf_version = "1.7";

        $this->css = file_get_contents(PROJECT_DIR . "module/" . $this->module . "/template/invoice_pdf_style.css");
        $this->WriteHTML($this->css, 1);

        $this->SetAutoPageBreak(true, 45);
        $this->AddPage();
        $this->SetTopMargin(78);
        $page = new PopupPage($this->module);
        $content = $page->Load("invoice_pdf_header.html");
        $content->SetVar("Module", $this->module);
        $content->LoadFromArray($data);
        $html = $page->Grab($content);
        $this->WriteHTML($html);

        $page = new PopupPage($this->module);
        $content = $data["INVOICE_invoice_type"] == "voucher_invoice"
            ? $page->Load("invoice_voucher_pdf_footer.html")
            : $page->Load("invoice_pdf_footer.html");
        $content->SetVar("Module", $this->module);
        $content->LoadFromArray($data);
        $html = $page->Grab($content);
        $this->SetHTMLFooter($html, null);
    }
}
