$(function(){

$('#wait_img').jqm({modal:true});
    
// Индикация AJAX процесса
$('#wait_img').ajaxStart(function() { $(this).jqmShow(); } );
$('#wait_img').ajaxStop(function()  { $(this).jqmHide(); } );

if( $.browser.opera ) {
    opera.setOverrideHistoryNavigationMode('compatible');
}

$('#SearchForm').ajaxForm({ 
	url:'art_search.php', 
	type:'POST', 
	dataType:'json', 
    data: {oper:'search_articles'}, 
	beforeSubmit: screen_lock, 
	success: submit_responce 
});

$('#SearchForm button')
    .button( { icons: { primary: "ui-icon-gear" } } );
$('#SearchForm input:text').css({'height':'18px'});
		
function screen_lock(arr, $form, options) {
    var i, k, c;
	$('#data_tab').html('');
	for(i=0; i<arr.length; i++) {
        if( arr[i].name == 'art_code' ) {
            var oval = arr[i].value.toLowerCase();
            var nval = '';
            for( k=0; k<oval.length; k++ ) {
                c = oval.charAt(k);
                if( ( c >= 'a' && c <= 'z' ) || ( c >= '0' && c <= '9' ) )
                    nval = nval+c;
            }
            arr[i].value = nval;
            $('#art_code').val(nval);
        }
		if( (arr[i].name == 'country_list' && arr[i].value == '' ) || 
			(arr[i].name == 'art_code' && arr[i].value.length < 4 ) ) {
			alert("Отсутствует или неправильный параметр поиска.");
			return false;
		}
	}
}

function submit_responce(rsp) {
    var i,k;
    var data = rsp.data;
    
    if( rsp.errcode ) {
		alert(rsp.errmess);
    }
    else {
		if( data.length == 0 ) {
			alert("Информация не найдена");
			return;
		}
        
        for( i=0; i<data.length-1; i++)
            for( k=1; k<data.length; k++)
                if( data[i].sup_brand.toUpperCase() > data[k].sup_brand.toUpperCase())
                {
                    var teo = data[i];
                    data[i] = data[k];
                    data[k] = teo;
                }
                
        var table = '<tr><td colspan="7" class="ui-state-default">Оригинальные артикулы</td></tr>';
        for(i=0; i<data.length; i++ ) {
            if( data[i].arl_kind != 1) continue;
            table = table+
                    '<tr><td style="display:none;">'+data[i].art_id+
                    '</td><td style="white-space:nowrap;">'+data[i].sup_brand+
                    '</td><td style="white-space:nowrap;">'+data[i].art_number+
                    '</td><td >'+data[i].gen_name+
                    '</td><td tecdata title="'+data[i].cri_text+'">'+((data[i].cri_text)?'<span class="ui-icon ui-icon-wrench"></span>':'')+
                    '</td><td photo="'+data[i].photo+'">'+((data[i].photo)?'<span class="ui-icon ui-icon-search"></span>':'')+
                    '</td><td >'+data[i].price+
                    '</td><td >'+data[i].store+
                    '</td><tr>';
        }
        table = table + '<tr><td colspan="7" class="ui-state-default">Заменители ( Не оригинальные артикулы )</td></tr>';
        for(i=0; i<data.length; i++ ) {
            if( data[i].arl_kind == 1) continue;
            table = table+
                    '<tr><td style="display:none;">'+data[i].art_id+
                    '</td><td style="white-space:nowrap;">'+data[i].sup_brand+
					'</td><td style="white-space:nowrap;">'+data[i].art_number+
                    '</td><td >'+data[i].gen_name+
                    '</td><td tecdata title="'+data[i].cri_text+'">'+((data[i].cri_text)?'<span class="ui-icon ui-icon-wrench"></span>':'')+
                    '</td><td photo="'+data[i].photo+'">'+((data[i].photo)?'<span class="ui-icon ui-icon-search"></span>':'')+
                    '</td><td >'+data[i].price+
                    '</td><td >'+data[i].store+
                    '</td><tr>';
        }
        
        table = '<table><tr class="search_panel ui-widget ui-widget-header ui-corner-all">'+
                '<th style="display:none">ID'+
                '</th><th>Поставщик'+
                '</th><th>Артикул'+
                '</th><th>Подгруппа'+
                '</th><th>ТД'+
                '</th><th>Фото'+
                '</th><th>Цена'+
                '</th><th>Кол.'+
                '</th></tr>'+
				table+'</table>';
		$("#data_tab").html(table);
        
//        $('td[photo!=""]').mouseover( function() {
//            $(this).css({'cursor':'pointer','text-decoration':'underline'});
//        });
        $('td[photo] span')
            .click( function() {
                var prnt = $(this).parent();
                var art_id = prnt.parent().find('td:first').text();
                ShowImage(art_id, prnt.attr('photo'), 
                    prnt.parent().find('td:eq(2)').text() + ' ( ' +
                    prnt.parent().find('td:eq(1)').text() + ' )' );
            })
            .hover(
                function(){ $(this).css( {'text-decoration':'underline', 'cursor':'pointer'} ); },
                function(){ $(this).css( {'text-decoration':'none', 'cursor':'default'} );}
            )
        $('td[tecdata] span')
            .hover(
                function(){ $(this).css( {'text-decoration':'underline', 'cursor':'pointer'} ); },
                function(){ $(this).css( {'text-decoration':'none', 'cursor':'default'} );}
            )
	}
}

function ShowImage(art_id, photo, title) {
    if( photo == '' || art_id == '' ) return; 
    
    var wimg = 'art_'+art_id+'_img';

    if($('#'+wimg).html()) {
        $('#'+wimg).dialog('moveToTop');
        return;
    }
    
    $('#wait_img').jqmShow();
    $('body').append('<div id="'+wimg+'" style="display:none"><img src="getimg.php?id='+art_id+
                        '&photo='+photo+'"></div>');

    var im = $('#'+wimg+' img');
    im.bind( 'load', function() {
        $('#'+wimg).dialog({
            title: title,
            modal: false,
            resizable: false,
            height: 'auto',
            width: 'auto',
            stack: true,
            close: function() {
                    $('#'+wimg).dialog("destroy");
                    $('#'+wimg).remove();
                 }
        });
        
        $('#wait_img').jqmHide();
        
        $('#'+wimg).parent().resizable( {'aspectRatio':true, 'alsoResize':im } );
//        $('#'+wimg).parent().resize( function() {
//             $('#'+wimg+' img').css({'height':$('#'+wimg).height()-2, 'width':$('#'+wimg).width()});
//        });
    });
    
}					
}); // End of ready function
