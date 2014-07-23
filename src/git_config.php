<?php
/**
 */

/**
 * Get the value for the specified configuration parameter.
 *
 * @param string $key    The configuration parameter.
 * @param mixed $default The default value if the configuration parameter is not found.
 * @return mixed The configuration value or the default value if not found.
 */
function igit_config_get( $key, $default = null ) {

    // Return the default value if settings aren't set or the key is missing.
    if ( false === ( $settings = get_option( IGIT_SETTINGS ) ) || ! isset( $settings[$key] ) ) {
        return $default;
    };

    return $settings[$key];

}