<?php
/**
 * Daily Playlist admin class
 *
 * Playlist quotidienne des titres passés en programmation automatique.
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 4.0
 */

class Campus_Daily_Playlist_Options {

	static $tax_prefix = 'album';

	var $page;

	var $message;

	var $page_url;

	var $file_url;

	var $song_table;

	static $table_name;

	static $default_song_fields;

	function __construct() {

		self::$default_song_fields = array(
			'time' 	  		 => '0000-00-00 00:00:00',
			'duration'		 => 0,
			'intro'   		 => 0,
			'category'		 => '',
			'artist_term_id' => '',
			'title_term_id'  => '',
			'genre_term_id'  => ''
		);

		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );

		$this->message = new WP_Error();
		$this->page_url = admin_url( 'admin.php?page=daily_playlist' );
		$this->file_url = str_replace( ABSPATH, get_bloginfo('url') . '/', dirname( __FILE__ ) );
		self::create_table();

		add_action( 'admin_init', array( $this, 'session' ) );
		add_action( 'admin_init', array( $this, 'set_song_table' ) );
		//add_action( 'admin_init', array(&$this, 'add_user_cap') );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		//add_action('admin_notices', array(&$this, 'admin_notice'));
		//add_action( 'wp_head', array(&$this, 'ajaxurl') );

		add_action( 'admin_print_scripts', array( $this, 'admin_scripts' ) );

		add_filter( 'manage_edit-album_song_columns', array( $this, 'term_columns' ) );
		add_filter( 'manage_edit-album_artist_columns', array( $this, 'term_columns' ) );
		add_filter( 'manage_edit-album_playlist_columns', array( $this, 'term_columns' ) );

		add_filter( 'manage_album_song_custom_column', array( $this, 'term_column' ), 10, 3 );
		add_filter( 'manage_album_artist_custom_column', array( $this, 'term_column' ), 10, 3 );
		add_filter( 'manage_album_playlist_custom_column', array( $this, 'term_column' ), 10, 3 );

		// Add term fields
		add_filter( 'campus_custom_term_fields', array( $this, 'campus_custom_term_fields' ), 10, 2 );
		add_action( 'album_playlist_add_form_fields', 'campus_add_term_fields' );
		add_action( 'album_playlist_edit_form_fields', 'campus_add_term_fields' );

