<?php 
Class Sparkle_WE_DP_Theme_Updater_Class{
	private $remote_api_url;
	private $request_data;
	private $response_key;
	private $theme_slug;
	private $license_key;
	private $version;
	private $author;

	function __construct( $args = array() ) {
		
		$defaults = array(
			'remote_api_url' => '',
			'request_data'   => array(),
			'theme_slug'     => get_template(), // use get_stylesheet() for child theme updates
			'item_name'      => '',
			'license'        => '',
			'version'        => '',
			'author'         => '',
			'product_id'     => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$this->license        = $args['license'];
		$this->item_name      = $args['item_name'];
		$this->version        = $args['version'];
		$this->theme_slug     = sanitize_key( $args['theme_slug'] );
		$this->author         = $args['author'];
		$this->remote_api_url = $args['remote_api_url'];
		$this->product_id     = $args['product_id'];
		
		add_filter( 'site_transient_update_themes', array( $this, 'swedl_theme_check_for_update' ) );
		add_filter( 'delete_site_transient_update_themes', array( $this, 'swedl_delete_theme_update_transient' ) );
		add_action( 'load-update-core.php',                array( $this, 'swedl_delete_theme_update_transient' ) );
		
		add_action( 'after_switch_theme', 					array($this, 'swedl_redirect_license_page'));

	}
	
	function swedl_redirect_license_page(){
		wp_redirect( admin_url( 'themes.php?page=' . $this->theme_slug . '-license' ) );
	}

	function swedl_delete_theme_update_transient(){
		delete_transient( 'sparkle_fetch_theme_details_from_cron_transient' );
	}

	function swedl_theme_check_for_update ( $transient ) {
		global $pagenow, $typenow;
		
		// Check Theme is active or not.
	    if( empty( $transient->checked[$this->theme_slug] ) )
	        return $transient;

		$transient_time = 12 * HOUR_IN_SECONDS;
		if ( $pagenow == 'themes.php' ) $transient_time = 30;
		
		
	    //check transient if there is transient return data from transient
	    if ( false === ( get_transient( 'sparkle_fetch_theme_details_from_cron_transient' ) ) ) {
	    	$data = $this->theme_fetch_data_of_latest_version();
			set_transient( 'sparkle_fetch_theme_details_from_cron_transient', $data, $transient_time  );
	    }else{
	    	$data = get_transient( 'sparkle_fetch_theme_details_from_cron_transient' );
	    }
		
		if( isset( $data->new_version ) ){
		    if ( version_compare( $transient->checked[$this->theme_slug], $data->new_version, '<' ) ) {
		        $transient->response[$this->theme_slug] = (array) $data;
		        add_action( 'admin_notices', array( $this, 'theme_update_admin_notice' ) );
		    }
		    return $transient;

		}else{
		    return $transient;

		}
	}

	function theme_fetch_data_of_latest_version() {
	    $params = array(
			// for license validation we need valid product id and license key
			'body' => array(
				'product_id' 	=> $this->product_id,
				'license_key'   => $this->license,
				'site_url' 		=> site_url() 	
			),
		);
		
		// Make the POST request
		$api_request_url = $this->remote_api_url.'/wp-json/sparkleddl/v1/theme_update/';
		$request = wp_remote_post( $api_request_url, $params );
		if ( !is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
			
			return json_decode($request['body']); 
		}
		return false;
	}

	function theme_update_admin_notice(){ ?>
		<div class="notice notice-warning notice-alt is-dismissible">
			<?php
			$data = get_transient( 'sparkle_fetch_theme_details_from_cron_transient' );
			$update_url     = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $this->theme_slug ), 'upgrade-theme_' . $this->theme_slug );
			$update_onclick = ' onclick="if ( confirm(\'' . esc_js( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update." ) . '\') ) {return true;}return false;"';

			echo '<p id="update-msg">';
			printf(
				'<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>',
				$this->item_name,
				$data->new_version,
				'#TB_inline?width=640&amp;inlineId=' . $this->theme_slug . '_changelog',
				$this->item_name,
				$update_url,
				$update_onclick
			);
			echo '</p>';
			echo '<div id="' . $this->theme_slug . '_' . 'changelog" style="display:none;">';
			echo wpautop( $data->change_log );
			echo '</div>';
			?>
		</div>
		<?php
	}
}