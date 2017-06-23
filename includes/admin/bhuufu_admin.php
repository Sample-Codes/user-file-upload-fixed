<?php

class Bhuppu_Admin {

    static $short_code_added;
    public $errors;

    function __construct() {
        register_activation_hook(BHUUFU_PLUGIN_FILE, array(&$this, 'activation'));
        add_action('admin_init', array(&$this, 'enqueue'), 10);
        add_action('edit_user_profile', array(&$this, 'add_user_file_upload_fields'));
        add_action('edit_user_profile_update', array(&$this, 'save_user_file_upload_fields'));
        add_filter('upload_dir', array(&$this, 'user_upload_files_dir'));
        add_shortcode('list_user_files', array(&$this, 'user_uploaded_files_list'));
        add_filter('the_posts', array(&$this, 'conditionally_add_scripts_and_styles'));
        add_action('template_redirect', array(&$this, 'template_redirect'));
        add_action('wp_ajax_query-attachments', array(&$this, 'change_media_display'), 0);
        add_filter('wp_prepare_attachment_for_js', array(&$this, 'change_upload_media_display'),10,3);
        
        //ajax
        add_action('wp_ajax_deletefile', array(&$this, 'deletefile'));
        do_action('BHUUFU/init');
    }


    //setup on activation 
    public function activation() {
        //create sub directory
        $baseDir = WP_CONTENT_DIR . '/uploads/user-files/';
        wp_mkdir_p($baseDir);

        //create .htacess file
        $server_address = $_SERVER['SERVER_ADDR'];
        $filename = $baseDir . '.htaccess';
        if (!file_exists($filename)) {
            $file_handle = fopen($filename, "w") or die("Error: Unable to create .htaccess file");
            $content_string = "Options -Indexes\n";
            fwrite($file_handle, $content_string);
            $content_string = "Deny from all\n";
            fwrite($file_handle, $content_string);
            fclose($file_handle);
        }
    }

    //change upload directory
    public function user_upload_files_dir($upload) {
        //check if this a user-edit page
        $current_page = basename($_SERVER['HTTP_REFERER']);
        $current_page_tmp = explode("?", $current_page);
        $current_page = $current_page_tmp[0];
        if ($current_page != "user-edit.php")
            return $upload;

        // check if it a async-upload request
        $referer = basename($_SERVER['REQUEST_URI']);
        if ($referer != "async-upload.php")
            return $upload;

        //get userid from http http_referer
        $p = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($p["query"], $get);
        $user_id = $get['user_id'];

        //change upload directory to user-files/$user_id
        $baseDir = WP_CONTENT_DIR . '/uploads/user-files';
        $baseUrl = WP_CONTENT_URL . '/uploads/user-files';
        $upload['subdir'] = $user_id;
        $upload['path'] = $baseDir;
        $upload['url'] = $baseUrl;

        return $upload;
    }

    //Enqueue class assets\
    public function enqueue() {
        global $pagenow;
        if ($pagenow != "user-edit.php")
            return;
        // Enqueue styles
        wp_enqueue_style('bhuufu_admin_styles', BHUUFU_URL . '/assets/css/admin_styles.css');
        // Enqueue scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('bhuufu_repeatable-fields.js', BHUUFU_URL . '/assets/js/repeatable-fields.js');
        wp_enqueue_script('bhuufu_admin_script', BHUUFU_URL . 'assets/js/stb_admin.js');
        // Hook to add/remove files
        do_action('BHUUFU/assets/enqueue');
    }

