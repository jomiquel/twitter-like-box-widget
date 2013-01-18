<?php
/*
Plugin Name: Twitter, the like-box widget
Plugin URI: http://www.jomiquel.net
Description: Muestra una caja con un enlace para seguir a un usuario de twitter, 
junto con el número de seguidores e imágenes de los mismos.
Author: Jorge Miquélez
Version: 0.1
Author URI: http://www.jomiquel.net
*/


/**
 * Muestra el valor de una variable en el HTML, a modo de comentarios.
 * No es necesaria para el funcionamiento del widget.
 *
 * @param mixed $val Variable a mostrar.
 * @param string $caption Texto descriptivo de la variable.
 */
function show_value($val, $caption=null)
{
	echo "<!-- \n$caption\n";
	print_r($val);
	echo "\n -->\n";
}

/**
 * Inicializa el hash de usuario 
 */
function tw_init()
{
	return array('screen_name' => 'JorgeMiquelez');
}

/**
 * Devuelve un hash con las propiedades por defecto del frame.
 */
function tw_init_frame()
{
	return array(
		'width' => '195px',
		'show_faces' => true,
		'faces_max_count' => 8
		);
}


/**
 * Función principal del widget. Obtiene los datos del usuario y los muestra en el widget.
 */
function tw_the_widget()
{

	$tw = get_option("twitterfollowerscount");

	// Solicitudes multitudinarias en menos de una hora pueden
	// ser bloqueadas por twitter, según informa en su sitio web.
	if ( $tw['lastcheck'] < ( mktime() - 3600 ) || (! isset($tw['frame'])) )
	{

		// Inicialización
		$tw_array = get_user_data(tw_init());
		$tw = $tw_array[0];
		$tw['frame'] = tw_init_frame();

		$tw['followers'] = get_user_data(get_user_followers_ids($tw));
	
		$tw['lastcheck'] = mktime();

		update_option( "twitterfollowerscount", $tw );

	}

	// Muestra el cuadro.
	tw_front_end($tw);

}
 

/**
 * Devuelve la URL específica de usuario para obtener sus datos.
 *
 * @param mixed $tw Usuario con el user_id o screen_name relleno.
 * También puede ser una cadena con los ids de varios usuarios, separados por comas.
 * @return URL para consultar los datos del usuario.
 */
function get_url_data($tw)
{
	$url = 'https://api.twitter.com/1/users/lookup.json?';

	if (is_array($tw)) 
	{
		// Se trata de un hash de usuario.
		if ($tw['id']) return $url.'user_id='.$tw['id'];
		else return $url.'screen_name='.$tw['screen_name'];
	}
	else
	{
		// Se trata de una cadena con los ids concatenados
		return $url.'user_id='.$tw;
	}
}


/**
 * Devuelve la URL específica de usuario para obtener sus datos.
 *
 * @param mixed $tw Usuario con el user_id o screen_name relleno.
 * @return URL para consultar los datos del usuario.
 */
function get_url_followers($tw)
{
	$url = 'https://api.twitter.com/1/followers/ids.json?cursor=-1&';
	if ($tw['id']) return $url.'user_id='.$tw['id'];
	else return $url.'screen_name='.$tw['screen_name'];
}


/** 
 * Devuelve un array con los datos de los usuarios.
 *
 * @param mixed $tw Usuario con el user_id o screen_name relleno.
 */
function get_user_data($tw)
{
	return json_decode(file_get_contents(get_url_data($tw)), true);
}


/** 
 * Devuelve un array con los id de los followers.
 *
 * @param mixed $tw Usuario con el user_id o screen_name relleno.
 */
function get_user_followers_ids($tw)
{
	$array = json_decode(file_get_contents(get_url_followers($tw)), true);

	return implode(',', $array['ids']);
}


/**
 * Vuelca el front-end del widget sobre el documento.
 *
 * @param mixed $tw Hash del usuario.
 */
