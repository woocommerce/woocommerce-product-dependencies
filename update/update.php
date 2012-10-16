<?php

class WpPluginAutoUpdate {
    # URL to check for updates, this is where the index.php script goes
    public $api_url;

    # Type of package to be updated
    public $package_type;

    public $plugin_slug;
    public $plugin_file;

    public function WpPluginAutoUpdate($api_url, $type, $slug) {
        $this->api_url = $api_url;
        $this->package_type = $type;
        $this->plugin_slug = $slug;
        $this->plugin_file = $slug .'/'. $slug . '.php';
    }

    public function print_api_result($res, $action, $args) {
        print_r($res);
        return $res;
    }

    public function check_for_plugin_update($checked_data) {
        if (empty($checked_data->checked))
            return $checked_data;
        
        $request_args = array(
            'slug' => $this->plugin_slug,
            'version' => $checked_data->checked[$this->plugin_file],
            'package_type' => $this->package_type,
        );

        $request_string = $this->prepare_request('basic_check', $request_args);
        
        // Start checking for an update
        $raw_response = wp_remote_post($this->api_url, $request_string);

        if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)) {
            $response = unserialize($raw_response['body']);

            if (is_object($response) && !empty($response)) // Feed the update data into WP updater
                $checked_data->response[$this->plugin_file] = $response;
        }
        
        return $checked_data;
    }

    public function plugins_api_call($result, $action, $args) {
        if ($args->slug != $this->plugin_slug)
            return $result;
        
        // Get the current version
        $plugin_info = get_site_transient('update_plugins');
        $current_version = $plugin_info->checked[$this->plugin_file];
        $args->version = $current_version;
        $args->package_type = $this->package_type;
        
        $request_string = $this->prepare_request($action, $args);
        
        $request = wp_remote_post($this->api_url, $request_string);

        if (is_wp_error($request)) {
            $res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
        } else {
            $res = unserialize($request['body']);
            
            if ($res === false)
                $res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
        }

        return $res;
    }

    public function prepare_request($action, $args) {
        $site_url = site_url();

        $wp_info = array(
            'site-url' => $site_url,
            'version' => $wp_version,
        );

        return array(
            'body' => array(
                'action' => $action, 'request' => serialize($args),
                'api-key' => md5($site_url),
                'wp-info' => serialize($wp_info),
            ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
        );
    }

}

?>