		// Save term fields
		add_action( 'created_term', array( $this, 'term_fields_save' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'term_fields_save' ), 10, 3 );
	}

	/**
	 * Thumbnail column added to term admin.
	 *
	 * @access public
	 * @param mixed $columns
	 * @return void
	 */
	function term_columns( $columns ) {

		$screen = get_current_screen();
		$taxonomy = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : $screen->taxonomy;

		switch ( $taxonomy ) {
			case self::$tax_prefix . '_song':

				unset( $columns['description'] );
				$columns = array_slice( $columns, 0, 3, true ) + array( 'artist' => __( 'Artist(s)', 'campus' ) ) + array_slice( $columns, 3, NULL, true );
				break;

			case self::$tax_prefix . '_artist':

				unset( $columns['description'] );
				$columns = array_slice( $columns, 0, 3, true ) + array( 'song' => __( 'Song(s)', 'campus' ) ) + array_slice( $columns, 3, NULL, true );
				break;

			case self::$tax_prefix . '_playlist':

				$columns += array( 'visibility' => '<span class="dashicons-before dashicons-visibility"></span>' );
				break;
		}

		return $columns;
	}

	/**
	 * Thumbnail column value added to category admin.
	 *
	 * @access public
	 * @param mixed $columns
	 * @param mixed $column
	 * @param mixed $id
	 * @return void
	 */
	function term_column( $columns, $column, $term_id ) {

		$list = array();

		switch ( $column ) {
			case 'artist':

				$taxonomy = self::$tax_prefix . '_artist';

				$artists = get_term_meta( $term_id, $taxonomy );

				foreach( $artists as $artist_id ) {
					$term = get_term( $artist_id, $taxonomy );
					if( $term )
						$list[] = sprintf( '<li><a href="%s">%s</a></li>', admin_url( sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s&s=%s', $taxonomy, self::$tax_prefix, $term->name ) ), $term->name );
				}
				break;

			case 'song':

				$taxonomy = self::$tax_prefix . '_song';

				$songs = get_term_meta( $term_id, $taxonomy );

				foreach( $songs as $song_id ) {
					$term = get_term( $song_id, $taxonomy );
					if( $term )
						$list[] = sprintf( '<li><a href="%s">%s</a></li>', admin_url( sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s&s=%s', $taxonomy, self::$tax_prefix, $term->name ) ), $term->name );
				}
				break;

			case 'visibility':

				$taxonomy = self::$tax_prefix . '_playlist_visibility';

				$visible = get_term_meta( $term_id, $taxonomy, true );

				if( $visible )
					echo '<span class="dashicons-before dashicons-yes"></span>';
				else
					echo '<span class="dashicons-before dashicons-no-alt"></span>';

				break;
		}

		if( $list ) {
			printf( '<ul>%s</lu>', join( '', $list ) );
		}

		return $columns;
	}

	function admin_scripts() {
		global $current_screen;

		if( $current_screen->base == $this->page ) {
			wp_enqueue_script( 'post' );
		}
	}

	/**
	 * Create MySQL table
	 *
	 * Doc: https://codex.wordpress.org/Creating_Tables_with_Plugins
	 */
	static function create_table() {
		global $wpdb;

   		$table_name = self::$table_name = $wpdb->prefix . CAMPUS_DAILY_PLAYLIST_TABLE;

   		if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name)
   			return $table_name;

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  duration smallint(6) DEFAULT '0' NOT NULL,
		  intro smallint(6) DEFAULT '0' NOT NULL,
		  category text NOT NULL,
		  artist_term_id text NOT NULL,
		  title_term_id text NOT NULL,
		  genre_term_id text NOT NULL,
		  PRIMARY KEY (time)
		) DEFAULT CHARSET=utf8;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		return dbDelta( $sql );
	}

	function session() {
		if( session_status() == PHP_SESSION_NONE ) {
			session_start();
		}

		if( ! isset( $_POST['send'] ) && ! isset( $_POST['update_rows'] ) && ! isset( $_POST['update_date'] ) ) {
			session_unset();
			session_destroy();
		}
	}

	function set_song_table() {
		// is song list
		if( ! empty( $_GET['page'] ) && $_GET['page'] === 'daily_playlist' && ! empty( $_GET['song_date'] ) ) {
			$this->song_table = new Campus_Daily_Playlist_Songs_List();
			$this->song_table->prepare_items();
		}
	}

	function read_playlist_file() {
		global $wpdb;

		$options = $this->playlist_options();

		$file_arr = ! empty( $_SESSION['files'] ) ? $_SESSION['files'] : $_FILES;

		if( empty( $file_arr ) )
			return;

		// si le fichier n'est pas du xml
		if( $file_arr['add_playlist_file']['type'] != 'text/xml' )
			return $this->message->add( 'error', 'Tu ne peux télécharger que des fichiers xml !' );

		$file = $file_arr['add_playlist_file']['tmp_name'];
		$filename = $file_arr['add_playlist_file']['name'];
		$filesize = $file_arr['add_playlist_file']['size'];

		// mise a jour ou non des données
		if( isset( $file_arr['add_playlist_file']['content'] ) ) {

			$data = $file_arr['add_playlist_file']['content'];

		} else {

			$handle = fopen( $file, 'r' );

			$data = fread( $handle, $filesize );
			$_SESSION['files'] = $_FILES;
			$_SESSION['files']['add_playlist_file']['content'] = $data;
			fclose($handle);

			// si la playlist est déjà en base
			if( array_key_exists( $filename, $options ) )
				return $this->message->add( 'error', 'Cette playlist est déjà dans la base.', 'update_row' );

		}

		// Parse xml
		$data = new SimpleXMLElement( $data );
		$songs_obj = array();

		//print_r($data);

		// Check if there is playlist rows
		if( isset( $data->collection_element ) && ! empty( $data->collection_element ) )
			$songs_obj = $data->collection_element;
		else
			return $this->message->add( 'error', 'Le contenu du fichier ne correspond pas à un fichier de playlist !' );

		if( isset( $_POST['update_date'] ) ) {

			$p_date = $_POST['update_date'];

			$date = sprintf( '%s-%s-%s', $p_date['year'], $p_date['month'], $p_date['day'] );

			// on vérifie que date soit bien au format date
			preg_match( '/([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|3[0-1])/i', $date, $matches );

			if( empty( $matches ) )
				return $this->message->add( 'error', 'Tu dois rentrer une date valide !', 'update_date' );

		} else if( isset( $_POST['update_rows'] ) ) {

			$date = $options[$filename];

		} else {

			// On créé la date a partir du nom du fichier pour l'insérer dans le champ "time"
			preg_match( '/(0[1-9]|1[0-9]|2[0-9]|3[0-1])[\.|-](0[1-9]|1[0-2])[\.|-]([0-9]{4})/i', $filename, $matches );

			// Create date
			if( $matches ) {
				$day = $matches[1];
				$month = $matches[2];
				$year = $matches[3];
				$date = sprintf( '%s-%s-%s', $year, $month, $day );

				// on vérifie que date soit bien au format date
				preg_match( '/([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|3[0-1])/i', $date, $matches );

			}

			if( empty( $matches ) )
				return $this->message->add( 'error', 'La date du fichier n\'a pas pu être extraite du titre du fichier !', 'update_date' );
		}

		$songs = array();
		$key = 0;

		//pour chaque ligne, on traite les données
		foreach( $songs_obj as $song ) {

			if( count( $song ) <= 2 || count( $song ) == 8 && ( (string) $song->Timing == (string) $song->Durée ) && ( (string) $song->Timing == (string) $song->Artiste ) )
				continue;

			// Convert decimal time to seconds (ex: 3.14 > 194)
			$duration_str = isset( $song->Durée ) ? trim( (string) $song->Durée ) : 0;
			$duration_sep = strpos( $duration_str, '.' );
			$duration = (int) substr( $duration_str, 0, $duration_sep ) * 60 + (int) substr( $duration_str, $duration_sep + 1 );

			$songs[$key]['time'] = $date . ' ' . trim( str_replace('H ', ':', (string) $song->Timing) );
			$songs[$key]['duration'] = $duration;
			$songs[$key]['intro'] = trim( (string) $song->Intro );
			$songs[$key]['category'] = trim( (string) $song->Catégorie );
			$songs[$key]['artist'] = trim( (string) $song->Artiste );
			$songs[$key]['title'] = trim( (string) $song->Titre );
			$songs[$key]['genre'] = trim( (string) $song->Sonorité );

			$key++;
		}

		$this->insert_songs( $songs, $date, $filename );
	}

	function insert_songs( $songs, $date, $filename ) {
		global $wpdb;

   		//$songs = wp_parse_args( (array) $songs, array( ) );

   		$table_name = self::$table_name;

		if( empty( $songs ) )
			return;

		$str_date = date_i18n( 'l j F Y', strtotime( $date ) );

		if( isset( $_POST['update_rows'] ) ) {
			$delete = $wpdb->query( "DELETE FROM `$table_name` WHERE `time` LIKE '%$date%';" );
			if( $delete == false )
				return $this->message->add( 'error', __( 'Problème lors de la mise à jour des données. Contacte ton webmaster préféré ;)' ) );
		}

		foreach( $songs as $k => $song ) {
			$insert = false;
			$message = false;

			if( $song['duration'] > 0 ) {

				$prepared_song = self::prepare_song( $song );

				if( $prepared_song )
					$insert = $wpdb->insert( $table_name, $prepared_song );

				if( $insert === false ) {
					$message = sprintf( __( 'Le morceau <strong>%s</strong> par <strong>%s</strong> programmé à <strong>%s</strong> n\'a pas pu être ajouté.' ), $song['title'], $song['artist'], date( get_option( 'time_format' ), strtotime( $song['time'] ) ) );
				}

			} else {
				$message = sprintf( __( 'Le morceau <strong>%s</strong> par <strong>%s</strong> programmé à <strong>%s</strong> n\'a pas été ajouté car <strong>sa durée est nulle</strong>.' ), $song['title'], $song['artist'], date( get_option( 'time_format' ), strtotime( $song['time'] ) ) );
			}

			if( $message !== false )
				$this->message->add( 'error', $message );
		}

		//update option
		$options = $this->playlist_options();
		$new_options[$filename] = $date;

		$options = array_merge( $new_options, $options );

		if( ! isset( $_POST['update_rows'] ) )
			update_option( 'playlist_option', $options );

		// Update scope_option
		$scope_option = $this->playlist_scope_option();

		if( empty($scope_option['end']) && empty($scope_option['begin']) ) {

			$scope_option['end'] = $date;
			$scope_option['begin'] = $date;

		} elseif( strtotime($date) < strtotime($scope_option['end']) ) {

			$scope_option['end'] = $date;

		} elseif( strtotime($date) > strtotime($scope_option['begin']) ) {

			$scope_option['begin'] = $date;
		}

		update_option( 'playlist_scope_option', $scope_option );


		if( isset( $_POST['update_rows'] ) )
			$this->message->add( 'updated', __('La playlist du <strong>' . $str_date . '</strong> à été mise à jour.') );
		else
			$this->message->add( 'updated', __('La playlist du <strong>' . $str_date . '</strong> à été ajoutée.') );

		session_unset();
		session_destroy();

		return;
	}

	static function prepare_song( $song ) {

		// Check for excluded terms
		$excluded_terms = Campus_Daily_Playlist::get_excluded_terms();
		foreach( $excluded_terms as $excluded_term ) {
			if( preg_match( '/' . $excluded_term . '/', $song['category'] ) )
				return false;
		}

		$song = array_merge( self::$default_song_fields, (array) $song );

		$artist = $song['artist'];
		$title = $song['title'];
		$genre = $song['genre'];

		unset($song['artist']);
		unset($song['title']);
		unset($song['genre']);

		$new_artist = $new_title = false;

		// Prepare Artist
		if( $artist != '' ) {
			$artist_title = ucwords( $artist );
			$artist_slug = sanitize_title( $artist_title );
			$artist_taxonomy = self::$tax_prefix . '_artist';

			$term = get_term_by( 'slug', $artist_slug, $artist_taxonomy );

			if( $term ) {

				$term_id = (int) $term->term_id;

			} else {

				$new_term = wp_insert_term( $artist_title, $artist_taxonomy, array() );

				if( ! is_wp_error( $new_term ) ) {
					$term_id = (int) $new_term['term_id'];

					$new_artist = true;
				}
			}

			// Set value
			if( $term_id )
				$song['artist_term_id'] = $term_id;
		}

		// Prepare Title
		if( $title != '' ) {
			$title_title = ucwords( $title );
			$title_slug = sanitize_title( $title_title );
			$title_taxonomy = self::$tax_prefix . '_song';

			$term = get_term_by( 'slug', $title_slug, $title_taxonomy );

			if( $term ) {

				$term_id = (int) $term->term_id;

			} else {

				$new_term = wp_insert_term( $title_title, $title_taxonomy, array() );

				if( ! is_wp_error( $new_term ) ) {
					$term_id = (int) $new_term['term_id'];
					$new_title = true;
				}
			}

			// Set value
			if( $term_id )
				$song['title_term_id'] = $term_id;
		}

		// Prepare Genre
		if( $genre != '' ) {

			$genres = explode( '+', $genre );

			$number_of_genre = count( $genres );

			// Set default value
			$song['genre_term_id'] = $number_of_genre > 1 ? array() : '';

			foreach( $genres as $genre ) {

				$genre_title = ucwords( $genre );
				$genre_slug = sanitize_title( $genre_title );
				$genre_taxonomy = self::$tax_prefix . '_genre';

				$term = get_term_by( 'slug', $genre_slug, $genre_taxonomy );

				if( $term ) {

					$term_id = (int) $term->term_id;

				} else {

					$new_term = wp_insert_term( $genre_title, $genre_taxonomy, array() );
					if( ! is_wp_error( $new_term ) ) {
						$term_id = (int) $new_term['term_id'];
					}
				}

				// Set value
				if( $term_id ) {
					if( $number_of_genre > 1 )
						$song['genre_term_id'][] = $term_id;
					else
						$song['genre_term_id'] = $term_id;
				}
			}

			if( is_array( $song['genre_term_id'] ) )
				$song['genre_term_id'] = serialize( $song['genre_term_id'] );
		}

		// Add title meta to the new artist
		if( $new_artist && $song['title_term_id'] && $song['artist_term_id'] ) {
			add_term_meta( $song['artist_term_id'], self::$tax_prefix . '_song', $song['title_term_id'], true );
		}

		// Add artist meta to the new title
		if( $new_title && $song['artist_term_id'] && $song['title_term_id'] ) {
			add_term_meta( $song['title_term_id'], self::$tax_prefix . '_artist', $song['artist_term_id'], true );
		}

		return $song;
	}

	function delete_playlist() {
   		global $wpdb;

   		$table_name = self::$table_name;

		if( ! isset( $_POST['delete_playlist'] ) )
			return;

		$date = $_POST['delete_playlist'];
		$str_date = date_i18n( 'l j F Y', strtotime( $date ) );
		$delete = $wpdb->query( "DELETE FROM `$table_name` WHERE `time` LIKE '%$date%';" );

		if( $delete == false ) {
		    $this->message->add( 'error', __( 'Problème lors de la suppression des données. Contacte ton webmaster préféré ;)' ) );
		} else {
		    $this->message->add( 'updated', __( 'La playlist du <strong>' . $str_date . '</strong> à été supprimée.' ) );

		    $options = $this->playlist_options();
		    $options = array_flip( $options );
		    unset($options[$date]);
		    $options = array_flip( $options );

		    update_option( 'playlist_option', $options );
		}

		return;

	}

	function playlist_options() {
		return get_option( 'playlist_option', array() );
	}

	function playlist_scope_option() {
		return get_option( 'playlist_scope_option', array('begin' => '', 'end' => '') );
	}


	/**
	 * Display update message
	 *
	 */
	function display_message() {

		$messages = $this->message;

		if( ! empty( $messages->errors ) ) {
			foreach( $messages->errors as $code => $array_msg ) {
				echo '<div class="' . $code . '">';
					foreach( $array_msg as $msg ) {
						echo '<p>' . $msg . '</p>';
					}
				echo '</div>';
			}
		}
	}


	/**
	 * Display the "Add playlist" form
	 *
	 */
	function display_form() {

   		$messages = $this->message;

   		echo '<form id="add-playlist-form" action="' . $this->page_url . '" method="post" enctype="multipart/form-data">';

   			if( ! empty( $messages->error_data ) ) {
   				foreach( $messages->error_data as $data ) {
   					$this->update_form( $data );
   				}
   			} else {
   				$this->update_form();
   			}

   		echo '</form>';
	}


	/**
	 * Register playlist page
	 *
	 */
	function register_menu_page() {

		$this->page = add_menu_page(
			'Programmation automatique',
			'Prog auto',
			'manage_daily_playlists',
			'daily_playlist',
			array( $this, 'menu_page' ),
			'dashicons-playlist-audio',
			38
		);
	}

	function bulk_messages() {

		$bulk_counts = array(
			'updated'   => isset( $_REQUEST['updated'] )   ? absint( $_REQUEST['updated'] )   : 0,
			'deleted'   => isset( $_REQUEST['deleted'] )   ? absint( $_REQUEST['deleted'] )   : 0,
		);

		$bulk_messages = array(
			'updated'   => _n( '%s item updated.', '%s items updated.', $bulk_counts['updated'] ),
			'deleted'   => _n( '%s item deleted.', '%s items deleted.', $bulk_counts['deleted'] ),
		);
		/**
		 * Filters the bulk action updated messages.
		 *
		 * By default, custom post types use the messages for the 'post' post type.
		 *
		 * @since 3.7.0
		 *
		 * @param array $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
		 *                             keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
		 * @param array $bulk_counts   Array of item counts for each message, used to build internationalized strings.
		 */
		$bulk_messages = apply_filters( 'bulk_dailyplaylist_updated_messages', $bulk_messages, $bulk_counts );
		$bulk_counts = array_filter( $bulk_counts );

		// If we have a bulk message to issue:
		$messages = array();
		foreach ( $bulk_counts as $message => $count ) {
			$messages[] = sprintf( $bulk_messages[ $message ], number_format_i18n( $count ) );
		}

		if ( $messages )
			echo '<div id="message" class="updated notice is-dismissible"><p>' . join( ' ', $messages ) . '</p></div>';
		unset( $messages );
	}

	function page_title() {

		if( ! empty( $_REQUEST['song_date'] ) ) {
			$title = sprintf( '<a href="%s">%s</a> %s',
				$this->page_url,
				'<i class="dashicons-before dashicons-arrow-left-alt"></i>',
				ucfirst( date_i18n( 'l j F Y', strtotime( $_REQUEST['song_date'] ) ) )
			);

		} else {
			$title = 'Ajouter une playlist';
		}

		echo '<h2>' . $title . '</h2>';
	}

	/**
	 * Return admin page content
	 *
	 */
	function menu_page() {
		$this->read_playlist_file();
		$this->delete_playlist();
		?>
		<div class="wrap columns-2">

			<?php
				$this->page_title();
				$this->bulk_messages();
			?>

			<?php $this->display_message(); ?>

			<div id="poststuff" class="content-wrap content-list">
				<div id="post-body" class="metabox-holder columns-2">

					<div id="post-body-content">

						<?php if( empty( $_GET['song_date'] ) || ! $this->song_table ) : ?>

					    <div class="postbox">
					    	<div class="inside">
					    		<?php $this->display_form(); ?>
					    	</div>
					    </div>
					    <?php Campus_Daily_Playlist::display_daily_playlist_form( $this->page_url ); ?>

						<?php else: ?>

							<form id="songs-filter" method="post">
								<input type="hidden" name="page" value="<?php echo CAMPUS_DAILY_PLAYLIST_PAGE; ?>">
								<input type="hidden" name="song_date" value="<?php echo esc_attr( $_GET['song_date'] ); ?>">
								<?php $this->song_table->display(); ?>
							</form>

						<?php endif; ?>
					</div>

					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php $this->postbox(); ?>

						</div>
					</div>
				</div>
				<br class="clear">
			</div>

			<div class="clear"></div>
		</div>
		<?php
	}


	/**
	 * Output the step form when page is updated
	 *
	 */
	function update_form( $part = '' ) {

		switch( $part ) {
			case 'update_date':
				?>
				<input type="text" value="<?php echo date('d'); ?>" name="update_date[day]" size="2" maxlength="2" />
				<input type="text" value="<?php echo date('m'); ?>" name="update_date[month]" size="2" maxlength="2" />
				<input type="text" value="<?php echo date('Y'); ?>" name="update_date[year]" size="4" maxlength="4" />
				<input id="send" class="button" type="submit" value="Envoyer" name="update_send">
				<?php
				break;

			case 'update_row':
				$files = isset($_POST['tmp_files']) ? $_POST['tmp_files'] : $_FILES;
				$page_url = $this->page_url;
				?>
				<input id="update_rows" class="button" type="submit" value="Remplacer les données" name="update_rows" />
				<input id="reset" class="button" type="reset" value="Annuler" name="undo" onclick="location.href='<?php echo $page_url; ?>'" />
				<?php
				break;

			default:
				?>
				<input type="file" name="add_playlist_file" />
				<input id="send" class="button-primary" type="submit" value="Envoyer" name="send">
				<?php
				break;
		}
	}


	/**
	 * Content of the box in the sidebar
	 *
	 */
	function postbox() {
		$options = $this->playlist_options();
		$scope_option = $this->playlist_scope_option();

		$playlist_all = '';
		$playlist_date = '';
		$playlist_scope = '';

		$options_all = array_slice( $options, 0, 20 );

		foreach( $options_all as $option ) {
			$str_date = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( array( 'page' => esc_attr( $_REQUEST['page'] ), 'song_date' => $option ), admin_url( 'admin.php' ) ) ), ucfirst( date_i18n('l j F Y', strtotime($option) ) ) );
			$playlist_all .= '<li class="playlist-li" id="playlist-'.$option.'">'.$str_date;
			$playlist_all .= ' <button type="submit" class="ntdelbutton" name="delete_playlist" value="'.$option.'" title="Supprimer la playlist"><span class="remove-tag-icon" aria-hidden="true"></span><span class="screen-reader-text">Supprimer la playlist</span></button>';
			$playlist_all .= '</li>'."\n";
		}

		arsort( $options );
		$options_date = array_slice( $options, 0, 20 );

		foreach( $options_date as $option ) {
			$str_date = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( array( 'page' => esc_attr( $_REQUEST['page'] ), 'song_date' => $option ), admin_url( 'admin.php' ) ) ), ucfirst( date_i18n('l j F Y', strtotime($option) ) ) );
			$playlist_date .= '<li class="playlist-li" id="playlist-'.$option.'">'.$str_date;
			$playlist_date .= ' <button type="submit" class="ntdelbutton" name="delete_playlist" value="'.$option.'" title="Supprimer la playlist"><span class="remove-tag-icon" aria-hidden="true"></span><span class="screen-reader-text">Supprimer la playlist</span></button>';
			$playlist_date .= '</li>'."\n";
		}

		foreach( $scope_option as $scope ) {
			$playlist_scope .= '<li class="playlist-li" id="playlist-'.$scope.'">'.date_i18n('l j F Y', strtotime($scope)).'</li>';
		}


		?>
		<div class="postbox " id="categorydiv">
		    <form id="delete-playlist-form" action="" method="post">
		    	<div title="Cliquer pour inverser." class="handlediv"><br></div><h3 class="hndle"><span>Playlists</span></h3>
		    	<div class="inside">
		    		<div class="categorydiv" id="taxonomy-category">
		    			<ul class="category-tabs" id="category-tabs">
		    				<li class="tabs"><a tabindex="3" href="#category-all">Derniers ajouts</a></li>
		    				<li class="hide-if-no-js"><a tabindex="3" href="#category-pop">Par dates</a></li>
		    				<li class="hide-if-no-js"><a tabindex="3" href="#category-scope">Intervalle</a></li>
		    			</ul>

		    			<div style="display: none;" class="tabs-panel" id="category-pop">
		    				<ul class="categorychecklist tagchecklist form-no-clear" id="categorychecklist-pop">

		    					<?php echo $playlist_date; ?>

		    				</ul>
		    			</div>

		    			<div class="tabs-panel" id="category-all" style="display: block;">
		    				<ul class="list:category categorychecklist tagchecklist form-no-clear" id="categorychecklist">

		    					<?php echo $playlist_all; ?>

		    				</ul>
		    			</div>

		    			<div style="display: none;"  class="tabs-panel" id="category-scope">
		    				<ul class="list:category categorychecklist tagchecklist form-no-clear">

		    					<?php echo $playlist_scope; ?>

		    				</ul>
		    			</div>

		    		</div>
		    	</div>
		    </form>
		</div>
		<?php
	}


	/**
	 * Add playlist form to Dashboard
	 *
	 */
	function add_dashboard_widgets() {
		if( current_user_can('manage_daily_playlists') )
			wp_add_dashboard_widget( 'add_daily_playlist', 'Ajouter une playlist', array( $this, 'dashboard_widget' ) );
	}


	/**
	 * Output the contents of the Dashboard Widget
	 *
	 */
	function dashboard_widget() {
		$options = $this->playlist_options();

		$option = array_shift( $options );

		$this->display_form();

		$last = date_i18n( 'l j F Y', strtotime( $option ) );
		echo '<p>Dernier ajout : ' . $last . '</p>';
	}

	/**
	 * Add playlist taxonomy fields
	 *
	 */
	function campus_custom_term_fields( $fields, $taxonomy ) {

		if( $taxonomy == 'album_playlist' ) {
			$fields = $this->playlist_options_fields();
		}

		return $fields;
	}


	/**
	 * Define playlist taxonomy fields
	 *
	 */
	function playlist_options_fields() {

		if( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'album_playlist' && isset( $_GET['tag_ID'] ) )
			$term_id = (int) $_GET['tag_ID'];
		else if( isset( $_POST['taxonomy'] ) && $_POST['taxonomy'] == 'album_playlist' && isset( $_POST['tag_ID'] ) )
			$term_id = (int) $_POST['tag_ID'];
		else
			$term_id = false;

		$album_playlist_visibility = get_term_meta( $term_id, 'album_playlist_visibility', true );
		$album_playlist_associated_post = get_term_meta( $term_id, 'album_playlist_associated_post', true );

		$fields = array(
			'album_playlist_visibility' => array(
				'type' 			=> 'checkbox',
				'title' 		=> 'Visibilité',
				'description'	=> 'Cette playlist est visible sur le site.<br> <span class="description">Attention ! Cocher cette case va mettre en avant la playlist dans le flux d\'articles de la page d\'accueil, assurez-vous d\'avoir d\'abord ajouté des albums !</span>',
				'value'			=> $album_playlist_visibility
			),
			'album_playlist_associated_post' => array(
				'type' 			=> 'hidden',
				'title' 		=> 'Block associé à cette playlist',
				'description'	=> $album_playlist_associated_post ? '<a href="' . admin_url( 'post.php?post=' . $album_playlist_associated_post . '&action=edit' ) . '">Modifier l\'article</a>' : 'Pour associer un article, cochez la case de visibilité.',
				'value'			=> $album_playlist_associated_post
			),
			'order' => array(
				'type'  	  => 'select',
				'title' 	  => 'Ordre d\'affichage',
				'value' 	  => get_term_meta( $term_id, 'order', true ),
				'class'		  => '',
				'options'	  => array(
        			'ASC' => 'A-Z / 0-9',
        			'DESC' => 'Z-A / 9-0'
				)
			),
		);

		// Add publish date if there's no post
		if( ! $album_playlist_associated_post ) {
			$input = $fields['album_playlist_associated_post'];
			unset($input['title']);
			$fields['album_playlist_associated_post'] = array(
				'type' 		=> 'composed',
				'title' 	=> 'Block associé à cette playlist',
				'inputs'	=> array(
					'album_playlist_associated_post' => $input,
					'album_playlist_associated_post_date' => array(
						'type' 			=> 'date',
						'title' 		=> 'Date de publication',
						'value'			=> date( 'Y-m-d' )
					),
				)
			);
		}

		return $fields;
	}

	function translate_month_in_str( $str ) {
		return str_ireplace(
			array( 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre' ),
			array( 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december' ),
			$str
		);
	}

	/**
	 * term_fields_save function.
	 *
	 * @access public
	 * @param mixed $term_id Term ID being saved
	 * @param mixed $tt_id
	 * @param mixed $taxonomy Taxonomy of the term being saved
	 * @return void
	 */
	function term_fields_save( $term_id = null, $tt_id = null, $taxonomy = null ) {
		$term_id = ! is_null( $term_id ) ? $term_id : (int) $_POST['tag_ID'];
		$taxonomy = ! is_null( $taxonomy ) ? $taxonomy : $_POST['taxonomy'];

		if( ! $term_id || ! $taxonomy )
			return;

		if( $taxonomy != 'album_playlist' )
			return;

		$fields = $this->playlist_options_fields();

		$name = isset( $_POST['name'] ) ? $_POST['name'] : $_POST['tag-name'];
		$slug = $_POST['slug'];

		// Formate and update slug
		if( preg_match( '/[A-Z|a-z|\s]+/', $slug ) ) {
			$slug = date( 'Y-m', strtotime( $this->translate_month_in_str( $name ) ) );

			// unhook these functions to prevent infinite looping
			remove_action( 'created_term', array( $this, 'term_fields_save' ), 10, 3 );
			remove_action( 'edit_term', array( $this, 'term_fields_save' ), 10, 3 );

			// update the term slug
			wp_update_term( $term_id, $taxonomy, array(
				'slug' => $slug
			) );

			// re-hook these functions
			add_action( 'created_term', array( $this, 'term_fields_save' ), 10, 3 );
			add_action( 'edit_term', array( $this, 'term_fields_save' ), 10, 3 );
		}

		// If visibility is checked,
		// Create a post if there no one yet
		if( ! empty( $_POST['album_playlist_visibility'] ) && empty( $_POST['album_playlist_associated_post'] ) ) {

			$post_date = ! empty( $_POST['album_playlist_associated_post_date'] ) ? $_POST['album_playlist_associated_post_date'] . ' 00:00:00' : false;

			$associated_post = wp_insert_post( array(
				'post_date' => $post_date,
				'post_title' => $name,
				'post_content' => '',
				'post_name' => 'playlist-' . $slug,
				'post_status' => 'publish',
				'post_type' => 'block',
				'comment_status' => 'closed',
				'ping_status' => 'closed'
			) );

			if( $associated_post ) {
				update_post_meta( $associated_post, 'block_type', 'album_playlist' );
				update_post_meta( $associated_post, 'block_type_album_playlist', array( 'term_id' => $term_id, 'title' => '' ) );
				update_term_meta( $term_id, 'album_playlist_associated_post', $associated_post );
			}
		}
	}
}

new Campus_Daily_Playlist_Options();
