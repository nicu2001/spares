function ipdepo_class() {
    this.MAX_FQDN_LENGTH = 255;
    this.MIN_FQDN_LENGTH = 4;
    this.MAX_LPART_LENGTH = 64;
    this.tooltip = false;
};

ipdepo_class.prototype.is_mail_pattern = function( mail ) {
    if( typeof( mail ) != 'string' || mail === '' )
	return false;
	
    mail = mail.toLowerCase();
    
    var uxo = mail.lastIndexOf('@');    
    var lpart = '';
    var domain = '';
    var result = null;
    
    while( true ) {
        if( uxo == -1 ) {
            domain = mail;
            if( domain.length > this.MAX_FQDN_LENGTH || domain.length < this.MIN_FQDN_LENGTH ) break;
            result = domain.match( /^(?:(?:\.[0-9a-z!*?])?(?:[0-9a-z!*?-]*[0-9a-z!*?])?)+\.[a-z*]{2,}$/ );
        }
        else if( uxo == 0 ) {
            domain = mail.substr( 1 );
            if( domain.length > this.MAX_FQDN_LENGTH || domain.length < this.MIN_FQDN_LENGTH ) break;
            result = domain.match( /^(?:[a-z0-9!*?](?:[a-z0-9!*?-]*[a-z0-9!*?])?\.)+[a-z*]{2,}$/ );
        }
        else if( uxo > 0 && uxo < mail.length - 1 ) {
            lpart = mail.substr( 0, uxo );
            domain = mail.substr(uxo+1);
    
            if( lpart.length > this.MAX_LPART_LENGTH ) break;
            if( !lpart.match( /^[a-z0-9_](?:[0-9a-z._+-]*[a-z0-9_])?$/ ) )
	        break;
	
            if( domain.length > this.MAX_FQDN_LENGTH || domain.length < this.MIN_FQDN_LENGTH ) break;	    
            result = domain.match( /^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z*]{2,}$/ );
        }
	break;
    }
	    
    return (result === null) ? false : true;
}

ipdepo_class.prototype.is_lpart = function( lpart ) {
    if( typeof( lpart ) != 'string' || lpart === '' )
        return false;
        
    lpart = lpart.toLowerCase();
    if( lpart.length > this.MAX_LPART_LENGTH || !lpart.match(/^[a-z0-9_](?:[0-9a-z._+-]*[a-z0-9_])?$/) )
        return false;
        
    return true;
}

ipdepo_class.prototype.is_subnet = function( subnet ) {
    if( typeof( subnet ) != 'string' || subnet === '' )
	return false;
	
    result = false;

    while( true ) {
        var matches = subnet.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})(?:\/(\d{1,2}))?$/);

        if( !matches ) 
            break;
    
        d3 = Number(matches[1]);
        d2 = Number(matches[2]);
        d1 = Number(matches[3]);
        d0 = Number(matches[4]);
        
        if( d3>255 || d2>255 || d1>255 || d0>255 )
            break;
	
        if( matches.length == 6 ) {
            mask = Number(matches[5]);
            
            if( mask > 32 ) break;
	
            ip = (d3<<24)+(d2<<16)+(d1<<8)+d0;
            mask = ( mask != 0 ) ? 0xffffffff << (32-mask) : 0;
            nip = ip & mask;
            if( ip != nip ) break; 
        }
    
        result = true;
        break;
    }

    return result;
}

ipdepo_class.prototype.alert = function( dlgID, title, msg, icon, doing, param ) {
var aldlg = $('#'+dlgID);
var newdlg = $('#'+dlgID+'_mess').length ? false : true;

if( newdlg )
    aldlg.html(
'<table id="'+dlgID+'_mess" style="display:none; border:thin solid transparent; height:100%; width:100%"><tr>'+
'    <td style="padding-left:64px; padding-right:10px; vertical-align:middle;"></td>'+
'</tr></table>'
);

    aldlg.dialog( 
        {
            modal: true, title: title,
            height: 180,
            width: 360,
            autoOpen: false,
            resizable: false,
            buttons: { "Закрыть": function() { $(this).dialog("destroy"); if( doing ) doing(param); } }
        }
    );
    aldlg.before( '<div id="'+dlgID+'_icon" class="'+icon+'" '+
        'style="display:none;position:absolute;left:16px;top:64px;"></div>' );    

    $('#'+dlgID+'_mess td').html(msg);
    aldlg.dialog('open');
    $('#'+dlgID+'_icon').show();
    $('#'+dlgID+'_mess').show();
}

