<?php


class twbox_class {

	/**
	 * Nombre de pantalla del usuario.
	 *
	 * @var string
	 **/
	var $screen_name;

	/**
	 * Ancho del cuadro
	 *
	 * @var string
	 **/
	var $width;

	/**
	 * Alto del cuadro
	 *
	 * @var string
	 **/
	var $height;

	/**
	 * Si se muestran los avatares de los seguidores.
	 *
	 * @var boolean
	 **/
	var $show_faces;

	/**
	 * Número máximo de avatares de seguidores
	 *
	 * @var int
	 **/
	var $faces_max_count;

	/**
	 * Intervalo de tiempo de refresco, en segundos.
	 *
	 * @var int
	 **/
	var $interval;

	/**
	 * Momento del último reresco.
	 *
	 * @var int
	 **/
	var $last_refresh;

	/**
	 * Array de seguidores
	 *
	 * @var mix
	 **/
	var $followers;

	/**
	 * Nombre del usuario
	 *
	 * @var string
	 **/
	var $name;

	/**
	 * URL de la imagen del usuario
	 *
	 * @var string
	 **/
	var $face;

	/**
	 * Constructor de la clase por defecto.
	 * Lee las propiedades de la clase desde las opciones de WP.
	 */
	function get_options()
	{
		$this->screen_name = get_option('screen_name', 'JorgeMiquelez');
		$this->width = get_option('width', '190px');
		$this->height = get_option('height', null);
		$this->show_faces = get_option('show_faces', 1);
		$this->faces_max_count = get_option('faces_max_count', 10);
		$this->interval = get_option('interval', 3600);

		$this->last_refresh = get_option('last_refresh', 0);
		$this->followers = get_option('followers', array());
		$this->followers_count = get_option('followers_count', 0);
		$this->name = get_option('name', '');
		$this->face = get_option('face', '');

		// Añade acción para iniciar el plugin
		add_action('plugins_loaded', array($this, 'init'));

		// Carga la hoja de estilos
		add_action( 'wp_print_styles', array($this, 'add_stylesheet' ));

	}

	/**
	 * Incluye la hoja de estilos de los settings.
	 */
	function add_stylesheet() 
	{
		wp_enqueue_style('language-link-css', plugin_dir_url(__FILE__) . 'twbox.css');
	}


	/**
	 * Constructor de la clase.
	 *
	 * @param array $val Array con los datos de usuario obtenidos de un POST a Twitter.
	 */
	function get_properties($val)
	{
		$this->screen_name = $val['screen_name'];
		$this->name = $val['name'];
		$this->face = $val['profile_image_url_https'];
	}

	/**
	 * Registra el plugin como widget.
	 */
	function init()
	{
	     register_sidebar_widget('Twitter, the like-box widget', array($this, 'execute'));
	}

	/**
	 * Función principal del widget. Obtiene los datos del usuario y los muestra en el widget.
	 */
	function execute()
	{
		// Solicitudes multitudinarias en menos de una hora pueden
		// ser bloqueadas por twitter, según informa en su sitio web.
		if ( $this->last_refresh < ( mktime() - $this->interval ) )
		{

			// Inicialización
			$this->get_user_data();
			$this->last_refresh = mktime();

			$this->update_options();

		}

		// Muestra el cuadro.
		$this->view();
	}

	private function get_user_data()
	{
		// Datos del usuario
		$url = 'https://api.twitter.com/1/users/lookup.json?screen_name='.$this->screen_name;
		$array = json_decode(file_get_contents($url), true);

		$this->name = $array[0]['name'];
		$this->face = $array[0]['profile_image_url_https'];
		$this->followers_count = $array[0]['followers_count'];

		// Ids de los seguidores
		$url = 'https://api.twitter.com/1/followers/ids.json?cursor=-1&screen_name='.$this->screen_name;
		$array = json_decode(file_get_contents($url), true);

		$ids = implode(',', self::select_user_id($array['ids'], min(100, $this->faces_max_count)));

		// Datos de los seguidores
		$url = 'https://api.twitter.com/1/users/lookup.json?user_id='.$ids;
		$array = json_decode(file_get_contents($url), true);

		$this->followers = array();

		foreach ($array as $follower) {
			$f = new twbox_class();
			$f->get_properties($follower);
			$this->followers[] = $f;
		}

	}

	private function update_options()
	{
		update_option('followers_count', $this->followers_count);
		update_option('followers', $this->followers);
		update_option('last_refresh', $this->last_refresh);
		update_option('face', $this->face);
		update_option('name', $this->name);
	}

	/**
	 * Escoje un subconjunto de elementos al azar de un array.
	 *
	 * @param array $users Array de partida.
	 * @param int $count Número de elementos máximo del subconjunto.
	 * @return array Un array con el subconjunto de elementos.
	 **/
	private static function select_user_id($users, $count)
	{
		if (2 > count($users)) return $users;

		$result = array();
		$rand_keys = array_rand($users, min($count, count($users)));

		foreach ($rand_keys as $key) {
			$result[] = $users[$key];
		}

		return $result;
	}


	/**
	 * Muestra la vista del widget.
	 */
	private function view()
	{
		?>
		<div class="clearfix"></div>

		<div id="tw_wg_frame" style="width: <?php echo $this->width.';'; if ( isset( $this->height ) && ( $this->height ) ) echo ' height: '. $this->height.';'; ?>">

			<div id="tw_wg_header">

				<?php if ($this->show_faces) : ?>
					<div id="tw_img_profile_frame">
						<a href="https://twitter.com/<?php echo $this->screen_name; ?>" target="_blank">
							<img id="tw_img_profile" class="img_profile" src="<?php echo $this->face; ?>" />
						</a>
					</div><!-- end of #tw_img_profile_frame -->
				<?php endif; ?>

				<div id="tw_profile">
					<div id="tw_txt_profile">
						<a href="https://twitter.com/<?php echo $this->screen_name; ?>" target="_blank">
							<strong><?php echo $this->name; ?></strong>
						</a>
						<br />
						en twitter.
					</div><!-- end of #tw_txt_profile -->
					<div id="tw_button_profile">
						<a href="https://twitter.com/<?php echo $this->screen_name; ?>" class="twitter-follow-button" data-show-count="false" data-lang="es" data-show-screen-name="false">
							Seguir a @<?php echo $this->screen_name; ?></a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
					</div><!-- end of #tw_button_profile -->

				</div><!-- end of #tw_profile -->

				<div class="clearfix"></div>
			</div><!-- end of #tw_wg_header -->

			<div id="tw_txt_followers">
				<?php
				echo number_format ( $this->followers_count , 0 , ',' , '.' ).' persona'. (($this->followers_count != 1) ? 's':'') .' está'. (($this->followers_count != 1) ? 'n':'') .' siguiendo a
				<strong>'.$this->screen_name.'</strong>';
				?>
			</div><!-- end of #tw_txt_followers -->

			<div id="tw_img_followers">
				<?php
					if ($this->show_faces && $this->followers && count($this->followers))
					{
						$number_faces = 0;
						foreach ($this->followers as $follower) :
				?>
							<a href="https://twitter.com/<?php echo $follower->screen_name; ?>" target="_blank">
								<img class="img_profile" src="<?php echo $follower->face; ?>" title="<?php echo $follower->name; ?>" />
							</a>

				<?php
						$number_faces++;

						if ($number_faces >= $this->faces_max_count) break;
						endforeach;
					}
				?>
			</div><!-- end of #tw_img_followers -->

			<div class="clearfix"></div>
		</div><!-- end of #tw_wg_frame -->
		<? 
	}



}




/** End of twbox_settings  **/