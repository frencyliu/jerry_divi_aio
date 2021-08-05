<?php

/**
 * Element Shortcode
 * https://pagely.com/blog/creating-custom-shortcodes/
 */

namespace Shortcode\JDAIO;

use Jerry_Divi_AIO;

defined('ABSPATH') || exit;


class Test extends Jerry_Divi_AIO
{


    public function __construct()
    {
        add_action('init', [$this, 'jdaio_add_shortcode']);
    }

    function jdaio_add_shortcode(){
        add_shortcode('dotifollow', [ $this, 'dotifollow_function' ] );
    }

    function dotifollow_function( $atts = [], $content = null ) {
        // set up default parameters
    extract(shortcode_atts([
        'rating' => '5'
    ], $atts));

       return '<a href="https://twitter.com/DayOfTheIndie" target="blank" class="doti-follow">' . $content . '</a><br><h1>RATING:' . $rating . '</h1>';
    }


}
