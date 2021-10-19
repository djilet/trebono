$(document).ready(function(){

    CreateFileInput('contract_file', '', '');

    $('.jtoggler').jtoggler();

    $('.jtoggler').each(function() {
        if ($(this).attr('value') == 'Y') {
            $(this).next().addClass('is-fully-active');
            $(this).next().children().eq(2).addClass('is-active');
        } else if ($(this).attr('value') == 'N') {
            $(this).next().addClass('is-fully-disabled');
            $(this).next().children().eq(0).addClass('is-active');
        } else {
            $(this).next().children().eq(1).addClass('is-active');
        }
    });

    $('[data-preference]').change(function() {
        $preferenceSelectID = $(this).attr('data-preference').length > 0
            ? $(this).attr('data-preference')
            : 0;
        if ($preferenceSelectID > 0) {
            $preferenceProductID = $(this).attr('data-preference_product_id').length > 0
                ? $(this).attr('data-preference_product_id')
                : 0;
            $optionName = "Product[" + $preferenceProductID + "][Option][" + $preferenceSelectID + "]";

            $preferenceSelect = $("select[name='" + $optionName + "']");
            $preferenceSelected = $("select[name='" + $optionName + "'] :selected").val();
            $preferenceInput = $("input[name='" + $optionName + "']");

            $value = $(this).val().length > 0
                ? $(this).val()
                : $("input[name='" + $optionName + "[inherited_value]" + "']").val();

            if ($value != "exchangeable") {
                $preferenceSelect.attr('disabled', false);
                $preferenceInput.remove();
            } else {
                $preferenceSelect.attr('disabled', true);
                if ($preferenceInput.length == 0) {
                    $preferenceSelect.after("<input type='hidden' name='" + $optionName +"'" +
                        " value='"+$preferenceSelected+"''>");
                }
            }
        }
    });

    $(document).on('jt:toggled:multi', function (event, target) {
        index = $(target).parent().index();

        if (index == 2) {
            $(target).parent().parent().prev().attr('value', 'Y');
        } else if (index == 0) {
            $(target).parent().parent().prev().attr('value', 'N');
        } else {
            $(target).parent().parent().prev().attr('value', '');
        }
    });

    if (isCompanyUnitAdmin == 'Y')
    {
        $("[name='sepa_service']").attr('readonly', 'readonly');
        $("[name='sepa_voucher']").attr('readonly', 'readonly');
        $("[name='sepa_service_date']").attr('readonly','readonly');
        $("[name='sepa_voucher_date']").attr('readonly','readonly');
        $("[name='sepa_service_date']").removeClass('datepicker');
        $("[name='sepa_voucher_date']").removeClass('datepicker');
        $("[name='bank_details']").attr('readonly','readonly');
        $("[name='iban']").attr('readonly','readonly');
        $("[name='bic']").attr('readonly','readonly');
    }

    if (isContractUser == 'Y') {
        $('input, select, textarea').not('[name=yearly_statistics_date]').attr('disabled','disabled');
        $('.btn, .confirm-remove').not('.statistics-show').remove();
    }

    $('.option-value-history').click(function(e){
        var optionID = $(this).attr('option_id');

        $.ajax({
            url: productAjax,
            type: 'GET',
            dataType: 'JSON',
            data:{
                Action: 'GetOptionValueHistoryCompanyUnitHTML',
                option_id: optionID,
                company_unit_id: companyUnitID
            },
            success: function(data){
                if(typeof data.HTML != 'undefined'){
                    $(data.HTML).modal('show').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                }
            }
        });

        e.preventDefault();
    });
    $(".date-of-params a").on("click",function(e){
        e.preventDefault();
        $(this).next("input").datepicker("show");
    });
    $(".date-of-params input").on("changeDate", function(){
        $(this).closest("div").find(".date-of-param").text($(this).val());
    });
    $('.contract-history').click(function(e){
        var productID = $(this).attr('product_id');

        $.ajax({
            url: productAjax,
            type: 'GET',
            dataType: 'JSON',
            data:{
                Action: 'GetContractHistoryCompanyUnitHTML',
                product_id: productID,
                company_unit_id: companyUnitID
            },
            success: function(data){
                if(typeof data.HTML != 'undefined'){
                    $(data.HTML).modal('show').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                }
            }
        });

        e.preventDefault();
    });
    $('.contract-property-history').click(function(e){
        var productID = $(this).attr('product_id');
        var propertyName = $(this).attr('property_name');
        var propertyNameTranslation = $("#tab-2 label[for='" + propertyName + "']").first().text();

        $.ajax({
            url: productAjax,
            type: 'GET',
            dataType: 'JSON',
            data:{
                Action: 'GetContractPropertyHistoryCompanyUnitHTML',
                product_id: productID,
                company_unit_id: companyUnitID,
                property_name: propertyName,
                property_name_translation: propertyNameTranslation
            },
            success: function(data){
                if(typeof data.HTML != 'undefined'){
                    $(data.HTML).modal('show').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                }
            }
        });

        e.preventDefault();
    });

    $('.property-history').click(function(e){
        var propertyName = $(this).attr('property_name');
        var documentID = $(this).attr('document_id');
        var propertyNameTranslation = $("label[for='" + propertyName + "']").text();

        $.ajax({
            url: companyAjax,
            type: 'GET',
            dataType: 'JSON',
            data:{
                Action: 'GetPropertyHistoryCompanyUnitHTML',
                property_name: propertyName,
                company_unit_id: companyUnitID,
                document_id: documentID,
                property_name_translation: propertyNameTranslation
            },
            success: function(data){
                if(typeof data.HTML != 'undefined'){
                    $(data.HTML).modal('show').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                }
            }
        });

        e.preventDefault();
    });

    $('.report-property-history').click(function(e){
        var reportID = $(this).attr('report_id');
        var propertyName = $(this).attr('property_name');

        $.ajax({
            url: companyAjax,
            type: 'GET',
            dataType: 'JSON',
            data:{
                Action: 'GetReportPropertyHistoryCompanyUnitHTML',
                report_id: reportID,
                company_unit_id: companyUnitID,
                property_name: propertyName,
            },
            success: function(data){
                if(typeof data.HTML != 'undefined'){
                    $(data.HTML).modal('show').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                }
            }
        });

        e.preventDefault();
    });

    $('#export-datev-submit').click(function(){
        var params = {'DateFrom': $('#export-datev-date-from').val(), 'DateTo': $('#export-datev-date-to').val()};
        $(this).attr('href', $(this).attr('href')+'&'+$.param(params));
    });

    $("#invoice-preview-after").click(function (e) {
        $("[name='invoice_for_period_after']").val(1);
    });
    $("#invoice-preview-before").click(function (e) {
        $("[name='invoice_for_period_after']").val(0);
    });

    $(".invoice-preview").click(function (e) {
        e.preventDefault();
        $("[name='Save']").val(0);
        $("[name='Do']").val("InvoicePreview");

        if (!$("[name='invoice_preview_date']").val())
            ModalAlert(errorMessage, invoicePreviewEmptyDate);
        else
            $("form").submit();
    });

    $("#invoice-voucher-preview-after").click(function (e) {
        $("[name='invoice_voucher_for_period_after']").val(1);
    });
    $("#invoice-voucher-preview-before").click(function (e) {
        $("[name='invoice_voucher_for_period_after']").val(0);
    });

    $(".invoice-voucher-preview").click(function (e) {
        e.preventDefault();
        $("[name='Save']").val(0);
        $("[name='Do']").val("InvoiceVoucherPreview");

        if (!$("[name='invoice_voucher_preview_date']").val())
            ModalAlert(errorMessage, invoiceVoucherPreviewEmptyDate);
        else
            $("form").submit();
    });

    $('a.confirm-remove').click(function(e){
        var a = $(this);
        var title = a.attr('title');
        var msg = removeMessage.replace(/%Title%/g, title);
        ModalConfirm(msg, function(){
            window.location.href = a.attr('href');
        });
        e.preventDefault();
    });
    $(document).on('click','a.confirm-activate',function(e){
        var a = $(this);
        var title = a.attr('title');
        var msg = activateMessage.replace(/%Title%/g, title);
        ModalConfirm(msg, function(){
            window.location.href = a.attr('href');
        });
        e.preventDefault();
    });

    $(".reset-payroll").click(function (e) {
        e.preventDefault();
        $("[name='Save']").val(0);
        $("[name='Do']").val("ResetPayroll");

        if (!$("[name='reset_payroll_date']").val())
            ModalAlert(errorMessage, payrollResetEmptyDate);
        else
            $("form").submit();
    });

    $("[name='yearly_report_date']").datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years"
        }).datepicker('setDate', new Date());

    $(".yearly-report").click(function (e) {
        e.preventDefault();
        $("[name='Save']").val(0);
        $("[name='Do']").val("YearlyReport");
        $("form").submit();
    });

    $("[data-toggle='tab']").click(function(e) {
        document.body.scrollTop = document.documentElement.scrollTop = 0;
    });

    $("#save").click(function (e) {
        $("[name='Save']").val(1);
        $("[name='Do']").val("Save");
    });
});