ipdepo_class.prototype.confirm = function( dlgID, title, msg, icon, doing, param ) {
var aldlg = $('#'+dlgID);
var newdlg = $('#'+dlgID+'_mess').length ? false : true;

if( newdlg )
    aldlg.html(
'<table id="'+dlgID+'_mess" style="display:none; border:thin solid transparent; height:100%; width:100%"><tr>'+
'    <td style="padding-left:64px; padding-right:10px; vertical-align:middle;"></td>'+
'</tr></table>'
);

    aldlg.dialog( 
        {
            modal: true, title: title,
            height: 180, width: 360,
            autoOpen: false, resizable: false,
            buttons: { 
                "Да": function()  { $(this).dialog("destroy"); doing(param); },
                "Нет": function() { $(this).dialog("destroy"); }
            }
        }
    );
    aldlg.before( '<div id="'+dlgID+'_icon" class="'+icon+'" '+
        'style="display:none;position:absolute;left:16px;top:64px;"></div>' );    

    $('#'+dlgID+'_mess td').html(msg);
    aldlg.dialog('open');
    $('#'+dlgID+'_icon').show();
    $('#'+dlgID+'_mess').show();
}

ipdepo_class.prototype.help = function( dlgID, title, app ) {
var aldlg = $('#'+dlgID);
var newdlg = $('#'+dlgID+'_HelpContent').length ? false : true;

    if( newdlg ) {
        aldlg.css({'overflow':'hidden', 'padding':'0px', 'padding-bottom':'14px' });
        aldlg.html( '<iframe id="'+dlgID+'_HelpContent" src=""'+
                    ' style="width:100%; height:100%; border: 1px solid #aaaaaa"></iframe>' );
        aldlg.dialog( {
            modal: false, title: title,
            height: 500, width: 680,
            position: ['right','bottom'],
            autoOpen: false, resizable: true
/*
            buttons: { 
                "Назад":function(){ window.history.back(); },
                "Вперед":function(){ window.history.forward(); },
                "Закрыть": function() { $(this).dialog("close"); } 
            }
*/
        } );
    }

    aldlg.dialog('open');
    
    ts = app.apptasks;
    HelpUrl = app.defhelpurl;
    for(i=0; i<ts.length; i++ ) if( ts[i].active && ts[i].helpurl ) { HelpUrl = ts[i].helpurl; break; }
    
    $('#'+dlgID+'_HelpContent').attr( 'src', HelpUrl );
}

ipdepo_class.prototype.go = function( url ) {
    if( typeof( url ) != 'string' || url === '' )
        return false;
    
    ipdepo.unload_warn = false;
    location.href = url;
    
    return true;
}

