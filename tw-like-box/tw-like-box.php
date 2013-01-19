<?php
/*
Plugin Name: Twitter, the like-box widget
Plugin URI: http://www.jomiquel.net/?p=791
Description: Muestra una caja con un enlace para seguir a un usuario de twitter, 
junto con el número de seguidores e imágenes de los mismos.
Author: Jorge Miquélez
Version: 1.0
Author URI: http://www.jomiquel.net
*/

// Se incluyen las clases.
include 'twbox_page_settings.php';
include 'twbox.php';


// Se inicializa el TwitterBox.
$u = new twbox_class();
$u->get_options();


/** End of file tw-like-box **/