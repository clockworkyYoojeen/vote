jQuery( document ).ready( function ( $ ) {
    // пока не выбран элемент - кнопка отправки неактивна
    $( 'input[name=vote_form]' ).prop( 'disabled', true );
    $( 'input[class=yoojeen_vote]' ).click( function () {
        $( 'input[name=vote_form]' ).prop( 'disabled', false );
    } );
    // voteInfo - данные, переданные из php скрипта функцией wp_localize_script
    $( '#total' ).text( voteInfo.total );
    var respData;
    var progress_value;
    $( '#vote_form' ).submit( function () {
        var updated = $( '#vote_form' ).find( 'input:checked' );
        var form_item = updated.val();
        $.ajax( {
            type: "POST",
            url: voteInfo.myurl,
            data: { formData: form_item, action: 'vote' },
            success: function ( res ) {
                // получаем новые  данные из php скрипта для обновления
                respData = JSON.parse( res );
                // обновляем 'Всего:'
                $( "#total" ).text( respData.total );
                // обновляем количество голосов выбранного элемента ('голосов:')
                updated.next().next().text( respData.option_updated );
                // обновляем ширину всех прогресс баров
                for ( var numofvotes in respData.item_votes ) {
                    // высчитываем ширину прогресс-бара
                    progress_value = ( respData.item_votes[numofvotes] / respData.total ) * 100;
                    // по атрибуту name "привязываемся" к нужному прогресс-бару и переписываем значение
                    $( 'progress[name=' + numofvotes + ']' ).val( progress_value );
                }
                // снимаем все чекбоксы
                $( "#vote_form" ).find( "input:checked" ).prop( 'checked', false );

            },
            error: function () {
                alert( 'error' );
            }
        } );
        // запрет отправки формы
        return false;
    } );

} )