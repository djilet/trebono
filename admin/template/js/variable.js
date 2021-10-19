$(document).ready(function(){
	
	//$.ajaxSetup({async:false});
	
	matchText(document.getElementsByTagName("html")[0], regExp, CallbackText, CallbackElement, [], true);
	
	//right click edit variable
	$(document).on('contextmenu', '.variable-edit-contextmenu', function(e){
		
		element = $(this).get(0);
		
		if(element.tagName == "SELECT") {
			
			var selectedOpt = element.options[0];
			
			for ( var i = 0, len = element.options.length; i < len; i++ ) {
		        opt = element.options[i];
		        if ( opt.selected === true ) {
		        	selectedOpt = opt
		        }
			}

			tag = selectedOpt.getAttribute('tag_name');
			value = selectedOpt.getAttribute('var_value');
			variableID = selectedOpt.getAttribute('variable_id');
			editorType = "text";
			if($(this).attr('type') == "html")
			{
				editorType = "html";
			}
			EditVariable(tag, editorType, variableID);
			
		} else if (element.getAttribute("var_in_attr") != null) {
			
			tag = element.getAttribute('tag_name');
			value = element.getAttribute('var_value');
			variableID = element.getAttribute('variable_id');
			
			editorType = "text";
			if($(this).attr('type') == "html")
			{
				editorType = "html";
			}
			EditVariable(tag, editorType, variableID);		
		}
		e.preventDefault();
	});
	
	
	$(document).on('click', '.variable-edit', function(e){
		tag = $(this).attr('tag_name');
		value = $(this).attr('var_value');
		variableID = $(this).attr('variable_id');
		editorType = "text";
		if($(this).attr('type') == "html")
		{
			editorType = "html";
		}
		EditVariable(tag, editorType, variableID);
		e.preventDefault();
	});
	
	$(document).on('click', '.variable-help', function(e){
		tag = $(this).attr('tag_name');
		value = $(this).attr('var_value');
		labelValue = $(this).siblings('label').text();
		console.log($(this).closest('header').text());
		editorType = "text";
		variableID = false;
		if($(this).attr('type') == "html")
		{
			editorType = "html";
		}
		EditVariable(tag, editorType, variableID, labelValue);
		e.preventDefault();
	});
	
	$(document).on('click', '.variable-help-tab', function(e){
		tag = $(this).attr('tag_name');
		value = $(this).attr('var_value');
		labelValue = $(this).closest('li').text().trim();
		editorType = "text";
		variableID = false;
		if($(this).attr('type') == "html")
		{
			editorType = "html";
		}
		EditVariable(tag, editorType, variableID, labelValue);
		e.preventDefault();
	});
	
	$(document).on('click', '.variable-help-header', function(e){
		tag = $(this).attr('tag_name');
		value = $(this).attr('var_value');
		labelValue = $(this).closest('header').text().trim();
		editorType = "text";
		variableID = false;
		if($(this).attr('type') == "html")
		{
			editorType = "html";
		}
		EditVariable(tag, editorType, variableID, labelValue);
		e.preventDefault();
	});
	
});

var module = "";
var type = "";
var template = "";

var regExp = new RegExp("#variable_[0-9]+#", "g");
var regExpID = new RegExp("[0-9]+(?=#)", "g");

//create observer
const observer = new MutationObserver(subscriber);

function InitVariableEdit(in_section)
{
	section = in_section;
	
	var parts = section.split('/');
	if (parts.length >= 2)
	{
		module = parts[0];
		type = parts[1];
	}
	else
	{
		alert(GetTranslation('incorrect-parameter'));
	}
	
	if (parts.length > 2)
		template = parts[2];
	else
		template = '';
}

