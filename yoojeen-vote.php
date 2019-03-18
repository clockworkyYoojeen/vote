<?php

/*
  Plugin Name: Yoojeen Vote
  Description: Создаёт форму голосования при помощи шорткода вида [vote name="langs" item1="PHP" item2="JavaScript" ]
  Author: Yoojeen
 */

// защита от запуска файла напрямую
if ( !defined( 'ABSPATH' ) )
	die( 'No monkey business!' );

// подключаем файл со вспомогательной функцией
require 'clear-content.php';

// подключение js скрипта 
add_action( 'wp_footer', 'vote_scripts' );
// только для страниц содержащих шорткод
function vote_scripts() {
    global $post;
	if( has_shortcode( $post->post_content, 'vote' ) ){
		wp_enqueue_script( 'yoojeen-vote', plugins_url( 'js/yoojeen_vote.js', __FILE__ ), array( 'jquery' ) );
	
		// передаём данные в js script, объект будет называться voteInfo
		wp_localize_script( 'yoojeen-vote', 'voteInfo', array(
			'myurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'yoojeen' ),
				) );
	}
}

// обработчик запроса для авторизованных и для неавторизованных пользователей
add_action( 'wp_ajax_vote', 'vote_form_capture' );
add_action( 'wp_ajax_nopriv_vote', 'vote_form_capture' );

// добавляем шорткод
add_shortcode( 'vote', 'vote_create' );

// создаём и выводим "голосовалку"
function vote_create( $atts ) {
	if ( empty( $atts ) )
		return "<p>No items to vote for</p>";
	$option_name = $atts['name'];
	
	$result = '<form method="post" id="'.$option_name.'">';
	$yoojeen_vote = array();

	// получаем данные из базы, если опция уже существует
	if ( $options = get_option( $option_name ) ) {
		$total = array_sum( $options );
	} else {
		$total = 0;
	}
	$i = 0;
	foreach ( $atts as $item ) {
		$item = sanitize_text_field( $item );
        if ($item == $option_name) {
            continue;
        }
		if ( $total ) {
			// переменная для установки ширины прогресс бара
			$progress_value = ($options[ $option_name . $i ] / $total) * 100;
		} else {
			$progress_value = 0;
		}
		//$result							 .= '<div>';
		$result							 .= "{$item}  <input type='radio' name='" . $option_name . "' value='" . $option_name. $i . "' class='yoojeen_vote'><span>голосов: </span><b>" . ($options[ $option_name . $i ] ? $options[ $option_name . $i ] : 0) . "</b><br>";
		$result							 .= "<progress name='" .$option_name.$i . "' value='" . $progress_value . "' max='100'></progress><br>";
		//$result							 .= '</div>';
		$yoojeen_vote[$option_name . $i] = 0;
		$i++;
	}
	$result .= '<input type="submit" name="'.$option_name.'" class="vote_form_button">
	</form>
	<h4>Всего: <span id="'.$option_name.'_total">'.$total.'</span></h4>';

	// добавляется только в первый раз, если существует, то ничего не делает
	add_option( $option_name, $yoojeen_vote );

	return $result;
}

// обработчик данных из ajax запроса
function vote_form_capture() {
     
	if ( isset( $_POST[ 'formData' ] ) ) {
        $option_name = $_POST['option_name'];
		$res = array();

		$item = $_POST[ 'formData' ];

		$opt_arr = get_option( $option_name );
		foreach ( $opt_arr as $key => $val ) {
			if ( $key == $item ) {
				$opt_arr[ $key ] ++;
			}
		}

		update_option( $option_name, $opt_arr );
		// передаём обновлённые данные в js script
		$res[ 'item_votes' ]		 = get_option( $option_name );
		// общее количество
		$res[ 'total' ]			 = array_sum( $opt_arr );
		// выбранный элемент
		$res[ 'option_updated' ]	 = $opt_arr[ $item ];

		$resJson = json_encode( $res );
		echo $resJson;
		wp_die();
	}
}

// при деактивации удаляем шорткод из контента
register_deactivation_hook( __FILE__, 'vote_deactivate' );

function vote_deactivate() {
	vote_clear_content();
}
