<?php

/**
 * Sync Element
 * https://theme-fusion.com/documentation/avada/functions/fusion_builder_map/
 *
 * Available Input Fields
 * https://theme-fusion.com/documentation/avada/available-input-fields/
 *
 * Hint
 * use 'param_name'  => 'element_content', for shortcode enclosed content
 */

namespace Sync\JAIO;

use Jerry_AIO;

defined('ABSPATH') || exit;


class Element extends Jerry_AIO
{
    public function __construct()
    {
        add_action('fusion_builder_before_init', [$this, 'fusion_element_text_test'],999);
        add_filter( 'fusion_builder_all_elements', [$this, 'filter_function_name'] );
    }

    //Disable Element
    function filter_function_name( $elements ) {
        // Process $elements here
        //var_dump($elements);
        //unset($elements['fusion_alert']);
        return $elements;
    }


    function fusion_element_text_test(){
        if (function_exists('fusion_builder_map')) {
            fusion_builder_map(
                array(
                    'name'            => esc_attr__( 'ABC123', 'fusion-builder' ),
                    'shortcode'       => 'dotifollow',
                    'icon'            => 'fusiona-font',
                    'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-text-preview.php',
                    'preview_id'      => 'fusion-builder-block-module-text-preview-template',
                    'allow_generator' => true,
                    'params'          => array(
                        array(
                            'type'        => 'textfield',
                            'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
                            'description' => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
                            'param_name'  => 'element_content',
                            'value'       => esc_attr__( 'Click edit button to change this text.', 'fusion-builder' ),
                        ),
                        array(
                            'type'        => 'upload',
                            'heading'     => esc_attr__( 'upload', 'fusion-builder' ),
                            'description' => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
                            'param_name'  => 'upload',
                            'value'       => esc_attr__( 'Click edit button to change this text.', 'fusion-builder' ),
                        ),
                        array(
                            'type'        => 'range',
                            'heading'     => esc_attr__( 'upload', 'fusion-builder' ),
                            'description' => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
                            'param_name'  => 'range',
                            'value'       => esc_attr__( 'Click edit button to change this text.', 'fusion-builder' ),
                            'min'         => '10',
                            'max'         => '70',
                        ),
                    ),
                )
            );
        }
    }





/* 置換Element
function fusion_element_text_test() {

    fusion_builder_map(
        array(
            'name'            => esc_attr__( 'Text Block123', 'fusion-builder' ),
            'shortcode'       => 'fusion_text',
            'icon'            => 'fusiona-font',
            'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-text-preview.php',
            'preview_id'      => 'fusion-builder-block-module-text-preview-template',
            'allow_generator' => true,
            'params'          => array(
                array(
                    'type'        => 'tinymce',
                    'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
                    'description' => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
                    'param_name'  => 'element_content',
                    'value'       => esc_attr__( 'Click edit button to change this text.', 'fusion-builder' ),
                ),
            ),
        )
    );
}
    add_action( 'fusion_builder_before_init', 'fusion_element_text_test', 999 );
*/
}