function tw_front_end($tw) {
	echo ''
		.'<div class="clearfix"></div>'
		.'<style type="text/css">'
//		.'*{margin:0;border:0;padding:0;}html,body,div,iframe,a,em,font,img,strong,ol,ul,li{margin:0;padding:0;border:0;outline:0;font-weight:inherit;font-style:inherit;font-size:100%;font-family:inherit;vertical-align:baseline;}body{font-family: \'lucida grande\',tahoma,verdana,arial,sans-serif;;font-size:11px;line-height:1;color:black;background:white;}a{text-decoration:none;}a:hover{text-decoration:underline;}div.clearfix{height:1px;width:1px;display:block;clear:both;content:".";}strong{font-weight:bold;}div{padding:2px;}#tw_wg_frame{margin:5px;width:'.$tw['frame']['width'].';padding:5px;border:1px solid #0080ff;'.(($tw['frame']['height']) ? $tw['frame']['height'].';': '').'overflow:hidden;}#tw_wg_header{border-bottom:1px solid #ccc;}#tw_img_profile_frame,#tw_txt_profile{float:left;}#tw_txt_followers{padding-top:10px;}.img_profile{float:left;padding:0 5px 5px 0;}'
		.'#tw_wg_frame{font-family: \'lucida grande\',tahoma,verdana,arial,sans-serif;font-size:11px;line-height:1;color:black;background:white;} #tw_wg_frame a{text-decoration:none;} #tw_wg_frame a:hover{text-decoration:underline;} div.clearfix{height:1px;width:1px;display:block;clear:both;content:".";} #tw_wg_frame strong{font-weight:bold;} #tw_wg_frame div{padding:2px;} #tw_wg_frame{margin:5px;width:'.$tw['frame']['width'].';padding:5px;border:1px solid #0080ff;'.(isset($tw['frame']['height']) ? $tw['frame']['height'].';': '').'overflow:hidden;} #tw_wg_header{border-bottom:1px solid #ccc;} #tw_img_profile_frame, #tw_profile{float:left;} #tw_txt_profile{padding-bottom: 4px;} #tw_txt_followers{padding-top:10px;}.img_profile{float:left;padding:0 5px 5px 0;}'
		.'</style>'
		.'<div id="tw_wg_frame" class="plugin webkit chrome Locale_es_ES" style="">'
		.'<div id="tw_wg_header">'
		.'<div id="tw_img_profile_frame">'
		.'<img id="tw_img_profile" class="img_profile" src="'.$tw['profile_image_url_https'].'"" />'
		.'</div>'
		.'<div id="tw_profile">'
		.'<div id="tw_txt_profile">'
		.'<a href="https://twitter.com/JorgeMiquelez" target="_blank"><strong>'.$tw['screen_name'].'</strong></a><br />en twitter.'
		.'</div>'
		.'<div id="tw_button_profile">'
		.'<a href="https://twitter.com/'.$tw['screen_name'].'" class="twitter-follow-button" data-show-count="false" data-lang="es" data-show-screen-name="false">Seguir a @'.$tw['screen_name'].'</a>'
		.'<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>'
		.'</div>'
		.'</div>'
		.'<div class="clearfix"></div>'
		.'</div>'
		.'<div id="tw_txt_followers">'
		.''.$tw['followers_count'].' persona'. (($tw['followers_count'] != 1) ? 's':'') .' está'. (($tw['followers_count'] != 1) ? 'n':'') .' siguiendo a <strong>'.$tw['screen_name'].'</strong>'
		.'</div>'
		.'<div id="tw_img_followers">'
		.'';

	if ($tw['frame']['show_faces'])
	{
		$number_faces = 0;
		foreach ($tw['followers'] as $follower) {
			echo ''
				.'<a href="https://twitter.com/'.$follower['screen_name'].'" target="_blank">'
				.'<img class="img_profile" src="'.$follower['profile_image_url_https'].'" title="'.$follower['name'].'" />'
				.'</a>'
				.'';
			$number_faces++;

			if ($number_faces >= $tw['frame']['faces_max_count']) break;
		}
	}

	echo ''
		.'</div>'
		.'<div class="clearfix"></div>'
		.'</div>'
		.'';
}


// Añade una acción al wp_head para incluir las imágenes del post.
add_action("plugins_loaded", "init_tw_the_widget");

/**
 * Registra el plugin como widget.
 */
function init_tw_the_widget(){
     register_sidebar_widget("Twitter, the like-box widget", "tw_the_widget");
}


?>