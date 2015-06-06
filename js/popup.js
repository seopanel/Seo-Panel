function scriptDoLoadDialog(scriptUrl, scriptPos, scriptArgs, widthVal, heightVal) {
	widthVal = widthVal ? widthVal : 900;
	heightVal = heightVal ? heightVal : 600;
	$('#dialogContent').dialog({
	    autoOpen : false,
	    width : widthVal,
	    height : heightVal,
	    title : 'Seo Panel',
	    modal : true,
	    close : function() {
	    	$("#dialogContent").html('');
	    	needPopup = false;
	    	changeDateInputField('parent_from_time', 'from_time');
	    	changeDateInputField('parent_to_time', 'to_time');
	    	$(this).dialog("destroy");
	    },
	    open : function() {
	    	var dataVals = {
	                "method" : "get",
	                "dataType" : "html",
	                "url" : scriptUrl,
	                "data" : scriptArgs + "&fromPopUp=1",
	                beforeSend: function(){
	                	$("#dialogContent").html('<div id="loading_content"></div>');
	                },
	                success : function(response) {
	        	    	needPopup = true;
	        	    	changeDateInputField('from_time', 'parent_from_time');
	        	    	changeDateInputField('to_time', 'parent_to_time');
	                	$("#dialogContent").html(response);
	                	$("#dialogContent").show();
	                },
	                error : function(xhr, status, error) {
	                },
	                complete : function() {
	                }
	            }
	            $.ajax(dataVals);
	    }
	});
	$('#dialogContent').dialog("open");
}

function changeDateInputField(inputName, changeInputName) {
	if ($('input[name="'+inputName+'"]').length) {
		$('input[name="'+inputName+'"]').attr("name", changeInputName);
	}
}

function scriptDoLoadPostDialog(scriptUrl, scriptForm, scriptPos, scriptArgs, noLoading) {
	if(!scriptArgs) { var scriptArgs = ''; }
	var loadingContent = showLoadingIcon(scriptPos, noLoading);
	var scriptPos = (scriptPos == "content") ? "#dialogContent" : "#dialogContent #" + scriptPos;
	var dataVals = {
            "method" : "post",
            "dataType" : "html",
            "url" : scriptUrl,
            "data" : $('#dialogContent #'+scriptForm).serialize() + scriptArgs + "&fromPopUp=1",
            beforeSend: function(){
            	$(scriptPos).html(loadingContent);
            },
            success : function(response) {
    	    	needPopup = true;
            	$(scriptPos).html(response);
            	$(scriptPos).show();
            },
            error : function(xhr, status, error) {
            },
            complete : function() {
            }
        }
	$.ajax(dataVals);
}

function scriptDoLoadGetDialog(scriptUrl, scriptPos, scriptArgs, noLoading) {
	if(!scriptArgs){ var scriptArgs = ''; }
	var loadingContent = showLoadingIcon(scriptPos, noLoading);
	var scriptPos = (scriptPos == "content") ? "#dialogContent" : "#dialogContent #" + scriptPos;
	var dataVals = {
            "method" : "get",
            "dataType" : "html",
            "url" : scriptUrl,
            "data" : scriptArgs + "&fromPopUp=1",
            beforeSend: function(){
            	$(scriptPos).html(loadingContent);
            },
            success : function(response) {
    	    	needPopup = true;
            	$(scriptPos).html(response);
            	$(scriptPos).show();
            },
            error : function(xhr, status, error) {
            },
            complete : function() {
            }
        }
	$.ajax(dataVals);
}