    //conditionally_add_scripts_and_styles
    public function conditionally_add_scripts_and_styles($posts) {

        if (empty($posts))
            return $posts;
        $shortcode_found = false;
        foreach ($posts as $post) {

            if (stripos($post->post_content, '[list_user_files]') !== false) {
                $shortcode_found = true;
                break;
            }
        }

        if ($shortcode_found) {
            wp_enqueue_style('user-upload-css', BHUUFU_URL . 'assets/css/styles.css');
        }

        return $posts;
    }

//user_file_upload_fields
    function add_user_file_upload_fields($user) {
        $bhu_uufef = get_user_meta($user->ID, 'user_file_uploads', true);
        wp_enqueue_media();
        ?>
        <h3><?php _e('File Uploads', 'wpcf7'); ?></h3>
        <div class="ff-repeatable">
            <table>
                <thead>
                    <tr>
                        <th><?php _e('Url', 'wpcf7'); ?></th>
                        <th><?php _e('Name', 'wpcf7'); ?></th>
                        <th><?php _e('Decsription', 'wpcf7'); ?></th>
                        <th><img alt="Add Row" class="ff-add-row" src="<?php echo BHUUFU_URL; ?>assets/images/add.png"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="ff-add-template" style="">
                        <td><input type="text" name="bhuufu-user-uploads[file_url_tmp][]" class="medium-text file_url" value="" /></td>
                        <td><input type="text" name="bhuufu-user-uploads[file_name_tmp][]" class="medium-text file_name" value="" /></td>
                        <td><input type="text" name="bhuufu-user-uploads[file_description_tmp][]" class="medium-text file_description" value="" /></td>
                        <td>
                            <input type="hidden" name="bhuufu-user-uploads[file_id_tmp][]" class="medium-text file_id" value="" />
                            <input type="hidden" name="bhuufu-user-uploads[file_oname_tmp][]" class="medium-text file_oname" value="" />
                            <input type="hidden" name="bhuufu-user-uploads[file_mime_tmp][]" class="medium-text file_mime" value="" />
                            <input class="button _unique_name_button" name="_unique_name_button" value="Select File" />
                            <img alt="Remove Row" class="ff-remove-row" src="<?php echo BHUUFU_URL; ?>assets/images/remove.png">
                        </td>
                    </tr>
                    <?php
                    if (isset($bhu_uufef['file_url']) && $extra_fields = array_filter($bhu_uufef['file_url'])) {
                        foreach ($extra_fields as $key => $value) {
                            echo'
                     <tr>
                        <td><input type="text" name="bhuufu-user-uploads[file_url_tmp][]" class="medium-text file_url" value="' . $bhu_uufef['file_url'][$key] . '" /></td>
                        <td><input type="text" name="bhuufu-user-uploads[file_name_tmp][]" class="medium-text file_name" value="' . $bhu_uufef['file_name'][$key] . '" /></td>
                        <td><input type="text" name="bhuufu-user-uploads[file_description_tmp][]" class="medium-text file_description" value="' . $bhu_uufef['file_description'][$key] . '" /></td>
                        <td>
                        <input type="hidden" name="bhuufu-user-uploads[file_id_tmp][]" class="medium-text file_id" value="' . $bhu_uufef['file_id'][$key] . '" />
                        <input type="hidden" name="bhuufu-user-uploads[file_oname_tmp][]" class="medium-text file_oname" value="' . $bhu_uufef['file_oname'][$key] . '" />
                        <input type="hidden" name="bhuufu-user-uploads[file_mime_tmp][]" class="medium-text file_mime" value="' . $bhu_uufef['file_mime'][$key] . '" />
                        <input class="button _unique_name_button" name="_unique_name_button" value="Select File" />
                        <img alt="Remove Row" class="ff-remove-row" src="' . BHUUFU_URL . 'assets/images/remove.png">
                        </td>
                    </tr>';
                        }
                    } else {
                        echo'
                     <tr>
                        <td><input type="text" name="bhuufu-user-uploads[file_url_tmp][]" class="medium-text file_url" value="" /></td>
                        <td><input type="text" name="bhuufu-user-uploads[file_name_tmp][]" class="medium-text file_name" value="" /></td>
                        <td><input type="text" name="bhuufu-user-uploads[file_description_tmp][]" class="medium-text file_description"  value="" /></td>
                        <td>
                        <input type="hidden" name="bhuufu-user-uploads[file_id_tmp][]" class="medium-text file_id" value="" />
                        <input type="hidden" name="bhuufu-user-uploads[file_oname_tmp][]" class="medium-text file_oname" value="" />
                        <input type="hidden" name="bhuufu-user-uploads[file_mime_tmp][]" class="medium-text file_mime" value="" />
                        <input class="button _unique_name_button" name="_unique_name_button" value="Select File" />
                        <img alt="Remove Row" class="ff-remove-row" src="' . BHUUFU_URL . 'assets/images/remove.png">
                        </td> 
                    </tr>';
                    }
                    ?>
                </tbody>			
            </table>
        </div>
        <?php
    }

//user porfile fields save
    public function save_user_file_upload_fields($user_id) {
        if (isset($_POST['bhuufu-user-uploads']['file_url_tmp']) && $extra_fields = array_filter($_POST['bhuufu-user-uploads']['file_url_tmp'])) {
            foreach ($extra_fields as $key => $value) {
                $_POST['bhuufu-user-uploads']['file_url'][] = $_POST['bhuufu-user-uploads']['file_url_tmp'][$key];
                $_POST['bhuufu-user-uploads']['file_name'][] = $_POST['bhuufu-user-uploads']['file_name_tmp'][$key];
                $_POST['bhuufu-user-uploads']['file_description'][] = $_POST['bhuufu-user-uploads']['file_description_tmp'][$key];
                $_POST['bhuufu-user-uploads']['file_id'][] = $_POST['bhuufu-user-uploads']['file_id_tmp'][$key];
                $_POST['bhuufu-user-uploads']['file_oname'][] = $_POST['bhuufu-user-uploads']['file_oname_tmp'][$key];
                $_POST['bhuufu-user-uploads']['file_mime'][] = $_POST['bhuufu-user-uploads']['file_mime_tmp'][$key];
            }
        }
		
		$user = get_userdata( $user_id );
		$blog_title = get_bloginfo('name');
		
		$options = get_option( 'bhuufu_settings' );
		$pagelink =  get_page_link($options['filespage']);
				
		$to = $user->user_email;
		$subject = $blog_title . ' new file added';
		$body = $_POST['bhuufu-user-uploads']['file_name_tmp'][$key] . ' has been added to your account. Please <a href="'. $pagelink .'">login</a> to download the file.';
		$headers = array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );
		
