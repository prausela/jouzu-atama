<?php

    function has_url_param($params, $param_name){
        return isset($params) && isset($params[$param_name]);
    }

    function get_protocol($server) {
        $protocol = '';
        if (isset($server['HTTPS']) && ($server['HTTPS'] == 'on' || $server['HTTPS'] == 1) || isset($server['HTTP_X_FORWARDED_PROTO']) && $server['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }
        else {
            $protocol = 'http://';
        }
        return $protocol;
    }

?>