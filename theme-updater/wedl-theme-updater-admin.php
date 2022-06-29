<?php
Class Sparkle_WE_DP_Theme_Updater_Admin{
	/**
	 * Variables required for the theme updater
	 *
	 * @since 1.0.0
	 * @type string
	 * @package Sparkle_WEDL_Sample_Theme_Updater
	 */
	 protected $remote_api_url = null;
	 protected $theme_slug = null;
	 protected $version = null;
	 protected $author = null;
	 protected $download_id = null;
	 protected $renew_url = null;
	 protected $product_id = null;
	 protected $site_url = null;

	function __construct( $config ){

		$config = wp_parse_args(
			$config,
			array(
				'theme_slug'     => get_template(),
				'item_name'      => '',
				'license'        => '',
				'version'        => '',
				'author'         => '',
				'download_id'    => '',
				'renew_url'      => '',
				'product_id'        => '',
				'site_url' 		 => get_site_url()
			)
		);

		// Set config arguments
		$this->remote_api_url = $config['remote_api_url'];
		$this->item_name      = $config['item_name'];
		$this->theme_slug     = sanitize_key( $config['theme_slug'] );
		$this->version        = $config['version'];
		$this->author         = $config['author'];
		$this->download_id    = $config['download_id'];
		$this->renew_url      = $config['renew_url'];
		$this->product_id     = $config['product_id'];
		$this->site_url       = $config['site_url'];

		add_action( 'init', array( $this, 'swedl_theme_updater' ) );

		add_action( 'admin_init', array( $this, 'register_options' ) );

		add_action( 'admin_init', array( $this, 'swedl_sample_activate_license') );
		add_action( 'admin_init', array( $this, 'swedl_sample_deactivate_license' ) );
		add_action( 'admin_init', array( $this, 'swedl_sample_delete_license' ) );
		add_action( 'admin_menu', array( $this, 'swedl_sample_theme_license_menu' ) );
	}

	function swedl_theme_updater(){

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( !class_exists( 'Sparkle_WE_DP_Theme_Updater_Class' ) ) {
			include( dirname( __FILE__ ) . '/wedl-theme-updater-class.php' );
		}

		new Sparkle_WE_DP_Theme_Updater_Class(
			array(
				'remote_api_url' => $this->remote_api_url,
				'version'        => $this->version,
				'license'        => trim( get_option( $this->theme_slug.'_theme_license_key' ) ),
				'item_name'      => $this->item_name,
				'author'         => $this->author,
				'product_id'     => $this->product_id,
				'theme_slug'     => $this->theme_slug,
			)
		);
	}

	function getBrowser($agent = null){
        $u_agent = ($agent!=null)? $agent : $_SERVER['HTTP_USER_AGENT']; 
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
        { 
            $bname = 'Internet Explorer'; 
            $ub = "MSIE"; 
        } 
        elseif(preg_match('/Firefox/i',$u_agent)) 
        { 
            $bname = 'Mozilla Firefox'; 
            $ub = "Firefox"; 
        } 
        elseif(preg_match('/Opera/i',$u_agent)) 
        { 
            $bname = 'Opera'; 
            $ub = "Opera"; 
        } 
        elseif(preg_match('/Netscape/i',$u_agent)) 
        { 
            $bname = 'Netscape'; 
            $ub = "Netscape"; 
        }
        elseif(preg_match('/Edg/i',$u_agent)){
		    $bname = 'Edge';
		    $ub = "Edg";
		}elseif(preg_match('/Trident/i',$u_agent)){
		    $bname = 'Internet Explorer';
		    $ub = "MSIE";
		} 
        elseif(preg_match('/Chrome/i',$u_agent)) 
        { 
            $bname = 'Google Chrome'; 
            $ub = "Chrome"; 
        } 
        elseif(preg_match('/Safari/i',$u_agent)) 
        { 
            $bname = 'Apple Safari'; 
            $ub = "Safari"; 
        } 

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return array(
            // 'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            // 'pattern'    => $pattern
        );
    }

	function swedl_sample_activate_license(){
		if( isset( $_POST[ "swedl_{$this->theme_slug}_validate_license"] ) ){
			if( ! check_admin_referer( "swedl_{$this->theme_slug}_license_nonce_action" , "swedl_{$this->theme_slug}_license_nonce_field" ) ) return;
			
			$license_key = trim( get_option( $this->theme_slug . '_theme_license_key' ) );
			$args = array(
						'timeout'     => 120,
					    'httpversion' => '1.1',
						'action' 	  => 'activate',
						'pid' 		  => $this->product_id,
						'license_key' => $license_key,
						'product_name'=> $this->item_name
					);
			
			$wp_version = get_bloginfo( 'version' );
			$phpversion = phpversion();
			$browserdetails = json_encode( $this->getBrowser() );
			$current_activated_date = date('Y-m-d H:i:s', time());

			$url = $this->remote_api_url."/wp-json/sparkleddl/v1/license_key/?action=activate&pid=".$this->product_id."&license_key=$license_key&product_name=".urlencode($this->item_name).'&site_url='.$this->site_url."&wp_version=$wp_version&php_version=$phpversion&browser=$browserdetails&activated_date=$current_activated_date";
			$request = wp_remote_get( $url, $args );

			if( is_wp_error( $request ) ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $request );
			$response = json_decode( $body );
			if( $response->status === 'fail' ){
				if( $response->error === 'notfoundlicense' ){
					$message = esc_html__( 'Your license key not found in our database.', 'sparkle-sample-plugin' );
				}

				if( $response->error === 'licensedisabled' ){
					$message = esc_html__( 'Your license key has been disabled. Please contact license provider.', 'sparkle-sample-plugin' );
				}

				if( $response->error === 'licenseexpired' ){
					$message = esc_html__( 'Your license key has been expired. Please contact license provider.', 'sparkle-sample-plugin' );
				}

				
				if( $response->error === 'activationlimitreached' ){
					$message = esc_html__( 'Your maximum limit for using this license key has been reached.', 'sparkle-sample-plugin' );
				}

				if( $response->error === 'licensekeyexpired' ){
					$message = esc_html__( 'Your license key has been expired.', 'sparkle-sample-plugin' );
				}
				
			}

			if( $response->status === 'success' ){
				if( $response->poststatus === 'alreadyactive' ){
					$remaining_activation = $response->activation_limit - $response->use_count;
					$message = esc_html__( "Your license key is already active. You have used the license in {$response->use_count} sites. Now remaining activation count is {$remaining_activation}", 'sparkle-sample-plugin' );
				}

				if( $response->poststatus === 'activated' ){
					$remaining_activation = $response->activation_limit - $response->use_count;
					$message = esc_html__( "Your license key has been activated successfully. You have used the license in {$response->use_count} sites. Now remaining activation count is {$remaining_activation}", 'sparkle-sample-plugin' );
				}

				if( $response->poststatus === 'reactivated' ){
					$remaining_activation = $response->activation_limit - $response->use_count;
					$message = esc_html__( "Your license key has been reactivated successfully. You have used the license in {$response->use_count} sites. Now remaining activation count is {$remaining_activation}", 'sparkle-sample-plugin' );
				}				
			}

			if( $response->status === 'success' ){
				$activation = 'true';

			}else if( $response->status === 'fail' ){
				$activation = 'false';
			}

			update_option( $this->theme_slug . '_license_key_status', $response );
			$base_url = admin_url( 'themes.php?page=' . $this->theme_slug . '-license' );
			$redirect = add_query_arg( array( 'sl_activation' => $activation, 'message' => urlencode( $message ) ), $base_url );
			wp_redirect( $redirect );
			exit();
		}	
	}

	function swedl_sample_deactivate_license(){
		if(isset($_POST["swedl_{$this->theme_slug}_deactivate_license"])){
			if( ! check_admin_referer( "swedl_{$this->theme_slug}_license_nonce_action" , "swedl_{$this->theme_slug}_license_nonce_field" )) return;
			
			$license_key = get_option( $this->theme_slug . '_theme_license_key' );
			$args 		 = array(
								'timeout'     => 120,
							    'httpversion' => '1.1',
							);

			$url = $this->remote_api_url."/wp-json/sparkleddl/v1/license_key/?action=deactivate&pid=".$this->product_id."&license_key=$license_key&product_name=".urlencode($this->item_name).'&site_url='.$this->site_url;
			
			$request = wp_remote_get( $url, $args );
	        
			if( is_wp_error( $request ) ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $request );
			$response = json_decode($body);
			
			if( $response->status === 'fail' ){
				$activation = 'false';
			}

			if( $response->status === 'success' ){
				$activation = 'true';
				if( $response->poststatus === 'deactivated' ){
					$message = esc_html__( 'Your license key has been successfully deactivated.', 'sparkle-sample-plugin' );
				}	

				if( $response->poststatus === 'alreadydeactivated' ){
					$message = esc_html__( 'Your license key has already been deactivated.', 'sparkle-sample-plugin' );
				}		
			}


			update_option( $this->theme_slug . '_license_key_status', $response );
			$base_url = admin_url( 'themes.php?page=' . $this->theme_slug . '-license' );
			$redirect = add_query_arg( array( 'sl_deactivation' => $activation, 'message' => urlencode( $message ) ), $base_url );
			wp_redirect( $redirect );
			exit();	
		}
	}

	function swedl_sample_delete_license(){
		if( isset( $_POST["swedl_{$this->theme_slug}_delete_license"] ) ){
			if( ! check_admin_referer( "swedl_{$this->theme_slug}_license_nonce_action" , "swedl_{$this->theme_slug}_license_nonce_field" )) return;
			
			$license_key = get_option( $this->theme_slug . '_theme_license_key' );
		
			$args = array(
						'timeout'     => 120,
					    'httpversion' => '1.1',
					);

			$url = $this->remote_api_url."/wp-json/sparkleddl/v1/license_key/?action=delete&pid=".$this->product_id."&license_key=$license_key&product_name=".urlencode($this->item_name).'&site_url='.$this->site_url;
		
			$request = wp_remote_get( $url, $args );
	        
			if( is_wp_error( $request ) ) {
				return false;
			}

			$body = wp_remote_retrieve_body($request);
			$response = json_decode($body);

			if( $response->status === 'fail' ){
				$activation = 'false';
				if( $response->error === 'cannotdelete' ){
					$message = esc_html__( 'Your license key for this site not found in our database.', 'sparkle-sample-plugin' );
				}
			}

			if( $response->status === 'success' ){
				$activation = 'true';
				if( $response->poststatus === 'deleted' ){
					$message = esc_html__( 'Your license key for this site has been deleted successfully.', 'sparkle-sample-plugin' );
				}
			}

			update_option( $this->theme_slug . '_license_key_status', $response );
			$base_url = admin_url( 'themes.php?page=' . $this->theme_slug . '-license' );
			$redirect = add_query_arg( array( 'sl_deactivation' => $activation, 'message' => urlencode( $message ) ), $base_url );
			wp_redirect( $redirect );
			exit();	
		}
	}

	function register_options(){
		register_setting( $this->theme_slug.'_license_bundle', $this->theme_slug.'_theme_license_key', 'sedd_sanitize_theme_license' );
	}

	function sedd_sanitize_theme_license(){
		$old = trim( get_option( $this->theme_slug . '_theme_license_key' ) );

		if ( $old && $old != $new ) {
			// New license has been entered, so must reactivate
			delete_option( $this->theme_slug . '_license_key_status' );
			delete_transient( $this->theme_slug . '_license_message' );
		}

		return $new;
	}

	function swedl_sample_theme_license_menu(){
		add_theme_page(
			'Theme Licensing',
			'Theme Licensing',
			'manage_options',
			$this->theme_slug . '-license',
			array( $this, 'sedd_license_page' )
		);
	}

	function sedd_license_page(){
		$license_key = trim( get_option( $this->theme_slug.'_theme_license_key' ) );
		$status 	 = get_option( $this->theme_slug . '_license_key_status' );

		if( isset( $_GET['sl_deactivation'] ) && isset( $_GET['message'] ) && $_GET['sl_deactivation'] == 'false' ){ ?>
			<div class="sedd-error-message notice notice-error is-dismissible">
					<p><?php echo esc_attr( $_GET['message'] ); ?></p>
			</div>
			<?php
		}

		if( isset( $_GET['sl_deactivation'] ) && isset( $_GET['message'] ) && $_GET['sl_deactivation'] == 'true' ){ ?>
			<div class="sedd-success-message notice notice-success is-dismissible">
					<p><?php echo esc_attr( $_GET['message'] ); ?></p>
			</div>
			<?php
		}

		if( isset( $_GET['sl_activation'] ) && isset( $_GET['message'] ) && $_GET['sl_activation'] == 'false' ){ ?>
			<div class="sedd-error-message notice notice-error is-dismissible">
					<p><?php echo esc_attr( $_GET['message'] ); ?></p>
			</div>
			<?php
		}

		if( isset( $_GET['sl_activation'] ) && isset( $_GET['message'] ) && $_GET['sl_activation'] == 'true' ){ ?>
			<div class="sedd-success-message notice notice-success is-dismissible">
					<p><?php echo esc_attr( $_GET['message'] ); ?></p>
			</div>
			<?php
		}
		?>
		<div class="sedd-license-verify-wrap">
			<h2><?php esc_html_e( 'License key verification', 'sparkle-sample-plugin' ); ?></h2>
			<form action="options.php" method="post" >
				<?php settings_fields( $this->theme_slug.'_license_bundle' ); ?>
				<p><?php _e('Note: After the license key update or change please save before any action!!', 'sparkle-sample-plugin'); ?></p>
				<table class='form-table' role='presentation'>
					<tbody>
						<tr>
							<th scope='row'><label for='sedd-<?php echo $this->theme_slug; ?>-license-key'><?php esc_html_e( 'License Key', 'sparkle-sample-plugin' ); ?></label></th>
							<td>
								<input type="text" id='sedd-<?php echo $this->theme_slug; ?>-license-key' class='regular-text' name="<?php echo $this->theme_slug.'_theme_license_key'; ?>" value="<?php echo esc_attr( $license_key ); ?>" />
								<?php if(isset($status->status) && $status->status =='success'){ echo "<p style='color:green;' class='wedl-status'>". $status->message. "</p>"; } ?>
							</td>
						</tr>
						<?php
						if( isset($status->action) && $status->action == 'activate' && $status->status == 'success'){ ?>
						<tr>
							<?php wp_nonce_field( "swedl_{$this->theme_slug}_license_nonce_action", "swedl_{$this->theme_slug}_license_nonce_field" ); ?>
							<th scope='row'><label for="sedd-<?php echo $this->theme_slug; ?>-validate-license-key"><?php esc_html_e( 'Validate License', 'sparkle-sample-plugin' ); ?></label></th>
							<td>
								<input type="submit" id='sedd-<?php echo $this->theme_slug; ?>-validate-license-key' class='button button-secondary' name="swedl_<?php echo $this->theme_slug; ?>_deactivate_license" value="<?php esc_html_e( 'Deactivate License', ''); ?>" /> 
								<input type="submit" id='sedd-sample-delete-license-key' class='button button-secondary' name="swedl_<?php echo $this->theme_slug; ?>_delete_license" value="<?php esc_html_e( 'Delete License', 'sparkle-sample-plugin'); ?>" /></td>
						</tr>
						<?php }else{ ?>
						<tr>
							<?php wp_nonce_field( "swedl_{$this->theme_slug}_license_nonce_action", "swedl_{$this->theme_slug}_license_nonce_field" ); ?>
							<th scope='row'><label for='sedd-<?php echo $this->theme_slug; ?>-activate-license-key'><?php esc_html_e( 'Validate License', 'sparkle-sample-plugin' ); ?></label></th>
							<td><input type="submit" id='sedd-<?php echo $this->theme_slug; ?>-activate-license-key' class='button button-secondary' name="swedl_<?php echo $this->theme_slug; ?>_validate_license" value="<?php esc_html_e( 'Activate License', 'sparkle-sample-plugin' ); ?>" /></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php submit_button(); ?>	
			</form>
		</div>
		<?php	
	}
}
