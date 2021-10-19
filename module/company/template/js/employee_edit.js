$(document).ready(function() {

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

            if ($value == "employee" || $value == "employee_flex") {
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


    $('#rules').on('click', function () {
        if ($('#rules').prop('checked') && checkUploadFile()) {
            $('#uploadReceiptButton').attr('disabled', false).removeClass('btn-secondary').addClass('btn-primary')
        } else {
            $('#uploadReceiptButton').attr('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
        }
    });

    $('#receipt-file').on('change', function () {
        checkUploadFile();
    })

    function checkUploadFile()
    {
        let file = $('#receipt-file').prop('files')[0];

        if (file === undefined || file === null) {
            $('#forErrors').append(
                '<div class="alert alert-danger alert-no-margin" id="receiptUploadErrors">'
                + emptyFileMessage +
                '</div>'
            );

            setTimeout(function () {
                $('#receiptUploadErrors').remove();
            }, 5000)

            $('#receipt-file').val(null);
            $('#rules').attr('disabled', true).attr('checked', false);
            $('#uploadReceiptButton').attr('disabled', true).removeClass('btn-primary').addClass('btn-secondary');

            return false;
        }

        if (file.type !== 'image/jpeg') {
            $('#forErrors').append(
                '<div class="alert alert-danger alert-no-margin" id="receiptUploadErrors">'
                + invalidFileMessage +
                '</div>'
            );

            setTimeout(function () {
                $('#receiptUploadErrors').remove();
            }, 5000)

            $('#receipt-file').val(null);
            $('#rules').attr('disabled', true).attr('checked', false);
            $('#uploadReceiptButton').attr('disabled', true).removeClass('btn-primary').addClass('btn-secondary');

            return false;
        }

        $('#rules').attr('disabled', false);

        return true;
    }



    $("#uploadReceiptButton").click(function (e) {
        e.preventDefault()
        $('#uploadReceiptButton').attr('disabled', true).removeClass('btn-primary').addClass('btn-secondary');

        const rawData = Object.fromEntries(new FormData(document.getElementById('employee-edit')).entries());

        let fileReader = new FileReader();

        fileReader.onload = function (event) {
            let data = new FormData();
            let wordArray = CryptoJS.enc.Latin1.parse(event.target.result)
            let hash = CryptoJS.SHA256(wordArray).toString();

            data.append('Action', 'UploadReceipt');
            data.append('employee_id', employeeID);
            data.append('selected_group_id', rawData.upload_group_id);
            data.append('accept', rawData.accept);
            data.append('user_id', rawData.user_id);
            data.append('file_image', rawData.receiptFile);
            data.append('hash', hash);
            data.append('is_web_upload', '1');

            $.ajax({
                url: companyAjax,
                enctype: 'multipart/form-data',
                type: 'POST',
                data: data,
                contentType: false,
                processData: false,
                success: function (response) {
                    data = JSON.parse(response)

                    if (data.code === 400) {
                        const err = data.errors.join('<br />')
                        $('#forErrors').append(
                            '<div class="alert alert-danger alert-no-margin" id="receiptUploadErrors">'
                            + err +
                            '</div>'
                        );

                        $('#receipt-file').val(null);
                        $('#rules').attr('checked', false);

                        setTimeout(function () {
                            $('#receiptUploadErrors').remove();
                        }, 5000)
                    }

                    if (data.code === 201) {
                        location.assign(location.href + "&ActiveTab=3")
                    }
                },
                error: function (r) {
                    console.log(r)
                }
            });
        };

        fileReader.readAsBinaryString(rawData.receiptFile);
    })

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

    $('.group-title').each(function(i,elem) {
        if($(this).next().siblings().length == 0) {
            $(this).hide();
        }
    });

    if($('.base-end-date').val() == '') {
        $('.reasons').attr('readonly', true);
        $('.reasons').attr('disabled', true);
        $('.reasons').val("");
    } else {
        $('.reasons').attr('readonly', false);
        $('.reasons').attr('disabled', false);
    }

    $('.base-end-date').change(function() {
        if($(this).val() != '') {
            $('.reasons').attr('readonly', false);
            $('.reasons').attr('disabled', false);
        } else {
            $('.reasons').attr('readonly', true);
            $('.reasons').attr('disabled', true);
            $('.reasons').val("");
        }
    });

    if (isEmployee == 'Y')
    {
        workingDays = $("[name='working_days_per_week'] :selected").val();
        $("[name='working_days_per_week']").attr('disabled', true);
        $("[name='working_days_per_week']").after("<input type='hidden' name='working_days_per_week' value='"+workingDays+"''>");

        salutation = $("[name='salutation'] :selected").val();
        $("[name='salutation']").attr('disabled', true);
        $("[name='salutation']").after("<input type='hidden' name='salutation' value='"+salutation+"''>");

        maritalStatus = $("[name='material_status'] :selected").val();
        $("[name='material_status']").attr('disabled', true);
        $("[name='material_status']").after("<input type='hidden' name='material_status' value='"+maritalStatus+"''>");

        companyUnitID = $("[name='company_unit_id'] :selected").val();
        $("[name='company_unit_id']").attr('disabled', true);
        $("[name='company_unit_id']").after("<input type='hidden' name='company_unit_id' value='"+companyUnitID+"''>");

        $(':text').attr('readonly',true);
        $(':text').removeClass('datepicker');

        $('#select-interface-language').attr('disabled', false);
        $('[type="checkbox"]').attr('disabled', true);

        $('.bank-data').attr('readonly', false);
        $('#phone').attr('readonly', false);
        $('#password1').attr('readonly', false);
        $('#password2').attr('readonly', false);
        $('#select-interface-language').attr('readonly', false);
        $('[name="monthly_statistics_date"]').attr('readonly', false);
        $('[name="yearly_statistics_date"]').attr('readonly', false);
        $('[name="phone"]').attr('readonly', false);

        $('[for="mobile__main__mobile_model"]').parent().next().next().attr('readonly', false);
        $('[for="mobile__main__mobile_number"]').parent().next().next().attr('readonly', false);
    }

    if (isEmployeeAdmin == 'Y')
    {
        $("[for='food__main__units_per_week']").parent().parent().hide();
    }
    if (isEmployeeAdmin == 'Y' && isEmployeeSelf != 'Y')
    {
        $('td:nth-child(7),th:nth-child(7)').hide();
        $('td:nth-child(8),th:nth-child(8)').hide();
    }


    $('.option-value-enable').click(function(e){
        var flag_name = $(this).attr('flag_name');
        var inputs = $("input[name='"+flag_name+"']");
        inputs.each(function(){
            if ($(this).attr("type")=="hidden")
                this.remove();
            else
                $(this).prop("disabled", false);
        });
        e.preventDefault();
    });

    if(employeeID)
    {
        $(".date-of-params a").on("click",function(e){
        e.preventDefault();
        $(this).next("input").datepicker("show");
    });
        $(".date-of-params input").on("changeDate", function(){
        $(this).closest("div").find(".date-of-param").text($(this).val());
    });
        $('.option-value-history').click(function(e){
        var optionID = $(this).attr('option_id');
        $.ajax({
        url: productAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
        Action: 'GetOptionValueHistoryEmployeeHTML',
        option_id: optionID,
        employee_id: employeeID
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
        $('.contract-history').click(function(e){
        var productID = $(this).attr('product_id');
        $.ajax({
        url: productAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
        Action: 'GetContractHistoryEmployeeHTML',
        product_id: productID,
        employee_id: employeeID
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
        Action: 'GetContractPropertyHistoryEmployeeHTML',
        product_id: productID,
        employee_id: employeeID,
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
        var propertyNameTranslation = $("label[for='" + propertyName + "']").first().text();
        $.ajax({
        url: companyAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
        Action: 'GetPropertyHistoryEmployeeHTML',
        property_name: propertyName,
        employee_id: employeeID,
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
        $('#statistics-show').click(function(e){
        $('#statistics-container').html('<div class="text-center"><i class="fa fa-spinner fa-pulse icon-md"></i></div>');
        var monthlyStatisticsDate = $('input[name=monthly_statistics_date]').val();
        $.ajax({
        url: statisticsAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
        Action: 'GetStatisticsHTML',
        employee_id: employeeID,
        monthly_statistics_date: monthlyStatisticsDate,
        is_employee_admin: isEmployeeAdmin,
        is_employee_self: isEmployeeSelf
    },
        success: function(data){
        if(typeof data.HTML != 'undefined'){
        $('#statistics-container').html(data.HTML);
    }
    }
    });
        e.preventDefault();
    });
        $('#statistics-show').trigger('click');
        $.ajax({
        url: companyAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
        Action: 'GetEmployeeUnitMap',
        employee_id: employeeID,
        month: current_yyyymm_,
        is_employee_admin: isEmployeeAdmin
    },
        success: function(data){
        unitMap = data;
        calendar = $("#pb-calendar").pb_calendar({
        schedule_list : function(callback_, yyyymm_){
        var temp_schedule_list_ = {};
        unitMap.forEach(function(element) {
        date = moment(element["date"]).format("YYYYMMDD");
        element["used"] = parseFloat(element["used"]);
        element["unit"] = parseFloat(element["unit"]);
        element["unit_voucher"] = parseFloat(element["unit_voucher"]);
        element["used_voucher"] = parseFloat(element["used_voucher"]);
        if(element["used"] >= element["unit"] && element["unit"] > 0 ||
        element["used_voucher"] >= element["unit_voucher"] && element["unit_voucher"] > 0){
        temp_schedule_list_[date] = [
    {'ID' : 1, style : "lachs"}
        ];
    }
        else if(element["used"] > 0 && element["unit"] > 0 ||
        element["used_voucher"] > 0 && element["unit_voucher"] > 0){
        temp_schedule_list_[date] = [
    {'ID' : 2, style : "sannenblum"}
        ];
    }
        else{
        temp_schedule_list_[date] = [
    {'ID' : 2, style : "azur"}
        ];
    }
    });
        callback_(temp_schedule_list_)
    },
        schedule_dot_item_render : function(dot_item_el_, schedule_data_, schedule_el_){
        schedule_el_.addClass(schedule_data_['style'], true);
        //dot_item_el_.addClass(schedule_data_['style'], true);
        return dot_item_el_;
    },
        callback_changed_month : LoadMonthCalendar,
        callback_selected_day : ShowCalendarPopup,
        day_selectable : true,
        next_month_button : "<img src='"+path2main+"plugins/pbcalendar/img/arrow-next.png' class='icon'>",
        prev_month_button : "<img src='"+path2main+"plugins/pbcalendar/img/arrow-prev.png' class='icon'>"
    });
    }
    });
        $('.voucher-receipts').click(function(e){
        var voucherID = $(this).attr('voucher_id');
        $.ajax({
        url: companyAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
        Action: 'GetVoucherReceipts',
        voucher_id: voucherID
    },
        success: function(data){
        if(typeof data.HTML != 'undefined'){
        $(data.HTML).modal('show').on('hidden.bs.modal', function () {
        $(this).remove();
    });
    }
    }
    });
        e.stopImmediatePropagation();
        e.preventDefault();
    });

        $("[name='yearly_statistics_date']").each(function() {
        $(this).datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years"
        }).datepicker('setDate', new Date());
    });
        yearlyStatisticsDate =  $("[name='yearly_statistics_date']").first().val();
        $(".statistics-container").each(function() {
        $(this).html('<div class="text-center"><i class="fa fa-spinner fa-pulse icon-md"></i></div>');
        productGroupID = $(this).attr("group-id");
        FetchVoucherStatistics(yearlyStatisticsDate, productGroupID);
    });

        $(".statistics-show").click(function(e){
        productGroupID = $(this).attr("group-id");
        yearlyStatisticsDate = $("[name='yearly_statistics_date'][group-id="+productGroupID+"]").val();
        $(".statistics-container[group-id="+productGroupID+"]").html('<div class="text-center"><i class="fa fa-spinner fa-pulse icon-md"></i></div>');
        FetchVoucherStatistics(yearlyStatisticsDate, productGroupID);
    });
    }

    $(document).on('click','a.confirm-remove',function(e){
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

    $("[data-toggle='tab']").click(function(e) {
        document.body.scrollTop = document.documentElement.scrollTop = 0;
    });

});

var calendar;
var date;
var current_yyyymm_ = moment().format("YYYYMM");
var unitMap;
function LoadMonthCalendar(monthTo, monthFrom){
    $.ajax({
        url: companyAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
            Action: 'GetEmployeeUnitMap',
            employee_id: employeeID,
            month: monthTo,
            is_employee_admin: isEmployeeAdmin
        },
        success: function(data){
            unitMap = data;
            calendar.update_view();
        }
    });

}

function ShowCalendarPopup(date){
    $.ajax({
        url: companyAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
            Action: 'GetEmployeeReceiptListForDate',
            employee_id: employeeID,
            company_unit_id: companyUnitID,
            date: date,
            is_employee_admin: isEmployeeAdmin,
            is_employee_self: isEmployeeSelf
        },
        success: function(data){
            $(data.HTML).modal('show').on('hidden.bs.modal', function () {
                $(this).remove();
            });
        }
    });
}

function FetchVoucherStatistics(yearlyStatisticsDate, productGroupID) {
    $.ajax({
        url: companyAjax,
        type: 'GET',
        dataType: 'JSON',
        data:{
            Action: 'GetVoucherStatisticsHTML',
            company_unit_id: companyUnitID,
            yearly_statistics_date: yearlyStatisticsDate,
            product_group_id: productGroupID,
            employee_id: employeeID
        },
        success: function(data){
            if (typeof data.HTML != 'undefined') {
                $(".statistics-container[group-id="+data.ProductGroupID+"]").html(data.HTML);
            }
        }
    });
}