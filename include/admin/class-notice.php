<?php

/**
 * customise admin
 */

namespace Admin\JDAIO;

defined('ABSPATH') || exit;

class Notice
{


    public function __construct()
    {
        /* admin notice
         * notice-error – error message displayed with a red border
         * notice-warning – warning message displayed with a yellow border
         * notice-success – success message displayed with a green border
         * notice-info – info message displayed with a blue border
        */
        add_action('admin_notices', [$this, 'jdaio_admin_notice'], 2);
    }

    function jdaio_admin_notice()
    {
?>
<!--
        <div class="notice jaio-notice notice-warning is-dismissible">
            <p>This is an example of a notice that appears on the settings page.</p>
        </div>
        -->
<?php
    }
}
