<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPPM_profile_update' ) ) :
  
    final class WPPM_profile_update {
    
        // constructor
        public function __construct() {
            $this->setup_actions();
        }
    
        /**
         * Setup the admin hooks, actions and filters
         *
         * @return void
         */
        function setup_actions() {
            // Bail if in network admin
            if ( is_network_admin() ) {
                return;
            }
    
            // User profile edit/display actions
            add_action( 'edit_user_profile', array($this, 'profile' ) );
            add_action( 'show_user_profile', array( $this, 'profile' ) );
            add_action( 'profile_update', array( $this, 'profile_update' ), 10, 2 );
        }
    
        function profile($profile_user){
            if ( ! current_user_can( 'edit_user', $profile_user->ID ) || !current_user_can( 'manage_options') ) {
                return;
            } ?>
            <h3><?php esc_html_e( 'Taskbuilder', 'taskbuilder' ); ?></h3>
            <?php
            $this->capbility_form( $profile_user );

            do_action( 'wppm_user_profile', $profile_user );

            wp_nonce_field( 'wppm_nonce', 'wppm_profile_nonce' );

        }
        protected function capbility_form( $user ) {
            global $wppmfunction, $wpdb;
            if ( user_can( $user->ID, 'manage_options' ) ) {
                return;
            }
    
            if ( $user->ID == get_current_user_id() ) {
                return;
            }
    
            $meta_value = get_user_meta( $user->ID, 'wppm_capability', true );
            ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><?php esc_html_e( 'Capability', 'taskbuilder' ); ?></th>
    
                        <td>
                            <fieldset>
                                <select name="wppm_capability">
                                    <option value="">
                                        <?php echo esc_html_e( '— No capability for this user —', 'taskbuilder' ); ?>
                                    </option>
                                    <?php
                                        foreach ( $wppmfunction->wppm_user_role() as $cap_key => $label ) { 
                                            $selected = (!empty($meta_value) && $cap_key == $meta_value) ? 'selected="selected"' : '';?>
                                            <option <?php echo $selected;?> value="<?php echo esc_attr( $cap_key ); ?>">
                                                <?php echo esc_html( $label['label'] ); ?>
                                            </option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }

        function profile_update( $user_id, $prev_data ) {
            global $wppmfunction,$wpdb;
            if (
                ! isset( $_POST['wppm_profile_nonce'] )
                ||
                ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wppm_profile_nonce'] ) ), 'wppm_nonce' )
            ) {
                return;
            }
    
            $cap_key = empty( $_POST['wppm_capability'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['wppm_capability'] ) );
    
            if ( !current_user_can( 'manage_options' ) ) {
                return;
            }
    
            $user_id = empty( $user_id ) ? 0 : absint( $user_id );
    
            $this->update_user_capability( $user_id, $cap_key );
    
            do_action( 'wppm_update_profile', $user_id, $prev_data );
        }

        function update_user_capability( $user_id, $cap_key ) {
            global $wppmfunction,$wpdb;
            if ( empty( $cap_key ) ) {
                update_user_meta( $user_id, 'wppm_capability', '' );
                $this->remove_capability( $user_id );
                return;
            }
    
            update_user_meta( $user_id, 'wppm_capability', $cap_key );
    
            $this->remove_capability( $user_id,$cap_key );
            $this->add_capability( $user_id, $cap_key );
    
        }
    
        function remove_capability( $user_id ) {
            global $wppmfunction,$wpdb;
            $user = get_user_by( 'id', $user_id );
    
            foreach ( $wppmfunction->wppm_user_role() as $meta_key => $label ) {
                $user->remove_cap( $meta_key );
            }
        }
    
        function add_capability( $user_id, $cap_key ) {
            global $wppmfunction,$wpdb;
            $user = get_user_by( 'id', $user_id );
            $user_cap_slug = $wppmfunction->wppm_user_role();
            foreach($user_cap_slug as $key=>$val){
                if($cap_key== $key){
                   $user->add_cap($key );
                }
            }
            $user->add_cap( $cap_key );
        }
    }
endif;

new WPPM_profile_update();