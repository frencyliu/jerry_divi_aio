<?php

/**
 * Video post type
 */

namespace JDAIO\Column;

use Jerry_Divi_AIO;

defined('ABSPATH') || exit;

//秀幾個月前的訂單金額


class _Column extends Jerry_Divi_AIO
{

    public function __construct()
    {


        if (!TAG_ENABLE) {
            add_filter("manage_post_posts_columns", function ($columns) {
                unset($columns['tags']);
                unset($columns['cs_replacement-hide']);
                return $columns;
            }, 200);
        }

        //設定欄位標題
        add_filter('manage_users_columns', [$this, 'set_custom_edit_users_columns'], 200, 1);
        //設定欄位值
        add_filter('manage_users_custom_column', [$this, 'custom_users_column'], 200, 3);
        //add_action( 'admin_head', [$this, 'get_order_data_by_user_date'],200 );

        //排序
        add_filter('users_list_table_query_args', function ($args) {
            if (isset($_REQUEST['ts_all'])) {
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_total_sales_in_life';
                $args['order'] = $_REQUEST['ts_all'];
                return $args;
            }
            for ($i = 0; $i < 3; $i++) {
                if (isset($_REQUEST['ts' . $i])) {
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = '_total_sales_in_' . $i . '_months_ago';
                    $args['order'] = $_REQUEST['ts' . $i];
                }
            }
            return $args;
        }, 200, 1);
    }

    static public function get_order_history_num()
    {
        $default_order_history = 4;
        return apply_filters('yc_change_order_history', $default_order_history);
    }

    /** !!!!!!!!!!!!!!!!!!!!
     * 取得客戶訂單
     *
     * 時間參考
     * //https://wisdmlabs.com/blog/query-posts-or-comments-by-date-time/
     */
    public function get_order_data_by_user_date($user_id, $months_ago = 0, $args = array())
    {
        $user = get_userdata($user_id);
        $that_date = strtotime("first day of -" . $months_ago . " month", time());
        $that_date = strtotime("first day of +1 month", $that_date);

        $user_registed_time = strtotime($user->data->user_registered);
        $is_registered = ($user_registed_time >=  $that_date) ? false : true;


        $month = current_time('m') - $months_ago;
        $year = current_time('Y');





        if (empty($args)) {
            $args = array(
                'numberposts' => -1,
                'meta_key'    => '_customer_user',
                'meta_value'  => $user_id,
                'post_type'   => array('shop_order'),
                'post_status' => array('wc-completed', 'wc-processing'),
                'date_query' => array(
                    'year'  => $year,
                    'month' => $month
                )
            );
        }
        $customer_orders = get_posts($args);
        $total = 0;
        foreach ($customer_orders as $customer_order) {
            $order = wc_get_order($customer_order);
            $total += $order->get_total();
        }
        $order_data['total'] = $total;
        $order_data['order_num'] = count($customer_orders);
        $order_data['user_is_registered'] = $is_registered;

        $user_role = get_user_by('id', $user_id)->roles[0];
        //var_dump($user_role);
        switch ($user_role) {
            case 'sh_vendor_a': //Administrator sh_vendor_a
                $goal = 20000;
                $order_data['goal'] = ($order_data['total'] >= $goal) ? 'yes' : 'no'; //yes=達標 no=不達標
                break;
            case 'administrator': //administrator sh_vendor_a
                $goal = 20000;
                $order_data['goal'] = ($order_data['total'] >= $goal) ? 'yes' : 'no'; //yes=達標 no=不達標
                break;
            case 'sh_vendor_b':
                $goal = 6000;
                $order_data['goal'] = ($order_data['total'] >= $goal) ? 'yes' : 'no'; //yes=達標 no=不達標
                break;
            default:
                $goal = 0;
                $order_data['goal'] = 'no_goal';
                break;
        }

        return $order_data;
    }






    function set_custom_edit_users_columns($columns)
    {
        if (!class_exists('WooCommerce', false)) return $columns;
        //$columns['user_id'] = 'User ID';
        $order = (@$_REQUEST['ts_all'] == 'DESC') ? 'ASC' : 'DESC';
        $columns['total_order_amount'] = '<a title="用戶註冊後至今累積總消費金額" href="?ts_all=' . $order . '">全部</a>';

        for ($i = 0; $i < SELF::get_order_history_num(); $i++) {
            $order = (@$_REQUEST['ts' . $i] == 'DESC') ? 'ASC' : 'DESC';
            $the_date = date('Y年m', strtotime("-" . $i . " month"));
            //$month = current_time('m') - $i;
            $columns['ts' . $i] = '<a title="' . $the_date . '月累積採購金額" href="?ts' . $i . '=' . $order . '">' . $the_date . '月</a>';
        }
        $columns['goal_gg'] = '<span title="連續未達標幾個月等狀態(不計算當月)">狀態</span>'; //

        unset($columns['wfls_last_login']);
        unset($columns['posts']);
        unset($columns['wfls_2fa_status']);
        // ["cb"]=> string(25) "" ["username"]=> string(8) "Username" ["name"]=> string(4) "Name" ["email"]=> string(5) "Email" ["role"]=> string(4) "Role" ["posts"]=> string(5) "Posts" ["wfls_2fa_status"]=> string(10) "2FA Status" ["wfls_last_login"]=> string(10) "Last Login" ["user_id"]=> string(7) "User ID" } array(9) { ["cb"]=> string(25) "" ["username"]=> string(8) "Username" ["name"]=> string(4) "Name" ["email"]=> string(5) "Email" ["role"]=> string(4) "Role" ["posts"]=> string(5) "Posts" ["wfls_2fa_status"]=> string(10) "2FA Status" ["wfls_last_login"]=> string(10) "Last Login" ["user_id"]=> string(7) "User ID" }
        return $columns;
    }

