<?php
/**
 */

/**
 * Receive post hooks from remote repos. The request requires a key parameter which is validated with the key
 * from the settings.
 */
function igit_ajax_post_hook() {

    // Check that the key is set.
    if ( ! isset( $_GET['key'] ) || igit_config_get( IGIT_SETTINGS_KEY ) !== $_GET['key'] ) {
        wp_die( __( 'The key parameter is not set or is invalid.', IGIT_LANGUAGE_DOMAIN ) );
    }

    // TODO: check that the key is valid.

    // Get the request body.
    if ( ! isset( $_POST['payload'] ) || empty( $_POST['payload']) ) {
        wp_die( __( 'The payload parameter is not set or is empty.', IGIT_LANGUAGE_DOMAIN ) );
    }

    // Get the directory and filename where to store the data.
    $body      = str_replace( '\"', '"', $_POST['payload'] );
    $body      = str_replace( "\\'", "'", $body );
    $directory = igit_config_get( IGIT_SETTINGS_ARCHIVE_DIRECTORY, sys_get_temp_dir() );
    $filename  = tempnam( $directory, 'igit-' );
    if ( false === file_put_contents( $filename , $body ) ) {
        wp_die( __( 'An error occurred while trying to save the data locally.', IGIT_LANGUAGE_DOMAIN ) );
    }

    igit_write_log( 'Data saved locally [ filename :: {filename} ]', array( 'filename' => $filename ) );

    // Extract repository data and clone it.
    $json = json_decode( $body );

    // Validate the JSON.
    if ( ! isset( $json->canon_url )
        || ! isset( $json->repository->absolute_url )
        || ! isset( $json->repository->slug ) ) {
        igit_write_log( 'JSON data is invalid' );
        wp_die( __( 'JSON data is invalid.', IGIT_LANGUAGE_DOMAIN ) );
    }

    // Get the repo GIT URL.
    $url  = $json->canon_url . $json->repository->absolute_url;
    // Get the repo slug.
    $slug = $json->repository->slug;

    $url  = "git@bitbucket.org:" . substr( $json->repository->absolute_url, 1 );

    // Clone the repo.
    igit_git_clone( $url, $slug );

    echo $body;

}
add_action( 'wp_ajax_nopriv_igit_post_hook', 'igit_ajax_post_hook' );


/**
 * Clone the repo from the specified URL to the local repo folder using the repo slug.
 *
 * @param string $url  The remote repo URL.
 * @param string $slug The repo slug.
 */
function igit_git_clone( $url, $slug) {

    // TODO: we should create repos according to some unique ID across repositories.

    // The git command line.
    $git       = igit_config_get( IGIT_SETTINGS_GIT, '/usr/bin/git' );

    // Get the full path to the local repo.
    $directory = igit_config_get( IGIT_SETTINGS_REPOSITORIES_DIRECTORY, sys_get_temp_dir() . '/igit-repos/'  ) . $slug;

    // Prepare for GIT PULL or CLONE accordingly.
    $command = "$git ";
    if ( file_exists( $directory ) && is_dir( $directory ) ) {
        // Update
        chdir($directory );
        $command .= "pull";
    } else {
        // Set up the command line.
        $command .= "clone $url $directory";
    }

    igit_write_log( 'Executing [ command :: {command} ]', array( 'command' => $command ) );

    // Add the key for the bitbucket host.
    igit_ssh_add_key( "bitbucket.org" );

    // Exec the command line.
    $output  = shell_exec( $command );

    igit_write_log( 'Executed [ output :: {output} ]', array( 'output' => $output ) );

    $config  = file_get_contents( $directory . '/igit.json' );
    $json    = json_decode( $config );

    // Check that the slug is specified.
    if ( ! isset( $json->slug ) ) {
        igit_write_log( __( 'The repo configuration is missing the slug name.', IGIT_LANGUAGE_DOMAIN ) );
        wp_die( __( 'The repo configuration is missing the slug name.', IGIT_LANGUAGE_DOMAIN ) );
    }

    $plugin_dir = WP_PLUGIN_DIR . '/' . $json->slug . '/';
    $copy_r     = "/bin/cp -R $directory/src/* $plugin_dir";

    igit_write_log( 'Copying [ command :: {command} ]', array( 'command' => $copy_r ) );

    $output     = shell_exec( $copy_r );

    igit_write_log( 'Copied [ output :: {output} ]', array( 'output' => $output ) );

}

function igit_ssh_add_key( $hostname ) {

    shell_exec( "ssh-keygen -R $hostname" );
    shell_exec( "ssh-keyscan -H $hostname >> /var/www/.ssh/known_hosts" );

}