<?php
define('IS_ADMIN', true);
require_once(dirname(__FILE__) . "/../../../include/init.php");

$language = GetLanguage();
$request = new LocalObject(array_merge($_GET, $_POST));
$translation = $request->GetProperty('Module')
    ? $language->LoadForJS($request->GetProperty('Module'))
    : $language->LoadForJS();
$cookieExpire = COOKIE_EXPIRE * 30;
?>

//global vars
PROJECT_PATH = '<?php echo PROJECT_PATH; ?>';
ADMIN_PATH = '<?php echo ADMIN_PATH; ?>';
PATH2MAIN = ADMIN_PATH+'template/';
var cookieExpires = <?php echo COOKIE_EXPIRE;?>*30;
INTERFACE_LANGCODE = $.cookie("ILangCode");
function GetTranslation(key)
{
    switch (key)
    {
<?php
foreach ($translation as $key => $value) {
    ?>
        case "<?php echo $key; ?>": return "<?php echo htmlspecialchars(addcslashes($value["Value"], "\r\n'\\")); ?>";
    <?php
}
?>
        default: return key;
    }
}

$(document).ready(function(){
    $('#select-interface-language').change(function(){
        changeILang($(this).val());
    });
    $('.check-all').on('ifToggled', function(e){
        checkAll(this, $(this).attr('InputName'));
    });
    $('a.confirm').click(function(e){
        var a = $(this);
        var title = a.attr('title') + "?";
        ModalConfirm(title, function(){
            window.location.href = a.attr('href');
        });
        e.preventDefault();
    });
    $('a.multiple-remove').click(function(e){
        var a = $(this);
        var form = $(this).closest('form');
        var elname = a.attr('elname');
        if(form.find('input[type=checkbox][name="'+elname+'"]:checked').size() > 0)
        {
            ModalConfirm(GetTranslation('remove-multiple-confirm'), function(){
                form.find('input[name=Action]').val('Remove');
                form.submit();
            });
        }
        else
        {
            alert(GetTranslation('remove-multiple-no-selection'));
        }
        e.preventDefault();
    });
    $('form').each(function(){
        $(this).on('keydown', 'input, select', function(e) {
            var self = $(this), 
                form = self.parents('form:eq(0)'),
                focusable,
                next;
                
            if (e.keyCode == 13) {
                focusable = form.find('input,select,button,textarea').filter(':visible');
                next = focusable.eq(focusable.index(this)+1);
                if (next.length) {
                    next.focus();
                } else {
                    form.submit();
                }
                return false;
            }
        });
    });
    
    $('.variable-edit-header').on('click', function(e){ 
    
        var checkbox = $(this);
        checkbox.disable;
        var value = $(this).get(0).checked;
        
        $.ajax({
            url: ADMIN_PATH+'ajax.php',
            method: 'POST',
            dataType: 'JSON',
            data: {
                    'Action': 'SetVariableEdit',
                    'Value': value ? 1 : 0
                },
            success:function(data){
                checkbox.enable;
                location.reload()
            },
            error:function(){
                
            }
        });
    });
    
    $('.update-variables').on('click', function(e){ 
    
        message = CreateStaticTopMessage(GetTranslation('updating-variables'), 'info');
        
        $.ajax({
            url: ADMIN_PATH+'ajax.php',
            method: 'POST',
            dataType: 'JSON',
            data: {
                    'Action': 'UpdateVariables'
                },
            success:function(data){
            
                if(data !== false) {
                    UpdateMessage(message, GetTranslation('variables-updated').replace(/%Count%/g, data), 'info');
                } else {
                    UpdateMessage(message, GetTranslation('error-updating-variables'), 'error');
                }
                setTimeout('HideMessage(message)', 3000);
            },
            error:function(){
                UpdateMessage(message, GetTranslation('error-updating-variables'), 'error');
                setTimeout('HideMessage(message)', 3000);
            }
        });
    });
    
    $('label[table][for]').each(function() {    

        tag = "help_var-" + $(this).attr("table") + "-" + $(this).attr("for")
        translation = GetTranslation(tag)
        
        if((translation != "" && translation != tag) || editVariables)
        {
            var icon = document.createElement("i");
            icon.className = "fa fa-question-circle"
            
            if(translation != "" && translation != tag && !editVariables)
            {
                var helpElement = document.createElement("span")
            } else {
                var helpElement = document.createElement("a")
            }
            
            helpElement.textContent = " ";
            helpElement.appendChild(icon);
            helpElement.setAttribute("href", "#")
            helpElement.setAttribute("tag_name", tag)
            helpElement.className = "help-tooltip variable-help"
            helpElement.setAttribute("rel", "tooltip")
            helpElement.setAttribute("data-color-class", "primary")
            helpElement.setAttribute("data-animate", "false")
            helpElement.setAttribute("data-toggle", "tooltip")
            
            var node = $(this).get(0)
            var parentNode = node.parentNode;
            
            if(translation != "" && translation != tag)
            {
                helpElement.setAttribute("data-original-title", translation)
                parentNode.insertBefore(helpElement, node.nextSibling);
            } else if (editVariables) {
                parentNode.insertBefore(helpElement, node.nextSibling);
            }
        }       
    });
    
    $('header[table][for]').each(function() {   

        tag = "help_var-" + $(this).attr("table") + "-" + $(this).attr("for")
        translation = GetTranslation(tag)
        if((translation != "" && translation != tag) || editVariables)
        {
            var icon = document.createElement("i");
            icon.className = "fa fa-question-circle"

            if(translation != "" && translation != tag)
            {
                var helpElement = document.createElement("span")
            } else {
                var helpElement = document.createElement("a")
            }
            
            helpElement.textContent = " ";
            helpElement.appendChild(icon);
            helpElement.setAttribute("href", "#")
            helpElement.setAttribute("tag_name", tag)
            helpElement.className = "help-tooltip variable-help-header"
            helpElement.setAttribute("rel", "tooltip")
            helpElement.setAttribute("data-color-class", "primary")
            helpElement.setAttribute("data-animate", "false")
            helpElement.setAttribute("data-toggle", "tooltip")
            helpElement.setAttribute("data-container", "body")
            
            var node = $(this).get(0)
            var parentNode = node.parentNode;
            //console.log(node)
            if(translation != "" && translation != tag)
            {
                helpElement.setAttribute("data-original-title", translation)
                node.appendChild(helpElement);
            } else if (editVariables) {
                node.appendChild(helpElement);
            }
        }       
    });
    
    $('a[table][for]').each(function() {    

        tag = "help_var-" + $(this).attr("table") + "-" + $(this).attr("for")
        translation = GetTranslation(tag)
        if((translation != "" && translation != tag) || editVariables)
        {
            var icon = document.createElement("i");
            icon.className = "fa fa-question-circle"
            
            if(translation != "" && translation != tag)
            {
                var helpElement = document.createElement("span")
            } else {
                var helpElement = document.createElement("a")
            }
            
            helpElement.textContent = " ";
            helpElement.appendChild(icon);
            helpElement.setAttribute("href", "#")
            helpElement.setAttribute("tag_name", tag)
            helpElement.className = "help-tooltip variable-help-tab"
            helpElement.setAttribute("rel", "tooltip")
            helpElement.setAttribute("data-color-class", "primary")
            helpElement.setAttribute("data-animate", "false")
            helpElement.setAttribute("data-toggle", "tooltip")
            helpElement.setAttribute("data-container", "body")
            
            var node = $(this).get(0)
            var parentNode = node.parentNode;
            //console.log(node)
            if(translation != "" && translation != tag)
            {
                helpElement.setAttribute("data-original-title", translation)
                node.appendChild(helpElement);
            } else if (editVariables) {
                node.appendChild(helpElement);
            }
        }       
    });

    $("label[show-additional-help='1']").each(function() {
        tag = "additional_help_var"
        translation = GetTranslation(tag)

        if((translation != "" && translation != tag) || editVariables)
        {
            var icon = document.createElement("i");
            icon.className = "fa fa-exclamation-circle"

            if(translation != "" && translation != tag && !editVariables)
            {
                var helpElement = document.createElement("span")
            } else {
                var helpElement = document.createElement("a")
            }

            helpElement.textContent = " ";
            helpElement.appendChild(icon);
            helpElement.setAttribute("href", "#")
            helpElement.setAttribute("tag_name", tag)
            helpElement.className = "help-tooltip variable-help"
            helpElement.setAttribute("rel", "tooltip")
            helpElement.setAttribute("data-color-class", "warning")
            helpElement.setAttribute("data-animate", "false")
            helpElement.setAttribute("data-toggle", "tooltip")

            var insertAfter = $(this)
            if ($(this).siblings(".variable-help:last").length > 0) {
                insertAfter =  $(this).siblings(".variable-help:last")
            }

            if(translation != "" && translation != tag)
            {
                helpElement.setAttribute("data-original-title", translation)
                insertAfter.after(helpElement)
            } else if (editVariables) {
                insertAfter.after(helpElement)
            }
        }
    });
    
    $('button[type=submit]').on('click', function(e){
        var href = $('li.active').find('[data-toggle=tab]').attr('href');
        var regExpID = new RegExp("[0-9]", "g");
        var tabID = regExpID.exec(href)
        if(tabID)
        {
            tabID = tabID[0];
            $('[name=ActiveTab]').val(tabID);
        }
        
        var scrollTop = window.scrollY;
        $('[name=ScrollTop]').val(scrollTop);
    });
    
    if(activeTab > 0) {
        $('li').find('[data-toggle=tab]').each(function() {     
            href = $(this).attr('href');
            if (href == '#tab-'+activeTab)
                $(this).click();
        });
    }

    $('a.actions.box_toggle').on('click', function() {
        var clickedElement = $(this);

        $('a.actions.box_toggle').each(function(){
            var arrow = $(this).children('div').find('i');

            if((clickedElement.is($(this)) || $(this).hasClass('open')) && arrow.hasClass("fa-chevron-up")) {
                arrow.removeClass("fa-chevron-up").addClass("fa-chevron-down");
            }
            else if((clickedElement.is($(this)) || !$(this).hasClass('open')) && arrow.hasClass("fa-chevron-down")) {
                arrow.removeClass("fa-chevron-down").addClass("fa-chevron-up");
            }
        });
     });
});

