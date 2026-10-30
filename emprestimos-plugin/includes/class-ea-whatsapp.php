<?php
/**
 * Classe para integração com WhatsApp API
 */

if (!defined('ABSPATH')) {
    exit;
}

class EA_WhatsApp {
    
    private static $api_type;
    private static $api_token;
    private static $instance_id;
    
    /**
     * Inicializar configurações
     */
    private static function init_config() {
        self::$api_type = EA_Database::get_config('whatsapp_api_type', 'whapi');
        self::$api_token = EA_Database::get_config('whatsapp_api_token');
        self::$instance_id = EA_Database::get_config('whatsapp_instance_id');
    }
    
    /**
     * Enviar mensagem via WhatsApp
     */
    public static function send_message($phone, $message) {
        self::init_config();
        
        if (empty(self::$api_token)) {
            return array(
                'success' => false,
                'error' => __('Token da API não configurado.', 'emprestimos-ativos')
            );
        }
        
        // Formatar número de telefone
        $phone = self::format_phone($phone);
        
        switch (self::$api_type) {
            case 'whapi':
                return self::send_via_whapi($phone, $message);
            case 'meta':
                return self::send_via_meta($phone, $message);
            case 'evolution':
                return self::send_via_evolution($phone, $message);
            default:
                return self::send_via_whapi($phone, $message);
        }
    }
    
    /**
     * Enviar via WHAPI
     */
    private static function send_via_whapi($phone, $message) {
        $url = 'https://gate.whapi.cloud/messages/text';
        
        $body = array(
            'to' => $phone . '@s.whatsapp.net',
            'body' => $message
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . self::$api_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        return self::process_response($response);
    }
    
    /**
     * Enviar via Meta API (WhatsApp Business)
     */
    private static function send_via_meta($phone, $message) {
        $phone_number_id = self::$instance_id;
        $url = "https://graph.facebook.com/v18.0/{$phone_number_id}/messages";
        
        $body = array(
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => array(
                'body' => $message
            )
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . self::$api_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        return self::process_response($response);
    }
    
    /**
     * Enviar via Evolution API
     */
    private static function send_via_evolution($phone, $message) {
        $base_url = EA_Database::get_config('evolution_api_url', 'http://localhost:8080');
        $instance = self::$instance_id;
        
        $url = "{$base_url}/message/sendText/{$instance}";
        
        $body = array(
            'number' => $phone,
            'text' => $message
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'apikey' => self::$api_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        return self::process_response($response);
    }
    
    /**
     * Processar resposta da API
     */
    private static function process_response($response) {
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($code >= 200 && $code < 300) {
            return array(
                'success' => true,
                'data' => $data
            );
        }
        
        return array(
            'success' => false,
            'error' => isset($data['error']['message']) ? $data['error']['message'] : __('Erro ao enviar mensagem.', 'emprestimos-ativos'),
            'code' => $code,
            'response' => $data
        );
    }
    
    /**
     * Formatar número de telefone
     */
    public static function format_phone($phone) {
        // Remover caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Se começa com 0, remover
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }
        
        // Se não tem código do país, adicionar Brasil (55)
        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Validar número de WhatsApp
     */
    public static function validate_phone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Número brasileiro válido: 10-11 dígitos (com DDD)
        // Com código do país: 12-13 dígitos
        $length = strlen($phone);
        
        return ($length >= 10 && $length <= 13);
    }
    
    /**
     * Testar conexão com a API
     */
    public static function test_connection() {
        self::init_config();
        
        if (empty(self::$api_token)) {
            return array(
                'success' => false,
                'error' => __('Token da API não configurado.', 'emprestimos-ativos')
            );
        }
        
        switch (self::$api_type) {
            case 'whapi':
                return self::test_whapi();
            case 'meta':
                return self::test_meta();
            case 'evolution':
                return self::test_evolution();
            default:
                return self::test_whapi();
        }
    }
    
    /**
     * Testar WHAPI
     */
    private static function test_whapi() {
        $url = 'https://gate.whapi.cloud/settings';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . self::$api_token
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        return array(
            'success' => ($code >= 200 && $code < 300),
            'message' => ($code >= 200 && $code < 300) ? __('Conexão estabelecida com sucesso!', 'emprestimos-ativos') : __('Falha na conexão.', 'emprestimos-ativos')
        );
    }
    
    /**
     * Testar Meta API
     */
    private static function test_meta() {
        $phone_number_id = self::$instance_id;
        $url = "https://graph.facebook.com/v18.0/{$phone_number_id}";
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . self::$api_token
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        return array(
            'success' => ($code >= 200 && $code < 300),
            'message' => ($code >= 200 && $code < 300) ? __('Conexão estabelecida com sucesso!', 'emprestimos-ativos') : __('Falha na conexão.', 'emprestimos-ativos')
        );
    }
    
    /**
     * Testar Evolution API
     */
    private static function test_evolution() {
        $base_url = EA_Database::get_config('evolution_api_url', 'http://localhost:8080');
        $instance = self::$instance_id;
        
        $url = "{$base_url}/instance/connectionState/{$instance}";
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'apikey' => self::$api_token
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        $connected = isset($body['state']) && $body['state'] === 'open';
        
        return array(
            'success' => $connected,
            'message' => $connected ? __('Conexão estabelecida com sucesso!', 'emprestimos-ativos') : __('Instância não conectada.', 'emprestimos-ativos')
        );
    }
}
