    
    function setItem(theId,theText)
    {
        document.getElementById('{FIELDITEM_HIDDEN}').value=theId;
        document.getElementById('{FIELDITEM_LABEL}').innerHTML=theText;
        tb_remove();
    }
    

    function sendFormByAjax(urldestination)
    {
        
        $.post(   urldestination, 
                  { filter:document.getElementById('searchfilter').value, curpage:1},
                  function(data){
                    document.getElementById('TB_ajaxContent').innerHTML=data;
                       tb_init('a.thickbox, area.thickbox, input.thickbox');//pass where to apply thickbox

                    },
                  'html');
        document.getElementById('TB_ajaxContent').innerHTML="[...]"+document.getElementById('TB_ajaxContent').innerHTML;
		return false;
    }

    function callActionAjax(urldestination,theClass,theAction,theParam)
    {
        
        $.get(   urldestination, 
                  { class:theClass, action:theAction, param:theParam},
                  function(data){
                    document.getElementById('TB_ajaxContent').innerHTML=data;
                       tb_init('a.thickbox, area.thickbox, input.thickbox');//pass where to apply thickbox

                    },
                  'html');
        
	return false;
    }
    