        unset($_POST['bhuufu-user-uploads']['file_url_tmp']);
        unset($_POST['bhuufu-user-uploads']['file_name_tmp']);
        unset($_POST['bhuufu-user-uploads']['file_description_tmp']);
        unset($_POST['bhuufu-user-uploads']['file_id_tmp']);
        unset($_POST['bhuufu-user-uploads']['file_oname_tmp']);
        unset($_POST['bhuufu-user-uploads']['file_mime_tmp']);
        update_user_meta($user_id, 'user_file_uploads', $_POST['bhuufu-user-uploads']);

    }

    //delete file
    public function deletefile() {
        global $wpdb;
        $file_id = $_REQUEST["file_id"];
        $user_id = $_REQUEST["user_id"];

        // Delete file
        $is_deleted = wp_delete_attachment($file_id);
        if ($is_deleted->ID) {
            $bhu_uufef = get_user_meta($user_id, 'user_file_uploads', true);
            $key = array_search($file_id, $bhu_uufef['file_id']);
            unset($bhu_uufef['file_url'][$key]);
            unset($bhu_uufef['file_name'][$key]);
            unset($bhu_uufef['file_description'][$key]);
            unset($bhu_uufef['file_id'][$key]);
            unset($bhu_uufef['file_oname'][$key]);
            unset($bhu_uufef['file_mime'][$key]);
            update_user_meta($user_id, 'user_file_uploads', $bhu_uufef);
            $result['status'] = "sucess";
        } else {
            $result['status'] = "fail";
        }

        echo json_encode($result);

        exit;
    }

    //user frontend to display the download list
    public function user_uploaded_files_list() {
        if (is_user_logged_in()) {
            //list all files uploaded for user
            global $current_user;
            $output = "";
            $bhu_uufef = get_user_meta($current_user->ID, 'user_file_uploads', true);
            if (isset($bhu_uufef['file_url']) && $extra_fields = array_filter($bhu_uufef['file_url'])) {
                $count = 1;
                $output .= "<tr>
                            <th>#</th>
                            <th>File name</th>
                            <th>Description</th>
                            <th>Size</th>
                            <th>Download</th>
                            </tr>";
                foreach ($extra_fields as $key => $value) {
                    $wpnonce = wp_create_nonce("bhu_" . $bhu_uufef['file_id'][$key]);
                    $size = size_format(filesize(get_attached_file($bhu_uufef['file_id'][$key])));
                    $download_link = "<a href='" . BHUUFU_URL . 'download.php?id=' . $bhu_uufef['file_id'][$key] . "&amp;wpnonce=$wpnonce' title='Download'>Download</a>";
                    if ($count & 1)
                        $class = 'odd';
                    else
                        $class = 'even';
                    $output .= "<tr class='$class'>
                            <td>$count</td>
                            <td>{$bhu_uufef['file_name'][$key]}</td>
                            <td>{$bhu_uufef['file_description'][$key]}</td>
                            <td>$size</td>
                            <td>$download_link</td>
                            </tr>";
                    $count++;
                }
            } else {
                $output = "<tr><td colspan='5'><p>No current uploads</p></td></tr>";
            }
            $logout_url = '<a href="' . wp_logout_url(home_url()) . '" title="Logout">Logout</a>';
            $table = "<table>
                        <thead>
                        <tr>
                        <td colspan='4'>Welcome $current_user->user_login</td>
                        <td>$logout_url</td>
                        </tr>
                        </thead>
                        <tbody>
                        $output
                        </tbody>
                    </table>";
            $final_html = "<div class='user-download-files' >" . $table . "</div>";
            return $final_html;
        } else {
            $form = isset($_REQUEST['form']) ? $_REQUEST['form'] : '';
			
			$returnData = '';
			if($form == 'lostpassword') {
				$returnData .= '<div class="useruploadforms">';
				global $post ;
				if (is_wp_error($this->errors)){
					$errors = $this->errors;
    				foreach ( $errors as $error){
        				foreach ( $error as $data => $error_single){
							if ( $data == "incorrect_password" ) {
								$returnData .= "<p class='red'><strong>ERROR:</strong> The credentials are wrong.<a href='".get_page_link($post->ID)."?form=lostpassword' > Password Lost ?</a></p>";
							} 
							else {
								$returnData .= "<p class='red'>$error_single[0]</p>";
							}
						 }
					}
				}
				else 
					if( $this->errors == "sucess" ){
						$returnData .= "<p class='green'>Check your e-mail for the confirmation link.</p>";
					}
					else 
						if( isset($_REQUEST['error']) && $_REQUEST['error'] == "invalidkey" ){
							$returnData .= "<p class='red'>Invalid Key.</p>";
						}
				
				$returnData .= '<form name="lostpasswordform" action="" method="post">';
				$returnData .= '<p><p>Please enter your username or email address. You will receive a link to create a new password via email.</p>';
				$returnData .= '<label for="log">'._e( 'Username or E-mail:' ).'</label>';
				$returnData .= '<input type="text" name="log"class="input" value="" size="20" />';
				$returnData .= '</p>';
				$returnData .= '<p class="submit">';
				$returnData .= '<input type="submit" name="wp-submit" value="'.__( 'Get New Password' ).'" />';
				$returnData .= '<input type="hidden" name="redirect_to" value="'.get_page_link($post->ID)."?form=lostpassword".'" />';
				$returnData .= '<input type="hidden" name="do_process" value="lostpassword" />';
				$returnData .= '</p>';
				$returnData .= '</form>';
				$returnData .= '</div>';
				
				
				
			}
			else
				if($form == 'resetpassword') {
					$returnData .= '<div class="useruploadforms">';
					global $post ;
					if(is_wp_error($this->errors)){
						$errors = $this->errors;
						foreach ( $errors as $error){
							foreach ( $error as $data => $error_single){
								if ( $data == "incorrect_password" ) {
									$returnData .=  "<p class='red'><strong>ERROR:</strong> The credentials are wrong.<a href='".get_page_link($post->ID)."?form=lostpassword' > Password Lost ?</a></p>";
								} else {
									$returnData .= "<p class='red'>$error_single[0]</p>";
								}
							}
						}
					}
					
					$returnData .= '<form name="resetpasswordform" id="resetpasswordform" action="" method="post">';
					$returnData .= '<p>';
					$returnData .= '<label for="pass1">'. __( 'New password' ).'</label>';
					$returnData .= '<input name="pass1" id="pass1" class="input" size="20" value="" type="password" autocomplete="off" />';
					$returnData .= '</p>';

					$returnData .= '<p>';
					$returnData .= '<label for="pass2">'.__( 'Confirm new password' ).'</label>';
					$returnData .= '<input name="pass2" id="pass2" class="input" size="20" value="" type="password" autocomplete="off" />';
					$returnData .= '</p>';
					$returnData .= '<p class="submit">';
					$returnData .= '<input type="submit" name="wp-submit" id="wp-submit" value="'.__( 'Reset Password' ).'" />';
					$returnData .= '<input type="hidden" name="key" value="'.$_REQUEST['key'].'" />';
					$returnData .= '<input type="hidden" name="login" id="user_login" value="'.$_REQUEST['login'].'" />';
					$returnData .= '<input type="hidden" name="action" value="resetpass" />';
					$returnData .= '</p>';
					$returnData .= '</form>';
					$returnData .= '</div>';

				}
				else {
					$returnData .= '<div class="useruploadforms">';
					global $post,$error;
					$url = get_page_link($post->ID);
					$query = parse_url($url, PHP_URL_QUERY);
					if ($query) {
						$url .= '&';
					} else {
						$url .= '?';
					}
					
					if (is_wp_error($this->errors)){
						$errors = $this->errors;
						$data = key($errors->errors);
						if ( $data == "invalid_username" ) {
							$returnData .= "<p class='red'><strong>ERROR:</strong> Invalid username.<a href='". $url . "form=lostpassword' > Lost your password ?</a></p>";
						}
						else
							if ( $data == "incorrect_password" ) {
								$returnData .= "<p class='red'><strong>ERROR:</strong> The password is incorrect.<a href='". $url ."form=lostpassword' > Lost your password ?</a></p>";
							} 
							else {
								foreach ($errors->errors as $datas) {
									$returnData .= "<p class='red'>" . $datas[0] . "</p>";
								}
							}
					} 
					else
						if( isset($_GET['resetpass']) && $_GET['resetpass'] == "complete"){
							$returnData .= "<p class='green'>Password sucessfully changed.Now try to login again </p>";
						}
					do_action( 'login_head' );
					if ($error) {
						$returnData .= "<p class='red'>$error</p>";
					}
					
					$returnData .= '<form name="loginform" id="loginform" action="" method="post">';
				    $returnData .= '<p class="login-username">';
            		$returnData .= '<label for="user_login">Username</label>';
            		$returnData .= '<input type="text" name="log" id="user_login" class="input" value="" size="20">';
    				$returnData .= '</p>';
    				$returnData .= '<p class="login-password">';
            		$returnData .= '<label for="user_pass">Password</label>';
            		$returnData .= '<input type="password" name="pwd" id="user_pass" class="input" value="" size="20">';
    				$returnData .= '</p>';
    				$returnData .= '<p class="login-submit">';
            		$returnData .= '<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Log In">';
            		$returnData .= '<input type="hidden" name="redirect_to" value="'.get_page_link($post->ID).'">';
    				$returnData .= '</p>';
	
					$returnData .= '<p>';
					$returnData .= "<a href='".$url."form=lostpassword' > Lost your password ?</a>";
					$returnData .= '</p>';
					$returnData .= '</form>';
					$returnData .= '</div>';
				}
			
			return $returnData;
            
        }
    }

    //to handle login,reset,new password
    public function template_redirect() {
        $do_process = isset($_REQUEST['do_process']) ? $_REQUEST['do_process'] : '';
        
		$returnData = '';
		if($do_process == 'lostpassword') {
			$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
			global $post ;
			$redirect_to = get_page_link($post->ID);
			if($http_post) {
				global $wpdb, $current_site;
				$errors = new WP_Error();
				if ( empty( $_POST['log'] ) ) {
						$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or e-mail address.' ) );
				} else if ( strpos( $_POST['log'], '@' ) ) {
						$user_data = get_user_by( 'email', trim( $_POST['log'] ) );
						if ( empty( $user_data ) )
								$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.' ) );
				} else {
						$login = trim( $_POST['log'] );
						$user_data = get_user_by( 'login', $login );
				}
			
				if ( $errors->get_error_code() )
					$this->errors = $errors;
			
				if ( ! is_wp_error( $this->errors) ){
					if ( ! $user_data ) {
							$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or e-mail.' ) );
							$this->errors = $errors; 
					}
					if ( ! is_wp_error( $this->errors) ){
						$user_login = $user_data->user_login;
						$user_email = $user_data->user_email;
			
						$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );
						if ( empty( $key ) ) {
								// Generate something random for a key...
								$key = wp_generate_password( 20, false );
								do_action( 'retrieve_password_key', $user_login, $key );
								// Now insert the new md5 key into the db
								$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
						}
			
						$message = __( 'Someone requested that the password be reset for the following account:' ) . "\r\n\r\n";
						$message .= network_home_url( '/' ) . "\r\n\r\n";
						$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
						$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
						$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
						$message .= '<' .  "$redirect_to?form=resetpassword&do_process=resetpassword&key=$key&login=" . rawurlencode( $user_login ) . ">\r\n";
			
						// The blogname option is escaped with esc_html on the way into the database in sanitize_option
						// we want to reverse this for the plain text arena of emails.
						$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			
						$title = sprintf( __( '[%s] Password Reset' ), $blogname );
			
						$title = apply_filters( 'retrieve_password_title', $title, $user_data->ID );
						$message = apply_filters( 'retrieve_password_message', $message, $key, $user_data->ID );
			
						if ( $message && ! wp_mail( $user_email, $title, $message ) )
								wp_die( __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) );
						$this->errors = "sucess";
					}
				}
			}
			
		}
		else
			if($do_process == 'resetpassword') {
				global $post ;
				$redirect_to = get_page_link($post->ID);
				$user = self::check_password_reset_key($_REQUEST['key'], $_REQUEST['login']);
				if (is_wp_error($user)) {
					$redirect_to = $redirect_to.'?form=lostpassword&error=invalidkey';
					wp_redirect($redirect_to);
					exit;
				}
					$errors = new WP_Error();
				if (isset($_POST['pass1']) && empty($_POST['pass1']) && isset($_POST['pass2']) && empty($_POST['pass2'])) { 
					$errors->add('password_empty', __('The passwords are empty.'));
				} elseif (isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2']) {
					$errors->add('password_reset_mismatch', __('The passwords do not match.'));
				} elseif (isset($_POST['pass1']) && !empty($_POST['pass1'])) {
					self::reset_password($user, $_POST['pass1']);
				
					$redirect_to = $redirect_to.'?resetpass=complete';
					wp_safe_redirect($redirect_to);
					exit;
				}
				if ( $errors->get_error_code() )
					$this->errors = $errors;
				
			}
			else {
				if ( isset($_POST['wp-submit']) && $_POST['wp-submit'] = "Log In" ) {
				$redirect_to = $_REQUEST['redirect_to'];
				$secure_cookie = '';
			
				if ( !empty($_POST['log']) && !force_ssl_admin() ) {
						$user_name = sanitize_user($_POST['log']);
						if ( $user = get_user_by('login', $user_name) ) {
								if ( get_user_option('use_ssl', $user->ID) ) {
										$secure_cookie = true;
										force_ssl_admin(true);
								}
						}
				}
			
				$reauth = empty($_REQUEST['reauth']) ? false : true;
			
				if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
						$secure_cookie = false;
			
				if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
						$user = new WP_Error('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));
				else
						$user = wp_signon('', $secure_cookie);
			
				if ( !is_wp_error($user) && !$reauth ) {
						wp_safe_redirect($redirect_to);
						exit();
				}
				
				$this->errors = $user;
			}
				
				
			}
		
		
		
		
		
		
		switch ($do_process) {
            case 'lostpassword':
                include_once BHUUFU_PATH . '/includes/user/process/lostpassword-process.php';
                break;
            case 'resetpassword':
                include_once BHUUFU_PATH . '/includes/user/process/resetpassword-process.php';
                break;
            default:
                include_once BHUUFU_PATH . '/includes/user/process/login-process.php';
                break;
        }
    }

    //check password reset key
    public static function check_password_reset_key($key, $login) {
        global $wpdb;

        $key = preg_replace('/[^a-z0-9]/i', '', $key);

        if (empty($key) || !is_string($key))
            return new WP_Error('invalid_key', __('Invalid key'));

        if (empty($login) || !is_string($login))
            return new WP_Error('invalid_key', __('Invalid key'));

        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));

        if (empty($user))
            return new WP_Error('invalid_key', __('Invalid key'));

        return $user;
    }

    //reset password
    public static function reset_password($user, $new_pass) {

        wp_set_password($new_pass, $user->ID);
    }

    //alter images display on miedia uploader
    public function change_media_display() {
        //check if this a user-edit page
        $current_page = basename($_SERVER['HTTP_REFERER']);
        $current_page_tmp = explode("?", $current_page);
        $current_page = $current_page_tmp[0];
        
        // check if it a async-upload request
        $referer = basename($_SERVER['REQUEST_URI']);
        
        if ($current_page == "user-edit.php" && $referer == "admin-ajax.php") {
            
            if ( ! current_user_can( 'upload_files' ) )
		wp_send_json_error();
            
            $query = isset($_REQUEST['query']) ? (array) $_REQUEST['query'] : array();
            $query = array_intersect_key($query, array_flip(array(
                's', 'order', 'orderby', 'posts_per_page', 'paged', 'post_mime_type',
                'post_parent', 'post__in', 'post__not_in',
                    )));

            $query['post_type'] = 'attachment';
            if (current_user_can(get_post_type_object('attachment')->cap->read_private_posts))
                $query['post_status'] = 'private';

            /**
             * Filter the arguments passed to WP_Query during an AJAX call for querying attachments.
             *
             * @since 3.7.0
             *
             * @param array $query An array of query variables. @see WP_Query::parse_query()
             */
            $query = apply_filters('ajax_query_attachments_args', $query);
            $query = new WP_Query($query);

            $posts = array_map('wp_prepare_attachment_for_js', $query->posts);
            $posts = array_filter($posts);
//            foreach ($posts as $key => $post) {
//                $posts[$key]['type'] = 'images';
//            }
            wp_send_json_success($posts);
        }
    }
    
    //change upload media display
    public function change_upload_media_display($response, $attachment, $meta) {
        //check if this a user-edit page
        $current_page = basename($_SERVER['HTTP_REFERER']);
        $current_page_tmp = explode("?", $current_page);
        $current_page = $current_page_tmp[0];
        
        // check if it a async-upload request
        //$referer = basename($_SERVER['REQUEST_URI']);
        if ($current_page == "user-edit.php") {
            //change image type
            if ( $response['type'] = "image" )
                $response['type'] = "images" ;
            
            //change attachment status to private
            if ( $attachment->post_status = "inherit" ) {
                $attachment_change = array( 'ID' => $attachment->ID, 'post_status' => 'private' );
                wp_update_post( $attachment_change );
            }
        }
        
        return $response;
    }
    
}

new Bhuppu_Admin;