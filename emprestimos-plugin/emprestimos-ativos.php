<?php
/**
 * Plugin Name: Sistema de Empréstimos de Ativos Audiovisuais
 * Plugin URI: https://revistaoeste.com
 * Description: Sistema completo para gerenciamento de empréstimos de equipamentos audiovisuais com notificações via WhatsApp.
 * Version: 1.0.0
 * Author: Revista Oeste
 * Author URI: https://revistaoeste.com
 * Text Domain: emprestimos-ativos
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Definições do plugin
define('EA_VERSION', '1.0.0');
define('EA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EA_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Classe principal do plugin
 */
final class Emprestimos_Ativos {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        // Classes principais
        require_once EA_PLUGIN_DIR . 'includes/class-ea-database.php';
        require_once EA_PLUGIN_DIR . 'includes/class-ea-assets.php';
        require_once EA_PLUGIN_DIR . 'includes/class-ea-whatsapp.php';
        require_once EA_PLUGIN_DIR . 'includes/class-ea-notifications.php';
        require_once EA_PLUGIN_DIR . 'includes/class-ea-ajax.php';
        require_once EA_PLUGIN_DIR . 'includes/class-ea-shortcodes.php';
        
        // Admin
        if (is_admin()) {
            require_once EA_PLUGIN_DIR . 'admin/class-ea-admin.php';
        }
        
        // Frontend
        require_once EA_PLUGIN_DIR . 'public/class-ea-public.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array('EA_Database', 'activate'));
        register_deactivation_hook(__FILE__, array('EA_Database', 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Intervalo personalizado
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));

        // Cron para notificações
        add_action('ea_check_notifications', array('EA_Notifications', 'check_and_send'));

        if (!wp_next_scheduled('ea_check_notifications')) {
         wp_schedule_event(time(), 'five_minutes', 'ea_check_notifications');
        }

    }
    
    public function init() {
        // Inicializar componentes
        EA_Assets::init();
        EA_Ajax::init();
        EA_Shortcodes::init();
        
        if (is_admin()) {
            EA_Admin::init();
        }
        
        EA_Public::init();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('emprestimos-ativos', false, dirname(EA_PLUGIN_BASENAME) . '/languages/');
    }
    
    public function add_cron_intervals($schedules) {
        $schedules['five_minutes'] = array(
            'interval' => 300,
            'display'  => __('A cada 5 minutos', 'emprestimos-ativos')
        );
        return $schedules;
    }
}

/**
 * Retorna instância principal do plugin
 */
function EA() {
    return Emprestimos_Ativos::instance();
}

// Inicializa o plugin
EA();