function EditVariable(tagName, editorType, variableID, labelValue = "")
{
	var value = ""
	if(variableID > 0) {
		$.ajax({
			url: ADMIN_PATH+'ajax.php',
			method: 'GET',
			dataType: 'JSON',
			//async: false,
			data: {
	                'Action': 'GetVariable',
					'variable_id': variableID
				},
			success:function(data){
				value = data;
				var html =  '<div class="form-group">';
				html += '	<label for="VariableValue">'+GetTranslation('variable-value')+'</label><br /><textarea name="VariableValue" id="VariableValue" class="form-control" rows="5"></textarea>';
				html += '</div>';
				html += '<input type="hidden" name="variable_id" id="variable_id" value="'+variableID+'" />';
				html += '<input type="hidden" name="Action" id="Action" value="SaveVariableToXML" />';
				html += '<input type="hidden" name="prev_value" id="prev_value" value="" />';
				$('#variable-edit .modal-body').html(html);
				$('#VariableValue').text(value);
				$('#prev_value').val(value);
				$('.variable-cancel, #variable-edit .modal-backdrop, #variable-edit .close').unbind('click');
				$('.variable-cancel, #variable-edit .modal-backdrop, #variable-edit .close').click(function(e){
					if(typeof(CKEDITOR.instances.VariableValue) != 'undefined')
					{
						CKEDITOR.instances.VariableValue.destroy();
					}
					$('#variable-edit .modal-body').empty();
					$('#variable-edit').modal('hide');
					e.preventDefault();
				});
				if(editorType == "html")
				{
					createCKEditor('VariableValue', null, null, '150px', {enterMode : CKEDITOR.ENTER_BR});
				}
				$('#variable-edit').modal('show');
			},
			error:function(){
				
			}
		});
	} else {
		translation = GetTranslation(tagName)
		titleCount = $('.variable-help[tag_name='+$('#tag_name').val()+'][data-original-title]').length
		title = $('.variable-help[tag_name='+$('#tag_name').val()+'][data-original-title]').attr('data-original-title')
		if(titleCount > 0) {
			value = title
		} else if (translation != tagName && translation != "") {
			value = translation
		}
		var html =  '<div class="form-group">';
		html += '<input class="form-control" disabled value="'+labelValue+'"/>';
		html += '</div>';
		html +=  '<div class="form-group">';
		html += '<label for="VariableValue">'+GetTranslation('variable-value')+'</label><br /><textarea name="VariableValue" id="VariableValue" class="form-control" rows="5"></textarea>';
		html += '</div>';
		html += '<input type="hidden" name="tag_name" id="tag_name" value="'+tagName+'" />';
		html += '<input type="hidden" name="label_value" id="label_value" value="'+labelValue+'" />';
		html += '<input type="hidden" name="Action" id="Action" value="CreateVariable" />';
		html += '<input type="hidden" name="prev_value" id="prev_value" value="" />';

		$('#variable-edit .modal-body').html(html);
		$('#VariableValue').text(value);
		$('#prev_value').val(value);
		$('.variable-cancel, #variable-edit .modal-backdrop, #variable-edit .close').unbind('click');
		$('.variable-cancel, #variable-edit .modal-backdrop, #variable-edit .close').click(function(e){
			if(typeof(CKEDITOR.instances.VariableValue) != 'undefined')
			{
				CKEDITOR.instances.VariableValue.destroy();
			}
			$('#variable-edit .modal-body').empty();
			$('#variable-edit').modal('hide');
			e.preventDefault();
		});
		if(editorType == "html")
		{
			createCKEditor('VariableValue', null, null, '150px', {enterMode : CKEDITOR.ENTER_BR});
		}
		$('#variable-edit').modal('show');
		
	}
}

function SaveVariable()
{
	if(typeof CKEDITOR.instances.VariableValue != 'undefined'){
		$('[name=VariableValue]').val(CKEDITOR.instances.VariableValue.getData());
	}
	message = CreateMessage(GetTranslation('saving-variable'), 'info');
	if($('#variable_id').val() > 0 || $('#tag_name').val() != "") {
		
		$.ajax({
			url: ADMIN_PATH+'ajax.php',
			method: 'POST',
			dataType: 'JSON',
			data:$('#variable-edit-form').serialize(),
			success:function(data){
				if(typeof data.SessionExpired != 'undefined')
	      		{
	      			window.location.href = ADMIN_PATH+"index.php";
	      			return;
	      		}
				if($('#variable_id').val() > 0) {
					callbackUpdateTranslations(data)
				} else {
					$('.variable-help[tag_name='+$('#tag_name').val()+']').each(function() {
						translation = data
						$(this).attr("data-original-title", translation);
					});
				}
				$('#variable-edit').modal('hide');
				UpdateMessage(message, GetTranslation('variable-saved'), 'success');
				if(typeof(CKEDITOR.instances.VariableValue) != 'undefined')
				{
					CKEDITOR.instances.VariableValue.destroy();
				}
			},
			error:function(){
				UpdateMessage(message, GetTranslation('error-saving-variable'), 'error');
			}
		});
		
	}  else {
		
		UpdateMessage(message, GetTranslation('error-saving-variable'), 'error');
		
	}
}

