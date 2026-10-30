<?php
/**
 * Classe para gerenciamento de assets (CSS/JS)
 */

if (!defined('ABSPATH')) {
    exit;
}

class EA_Assets {
    
    public static function init() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
    }
    
    /**
     * Assets do frontend
     */
    public static function enqueue_frontend_assets() {
        // jQuery UI para datepicker
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
        
        // CSS principal do frontend
        wp_enqueue_style(
            'ea-frontend-style',
            EA_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            EA_VERSION
        );
        
        // JS principal do frontend
        wp_enqueue_script(
            'ea-frontend-script',
            EA_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery', 'jquery-ui-datepicker'),
            EA_VERSION,
            true
        );
        
        // Variáveis para JS
        wp_localize_script('ea-frontend-script', 'ea_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ea_nonce'),
            'messages' => array(
                'loading' => __('Carregando...', 'emprestimos-ativos'),
                'error' => __('Ocorreu um erro. Tente novamente.', 'emprestimos-ativos'),
                'success' => __('Operação realizada com sucesso!', 'emprestimos-ativos'),
                'confirm_cancel' => __('Tem certeza que deseja cancelar este empréstimo?', 'emprestimos-ativos'),
                'required_fields' => __('Preencha todos os campos obrigatórios.', 'emprestimos-ativos'),
                'invalid_phone' => __('Número de WhatsApp inválido.', 'emprestimos-ativos'),
                'schedule_conflict' => __('Conflito de horário detectado.', 'emprestimos-ativos')
            ),
            'date_format' => get_option('date_format'),
            'time_format' => get_option('time_format')
        ));
    }
    
    /**
     * Assets do admin
     */
    public static function enqueue_admin_assets($hook) {
        // Verificar se está na página do plugin
        if (strpos($hook, 'emprestimos') === false && strpos($hook, 'ea-') === false) {
            return;
        }
        
        // Media uploader
        wp_enqueue_media();
        
        // jQuery UI
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
        
        // CSS do admin
        wp_enqueue_style(
            'ea-admin-style',
            EA_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            EA_VERSION
        );
        
        // JS do admin
        wp_enqueue_script(
            'ea-admin-script',
            EA_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-datepicker'),
            EA_VERSION,
            true
        );
        
        // Chart.js para estatísticas
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            array(),
            '4.4.0',
            true
        );
        
        // Variáveis para JS
        wp_localize_script('ea-admin-script', 'ea_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ea_admin_nonce'),
            'messages' => array(
                'loading' => __('Carregando...', 'emprestimos-ativos'),
                'error' => __('Ocorreu um erro. Tente novamente.', 'emprestimos-ativos'),
                'success' => __('Operação realizada com sucesso!', 'emprestimos-ativos'),
                'confirm_delete' => __('Tem certeza que deseja excluir?', 'emprestimos-ativos'),
                'confirm_return' => __('Confirmar devolução do equipamento?', 'emprestimos-ativos'),
                'select_image' => __('Selecionar Imagem', 'emprestimos-ativos'),
                'use_image' => __('Usar esta imagem', 'emprestimos-ativos')
            )
        ));
    }
}
