// to add RenewCourse feature in the Group Dashboard. 

Editing ld-group-registration/modules/templates/ldgr-group-users/tabs/enrolled-users-tab.template.php (active)

<td data-title="Renewal" class="wdm_ldgr_renew_course">
  <a
     href="#"
     data-user_id ="<?php echo esc_attr( $value ); ?>"
     data-group_id="<?php echo esc_attr( $group_id ); ?>"
     data-course_id="<?php echo esc_attr( $course_id ); ?>" 
     data-nonce="<?php echo esc_attr( wp_create_nonce( 'ldgr_nonce_renew_course' ) ); ?>"
     class="wdm_renew_course">
    <i class="fas fa-smile"></i> Renew Course
  </a>
</td>

//

// Editing ld-group-registration/modules/js/wdm_remove.js (active)

jQuery('body').on('click', '.wdm_renew_course', function (e) {
        e.preventDefault();
        var temp = jQuery(this);

        sendRenewCourseRequest(temp);
    });
	
	function sendRenewCourseRequest(temp) {
        var student_name = temp.parent().siblings('td[data-title="Name"]').text().trim();
        if (!student_name.length) {
            temp.parent().siblings('td').each(function (ind, obj) {
                if ('Name' === jQuery(obj).data('title')) {
                    student_name = jQuery(obj).text().trim();
                }
            })
        }
        var removal_message = "Are you sure you want to Renew the Course status for the following user? \n\n {user}";
        if (0 === student_name.length) {
            removal_message = removal_message.replace('{user}', '');
            removal_message = removal_message.replace('the following user', wdm_data.student_singular);
        }

        var removal_message = removal_message.replace('{user}', student_name);
        if (confirm(removal_message)) {
            // jQuery(temp).parent().append('<img id="wdm_ajax_loader" src="' + wdm_data.ajax_loader + '">');
            var user_id = jQuery(temp).data('user_id');
            var group_id = jQuery(temp).data('group_id');
			var course_id = jQuery(temp).data('course_id');
            var nonce = jQuery(temp).data('nonce');

            jQuery.ajax({
                type: "post",
                dataType: 'json',
                url: wdm_data.ajaxurl,
                data: {
                    action: 'wdm_renew_course',
                    user_id: user_id,
                    group_id: group_id,
					course_id: course_id,
                    nonce: nonce
                },
                timeout: 30000,
                beforeSend: function () {
                    temp.siblings('.dashicons-update').removeClass('hide');
                },
                complete: function () {
                    temp.siblings('.dashicons-update').addClass('hide');
                },
                success: function (response) {
                    jQuery.each(response, function (status, message) {
                        switch (status) {
                            case 'success':
                                snackbar(message);
                                if (wdm_data.admin_approve == 'on') {
                                    jQuery('#wdm_search_submit').trigger('submit');
                                }
                                else {
                                    temp.removeClass('wdm_remove');
                                    temp.addClass('request_sent');
                                    temp.text(wdm_data.request_sent);
                                }

                                break;
                            case 'error':
                                snackbar(message);
                                temp.siblings('.dashicons-update').addClass('hide');
                                break;
                        }
                    });
                    wdm_datatable.draw(false);
                },
                error: function () {
                    alert(wdm_data.error_msg);
                }
            });
        }
    }



/// Editing ld-group-registration/modules/classes/class-ld-group-registration-groups.php (active)

public function handle_renew_course() {
			check_ajax_referer( 'ldgr_nonce_renew_course', 'nonce' );

			$user_id  = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
			$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
			$course_id = filter_input( INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT );
			
			learndash_delete_course_progress($course_id,  $user_id );

			echo wp_json_encode( $this->remove_group_user( $user_id, $group_id ) );
			
			ld_update_group_access( $user_id,  $group_id );
			
/*			$user_info = get_userdata($user_id);
			$user_email = $user_info->user_email;

			$sub_groups         = learndash_get_group_children( $group_id );
			if ( empty( $sub_groups ) ) {
					$user = get_user_by( 'email', $user_email );
					if ( ! empty( $user ) ) {
						$user_check_in_group = learndash_get_users_group_ids( $user_id, $group_id );
						if ( in_array( $group_id, $user_check_in_group ) ) {
							$user_already_in_parent_group [] = $user_email;
						}
					}
			}

			if ( isset( $user_already_in_parent_group ) && ! empty( $user_already_in_parent_group ) ) {
				$error_data_for_enroll_users = array(
					'status' => 'failed',
					'users'  => $user_already_in_parent_group,
					'msg'    => sprintf(
						// translators: group, sub-group.
						esc_html__( 'This user is already part of the main %1$s and cannot be added to this %2$s.', 'wdm_ld_group' ),
						\LearnDash_Custom_Label::label_to_lower( 'group' ),
						\LearnDash_Custom_Label::label_to_lower( 'subgroup' )
					),
				);
				echo wp_json_encode( $error_data_for_enroll_users );
			} else {
				echo wp_json_encode( array( 'status' => 'success' ) );
			}
*/			
			die();
		}



/// Editing ld-group-registration/includes/class-ld-group-registration.php (active)

$this->loader->add_action( 'wp_ajax_wdm_renew_course', $plugin_groups, 'handle_renew_course' );
