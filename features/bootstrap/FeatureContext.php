<?php

use Behat\Behat\Exception\PendingException;
use Behat\MinkExtension\Context\MinkContext;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context.
 */
class FeatureContext extends MinkContext {

	/**
	 * Initializes context with parameters from behat.yml.
	 *
	 * @param array $parameters
	 */
	public function __construct( array $parameters ) {
		$this->parameters = $parameters;
		$this->install_dir     = $this->path( dirname( dirname( dirname( __FILE__ ) ) ), 'install' );
		$this->wp_install_file = $this->path( $this->install_dir, $this->parameters['wordpress_install_file'] );
		$this->sqlite_integration_install_file = $this->path( $this->install_dir, $this->parameters['sqlite_integration_install_file'] );
		$this->webserver_dir   = $this->parameters['webserver_dir'];
		$this->database_file   = $this->parameters['database_file'];
		$this->create_wp_config_replacements();
	}

	private function create_wp_config_replacements() {
		$this->wp_config_replacements = array();
		$this->wp_config_replacements['AUTH_KEY']         = '9Hw0Kk}&5c%YigU#p8c@:/6$MZo[f@u:F6M=v=}!v;fr^W32!/h&*Mo ~92E.C}C';
		$this->wp_config_replacements['SECURE_AUTH_KEY']  = '?<,9^IG-c)HG.JPc #v#E/IBs5J=LK/D0&0Q-BY0dW|55YZ dzuxAsDpS=CO,aN&';
		$this->wp_config_replacements['LOGGED_IN_KEY']    = '/0,Ue5fMnZ8%vE&AokeWl$p5P`y$^~v:%u!H!Gn1NH]|Ko/!zE=7F^z,[7{JW0xN';
		$this->wp_config_replacements['NONCE_KEY']        = '}vDmbs}$q5R64&q`UZg#fE_a*uJD3:/^m/q]GNY~|&)vMd#|$v.p<~#VTC.^Rkh3';
		$this->wp_config_replacements['AUTH_SALT']        = '-}H 7a0)V,kX|#a%:F;UQ+tZK0V9{@_1<B5[V/o6g]3a]EA%,s=)=~@`$U9I~Wgf';
		$this->wp_config_replacements['SECURE_AUTH_SALT'] = '6f=C`:P ?#fes])N`kct`Z+ :1Ty`lAt&AJuQT&.2ZB+o2%WUQ#P_]78lWL1m`8&';
		$this->wp_config_replacements['LOGGED_IN_SALT']   = 'z%vk: dd+>FKGFJ:6Z4c(<JnHZL6%i=tSO%=^+rHtPi<&WAr@2Cl67Jqo:7MKtOE';
		$this->wp_config_replacements['NONCE_SALT']       = '/2K@9/*3M&;.2[RJ8$V0L[MmId.<x}R< 7/0 K=mgy=:89],Z2<~LE4(Cs%?!sjd';
	}

	/**
	 * @Given /^a fresh WordPress installation$/
	 */
	public function a_fresh_wordress_installation() {
		$this->create_temp_dir();
		$this->prepare_wp_in_webserver();
		$this->prepare_sqlite_integration_in_webserver();
		$this->prepare_sqlite_database();
		$this->create_wp_config_file();		

		// $target_dir   = ;
		// take zip from anywhere
		// unzip in configured dir
		// copy sqlite file from anywhere
		// configure wp-config.php
	}

	/**
	 * @Given /^the plugin "([^"]*)" in the plugin directory$/
	 */
	public function the_plugin_in_the_plugin_directory( $plugin_target_dir ) {
		$this->copy_dir( $this->path( dirname( dirname( dirname( __FILE__ ) ) ), 'src' ), $this->path( $this->webserver_dir, 'wp-content', 'plugins', $plugin_target_dir ) );
	}

	/**
	 * @Given /^I am logged as an administrator$/
	 */
	public function i_am_logged_in_as_an_administrator()
	{
		$this->login( 'admin', 'admin' );
	}


	private function create_temp_dir() {
		$tempfile = tempnam( sys_get_temp_dir(), '' );
		if ( ! file_exists( $tempfile ) ) {
			throw new Exception( 'Could not create temp file', 1 );
		}
		unlink( $tempfile );
		mkdir( $tempfile );
		if ( ! is_dir( $tempfile ) ) {
			throw new Exception( 'Could not create temp dir', 1 );
		}
		$this->temp_dir = $tempfile;
	}

	private function prepare_wp_in_webserver() {
		$this->extract_zip_to_dir( $this->wp_install_file, $this->temp_dir );
		$this->move_former_webserver_dir_to_temp( $this->webserver_dir, $this->temp_dir );
		$this->move_file_or_dir( $this->path( $this->temp_dir, 'wordpress' ), $this->webserver_dir );
	}

	private function prepare_sqlite_integration_in_webserver() {
		$this->extract_zip_to_dir( $this->sqlite_integration_install_file, $this->temp_dir );
		$this->move_file_or_dir( $this->path( $this->temp_dir, 'sqlite-integration' ), $this->path( $this->webserver_dir, 'wp-content', 'plugins', 'sqlite-integration' ) );
		$this->copy_file( $this->path( $this->webserver_dir, 'wp-content', 'plugins', 'sqlite-integration', 'db.php' ), $this->path( $this->webserver_dir, 'wp-content', 'db.php' ) );
	}

