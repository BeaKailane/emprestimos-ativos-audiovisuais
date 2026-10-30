<?php
/**
 * Classe para gerenciamento de notificações corrigida
 */

if (!defined('ABSPATH')) {
    exit;
}

class EA_Notifications {
    
    /**
     * Verificar e enviar notificações pendentes (Geralmente via Cron)
     */
    public static function check_and_send() {
        // Atualizar status de empréstimos atrasados
        EA_Database::update_delayed_loans();
        
        // Enviar avisos de devolução (30 min antes)
        self::send_return_warnings();
        
        // Enviar avisos de atraso
        self::send_delay_warnings();
    }
    
    /**
     * Enviar avisos de devolução próxima
     * CORREÇÃO: Ajustada a lógica de tempo para ser mais resiliente ao Cron
     */
    private static function send_return_warnings() {
        global $wpdb;
        $tempo_aviso = (int) EA_Database::get_config('tempo_aviso_devolucao', 30);
        $table_emprestimos = $wpdb->prefix . 'ea_emprestimos';
        $table_funcionarios = $wpdb->prefix . 'ea_funcionarios';
        $table_ativos = $wpdb->prefix . 'ea_ativos';
        
        $now = current_time('mysql');
        // Busca empréstimos que vencem nos próximos X minutos
        $limit = date('Y-m-d H:i:s', strtotime("+{$tempo_aviso} minutes", strtotime($now)));
        
        // Buscar empréstimos que precisam de aviso

        $emprestimos = $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, f.nome as funcionario_nome, f.whatsapp, a.nome as ativo_nome
             FROM $table_emprestimos e
             JOIN $table_funcionarios f ON e.funcionario_id = f.id
             JOIN $table_ativos a ON e.ativo_id = a.id
             WHERE e.status = 'ativo'
             AND e.notificacao_30min_enviada = 0
             AND CONCAT(e.data_devolucao_prevista, ' ', e.hora_devolucao_prevista) <= %s
             AND CONCAT(e.data_devolucao_prevista, ' ', e.hora_devolucao_prevista) > %s",
            $limit, $now
        ));
        
        if ($emprestimos) {
            foreach ($emprestimos as $emp) {
                $message = self::parse_message_template('mensagem_aviso_devolucao', $emp);
                $result = EA_WhatsApp::send_message($emp->whatsapp, $message);
                self::log_notification($emp->id, 'aviso_devolucao', $emp->whatsapp, $message, $result);
                if ($result['success']) {
                    $wpdb->update($table_emprestimos, array('notificacao_30min_enviada' => 1), array('id' => $emp->id));
                }
            }
        }
    }

    /**
     * NOVO: Método para enviar notificação de cancelamento
     */
    public static function send_loan_cancellation($emprestimo_id) {
        global $wpdb;
        $table_emprestimos = $wpdb->prefix . 'ea_emprestimos';
        $table_funcionarios = $wpdb->prefix . 'ea_funcionarios';
        $table_ativos = $wpdb->prefix . 'ea_ativos';
        
        $emp = $wpdb->get_row($wpdb->prepare(
            "SELECT e.*, f.nome as funcionario_nome, f.whatsapp, a.nome as ativo_nome
             FROM $table_emprestimos e
             JOIN $table_funcionarios f ON e.funcionario_id = f.id
             JOIN $table_ativos a ON e.ativo_id = a.id
             WHERE e.id = %d", $emprestimo_id
        ));
        
        if (!$emp) return array('success' => false, 'error' => 'Empréstimo não encontrado.');
        
        $message = self::parse_message_template('mensagem_cancelamento', $emp);
        $result = EA_WhatsApp::send_message($emp->whatsapp, $message);
        self::log_notification($emp->id, 'cancelamento', $emp->whatsapp, $message, $result);
        return $result;
    }

     /**
     * Enviar avisos de atraso
     */
    private static function send_delay_warnings() {
        global $wpdb;
        
        $table_emprestimos = $wpdb->prefix . 'ea_emprestimos';
        $table_funcionarios = $wpdb->prefix . 'ea_funcionarios';
        $table_ativos = $wpdb->prefix . 'ea_ativos';
        
        // Buscar empréstimos atrasados sem notificação
        $emprestimos = $wpdb->get_results(
            "SELECT e.*, f.nome as funcionario_nome, f.whatsapp, a.nome as ativo_nome
             FROM $table_emprestimos e
             JOIN $table_funcionarios f ON e.funcionario_id = f.id
             JOIN $table_ativos a ON e.ativo_id = a.id
             WHERE e.status = 'atrasado'
             AND e.notificacao_atraso_enviada = 0"
        );
        
        foreach ($emprestimos as $emp) {
            $message = self::parse_message_template('mensagem_atraso', $emp);
            
            $result = EA_WhatsApp::send_message($emp->whatsapp, $message);
            
            // Registrar notificação
            self::log_notification($emp->id, 'atraso', $emp->whatsapp, $message, $result);
            
            // Marcar como enviada
            if ($result['success']) {
                $wpdb->update(
                    $table_emprestimos,
                    array('notificacao_atraso_enviada' => 1),
                    array('id' => $emp->id)
                );
            }
        }
    }
    
    public static function send_loan_confirmation($emprestimo_id) {
        global $wpdb;
        $table_emprestimos = $wpdb->prefix . 'ea_emprestimos';
        $table_funcionarios = $wpdb->prefix . 'ea_funcionarios';
        $table_ativos = $wpdb->prefix . 'ea_ativos';
        
        $emp = $wpdb->get_row($wpdb->prepare(
            "SELECT e.*, f.nome as funcionario_nome, f.whatsapp, a.nome as ativo_nome
             FROM $table_emprestimos e
             JOIN $table_funcionarios f ON e.funcionario_id = f.id
             JOIN $table_ativos a ON e.ativo_id = a.id
             WHERE e.id = %d", $emprestimo_id
        ));
        
        if (!$emp) return array('success' => false, 'error' => 'Empréstimo não encontrado.');
        
        $message = self::parse_message_template('mensagem_confirmacao', $emp);
        $result = EA_WhatsApp::send_message($emp->whatsapp, $message);
        self::log_notification($emp->id, 'confirmacao', $emp->whatsapp, $message, $result);
        return $result;
    }

    public static function send_return_confirmation($emprestimo_id) {
        global $wpdb;
        $table_emprestimos = $wpdb->prefix . 'ea_emprestimos';
        $table_funcionarios = $wpdb->prefix . 'ea_funcionarios';
        $table_ativos = $wpdb->prefix . 'ea_ativos';
        
        $emp = $wpdb->get_row($wpdb->prepare(
            "SELECT e.*, f.nome as funcionario_nome, f.whatsapp, a.nome as ativo_nome
             FROM $table_emprestimos e
             JOIN $table_funcionarios f ON e.funcionario_id = f.id
             JOIN $table_ativos a ON e.ativo_id = a.id
             WHERE e.id = %d", $emprestimo_id
        ));
        
        if (!$emp) return array('success' => false, 'error' => 'Empréstimo não encontrado.');
        
        $message = self::parse_message_template('mensagem_devolucao', $emp);
        $result = EA_WhatsApp::send_message($emp->whatsapp, $message);
        self::log_notification($emp->id, 'devolucao', $emp->whatsapp, $message, $result);
        return $result;
    }
    /**
     * Parsear template com suporte a cancelamento
     */
    private static function parse_message_template($template_key, $emprestimo) {
        $template = EA_Database::get_config($template_key);
        
        $header = "";
        switch ($template_key) {
            case 'mensagem_confirmacao':      $header = "✅ *CONFIRMAÇÃO DE EMPRÉSTIMO*"; break;
            case 'mensagem_aviso_devolucao':  $header = "⏰ *LEMBRETE DE DEVOLUÇÃO*"; break;
            case 'mensagem_atraso':           $header = "⚠️ *ALERTA DE ATRASO*"; break;
            case 'mensagem_devolucao':        $header = "📦 *EQUIPAMENTO DEVOLVIDO*"; break;
            case 'mensagem_cancelamento':     $header = "❌ *EMPRÉSTIMO CANCELADO*"; break;
            default:                          $header = "🔔 *NOTIFICAÇÃO DE SISTEMA*";
        }

        $data_retirada  = date('d/m/Y', strtotime($emprestimo->data_emprestimo));
        $hora_retirada  = date('H:i', strtotime($emprestimo->hora_retirada));
        $data_devolucao = date('d/m/Y', strtotime($emprestimo->data_devolucao_prevista));
        $hora_devolucao = date('H:i', strtotime($emprestimo->hora_devolucao_prevista));

        $replacements = array(
            '{nome}'           => $emprestimo->funcionario_nome,
            '{ativo}'          => $emprestimo->ativo_nome,
            '{codigo}'         => $emprestimo->codigo,
            '{data_retirada}'  => $data_retirada,
            '{hora_retirada}'  => $hora_retirada,
            '{data_devolucao}' => $data_devolucao,
            '{hora_devolucao}' => $hora_devolucao
        );
        
        $user_text = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        $info_list = "\n\n👤 *Colaborador:* " . $emprestimo->funcionario_nome . 
                     "\n💻 *Equipamento:* " . $emprestimo->ativo_nome . 
                     "\n🔢 *Patrimônio:* `" . $emprestimo->codigo . "`" .
                     "\n🕒 *Período:* " . $hora_retirada . " às " . $hora_devolucao;

        return $header . "\n\n" . $user_text . $info_list . "\n\n_Mensagem automática, não responda._";
    }

    /**
     * Registrar notificação
     * CORREÇÃO: Alterado 'data_envio' para 'created_at' para bater com o banco de dados
     */
    private static function log_notification($emprestimo_id, $tipo, $destinatario, $mensagem, $result) {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_notificacoes';
        $wpdb->insert($table, array(
            'emprestimo_id' => $emprestimo_id,
            'tipo'          => $tipo,
            'destinatario'  => $destinatario,
            'mensagem'      => $mensagem,
            'status'        => ($result && $result['success']) ? 'enviada' : 'erro',
            'erro_mensagem' => (isset($result['error'])) ? $result['error'] : null,
            'created_at'    => current_time('mysql') 
        ));
    }
}