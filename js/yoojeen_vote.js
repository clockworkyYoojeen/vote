jQuery( document ).ready( function ( $ ) {
    // пока не выбран элемент - кнопка отправки неактивна
	$( 'input[class="vote_form_button"]' ).prop( 'disabled', true );
	// активируем кнопку, когда выбран элемент
    $( 'input[class="yoojeen_vote"]' ).click( function () {
			$(this).parent().find('.vote_form_button').prop('disabled', false);
	 } );
	
    var respData;
	var progress_value;
	// по клику по кнопке отправки формы, отправляем ajax запрос
    $( '.vote_form_button' ).click( function (e) {
		var elem = e.target;
		// выясняем, с какой именно формой работаем, и с какой опцией из базы данных
		// так как имя формы совпадает с названием опции в базе
		var voteID = elem.getAttribute('name');

        var updated = $( '#'+voteID ).find( 'input:checked' );
		var form_item = updated.val();
		// делаем кнопку снова неактивной
		$(this).prop('disabled', true);
        //  запрос
        $.ajax( {
            type: "POST",
            url: voteInfo.myurl,
            data: { formData: form_item, action: 'vote', option_name: voteID },
            success: function ( res ) {
                // получаем новые  данные из php скрипта для обновления
				respData = JSON.parse( res );
                // обновляем 'Всего:'
                $( "#"+voteID+"_total" ).text( respData.total );
                // обновляем количество голосов выбранного элемента ('голосов:')
                updated.next().next().text( respData.option_updated );
                // обновляем ширину всех прогресс баров
                for ( var numofvotes in respData.item_votes ) {
                    // высчитываем ширину прогресс-бара
                    progress_value = ( respData.item_votes[numofvotes] / respData.total ) * 100;
                    // по атрибуту name "привязываемся" к нужному прогресс-бару и переписываем значение
                    $( 'progress[name=' + numofvotes + ']' ).val( progress_value );
                }
                // снимаем чекбокс
				updated.prop('checked', false);

            },
            error: function () {
                alert( 'error' );
            }
        } );
        // запрет отправки формы
        return false;
    } );

} )