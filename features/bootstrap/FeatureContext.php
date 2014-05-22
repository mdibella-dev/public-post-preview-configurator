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
	 * @Given /^a fresh WordPress installation( \(([^\)]*)\))?$/
	 */
	public function a_fresh_wordress_installation( $language_expr = null, $locale = '' ) {
		$this->wp_config_replacements['WPLANG'] = $locale;
		$this->create_temp_dir();
		$this->prepare_wp_in_webserver();
		$this->prepare_sqlite_integration_in_webserver();
		$this->prepare_sqlite_database();
		$this->create_wp_config_file();
	}

	/**
	 * @Given /^the plugin "([^"]*)" in the plugin directory$/
	 */
	public function the_plugin_in_the_plugin_directory( $plugin_target_dir ) {
		$this->copy_file_or_dir( $this->path( dirname( dirname( dirname( __FILE__ ) ) ), 'src' ), $this->path( $this->webserver_dir, 'wp-content', 'plugins', $plugin_target_dir ) );
	}

	/**
	 * @Given /^I am logged as an administrator$/
	 */
	public function i_am_logged_in_as_an_administrator() {
		$this->login( 'admin', 'admin' );
	}

	/**
	 * @Given /^I activate the plugin "([^"]*)"$/
	 */
	public function i_activate_the_plugin( $plugin_id ) {
		$page = $this->get_page();
		$plugin_area = $page->find( 'css', "#$plugin_id" );
		foreach ( $plugin_area->findAll( 'css', 'a' ) as $link ) {
			if ( preg_match( '/action=activate/', $link->getAttribute( 'href' ) ) ) {
				$link->click();
				break;
			}
		}
	}

	/**
	 * @Given /^the plugin "([^"]*)" is activated$/
	 */
	public function the_plugin_is_activated( $plugin_id ) {
		$plugin_file = "$plugin_id/$plugin_id.php";
		$pdo  = $this->create_pdo();
		$stmt = $pdo->prepare( 'SELECT * FROM wp_options WHERE option_name = :option_name' );
		$stmt->execute( array( ':option_name' => 'active_plugins' ) );
		$option_value = $stmt->fetch( PDO::FETCH_ASSOC )['option_value'];
		$unserialized = unserialize( $option_value );
		foreach ( $unserialized as $active_plugin ) {
			if ( $active_plugin == $plugin_file ) {
				return;
			}
		}
		$unserialized[] = $plugin_file;
		$option_value   = serialize( $unserialized );
		$stmt = $pdo->prepare( 'UPDATE wp_options SET option_value = :option_value WHERE option_name = :option_name' );
		$stmt->execute( array( ':option_name' => 'active_plugins', ':option_value' => $option_value ) );
	}

	/**
	 * @Given /^the option "([^"]*)" has the value "([^"]*)"$/
	 */
	public function the_option_has_the_value( $option_name, $option_value ) {
		$pdo  = $this->create_pdo();
		$stmt = $pdo->prepare( 'SELECT * FROM wp_options WHERE option_name = :option_name AND option_value = :option_value' );
		$stmt->execute( array( ':option_name' => $option_name, ':option_value' => $option_value ) );
		if ( 0 == $this->num_of_rows( $stmt ) ) {
			$stmt = $pdo->prepare( 'INSERT INTO wp_options (option_name, option_value) VALUES (:option_name, :option_value)' );
		} else {
			$stmt = $pdo->prepare( 'UPDATE wp_options SET option_value = :option_value WHERE option_name = :option_name' );
		}
		$stmt->execute( array( ':option_name' => $option_name, ':option_value' => $option_value ) );
	}

	/**
	 * @Given /^I should see the message "([^"]*)"$/
	 */
	public function i_should_see_the_message( $msg ) {
		assertNotNull( $this->get_page()->find( 'css', '.updated' ), "Can't find element" );
		assertTrue( $this->get_page()->hasContent( $msg ), "Can't find message" );
	}

	/**
	 * @Given /the option "([^"]*)" should have the value "([^"]*)"$/
	 */
	public function the_option_should_have_the_value( $option_name, $option_value ) {
		$pdo  = $this->create_pdo();
		$stmt = $pdo->prepare( 'SELECT * FROM wp_options WHERE option_name = :option_name AND option_value = :option_value' );
		$stmt->execute( array( ':option_name' => $option_name, ':option_value' => $option_value ) );
		assertEquals( $this->num_of_rows( $stmt ), 1 );
	}

	/**
	 * @Given /^I wait for ([\d\.]*) seconds$/
	 */
	public function i_wait_for( $seconds ) {
		sleep( intval( $seconds ) );
	}

	private function create_temp_dir() {
		$tempfile = tempnam( sys_get_temp_dir(), '' );
		if ( ! file_exists( $tempfile ) ) {
			throw new Exception( 'Could not create temp file' );
		}
		unlink( $tempfile );
		mkdir( $tempfile );
		if ( ! is_dir( $tempfile ) ) {
			throw new Exception( 'Could not create temp dir' );
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
		$this->copy_file_or_dir( $this->path( $this->webserver_dir, 'wp-content', 'plugins', 'sqlite-integration', 'db.php' ), $this->path( $this->webserver_dir, 'wp-content', 'db.php' ) );
	}

	private function prepare_sqlite_database() {
		$this->mkdir( $this->path( $this->webserver_dir, 'wp-content', 'database' ) );
		$this->copy_file_or_dir( $this->path( $this->install_dir, $this->database_file ), $this->path( $this->webserver_dir, 'wp-content', 'database', $this->database_file ) );
	}

	private function create_wp_config_file() {
		$source_handle = fopen( $this->path( $this->webserver_dir, 'wp-config-sample.php' ), 'r' );
		$target_handle = fopen( $this->path( $this->webserver_dir, 'wp-config.php' ), 'w' );
		try {
			if ( ! $source_handle ) {
				throw new Exception( 'Can\'t read wp-config-sample.php' );
			} 
			if ( ! $source_handle ) {
				throw new Exception( 'Can\'t write wp-config.php' );
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
			throw new Exception( 'Unable to open zip file '.$zip_file );
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
			throw new Exception( 'Can\'t move '.$source.' to '.$target );
		}
	}

	private function copy_file_or_dir( $source, $target ) {
		if ( is_file( $source ) ) {
			if ( ! copy( $source, $target ) ) {
				throw new Exception( 'Can\'t copy file '.$source.' to '.$target );
			}
		} else {
			$this->mkdir( $target );
			foreach ( scandir( $source ) as $found ) {
				if ( $found == '.' || $found == '..' ) {
					continue;
				}
				$this->copy_file_or_dir( $this->path( $source, $found ), $this->path( $target, $found ) );
			}
		}
	}

	private function mkdir( $dir ) {
		if ( ! mkdir( $dir ) ) {
			throw new Exception( 'Can\'t create directory '.$dir );
		}
	}

	private function write_to_file( $handle, $string ) {
		if ( ! fwrite( $handle, $string ) ) {
			throw new Exception( 'Can\'t write to file' );
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
	private function login( $username, $password ) {
		$this->visit( 'wp-admin' );
		$page = $this->get_page();
		$page->fillField( 'user_login', $username );
		$page->fillField( 'user_pass', $password );
		$page->findButton( 'wp-submit' )->click();
		assertTrue( $page->hasContent( 'Dashboard' ) );
	}

	private function get_page() {
		return $this->getSession()->getPage();
	}

	private function create_pdo() {
		$pdo = new PDO( 'sqlite:'.$this->path( $this->webserver_dir, 'wp-content', 'database', $this->database_file ) );
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		return $pdo;
	}

	private function num_of_rows( $result ) {
		$count = 0;
		foreach ( $result as $row ) $count++;
		return $count;
	}
}