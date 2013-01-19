<?php

/**
* Registra y controla la página de propiedades
*/
class twbox_page_settings_class
{
	/**
	 * Nombre del grupo de settings
	 *
	 * @var string $group_name
	 **/
	var $settings_group_name = 'twbox-settings-group';

	/**
	 * Propiedades a registrar
	 *
	 * @var array $settings
	 **/
	var $settings = array (
		'screen_name' => null, // Nombre de usuario de twitter
		'width'  => null, // Anchura del cuadro
		'height' => null, // Altura (opcional) del cuadro.
		'show_faces' => null, // Si muestra o no los avatares de seguidores
		'faces_max_count' => 'intval', // Número máximo de caras a mostrar
		'interval' => 'intval', // Intervalo de refresco
		'last_refresh' =>null,
		'followers' => null,
		'face' => null,
		'name' => null,
		'followers_count' => 'intval'
	);

	/**
	 * Parámetros de la página.
	 *
	 * @var array $page
	 **/
	var $page = array(
		'name' => 'Opciones del like-box',
		'title' => 'Twitter, the like-box widget',
		'access' => 'manage_options',
		'slug' => 'twbox-settings-page',
	);

	/**
	 * undocumented class variable
	 *
	 * @var string
	 **/
	var $stylesheet = 'twbox-stylesheet';


	/**
	 * Constructor de la clase. Registra las acciones en el contexto
	 * de WordPress.
	 */
	function __construct()
	{
		// Registra las propiedades
		add_action( 'admin_init', array($this, 'register_settings' ));
		// Registra la página de propiedades
		add_action( 'admin_menu', array($this, 'admin_menu' ));
		// Carga la hoja de estilos
		add_action( 'admin_enqueue_scripts', array($this, 'add_stylesheet' ));
	}

	function add_stylesheet($hook) 
	{
	    wp_enqueue_style( $this->stylesheet );
	}


	/**
 	* Registra las propiedades del plugin
 	*/
	function register_settings()
	{
		// Registra las propiedades del plugin
		foreach ($this->settings as $setting => $sanitize) {
			register_setting( $this->settings_group_name, $setting, $sanitize);
		}

		// Registra la hoja de estilos
		wp_register_style( $this->stylesheet, plugins_url('twbox_settings.css', __FILE__));
	}


	/**
	 * Crea la página de opciones.
	 */
	function admin_menu () {
		$page = add_options_page(
			$this->page['name'], 
			$this->page['title'], 
			$this->page['access'], 
			$this->page['slug'], 
			array($this, 'settings_page')  // Vista de la página.
		);

		add_action( 'admin_print_styles-' . $page, array($this, 'add_stylesheet' ) );
	}

	/**
	 * Vista de la página de opciones.
	 */
	function  settings_page () {
		?>
			<h2>Opciones del like-box de Twitter</h2>
			<form id="setting_pages" action="options.php" method="post">

				<?php settings_fields( $this->settings_group_name ); ?>

				<div class="block">
					<label for="screen_name">Nombre de usuario:</label>
					<input name="screen_name" type="text" value="<?php echo get_option('screen_name'); ?>" />
				</div><!-- end of .block -->

				<div class="block">
					Visualización:
					<div id="lookup" style="margin-left: 10px">
						<table>
							<tr>
								<td><label for="width"><?php _e('Width') ?>:</label></td>
								<td><input class="thick_input" name="width" type="text" value="<?php echo get_option('width'); ?>" /> <span>(no incluye 12px de padding y borde)</span></td>
							</tr>
							<tr>
								<td><label for="height"><?php _e('Height') ?> <em>(opcional)</em>:</label></td>
								<td><input class="thick_input" name="height" type="text" value="<?php echo get_option('height'); ?>" /> <span>(no incluye 12px de padding y borde)</span></td>
							</tr>
							<tr>
								<td><label for="show_faces">Mostrar avatars:</label></td>
								<td><input name="show_faces" type="checkbox" value="1" <?php checked( '1', get_option('show_faces') ); ?> /></td>
							</tr>
							<tr>
								<td><label for="faces_max_count">Número máximo de avatars:</label></td>
								<td><input class="thick_input" name="faces_max_count" type="text" value="<?php echo get_option('faces_max_count'); ?>" /></td>
							</tr>
						</table>
						
					</div><!-- end of #lookup -->
				</div><!-- end of .block -->
					<label for="screen_name">Intervalo de caché:</label>
					<input class="thick_input" name="interval" type="text" value="<?php echo get_option('interval'); ?>" /> <?php _e('segundos') ?>
				<div class="block">

				</div><!-- end of .block -->

				<input type="hidden" name="last_refresh" value="0" />

			    <p>
			      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			    </p>

				</form>

		<?php 

	}

}

new twbox_page_settings_class;


/** End of tw-settings.php */