window.onload = function() {
    if(scrollTop > 0) {
        window.scroll(0, scrollTop)
    }
}

function changeILang(languageCode)
{
    $.cookie("ILangCode", escape(languageCode), {expire: <?php echo $cookieExpire; ?>, path: "<?php echo PROJECT_PATH; ?>"});
    window.location.href = window.location.href;
}

function createCKEditor(name, toolbarSet, width, height, params)
{
    var toolbars = {basic: [ [ 'Source'], [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-','RemoveFormat'], [ 'NumberedList','BulletedList']], 
                    standart: [['Source','-','Maximize','-','Templates' ], [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ],[ 'Find','Replace','-','SelectAll'], [ 'Link','Unlink','Anchor' ], [ 'Image','Flash','Table','HorizontalRule','SpecialChar','Iframe' ], '/',
                            ['Format', '-', 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ], [ 'NumberedList','BulletedList','-','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                            ['TextColor', 'BGColor']
                    ]
    };  
    cfg = new Object({
        contentsCss : [ '<?php echo PROJECT_PATH; ?>website/<?php echo WEBSITE_FOLDER; ?>/fckconfig/fck_editorarea.css'],
        width: width || '100%',
        height : height || '400px',
        toolbar: toolbars[toolbarSet] ? toolbars[toolbarSet] : toolbars['standart'] 
    });
    if(typeof params == 'object')
    {
        for (var attrname in params) { cfg[attrname] = params[attrname]; }
    }
    var editor = CKEDITOR.replace(name, cfg);
    
    // Add FileManager to CKEditor
    //ajexFileManager(editor, '<?php echo substr(CKEDITOR_PATH, 0, -1); ?>');
}

function CreateImageInput(imageName, image, imagePath, savedImage, itemID, removeAction, ajaxPath, pageID, params)
{
    var html = $('#'+imageName+'-box').html();
    html += '<div id="'+imageName+'-img" '+(image ? '' : ' style="display:none;"')+'><img src="'+image+'" alt="" style="max-width:260px;" class="input-img" /></div>';
    html += '<a id="'+imageName+'-btn" class="btn btn-sm btn-primary btn-icon change-file" title="'+(image ? GetTranslation('change-image') : GetTranslation('add-image'))+'"><i class="fa fa-image"></i>'+(image ? GetTranslation('change-image') : GetTranslation('add-image'))+'</a> ';
    html += '<a onclick="$(\'#'+imageName+'-crop\').modal(\'show\');" id="'+imageName+'-cfg" class="btn btn-sm btn-primary btn-icon configure-file" style="display:none;"><i class="fa fa-cog"></i>'+GetTranslation('configure-image')+'</a> ';
    html += '<a id="'+imageName+'-del" class="btn btn-sm btn-danger btn-icon delete-file" ImageName="'+imageName+'" ItemID="'+itemID+'" RemoveAction="'+removeAction+'" AjaxPath="'+ajaxPath+'" PageID="'+pageID+'" '+(image ? '' : ' style="display:none;"')+'><i class="fa fa-remove"></i>'+GetTranslation('remove-image')+'</a>';
    html += '<div class="clearfix"></div>';
    html += '<div class="hidden" id="'+imageName+'-file"><input name="'+imageName+'" id="'+imageName+'" type="file" size="1" /></div>';
    html += '<input type="hidden" name="saved_'+imageName+'" id="saved_'+imageName+'" value="'+savedImage+'" />';
    $('#'+imageName+'-box').html(html);
    
    if(typeof params != 'undefined' && params.length > 0)
    {
        showConfigBtn = false;
        for(i = 0; i < params.length; i++)
        {
            if(params[i]["Resize"] == 13)
            {
                showConfigBtn = true;
                if(image)
                {
                    //create inputs to save image config
                    html  = '<input type="hidden" name="'+imageName+'_config['+params[i]['Name']+'][X1]" value="'+params[i]['X1']+'" />';
                    html += '<input type="hidden" name="'+imageName+'_config['+params[i]['Name']+'][Y1]" value="'+params[i]['Y1']+'" />';
                    html += '<input type="hidden" name="'+imageName+'_config['+params[i]['Name']+'][X2]" value="'+params[i]['X2']+'" />';
                    html += '<input type="hidden" name="'+imageName+'_config['+params[i]['Name']+'][Y2]" value="'+params[i]['Y2']+'" />';
                    $('#'+imageName+'-box').closest('form').append(html);
                    
                    //create cropper popup
                    if($('#'+imageName+'-crop').size() == 0)
                    {
                        html =  '<div id="'+imageName+'-crop" class="modal">';
                        html += '   <div class="modal-dialog">';
                        html += '       <div class="modal-content"><div class="modal-body"></div></div>';
                        html += '   </div>';
                        html += '</div>';
                        $('#'+imageName+'-box').after(html);
                    }
                    
                    $('#'+imageName+'-crop .modal-body').append('<img id="'+imageName+params[i]['Name']+'-cropper-img" src="'+imagePath+'" />');
                    
                    //init cropper
                    var $image = $("#"+imageName+params[i]["Name"]+"-cropper-img"),
                    $dataX1 = $("[name='"+imageName+"_config["+params[i]["Name"]+"][X1]']"),
                    $dataY1 = $("[name='"+imageName+"_config["+params[i]["Name"]+"][Y1]']"),
                    $dataX2 = $("[name='"+imageName+"_config["+params[i]["Name"]+"][X2]']"),
                    $dataY2 = $("[name='"+imageName+"_config["+params[i]["Name"]+"][Y2]']");
                    $image.cropper({
                        x1: $dataX1,
                        y1: $dataY1,
                        x2: $dataX2,                        
                        y2: $dataY2,
                        aspectRatio: params[i]["Width"] / params[i]["Height"],
                        data: {
                            x: params[i]["X1"],
                            y: params[i]["Y1"],
                            width: params[i]["X2"] - params[i]["X1"],
                            height: params[i]["Y2"] - params[i]["Y1"]
                        },
                        minContainerWidth:540,
                        minContainerHeight:360,
                        done: function(data) {
                            this.x1.val(parseInt(data.x));
                            this.y1.val(parseInt(data.y));
                            this.x2.val(parseInt(data.x + data.width));
                            this.y2.val(parseInt(data.y + data.height));
                        },
                        zoomable: false
                    });
                    $('#'+imageName+params[i]['Name']+'-cropper-img').before('<h3 align="center">'+params[i]["Name"]+'</h3>');
                }
            }
        }
        if(showConfigBtn == true && image)
        {
            $('#'+imageName+'-crop .modal-body').append('<div class="text-center top15"><button class="btn btn-primary" type="button" data-dismiss="modal">'+GetTranslation('close')+'</button></div>');
            $('#'+imageName+'-box .configure-file').show();
        }
    }
    
    $('#'+imageName+'-del').click(function(e){
        RemoveImage($(this).attr('ImageName'), $(this).attr('ItemID'), $(this).attr('RemoveAction'), $(this).attr('AjaxPath'), $(this).attr('PageID'));
        e.preventDefault();
    });
    
    $('#'+imageName+'-btn').click(function(e){
        $('#'+imageName).trigger('click');
        e.preventDefault();
    });
    
    $('#'+imageName).change(function(){
        if ($(this).val())
        {
            var fileName = $(this).val().replace(/^([^\\\/]*(\\|\/))*/, "");
            if (fileName.length > 20)
                newFileName = fileName.substr(0, 14)+'...'+fileName.substr(fileName.length-3, 3);
            else
                newFileName = fileName;
            $('#'+imageName+'-btn').html(newFileName);
        }
        else
        {
            $('#'+imageName+'-btn').html($('#'+imageName+'-btn').attr('title'));
        }
    });
}

function CreateImageCropper(container, imageName, imagePath, params, srcImgW, srcImgH)
{
    if(typeof params != 'undefined' && params.length > 0)
    {
        for(i = 0; i < params.length; i++)
        {
            if(params[i]["Resize"] == 13)
            {
                //create inputs to save image config
                html  = '<input type="hidden" name="'+imageName+'_config['+params[i]['SourceName']+'][X1]" value="'+params[i]['X1']+'" />';
                html += '<input type="hidden" name="'+imageName+'_config['+params[i]['SourceName']+'][Y1]" value="'+params[i]['Y1']+'" />';
                html += '<input type="hidden" name="'+imageName+'_config['+params[i]['SourceName']+'][X2]" value="'+params[i]['X2']+'" />';
                html += '<input type="hidden" name="'+imageName+'_config['+params[i]['SourceName']+'][Y2]" value="'+params[i]['Y2']+'" />';
                html += '<input type="hidden" name="'+imageName+'_config[width]" value="'+srcImgW+'" />';
                html += '<input type="hidden" name="'+imageName+'_config[height]" value="'+srcImgH+'" />';
                container.append(html);
                container.append('<img id="'+imageName+params[i]['SourceName']+'-cropper-img" src="'+imagePath+'" />');
                
                //init cropper
                var $image = $("#"+imageName+params[i]["SourceName"]+"-cropper-img"),
                $dataX1 = $("[name='"+imageName+"_config["+params[i]["SourceName"]+"][X1]']"),
                $dataY1 = $("[name='"+imageName+"_config["+params[i]["SourceName"]+"][Y1]']"),
                $dataX2 = $("[name='"+imageName+"_config["+params[i]["SourceName"]+"][X2]']"),
                $dataY2 = $("[name='"+imageName+"_config["+params[i]["SourceName"]+"][Y2]']");
                $image.cropper({
                    x1: $dataX1,
                    y1: $dataY1,
                    x2: $dataX2,
                    y2: $dataY2,
                    aspectRatio: params[i]["Width"] / params[i]["Height"],
                    data: {
                        x: params[i]["X1"],
                        y: params[i]["Y1"],
                        width: params[i]["X2"] - params[i]["X1"],
                        height: params[i]["Y2"] - params[i]["Y1"]
                    },
                    minContainerWidth:540,
                    minContainerHeight:360,
                    done: function(data) {
                        this.x1.val(parseInt(data.x));
                        this.y1.val(parseInt(data.y));
                        this.x2.val(parseInt(data.x + data.width));
                        this.y2.val(parseInt(data.y + data.height));
                    },
                    zoomable: false
                });
                $('#'+imageName+params[i]['SourceName']+'-cropper-img').before('<h3 align="center">'+params[i]["SourceName"]+'</h3>');
            }
        }
    }
}

function RemoveImage(imageName, itemID, removeAction, ajaxPath, pageID)
{
    if (!confirm(GetTranslation('remove-image-confirm')))
        return;

    $('#'+imageName+'-img').html('<i>'+GetTranslation('removing-image')+'</i>');

    message = CreateMessage(GetTranslation('removing-image'), 'info');
    $.ajax({
        url: ajaxPath,
        dataType: 'JSON',
        data:{
            'Action': removeAction,
            'PageID': pageID,
            'ItemID': itemID,
            'ImageName': imageName,
            'SavedImage': $('#saved_'+imageName).val()
        },
        success: function(data){
            if (data['Status'] === 'success') {
                $('#'+imageName+'-btn').html('<i class="fa fa-image"></i>'+GetTranslation('add-image'));
                $('#'+imageName+'-img').hide();
                $('#'+imageName+'-del').hide();
                $('#'+imageName+'-cfg').hide();
                $("[name^='"+imageName+"_config']").remove();
                $('#saved_'+imageName).val('');
                UpdateMessage(message, GetTranslation('image-removed'), 'success');
            }
            else {
                UpdateMessage(message, GetTranslation('error-removing-image'), 'error');
            }
        },
        error:function(){
            UpdateMessage(message, GetTranslation('error-removing-image'), 'error');
        }
    });
}

function ModalAlert(title, content)
{
    html = '<div id="alert-dialog" class="modal">';
    html +='    <div class="modal-dialog">';
    html +='        <div class="modal-content">';
    html +='            <div class="modal-header">';
    html +='                <button type="button" class="close" aria-hidden="true">&times;</button>';
    html +='                <h4 class="modal-title">'+title+'</h4>';
    html +='            </div>';
    html +='            <div class="modal-body">'+content+'</div>';
    html +='        </div>';
    html +='    </div>';
    html +='</div>';
    
    $(html).modal('show');
    
    //custom handler to close and completely remove dialog
    $('#alert-dialog .close, #alert-dialog .modal-backdrop').click(function(){
        $('#alert-dialog').modal('hide');
        $('#alert-dialog').remove();
    });
}

function ModalConfirm(message, onconfirm)
{
    if(!$.isFunction(onconfirm))
        onconfirm = function(){};
    html = '<div id="confirm-dialog" class="modal">';
    html +='    <div class="modal-dialog">';
    html +='        <div class="modal-content">';
    html +='            <div class="modal-header">';
    html +='                <button type="button" class="close" aria-hidden="true">&times;</button>';
    html +='                <h4 class="modal-title">'+GetTranslation('confirm-action')+'</h4>';
    html +='            </div>';
    html +='            <div class="modal-body">'+message+'</div>';
    html +='            <div class="modal-footer">';
    html +='                <button id="confirm-no" type="button" class="btn btn-icon"><i class="fa fa-ban"></i>'+GetTranslation('no')+'</button>';
    html +='                <button id="confirm-yes" type="button" class="btn btn-warning btn-icon"><i class="fa fa-check"></i>'+GetTranslation('yes')+'</button>';
    html +='            </div>';
    html +='        </div>';
    html +='    </div>';
    html +='</div>';
    
    $(html).modal('show');
    
    $('#confirm-yes').click(function(){
        onconfirm();
        $(this).closest('.modal').modal('hide');
        $(this).closest('.modal').remove();
    });
    
    //custom handler to close and completely remove dialog
    $('#confirm-no, #confirm-dialog .close, #confirm-dialog .modal-backdrop').click(function(){
        $('#confirm-dialog').modal('hide');
        $('#confirm-dialog').remove();
    });
}

function ModalInputField(message, propertyName, isFile, emptyError, onconfirm)
{
    if(!$.isFunction(onconfirm))
        onconfirm = function(){};
    html = '<div id="confirm-dialog" class="modal">';
    html +='    <div class="modal-dialog">';
    html +='        <div class="modal-content">';
    html +='            <div class="modal-header">';
    html +='                <button type="button" class="close" aria-hidden="true">&times;</button>';
    html +='                <h4 class="modal-title">'+GetTranslation('modal-input-fill')+'</h4>';
    html +='            </div>';
    html +='            <div class="alert alert-danger alert-no-margin"></div>';
    if(isFile)
    {
        html +='<div class="modal-body">'+message+'</br></br>';
        html += '<div id="'+propertyName+'-file" style="display:none;" class="input-file-ref" ><a href="#" target="_blank">'+GetTranslation('download-file')+'</a></div>';
        html += '<a id="'+propertyName+'-btn" class="btn btn-sm btn-primary btn-icon change-file" title="'+GetTranslation('add-file')+'"><i class="fa fa-file"></i>'+GetTranslation('add-file')+'</a> ';
        html += '<div class="clearfix"></div>';
        html += '<div class="hidden" id="'+propertyName+'-file"><input name="'+propertyName+'" id="'+propertyName+'" type="file" size="1" /></div>';
        html +='</div>';
    }
    else
    {
        html +='<div class="modal-body">'+message+'</br></br><input class="form-control" name="'+propertyName+'"></div>';
    }
    html +='            <div class="modal-footer">';
    html +='                <button id="cancel-field" type="button" class="btn btn-icon"><i class="fa fa-ban"></i>'+GetTranslation('no')+'</button>';
    html +='                <button id="submit-field" type="button" class="btn btn-warning btn-icon"><i class="fa fa-check"></i>'+GetTranslation('yes')+'</button>';
    html +='            </div>';
    html +='        </div>';
    html +='    </div>';
    html +='</div>';

    $(html).modal('show');
    $(".alert-danger").hide();

    if(isFile)
    {
        $('#'+propertyName+'-btn').click(function(e){
            $('#'+propertyName).trigger('click');
            e.preventDefault();
        });

        $('#'+propertyName).change(function(){
            if ($(this).val())
            {
                var currentFileName = $(this).val().replace(/^([^\\\/]*(\\|\/))*/, "");
                if (currentFileName.length > 20)
                    newFileName = currentFileName.substr(0, 14)+'...'+currentFileName.substr(currentFileName.length-3, 3);
                else
                    newFileName = currentFileName;
                $('#'+propertyName+'-btn').html(newFileName);
            }
            else
            {
                $('#'+propertyName+'-btn').html($('#'+propertyName+'-btn').attr('title'));
            }
        });
    }

    $('#submit-field').click(function(){
        if(!$("[name='"+propertyName+"']").val())
        {
            $(".alert-danger").html(emptyError);
            $(".alert-danger").show();
        }
        else
        {
            if(isFile)
                onconfirm($("[name='"+propertyName+"']"));
            else
                onconfirm($("[name='"+propertyName+"']").val());
            $(this).closest('.modal').modal('hide');
            $(this).closest('.modal').remove();
        }
    });

    //custom handler to close and completely remove dialog
    $('#cancel-field, #confirm-dialog .close, #confirm-dialog .modal-backdrop').click(function(){
        $('#confirm-dialog').modal('hide');
        $('#confirm-dialog').remove();
    });
}

function checkAll(elm, name)
{
    for (i = 0; i < elm.form.elements.length; i++)
    {
        if (elm.form.elements[i].type == "checkbox" && elm.form.elements[i].name == name)
        {
            if(elm.checked){
                $(elm.form.elements[i]).iCheck('check');
            }else{
                $(elm.form.elements[i]).iCheck('uncheck');
            }
        }
    }
}

function GetMessenger()
{
    return Messenger({
        extraClasses: 'messenger-fixed messenger-on-right messenger-on-top',
        theme: 'flat'
    });
}

function GetStaticTopMessenger()
{
    return Messenger({
        extraClasses: 'messenger-fixed messenger-on-right messenger-on-top',
        theme: 'flat'
    });
}

function GetStaticMessenger()
{
    return Messenger({
        extraClasses: 'messenger-fixed messenger-on-right messenger-on-top messenger-margin-top-100',
        theme: 'flat'
    });
}

function CreateMessage(msg, type) 
{
    return GetMessenger().post({
        message: msg,
        type: type,
        showCloseButton: true,
        hideAfter: 4
    });
}

function CreateStaticMessage(msg, type) 
{
    return GetStaticMessenger().post({
        message: msg,
        type: type,
        showCloseButton: true,
        hideAfter: false,
    });
}

function CreateStaticTopMessage(msg, type) 
{
    return GetStaticTopMessenger().post({
        message: msg,
        type: type,
        showCloseButton: true,
        hideAfter: false,
    });
}

function HideMessage(message)
{
    message.hide();
}

function UpdateMessage(message, msg, type)
{
    message.update({
        type: type,
        message: msg
    });
}

function CreateFileInput(fileName, file, savedFile)
{
    var html = $('#'+fileName+'-box').html();
    html += '<div id="'+fileName+'-file" '+(file ? '' : ' style="display:none;"')+' class="input-file-ref" ><a href="'+savedFile+'" target="_blank">'+GetTranslation('download-file')+'</a></div>';
    html += '<a id="'+fileName+'-btn" class="btn btn-sm btn-primary btn-icon change-file" title="'+(file ? GetTranslation('change-file') : GetTranslation('add-file'))+'"><i class="fa fa-file"></i>'+(file ? GetTranslation('change-file') : GetTranslation('add-file'))+'</a> ';
    html += '<div class="clearfix"></div>';
    html += '<div class="hidden" id="'+fileName+'-file"><input name="'+fileName+'" id="'+fileName+'" type="file" size="1" /></div>';
    html += '<input type="hidden" name="saved_'+fileName+'" id="saved_'+fileName+'" value="'+savedFile+'" />';
    $('#'+fileName+'-box').html(html);
    
    $('#'+fileName+'-btn').click(function(e){
        $('#'+fileName).trigger('click');
        e.preventDefault();
    });
    
    $('#'+fileName).change(function(){
        if ($(this).val())
        {
            var currentFileName = $(this).val().replace(/^([^\\\/]*(\\|\/))*/, "");
            if (currentFileName.length > 20)
                newFileName = currentFileName.substr(0, 14)+'...'+currentFileName.substr(currentFileName.length-3, 3);
            else
                newFileName = currentFileName;
            $('#'+fileName+'-btn').html(newFileName);
        }
        else
        {
            $('#'+fileName+'-btn').html($('#'+fileName+'-btn').attr('title'));
        }
    });
}