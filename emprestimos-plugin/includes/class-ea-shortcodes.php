<?php
/**
 * Classe para shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class EA_Shortcodes {
    
    public static function init() {
        // Registra todos os shortcodes
        add_shortcode('ea_catalogo', [__CLASS__, 'render_catalog']);
        add_shortcode('ea_painel_geral', [__CLASS__, 'render_public_panel']);
        add_shortcode('ea_meus_emprestimos', [__CLASS__, 'render_my_loans']);
        add_shortcode('ea_painel_usuario', [__CLASS__, 'render_user_panel']);
    }
    
    /**
     * Renderizar catálogo de ativos
     */
    public static function render_catalog($atts) {
        $atts = shortcode_atts([
            'tipo' => '',
            'columns' => 3
        ], $atts);
        
        ob_start();
        include EA_PLUGIN_DIR . 'templates/catalog.php';
        return ob_get_clean();
    }
    
    /**
     * Renderizar painel público
     */
    public static function render_public_panel($atts = [], $content = null, $tag = '') {
        ob_start();
        include EA_PLUGIN_DIR . 'templates/public-panel.php';
        return ob_get_clean();
    }
    
    /**
     * Renderizar meus empréstimos
     */
    public static function render_my_loans($atts = [], $content = null, $tag = '') {
        ob_start();
        include EA_PLUGIN_DIR . 'templates/my-loans.php';
        return ob_get_clean();
    }
    
    /**
     * Renderizar painel do usuário
     */
    public static function render_user_panel($atts = [], $content = null, $tag = '') {
        ob_start();
        include EA_PLUGIN_DIR . 'templates/user-panel.php';
        return ob_get_clean();
    }
}