    function custom_users_column($default_value, $column_name, $user_id)
    {

        $default_value = '0';
        //$user = get_userdata( $user_id );
        for ($i = 0; $i < SELF::get_order_history_num(); $i++) {
            if ($column_name == 'ts' . $i) {
                $order_data = $this->get_order_data_by_user_date($user_id, $i);

                if ($order_data['user_is_registered']) {
                    switch ($order_data['goal']) {
                        case 'no_goal':
                            $text = '';
                            break;
                        case 'yes':
                            $text = '<span class="jdaio_success">達標<span>';
                            break;
                        case 'no':
                            $text = '<span class="jdaio_warning">不達標<span>';
                            break;
                        default:
                            $text = '';
                            break;
                    };
                    $html = 'NT$ ' . $order_data['total'] . '<br>訂單' . $order_data['order_num'] . '筆<br>' . $text;
                } else {
                    $html = '<span class="jdaio_general">當時尚未註冊</span>';
                }

                update_user_meta($user_id, '_total_sales_in_' . $i . '_months_ago', strip_tags($html));

                return $html;
            }
        }
        switch ($column_name) {
            case 'goal_gg':

                $total_in_six_months = 0;
                for ($i = 1; $i < 7; $i++) {
                    $order_data = $this->get_order_data_by_user_date($user_id, $i);
                    $goal_status_in_six_months[$i] = $order_data['goal'];
                    $total_in_six_months += $order_data['total'];
                }

                $count = 0;
                for ($i = 1; $i < 7; $i++) {
                    if ($goal_status_in_six_months[$i] == 'yes')
                        $count++;
                }
                if ($count == 6) { //連續N個月達標
                    $bonus = $total_in_six_months * 0.05;
                    $output = '<span class="jdaio_success">連續' . $count . '個月達標</span><br>總採購金額NT$ ' . $total_in_six_months . '<br>5%金額為NT$ ' . $bonus;
                    update_user_meta($user_id, 'goal_gg', strip_tags($output));
                    return $output;
                }


                for ($i = 1; $i < 7; $i++) {
                    $order_data = $this->get_order_data_by_user_date($user_id, $i);
                    if ($order_data['user_is_registered']) {
                        if ($order_data['goal'] == 'no') {
                            if ($i >= 4) {
                                $j = $i - 1;
                                $output = '<span class="jdaio_warning">連續' . $j . '個月未達標</span>';
                                update_user_meta($user_id, 'goal_gg', strip_tags($output));
                                return $output;
                                exit;
                            }
                        } elseif ($i == 1 && $order_data['goal'] == 'yes') {
                            $output = '';
                            update_user_meta($user_id, 'goal_gg', strip_tags($output));
                            return $output;
                            exit;
                        } elseif ($order_data['goal'] == 'yes') {
                            $j = $i - 1;
                            $output = '<span class="jdaio_warning">連續' . $j . '個月未達標</span>';
                            update_user_meta($user_id, 'goal_gg', strip_tags($output));
                            return $output;
                            exit;
                        }
                    } else {
                        $output = '<span class="jdaio_general">註冊未滿3個月</span>';
                        update_user_meta($user_id, 'goal_gg', strip_tags($output));
                        return $output;
                    }
                }

                break;
            case 'total_order_amount':
                $args = array(
                    'numberposts' => -1,
                    'meta_key'    => '_customer_user',
                    'meta_value'  => $user_id,
                    'post_type'   => array('shop_order'),
                    'post_status' => array('wc-completed', 'wc-processing'),
                );
                $order_data = $this->get_order_data_by_user_date($user_id, 0, $args);
                update_user_meta($user_id, '_total_sales_in_life', $order_data['total']);

                $html = 'NT$ ' . $order_data['total'] . '<br>訂單' . $order_data['order_num'] . '筆';
                return $html;

            default:
                return $default_value;
                break;
        }
    }
}


    new _Column();
