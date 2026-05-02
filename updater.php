<?php
if ( ! class_exists( 'School_Result_Management_Plugin_Updater' ) ) :

class School_Result_Management_Plugin_Updater {
	private $file;
	private $plugin;
	private $basename;
	private $username;
	private $repository;
	private $authorize_token;  
	private $github_response;

	public function __construct( $file ) {
		$this->file     = $file;
		$this->plugin   = plugin_basename( $file );
		$this->basename = dirname( $this->plugin );

		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'modify_transient' ], 10, 1 );
		add_filter( 'plugins_api', [ $this, 'plugin_popup' ], 10, 3 );
		add_filter( 'upgrader_post_install', [ $this, 'after_install' ], 10, 3 );
		add_filter( 'http_request_args', [ $this, 'add_github_auth_header' ], 10, 2 );

	}
	 


	public function set_username( $username ) {
		$this->username = $username;
	}

	public function set_repository( $repository ) {
		$this->repository = $repository;
	}

	public function authorize( $token ) {
		$this->authorize_token = $token;
	}

	
	public function add_github_auth_header( $args, $url ) {

    if ( strpos( $url, "api.github.com/repos/{$this->username}/{$this->repository}" ) === false ) {
        return $args;
    }

    $args['headers']['User-Agent'] = 'Wp-Evisort-Updater';

    if ( ! empty( $this->authorize_token ) ) {
        $args['headers']['Authorization'] = 'token ' . $this->authorize_token;
    }

    return $args;
}


	private function get_repository_info() {

		if ( ! is_null( $this->github_response ) ) {
			return;
		}

		$request_uri = "https://api.github.com/repos/{$this->username}/{$this->repository}/releases/latest";

		$args = [
			'timeout'   => 20,
			'sslverify'=> true,
			'headers'  => [
				'User-Agent' => 'Wp-Evisort-Updater',
				'Accept'     => 'application/vnd.github+json',
			],
		];

		// Add auth header if token exists (private repos / higher rate limits)
		if ( ! empty( $this->authorize_token ) ) {
			$args['headers']['Authorization'] = 'token ' . $this->authorize_token;
		}

		$response = wp_remote_get( $request_uri, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return false;
		}

		$this->github_response = json_decode( $body, true );
	}


	public function modify_transient( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$this->get_repository_info();

		if ( empty( $this->github_response['tag_name'] ) ) {
			return $transient;
		}

		$new_version = $this->github_response['tag_name'];
		/*$current_version = $transient->checked[ $this->plugin ];

		if ( version_compare( $new_version, $current_version, '<=' ) ) {
			return $transient;
		}*/

		// Make sure WP knows about this plugin
		if ( ! isset( $transient->checked[ $this->plugin ] ) ) {
			return $transient;
		}

		$current_version = $transient->checked[ $this->plugin ];

		// Extra safety
		if ( empty( $current_version ) ) {
			return $transient;
		}

		if ( version_compare( $new_version, $current_version, '<=' ) ) {
			return $transient;
		}


		// Build token-authenticated zip link (works in ManageWP)
		$token = $this->authorize_token;
		$download_link = "https://api.github.com/repos/{$this->username}/{$this->repository}/zipball/{$new_version}";


		$obj = new stdClass();
		$obj->slug = $this->basename;
		$obj->new_version = $new_version;
		$obj->url = $this->github_response['html_url'];
		$obj->package = $download_link;

		$transient->response[ $this->plugin ] = $obj;
		return $transient;
	}

	public function plugin_popup( $result, $action, $args ) {
		if ( $action !== 'plugin_information' ) {
			return false;
		}

		if ( $args->slug !== $this->basename ) {
			return $result;
		}

		$this->get_repository_info();

		$result = (object) [
			'name'        => $this->github_response['name'],
			'slug'        => $this->basename,
			'version'     => $this->github_response['tag_name'],
			'author'      => $this->github_response['author']['login'],
			'homepage'    => $this->github_response['html_url'],
			'download_link' => "https://api.github.com/repos/{$this->username}/{$this->repository}/zipball/{$this->github_response['tag_name']}",

			'sections'    => [
				'description' => $this->github_response['body'],
			],
		];

		return $result;
	}

	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;
		$plugin_folder = WP_PLUGIN_DIR . '/' . $this->basename;
		$wp_filesystem->move( $result['destination'], $plugin_folder );
		$result['destination'] = $plugin_folder;
		return $result;
	}

	

}

endif;
