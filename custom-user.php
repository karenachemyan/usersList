<?php
/*
Plugin Name: User Table
Description: Custom HTML Users table...
Version: 1.0
Author: Karen Achemyan
*/

if (!defined('ABSPATH')) {
    exit;
}

class User_Table {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_get_users', [$this, 'get_users']);
    }

    public function add_admin_menu() {
        add_menu_page('User Table', 'User Table', 'manage_options', 'user-table', [$this, 'admin_page'], 'dashicons-admin-users', 6);
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Users Table List</h1>
            <select id="filter">
                <option value="">All Roles</option>
                <?php
                global $wp_roles;
                foreach ($wp_roles->roles as $role_slug => $role) {
                    echo '<option value="' . esc_attr($role_slug) . '">' . esc_html($role['name']) . '</option>';
                }
                ?>
            </select>
            <table id="user-table" class="table table-striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
            <nav aria-label="Page navigation example">
                <ul id="pagination" class="pagination">
                   
                </ul>
            </nav>
           
        </div>
        <?php
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_user-table') {
            return;
        }

        wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', ['jquery'], null, true);


        wp_enqueue_script('user-table', plugin_dir_url(__FILE__) . 'assets/users.js', ['jquery'], null, true);
        wp_localize_script('user-table', 'UserTable', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public function get_users() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $role = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : '';
        $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'username';
        $page = isset($_GET['page']) ? absint($_GET['page']) : 1;

        $args = [
            'number' => 10,
            'offset' => ($page - 1) * 10,
            'orderby' => $sort,
            'order' => 'ASC',
        ];

        if (!empty($role)) {
            $args['role'] = $role;
        }

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();

        ob_start();
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . esc_html($user->user_login) . '</td>';
            echo '<td>' . esc_html(implode(', ', $user->roles)) . '</td>';
            echo '<td>' . esc_html($user->user_email) . '</td>';
            echo '</tr>';
        }
        $table_rows = ob_get_clean();

        $total_pages = ceil($total_users / 10);
        ob_start();
        for ($i = 1; $i <= $total_pages; $i++) {
            echo '<li class="page-item"><a href="#" class="page-link" data-page="' . esc_attr($i) . '">' . esc_html($i) . '</a> </li>';
        }
        $pagination = ob_get_clean();

        wp_send_json_success(['rows' => $table_rows, 'pagination' => $pagination]);
    }
}

new User_Table();