var matchText = function(node, regex, callback, callbackAttr, excludeElements, isFirst) { 
	
    excludeElements || (excludeElements = [/*'script', 'style', 'iframe', 'canvas'*/]);
    
    var excludeElements2 = ['option'];
    
    var child = node.firstChild;
   
    do {
    	
        if(child != null){

            switch (child.nodeType) {
    
            case 1:
                if (excludeElements.indexOf(child.tagName.toLowerCase()) > -1) {
                    continue;
                }
                
               //search for variables in attributes 
               for (i = 0; i < child.attributes.length; i++) {
               
                  if (regex.test(child.attributes[i].value)) {
                  
                        callbackAttr.apply(window, [child, child.attributes[i].name, child.attributes[i].value]);
                    }
                }
    
                matchText(child, regex, callback, callbackAttr, excludeElements, false);
                break;
    
            case 3:
            	var contextmenu = false;
            	if (regex.test(child.data) && excludeElements2.indexOf(child.parentNode.tagName.toLowerCase()) > -1) {

                  var variable = /#variable_[0-9]+#/g.exec(child.data)[0];
                  callbackAttr.apply(window, [child.parentNode, false, variable]);
                  contextmenu = true
                }
                    
           	   var bk = 0;
               child.data.replace(regex, function(all) {
               		var args = [].slice.call(arguments),
                    offset = args[args.length - 2],
                    newTextNode = child.splitText(offset+bk), tag;
                bk -= child.data.length + all.length;

                newTextNode.data = newTextNode.data.substr(all.length);
                tag = callback.apply(window, [child].concat(args, contextmenu));
                child.parentNode.insertBefore(tag, newTextNode);
                child = newTextNode;
            });
            regex.lastIndex = 0;
            break;
    
            }
            
            child = child.nextSibling
        }

    } while (child != null);
    
    if(isFirst){

    	observer.observe(document, {
    	    attributes: false,
    	    characterData: true,
    	    childList: true,
    	    subtree: true,
    	    attributeOldValue: false,
    	    characterDataOldValue: true
    	});
    }

    return node;

}

function subscriber (mutations) {
	
  mutations.forEach((mutation) => {
	  
	  switch (mutation.target.nodeType) {

        case 1:
		  if(mutation.target.getAttribute("variable_id") != null)
			  return
		  break;
		  
        case 3:
		  if(mutation.target.parentNode.getAttribute("variable_id") != null)
			  return
		default:
			return
			break;
	  }

	  matchText(mutation.target, regExp, CallbackText, CallbackElement, [], false);
  });
}


function CallbackText(node, match, offset, trash, contextmenu) {
	
    var span = document.createElement("span");    
    var regExpID = new RegExp("[0-9]+(?=#)", "g") 
    var variableID = regExpID.exec(match)[0];
    span.setAttribute("variable_id", variableID);
    
    if(!contextmenu) {
		span.textContent = " ";
		var icon = document.createElement("i");
		icon.className = "fa fa-edit variable-edit";
		icon.setAttribute("variable_id", variableID);
		span.appendChild(icon);
	} else{
		span.textContent = " \u270E"
	}
   	
   	return span;
}
function CallbackElement(node, attribute, value) {

    var variableID = /[0-9]+(?=#)(?!#v)/g.exec(value)[0];
    
    node.setAttribute("variable_id", variableID);
    
    if(attribute) {
		var newValue = value.replace(regExp, "") + " \u270E";
		node.setAttribute(attribute, newValue);
		node.setAttribute("var_in_attr", attribute);
		node.classList.add("variable-edit-contextmenu");
	} else {
		node.parentNode.classList.add("variable-edit-contextmenu");
		node.setAttribute("variable_id", variableID);
	}
}

function callbackUpdateTranslations(data) {
	prevValue = $('input#prev_value').val();
	regExpValue = new RegExp(prevValue.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), "g");
	
	$('span[variable_id='+$('input#variable_id').val()+']').each(function() {				
		text = $(this).parent().html()
		if(text)
			$(this).parent().html(text.replace(regExpValue, data))
	});
	
	$('[variable_id='+$('input#variable_id').val()+'][var_in_attr]').each(function() {				
		text = $(this).attr($(this).attr("var_in_attr"));
		$(this).attr($(this).attr("var_in_attr"), text.replace(regExpValue, data));
	});
}
