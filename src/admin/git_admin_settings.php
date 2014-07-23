<?php
/**
 */


/**
 * Create a menu entry in WordPress *Settings* menu.
 *
 * @uses igit_admin_options_page to display the options page.
 */
function igit_admin_menu() {

    add_options_page(
        __( 'Git Deploy', IGIT_LANGUAGE_DOMAIN ),
        __( 'Git Deploy', IGIT_LANGUAGE_DOMAIN ),
        'manage_options',
        'igit',
        'igit_admin_options_page'
    );

}
add_action( 'admin_menu', 'igit_admin_menu' );

/**
 * Display the 10igit options page.
 */
function igit_admin_options_page() {
    ?>
    <div class="wrap">
        <h2><?php esc_html_e( 'Git Options', IGIT_LANGUAGE_DOMAIN ) ?></h2>
        <form action="options.php" method="POST">
            <?php settings_fields( 'igit' ); ?>
            <?php do_settings_sections( 'igit' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

/**
 * Register 10chat settings and related configuration screen.
 */
function igit_admin_settings() {

    // Register the settings.
    register_setting( 'igit', IGIT_SETTINGS );

    // Add the general section.
    add_settings_section(
        'igit_settings_section',
        'General settings',
        'igit_admin_settings_section_callback',
        'igit'
    );

    // Add the field for Application Key.
    add_settings_field(
        IGIT_SETTINGS_ARCHIVE_DIRECTORY,
        __( 'Archive Directory', IGIT_LANGUAGE_DOMAIN ),
        'igit_admin_settings_input_text',
        'igit',
        'igit_settings_section',
        array(
            'name'    => IGIT_SETTINGS_ARCHIVE_DIRECTORY,
            'default' => igit_config_get( IGIT_SETTINGS_ARCHIVE_DIRECTORY, sys_get_temp_dir() )
        )
    );

    // Add the field for Application Key.
    add_settings_field(
        IGIT_SETTINGS_REPOSITORIES_DIRECTORY,
        __( 'Repos Directory', IGIT_LANGUAGE_DOMAIN ),
        'igit_admin_settings_input_text',
        'igit',
        'igit_settings_section',
        array(
            'name'    => IGIT_SETTINGS_REPOSITORIES_DIRECTORY,
            'default' => igit_config_get( IGIT_SETTINGS_REPOSITORIES_DIRECTORY, sys_get_temp_dir() . 'igit-repos/' )
        )
    );

    // Add the field for default OTP TTL.
    add_settings_field(
        IGIT_SETTINGS_KEY,
        __( 'Authentication Key', IGIT_LANGUAGE_DOMAIN ),
        'igit_admin_settings_input_text',
        'igit',
        'igit_settings_section',
        array(
            'name'    => IGIT_SETTINGS_KEY,
            'default' => igit_config_get( IGIT_SETTINGS_KEY, wp_generate_password( 64, false, false ) )
        )
    );

}
add_action( 'admin_init', 'igit_admin_settings' );

/**
 * Print the general section header.
 */
function igit_admin_settings_section_callback() {

    echo '<p>' .
        esc_html__( 'Set here the basic settings for 10chat.' ) .
        '</p>';

}


/**
 * Print an input box with the specified name. The value is loaded from the stored settings. If not found, the default
 * value is used.
 *
 * @uses igit_get_option to get the option value.
 *
 * @param array $args An array with a *name* field containing the option name and a *default* field with its default
 *                    value.
 */
function igit_admin_settings_input_text( $args ) {

    $value_e = esc_attr( igit_config_get( $args['name'], $args['default'] ) );
    $name_e  = esc_attr( $args['name'] );

    echo "<input name='" . IGIT_SETTINGS . "[$name_e]' type='text' value='$value_e' size='40' />";
}