	private function prepare_sqlite_database() {
		$this->mkdir( $this->path( $this->webserver_dir, 'wp-content', 'database' ) );
		$this->copy_file( $this->path( $this->install_dir, $this->database_file ), $this->path( $this->webserver_dir, 'wp-content', 'database', $this->database_file ) );
	}

	private function create_wp_config_file() {
		$source_handle = fopen( $this->path( $this->webserver_dir, 'wp-config-sample.php' ), 'r' );
		$target_handle = fopen( $this->path( $this->webserver_dir, 'wp-config.php' ), 'w' );
		try {
			if ( ! $source_handle ) {
				throw new Exception( 'Can\'t read wp-config-sample.php', 1 );
			} 
			if ( ! $source_handle ) {
				throw new Exception( 'Can\'t write wp-config.php', 1 );
			} 
			$db_config_started = false;
			while ( ($line = fgets( $source_handle ) ) !== false ) {
				$db_config_started = $db_config_started || preg_match( '/^define\(\'DB_[^\']*\',[ ]*\'[^\']*\'\);/', $line );
				$line = $this->replace_config_value( $line );
				if ( $db_config_started && preg_match( '/^\/\*\*#@\+/', $line ) ) {
					$this->write_to_file( $target_handle, "define('DB_FILE', '".$this->database_file."');\r\n" );
					$this->write_to_file( $target_handle, "\r\n" );
				}
				$this->write_to_file( $target_handle, $line );
			} 
		} finally {
			fclose( $source_handle );
			fclose( $target_handle );
		}
	}

	private function replace_config_value( $line ) {
		if ( ! preg_match( '/^define\(\'([^\']*)\',[ ]*\'([^\']*)\'\);/', $line, $matches ) ) {
			return $line;
		}
		$key   = $matches[1];
		$value = $matches[2];
		if ( ! array_key_exists( $key, $this->wp_config_replacements ) ) {
			return $line;
		}
		return preg_replace( '/'.$value.'/', $this->wp_config_replacements[$key], $line );
	}

	private function path() {
		return implode( func_get_args(), DIRECTORY_SEPARATOR );
	}

	private function extract_zip_to_dir( $zip_file, $dir ) {
		$zip = new ZipArchive;
		$res = $zip->open( $zip_file );
		if ( $res === TRUE ) {
			$zip->extractTo( $dir );
			$zip->close();
		} else {
			throw new Exception( 'Unable to open zip file '.$zip_file, 1 );
		}		
	}

	private function move_former_webserver_dir_to_temp( $webserver_dir, $temp_dir ) {
		if ( ! is_dir( $webserver_dir ) ) {
			return;
		}
		$this->move_file_or_dir( $webserver_dir, $this->path( $temp_dir, 'wordpress_old' ) );
	}

	private function move_file_or_dir( $source, $target ) {
		if ( ! rename( $source, $target ) ) {
			throw new Exception( 'Can\'t move '.$source.' to '.$target, 1 );
		}
	}

	private function copy_dir( $source_dir, $target_dir ) {
		$this->mkdir( $target_dir );
		foreach ( scandir( $source_dir ) as $found ) {
			if ( $found == '.' || $found == '..' ) {
				continue;
			}
			$source_file_or_dir = $this->path( $source_dir, $found );
			$target_file_or_dir = $this->path( $target_dir, $found );
			if ( is_file( $source_file_or_dir ) ) {
				$this->copy_file( $source_file_or_dir, $target_file_or_dir );
			} else {
				$this->copy_dir( $source_file_or_dir, $target_file_or_dir );
			}
		}
	}

	private function copy_file( $source_file, $target_file ) {
		if ( ! copy( $source_file, $target_file ) ) {
			throw new Exception( 'Can\'t copy file '.$source_file.' to '.$target_file, 1 );
		}
	}

	private function mkdir( $dir ) {
		if ( ! mkdir( $dir ) ) {
			throw new Exception( 'Can\'t create directory '.$dir, 1 );
		}
	}

	private function write_to_file( $handle, $string ) {
		if ( ! fwrite( $handle, $string ) ) {
			throw new Exception( 'Can\'t write to file', 1 );
		}
	}
	
	/**
	 * Makes sure the current user is logged out, and then logs in with
	 * the given username and password.
	 *
	 * @param string $username
	 * @param string $password
	 * @author Maarten Jacobs
	 **/
	protected function login( $username, $password ) {
		$this->visit( 'wp-admin' );

		// And login
		$session = $this->getSession();
		$current_page = $session->getPage();
		$current_page->fillField( 'user_login', $username );
		$current_page->fillField( 'user_pass', $password );
		$current_page->findButton( 'wp-submit' )->click();

		// Assert that we are on the dashboard
		assertTrue( $session->getPage()->hasContent( 'Dashboard' ) );
	}

}