//
// dlgID, loader, processor, progress, domain
//
ipdepo_class.prototype.doImport = function( parm ) {
var timerID = 0;
var dlgID = parm.dlgID;
var impdlg = $('#'+dlgID);
var newdlg = $('#'+dlgID+'_form').length ? false : true;

if( newdlg )
    impdlg.html(
'<form id="'+dlgID+'_form" method="post" enctype="multipart/form-data" style="display:none">'+ 
'    <label for="'+dlgID+'_fname"></label><br>'+
'    <input type="file" name="filename" id="'+dlgID+'_fname" size=40 class="ui-widget-content ui-corner-all" />'+
'</form>'+
'<div id="'+dlgID+'_progress" style="display:none" >'+
'    <div id="'+dlgID+'_stage">Выполняется: <b></b></div><br>'+
'    <div id="'+dlgID+'_done_num">&nbsp; Выполнено: <b></b></div><br>'+
'    <div id="'+dlgID+'_done"></div>'+
'</div>'+
'<table id="'+dlgID+'_mess" style="display:none; border:thin solid transparent; height:100%; width:100%"><tr>'+
'    <td style="padding-left:64px; padding-right:10px; vertical-align:middle;"></td>'+
'</tr></table>'
);

    if( parm.label )
        $('#'+dlgID+'_form label').html(parm.label);
    else
        $('#'+dlgID+'_form label').html('<b>Выберите файл для импорта</b>');
    
    impdlg.dialog( { 
        title: parm.title ? parm.title : "Импорт из Excel файла", 
        autoOpen:false, modal:true,
        height:180, width:460,
        buttons:{ "Импорт": import_run, "Отмена": import_close }
    });

    var imp_form = $('#'+dlgID+'_form');
    var imp_fname = $('#'+dlgID+'_fname');
    var imp_mess = $('#'+dlgID+'_mess');
    var imp_mess_td = $('#'+dlgID+'_mess td');
    var imp_progress = $('#'+dlgID+'_progress');
    var imp_stage = $('#'+dlgID+'_stage b');
    var imp_done_num = $('#'+dlgID+'_done_num b');
    var imp_done = $('#'+dlgID+'_done');

    $('#'+dlgID+' > *').hide(); 
    imp_form.show();
    impdlg.dialog('open');

// Будет убита после dialog('destroy')
    impdlg.before( '<div id="'+dlgID+'_icon" class="ipdepo-icon-empty32" '+
        'style="display:none;position:absolute;left:16px;top:64px;"></div>' );    

    var imp_icon = $('#'+dlgID+'_icon');
    
    margleft = ( impdlg.width() - imp_fname.width() )/2;
    imp_form.eq(0).css( { 'margin-top':'20px', 'margin-left':margleft+'px' } );
    
function import_strobe() {
// Debug - return;
var lastdone=-1;
var curdone=0;
var laststage=-1;
var curstage=0;
    
    $.post(parm.progress, {domain:parm.domain}, function( rsp, textStatus)
    {
        if( rsp.errcode ) {
            ipdepo.alert('AlertDlg', 'Ошибка', rsp.errmess, 'ipdepo-icon-stop32');
            return;
        }
        if( $.isPlainObject(rsp.data) ) {
            curdone = rsp.data.done;
            curstage = rsp.data.stage_num;
            if( curstage != laststage || curdone >= lastdone ) {
                if( curstage != laststage ) {
                    imp_stage.text(rsp.data.stage_name);
                    laststage = curstage;
                }
                imp_done_num.text(curdone+'%');
                imp_done.progressbar( 'value' , curdone );
                lastdone = curdone;
            }
        }
        if( timerID && !( curstage == 999 && curdone == 100 ) ) {
            timerID = setTimeout( import_strobe, 1000 );
        }
    }, 'json');
}

function import_close() { 
    if( timerID) 
        clearTimeout(timerID);
    timerID = 0;

    $(this).dialog("destroy");
    $('#'+dlgID+' > *').hide();
    impdlg.hide();
}

// Отображение результатов импорта
function import_result( rsp ) {

    if( timerID) 
        clearTimeout(timerID);
    timerID = 0;
    
    imp_done.progressbar('destroy');
    imp_progress.hide();
    imp_mess.show();
    
    if( rsp.errcode ) {
        imp_icon.attr({ "class": 'ipdepo-icon-stop32' });
        if( rsp.errcode == 999 ) {
            alert( "Ваша сессия закончилась по таймауту.\nПожалуйста перерегистрируйтесь." );
            location.href = 'login.php';
            return false;
        }
    }
    else {
        imp_icon.attr({ "class": 'ipdepo-icon-info32' });
    }

    imp_icon.show();
    imp_mess_td.html( rsp.errmess );
//    $('#refresh_'+parm.caller).click();
    if( parm.callerCB ) parm.callerCB( true );
}

function import_onload( data ) {
var rsp = $.parseJSON(data);
    
    if( rsp.errcode ) {
        if( rsp.errcode == 999 ) {
            alert( "Ваша сессия закончилась по таймауту.\nПожалуйста перерегистрируйтесь." );
            location.href = 'login.php';
            return false;
        }
        imp_icon.attr({ "class": 'ipdepo-icon-stop32' });
        imp_mess_td.html( rsp.errmess );
    }
    else {
        imp_mess.hide();
        imp_icon.hide();
        imp_progress.show();    
        imp_done.progressbar({ value: 0 });
// Comment for debug
        timerID = setTimeout( import_strobe, 10 );
        $.post(parm.processor, {oper:'import', fname:rsp.errmess}, import_result, 'json' );
    }
}

// Реакция на кнопки диалога импорта
function import_run() {
    impdlg.dialog( "option", "buttons", { "Закрыть": import_close } );

    imp_icon.attr({ "class": 'ipdepo-icon-load32' });
    imp_mess_td.html( "Идет загрузка файла для импорта. Ожидайте ..." );

    imp_form.hide();
    imp_icon.show();
    imp_mess.show();

    imp_form.attr({ action: parm.loader });

/*
// iframe-post-form.js variant
    imp_form.iframePostForm({ iframeID: dlgID+'_iframe', complete: import_onload });
*/

// jquery.form.js variant

    imp_form.ajaxForm({
//        iframeSrc: 'blank.html',
        iframe: true,
        success: import_onload
    });
    imp_form.submit();
    imp_form.unbind('submit');

}

} // doImport() End


