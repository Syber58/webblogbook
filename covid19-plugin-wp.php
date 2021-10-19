<?php
/*
  Plugin Name: COVID-19 Live Statistics
  Plugin URI: https://1.envato.market/nyc
  Description: The plugin provided a shortcode [COVID19] to display Live Statistics, you can use the shortcode in posts or pages.
  Version: 1.0.3
  Author: NYCreatis
  Author URI: https://nycreatis.com/
  Domain Path: languages
  Text Domain: covid
 */
 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Covid' ) ) {
	class Covid {


		function __construct() {
	
			if ( ! defined( 'COVID_URL' ) ) {
				define( 'COVID_URL', plugin_dir_url( __FILE__ ) );
			}
			if ( ! defined( 'COVID_PATH' ) ) {
				define( 'COVID_PATH', plugin_dir_path( __FILE__ ) );
			}

		function covid_shortcode(){
				  ob_start();
				  //include the specified file
				  include(dirname(__FILE__).'/includes/index.php');
				  //assign the file output to $content variable and clean buffer
				  $content = ob_get_clean();
				  //return the $content
				  //return is important for the output to appear at the correct position
				  //in the content
				  return $content;
			}
			add_shortcode('COVID19', 'covid_shortcode'); 
			
				add_action( 'init', array( $this, 'load_textdomain' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
				add_action( 'admin_menu', array( $this, 'register_custom_menu_page' ) );
			
			add_shortcode( 'COVID19-WIDGET', array($this, 'shortcode') );
			$this->ui7l5aq6Kmx0();
		
		

			$getOptionAll = get_option('covid_all');
			$getOptionCountries = get_option('covid_country');

			if (!$getOptionCountries) {
				$countries = $this->getData(true);
				update_option( 'covid_country', $countries );
			}

			if (!$getOptionAll) {
				$all = $this->getData(false);
				update_option( 'covid_all', $all );
			}
		}

		/*
		 * Load textdomain
		 * @since 1.0
		 */
		function load_textdomain() {
			load_plugin_textdomain( 'covid19', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}
		
		/**
		 * Register a custom menu page.
		 */
		function register_custom_menu_page(){
			add_options_page( 
				esc_attr__( 'Covid-19 Options', 'covid' ),
				esc_attr__( 'Covid-19 Options', 'covid' ),
				'manage_options',
				'covid-plugin-options',
				array($this, 'true_option_page')
			); 
		}
		
		
		public function admin_enqueue_assets() {
			wp_enqueue_script( 'covid-admin', COVID_URL . 'assets/js/admin-script.js', array( 'jquery' ), '', true );
			wp_enqueue_style( 'covid', COVID_URL . 'assets/admin-style.css', array(), '1.0.3' );
		}
		
 
		function ui7l5aq6Kmx0(){
			add_filter( 'cron_schedules', array( $this, 'add_wp_cron_schedule' ) );

			if ( ! wp_next_scheduled( 'covid_u5GtURy' ) ) {

				$next_timestamp = wp_next_scheduled( 'covid_u5GtURy' );
				if ( $next_timestamp ) {
					wp_unschedule_event( $next_timestamp, 'covid_u5GtURy' );
				}

				wp_schedule_event( time(), 'every_10minute', 'covid_u5GtURy' );
			}


			add_action( 'covid_u5GtURy', array($this,'getDatafromAPI') );
		}

		function add_wp_cron_schedule( $schedules ) {

			$schedules['every_10minute'] = array(
				'interval' => 10*60,
				'display'  => esc_attr__( 'Every 10 minutes', 'covid' ),
			);
	
			return $schedules;
		}
		
		
		function getDatafromAPI() {
			$all = $this->getData(false);
			$countries = $this->getData(true);
			$getOptionAll = get_option('covid_all');
			$getOptionCountries = get_option('covid_country');

			if ($getOptionAll) {
				update_option( 'covid_all', $all );
			} else {
				add_option('covid_all', $all);
			}

			if ($getOptionCountries) {
				update_option( 'covid_country', $countries );
			} else {
				add_option('covid_country', $countries);
			}
			
		}
		
		
		function getData($countryCode = false){
			$endPoint 	= 'https://corona.lmao.ninja/';
			$methodPath = 'all';
			if ($countryCode) {
				$methodPath = 'countries';
			}
			$endPoint = $endPoint.$methodPath;

			$args = array(
				'timeout' => 60
			); 

			$request = wp_remote_get($endPoint, $args);
			$body = wp_remote_retrieve_body( $request );
			$data = json_decode( $body );

			return $data;
		}
		
		function shortcode( $atts ){
			$params = shortcode_atts( array(
				'title_widget' => esc_attr__( 'World', 'covid' ),
				'country' => null,
				'confirmed_title' => esc_attr__( 'Confirmed', 'covid' ),
				'deaths_title' => esc_attr__( 'Deaths', 'covid' ),
				'recovered_title' => esc_attr__( 'Recovered', 'covid' )
			), $atts );

			$data = get_option('covid_all');

			if ($params['country']) {
				$data = get_option('covid_country');
				$new_array = array_filter($data, function($obj) use($params) {
					if ($obj->country === $params['country']) {
						return true;
					}
					return false;
				});

				if ($new_array) {
					$data = reset($new_array);
				}
				
			}

			ob_start();
			echo $this->render_item($params, $data);
			return ob_get_clean();
		}
		
		function render_item($params, $data){
			wp_enqueue_style( 'covid' );
			ob_start();
			?><?php $all_options = get_option( 'covid_options' );?>
			<div class="covid19-card <?php echo $all_options['cov_theme'];?> <?php if($all_options['cov_rtl']==!$checked) echo 'rtl_enable'; ?>" style="font-family:<?php echo $all_options['cov_font'];?>">
				<h4 class="covid19-title-big"><?php echo esc_html(isset($params['title_widget']) ? $params['title_widget'] : ''); ?></h4>
				<div class="covid19-row">
					<div class="covid19-col covid19-cases">
						<div class="covid19-num"><?php echo esc_html($data->cases); ?></div>
						<div class="covid19-title"><?php echo esc_html($params['confirmed_title']); ?></div>
					</div>
					<div class="covid19-col covid19-deaths">
						<div class="covid19-num"><?php echo esc_html($data->deaths); ?></div>
						<div class="covid19-title"><?php echo esc_html($params['deaths_title']); ?></div>
					</div>
					<div class="covid19-col covid19-recovered">
						<div class="covid19-num"><?php echo esc_html($data->recovered); ?></div>
						<div class="covid19-title"><?php echo esc_html($params['recovered_title']); ?></div>
					</div>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}
		
		/**
		 * Callback
		 */ 
		function true_option_page(){
			global $true_page;
			?><div class="wrap">
				<h2><?php echo esc_html__( 'COVID-19 Options', 'covid' );?></h2>
				<div class="info"><?php echo _e( '<strong>What are the sources of data informing the dashboard?<br></strong>The data sources include the <a href="https://www.who.int/emergencies/diseases/novel-coronavirus-2019/situation-reports" target="_blank">World Health Organization</a>, the <a href="https://www.cdc.gov/coronavirus/2019-ncov/index.html" target="_blank">U.S. Centers for Disease Control and Prevention</a>, the <a href="https://www.ecdc.europa.eu/en/geographical-distribution-2019-ncov-cases" target="_blank">European Center for Disease Prevention and Control</a>, the <a href="http://www.nhc.gov.cn/xcs/yqtb/list_gzbd.shtml" target="_blank">National Health Commission of the People’s Republic of China</a>, and the <a href="https://ncov.dxy.cn/ncovh5/view/pneumonia?scene=2&amp;clicktime=1579582238&amp;enterid=1579582238&amp;from=singlemessage&amp;isappinstalled=0" target="_blank">DXY</a>, one of the world’s largest online communities for physicians, health care professionals, pharmacies and facilities.<br>2019 Novel Coronavirus COVID-19 (2019-nCoV) Data Repository by Johns Hopkins CSSE: <a href="https://github.com/CSSEGISandData/COVID-19" target="_blank">https://github.com/CSSEGISandData/COVID-19</a>', 'covid' );?></div>
				<div class="notify"><?php echo _e( 'The plugin provided a shortcode <b>[COVID19]</b> to display Live Statistics, you can use the shortcode in posts or pages.', 'covid' );?></div>
				<form method="post" enctype="multipart/form-data" action="options.php">
					<?php 
					settings_fields('covid_options');
					do_settings_sections($true_page);
					?>
					<p class="submit">  
						<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />  
					</p>
				</form><hr>
			</div>
			
		<?php $data = get_option('covid_country');?>

		<div id="covid19">
			<h1><?php esc_html_e('Widget Shortcode ', 'covid'); ?></h1>
			<h2><?php esc_html_e('Countries:', 'covid'); ?></h2>
			<select name="covid_country">
				<option value=""><?php esc_html_e('All Countries - World Statistics', 'covid'); ?></option>
				<?php
				foreach ($data as $item) {
					echo '<option value="'.$item->country.'">'.$item->country.'</option>';
				}
				?>
			</select>
			<p><?php _e('Paste this shortcode into <b>Text widget</b>.', 'covid'); ?></p>
			<p id="covidsh" class="covid_shortcode"><?php esc_html_e('[COVID19-WIDGET]', 'covid'); ?></p>
			<h2><?php esc_html_e('Options:', 'covid'); ?></h2>
			<ul class="covid-attributes">
				<li><strong><?php esc_html_e('title_widget:', 'covid'); ?></strong> <?php esc_html_e('Title of Widget', 'covid'); ?></li>
				<li><strong><?php esc_html_e('confirmed_title:', 'covid'); ?></strong> <?php esc_html_e('Label Confirmed', 'covid'); ?></li>
				<li><strong><?php esc_html_e('deaths_title:', 'covid'); ?></strong> <?php esc_html_e('Label Deaths', 'covid'); ?></li>
				<li><strong><?php esc_html_e('recovered_title:', 'covid'); ?></strong> <?php esc_html_e('Label Recovered', 'covid'); ?></li>
			</ul>
			<h3><?php esc_html_e('Examples:', 'covid'); ?></h3>
			<p class="covid_shortcode"><?php esc_html_e('[COVID19-WIDGET country="USA" title_widget="USA 🇺🇸" confirmed_title="Confirmed" deaths_title="Deaths" recovered_title="Recovered"]', 'covid'); ?></p>
			<p class="covid_shortcode"><?php esc_html_e('[COVID19-WIDGET country="Italy" title_widget="Italy 🇮🇹" confirmed_title="Infetto" deaths_title="Passa" recovered_title="Recuperare"]', 'covid'); ?></p>
			<p class="covid_shortcode"><?php esc_html_e('[COVID19-WIDGET country="Russia" title_widget="Russia 🇷🇺" confirmed_title="Инфицировано" deaths_title="Скончалось" recovered_title="Выздоровело"]', 'covid'); ?></p>
		</div>
			
			<?php
		}
		}
			new Covid();
		}

		add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'covid_add_plugin_page_contact_link');
		function covid_add_plugin_page_contact_link( $links ) {
			$links[] = '<a href="https://nycreatis.com/support/" target="_blank">' . __('Get Help') . '</a>';
			return $links;
		}

		add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'covid_add_plugin_page_settings_link');
		function covid_add_plugin_page_settings_link( $links ) {
			$links[] = '<a href="' .
				admin_url( 'options-general.php?page=covid-plugin-options' ) .
				'">' . __('Settings') . '</a>';
			return $links;
		}

		function true_option_settings() {
			global $true_page;
			// ( true_validate_settings() )
			register_setting( 'covid_options', 'covid_options', 'true_validate_settings' ); // covid_options
		 
			// Add section
			add_settings_section( 'true_section_1', esc_html__( 'Options', 'covid' ), '', $true_page );

			$true_field_params = array(
				'type'      => 'text', // type
				'id'        => 'cov_title',
				'value'        => esc_html__( 'An interactive web-based dashboard to track COVID-19 in real time.', 'covid' ),
				'desc'      => esc_html__( 'Default: An interactive web-based dashboard to track COVID-19 in real time.', 'covid' ), // description
				'label_for' => 'cov_title'
			);
			add_settings_field( 'my_text_field', esc_html__( 'Title', 'covid' ), 'true_option_display_settings', $true_page, 'true_section_1', $true_field_params );
		 
			$true_field_params = array(
				'type'      => 'textarea',
				'id'        => 'cov_desc',
				'value'        => esc_html__( 'To identify new cases, we monitor various twitter feeds, online news services, and direct communication sent through the dashboard.', 'covid' ),
				'desc'      => esc_html__( 'Optional', 'covid' )
			);
			add_settings_field( 'cov_desc_field', esc_html__( 'Description', 'covid' ), 'true_option_display_settings', $true_page, 'true_section_1', $true_field_params );
		 
			add_settings_section( 'true_section_2', esc_html__( 'Customization', 'covid' ), '', $true_page );

			$true_field_params = array(
				'type'      => 'checkbox',
				'id'        => 'cov_countries_hide',
				'desc'      => esc_html__( 'Hide', 'covid' )
			);
			add_settings_field( 'cov_countries_hide_field', esc_html__( 'List of countries', 'covid' ), 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );
			
			$true_field_params = array(
				'type'      => 'checkbox',
				'id'        => 'cov_map_hide',
				'desc'      => esc_html__( 'Hide', 'covid' )
			);
			add_settings_field( 'cov_map_hide_field', esc_html__( 'World Map', 'covid' ), 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );

			$true_field_params = array(
				'type'      => 'checkbox',
				'id'        => 'cov_rtl',
				'desc'      => esc_html__( 'Enable', 'covid' )
			);
			add_settings_field( 'cov_rtl_field', esc_html__( 'RTL support', 'covid' ), 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );


			$true_field_params = array(
				'type'      => 'select',
				'id'        => 'cov_theme',
				'desc'      => '',
				'vals'		=> array( 'dark_theme' => esc_html__( 'Dark', 'covid' ), 'light_theme' => esc_html__( 'Light', 'covid' ))
			);
			add_settings_field( 'cov_theme_field', esc_html__( 'Theme', 'covid' ), 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );
			
			$true_field_params = array(
				'type'      => 'select',
				'id'        => 'cov_font',
				'desc'      => '',
				'vals'		=> array( '-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Ubuntu,Helvetica Neue,sans-serif' => 'Default', 'inherit' => 'As on the website', 'Arial,Helvetica,sans-serif' => 'Arial, Helvetica', 'Tahoma,Geneva,sans-serif' => 'Tahoma, Geneva', 'Trebuchet MS, Helvetica,sans-serif' => 'Trebuchet MS, Helvetica', 'Verdana,Geneva,sans-serif' => 'Verdana, Geneva', 'Georgia,sans-serif' => 'Georgia', 'Palatino,sans-serif' => 'Palatino', 'Times New Roman,sans-serif' => 'Times New Roman')
			);
			add_settings_field( 'cov_font_field', esc_html__( 'Font', 'covid' ), 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );
		 
			$true_field_params = array(
				'type'      => 'textarea',
				'id'        => 'cov_css',
				'desc'      => esc_html__( 'Without &lt;style&gt; tags', 'covid' )
			);
			add_settings_field( 'cov_css_field', esc_html__( 'Custom CSS', 'covid' ), 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );
		}
		add_action( 'admin_init', 'true_option_settings' );
		 
		/*
		 * Show fields
		 */
		function true_option_display_settings($args) {
			extract( $args );
		 
			$option_name = 'covid_options';
		 
			$o = get_option( $option_name );
		 
			switch ( $type ) {  
				case 'text':  
					$o[$id] = esc_attr( stripslashes($o[$id]) );
					echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='$o[$id]' />";  
					echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
				break;
				case 'textarea':  
					$o[$id] = esc_attr( stripslashes($o[$id]) );
					echo "<textarea class='code regular-text' cols='12' rows='5' type='text' id='$id' name='" . $option_name . "[$id]'>$o[$id]</textarea>";  
					echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
				break;
				case 'checkbox':
					$checked = ($o[$id] == 'on') ? " checked='checked'" :  '';  
					echo "<label><input type='checkbox' id='$id' name='" . $option_name . "[$id]' $checked /> ";  
					echo ($desc != '') ? $desc : "";
					echo "</label>";  
				break;
				case 'select':
					echo "<select id='$id' name='" . $option_name . "[$id]'>";
					foreach($vals as $v=>$l){
						$selected = ($o[$id] == $v) ? "selected='selected'" : '';  
						echo "<option value='$v' $selected>$l</option>";
					}
					echo ($desc != '') ? $desc : "";
					echo "</select>";  
				break;
				case 'radio':
					echo "<fieldset>";
					foreach($vals as $v=>$l){
						$checked = ($o[$id] == $v) ? "checked='checked'" : '';  
						echo "<label><input type='radio' name='" . $option_name . "[$id]' value='$v' $checked />$l</label><br />";
					}
					echo "</fieldset>";  
				break; 
			}
		}
		 
		/*
		 * Check fields
		 */
		function true_validate_settings($input) {
			foreach($input as $k => $v) {
				$valid_input[$k] = trim($v);
			}
			return $valid_input;
		}
