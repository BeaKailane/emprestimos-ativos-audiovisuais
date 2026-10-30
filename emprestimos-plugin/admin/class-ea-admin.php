<?php
/**
 * Classe para área administrativa
 */

if (!defined('ABSPATH')) {
    exit;
}

class EA_Admin {
    
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
    }
    
    public static function add_menu() {
        add_menu_page(__('Empréstimos', 'emprestimos-ativos'), __('Empréstimos', 'emprestimos-ativos'), 'manage_options', 'emprestimos', array(__CLASS__, 'page_dashboard'), 'dashicons-portfolio', 30);
        add_submenu_page('emprestimos', __('Dashboard', 'emprestimos-ativos'), __('Dashboard', 'emprestimos-ativos'), 'manage_options', 'emprestimos', array(__CLASS__, 'page_dashboard'));
        add_submenu_page('emprestimos', __('Ativos', 'emprestimos-ativos'), __('Ativos', 'emprestimos-ativos'), 'manage_options', 'ea-ativos', array(__CLASS__, 'page_assets'));
        add_submenu_page('emprestimos', __('Empréstimos', 'emprestimos-ativos'), __('Empréstimos', 'emprestimos-ativos'), 'manage_options', 'ea-emprestimos', array(__CLASS__, 'page_loans'));
        add_submenu_page('emprestimos', __('Setores', 'emprestimos-ativos'), __('Setores', 'emprestimos-ativos'), 'manage_options', 'ea-setores', array(__CLASS__, 'page_sectors'));
        add_submenu_page('emprestimos', __('Funcionários', 'emprestimos-ativos'), __('Funcionários', 'emprestimos-ativos'), 'manage_options', 'ea-funcionarios', array(__CLASS__, 'page_employees'));
        add_submenu_page('emprestimos', __('Histórico', 'emprestimos-ativos'), __('Histórico', 'emprestimos-ativos'), 'manage_options', 'ea-historico', array(__CLASS__, 'page_history'));
        add_submenu_page('emprestimos', __('Configurações', 'emprestimos-ativos'), __('Configurações', 'emprestimos-ativos'), 'manage_options', 'ea-configuracoes', array(__CLASS__, 'page_settings'));
    }
    
    public static function page_dashboard() { include EA_PLUGIN_DIR . 'admin/views/dashboard.php'; }
    public static function page_assets() { include EA_PLUGIN_DIR . 'admin/views/assets.php'; }
    public static function page_loans() { include EA_PLUGIN_DIR . 'admin/views/loans.php'; }
    public static function page_sectors() { include EA_PLUGIN_DIR . 'admin/views/sectors.php'; }
    public static function page_employees() { include EA_PLUGIN_DIR . 'admin/views/employees.php'; }
    public static function page_history() { include EA_PLUGIN_DIR . 'admin/views/history.php'; }
    public static function page_settings() { include EA_PLUGIN_DIR . 'admin/views/settings.php'; }
}
