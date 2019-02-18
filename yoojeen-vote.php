<?php

/*
  Plugin Name: Yoojeen Vote
  Description: Создаёт форму голосования при помощи шорткода вида [vote item1="PHP" item2="JavaScript" ]
  Author: Yoojeen
 */

// защита от запуска файла напрямую
if ( !defined( 'ABSPATH' ) )
	die( 'No monkey business!' );

// подключаем файл со вспомогательной функцией
require 'clear-content.php';

// подключение js скрипта 
add_action( 'wp_footer', 'vote_scripts' );

function yoojeen_scripts() {
	$vote_info	 = get_option( 'yoojeen_vote' );
	$total		 = array_sum( $vote_info );
	wp_enqueue_script(
	'yoojeen-vote', plugins_url( 'js/yoojeen-vote.js', __FILE__ ), array( 'jquery' ) );
	// передаём данные в js script, объект будет называться voteInfo
	wp_localize_script( 'yoojeen-vote', 'voteInfo', array( 'myurl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'yoojeen' ), 'items' => $vote_info, 'total' => $total ) );
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

	$result = '<form method="post" id="vote_form">';

	$yoojeen_vote = array();

	// получаем данные из базы, если опция уже существует
	if ( $options = get_option( 'yoojeen_vote' ) ) {
		$total = array_sum( $options );
	} else {
		$total = 0;
	}
	$i = 0;
	foreach ( $atts as $item ) {
		$item = sanitize_text_field( $item );
		if ( $total ) {
			// переменная для установки ширины прогресс бара
			$progress_value = ($options[ 'yoojeen_vote' . $i ] / $total) * 100;
		} else {
			$progress_value = 0;
		}
		$result							 .= '<div>';
		$result							 .= "{$item}  <input type='radio' name='yoojeen_vote" . $i . "' value='yoojeen_vote" . $i . "' class='yoojeen_vote'><span>голосов: </span><b>" . ($options[ 'yoojeen_vote' . $i ] ? $options[ 'yoojeen_vote' . $i ] : 0) . "</b><br>";
		$result							 .= "<progress name='yoojeen_vote" . $i . "' value='" . $progress_value . "' max='100'></progress><br>";
		$result							 .= '</div>';
		$yoojeen_vote[ 'yoojeen_vote' . $i ] = 0;
		$i++;
	}
	$result .= '<input type="submit" name="vote_form">
	</form>
	<h4>Всего: <span id="total"></span></h4>';

	// добавляется только в первый раз, если существует, то ничего не делает
	add_option( 'yoojeen_vote', $yoojeen_vote );
	return $result;
}

// обработчик данных из ajax запроса
function vote_form_capture() {
	if ( isset( $_POST[ 'formData' ] ) ) {

		$res = array();

		$item = $_POST[ 'formData' ];

		$opt_arr = get_option( 'yoojeen_vote' );
		foreach ( $opt_arr as $key => $val ) {
			if ( $key == $item ) {
				$opt_arr[ $key ] ++;
			}
		}

		update_option( 'yoojeen_vote', $opt_arr );
		// передаём обновлённые данные в js script
		$res[ 'item_votes' ]		 = get_option( 'yoojeen_vote' );
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
