<?php
/**
 * Classe para gerenciamento do banco de dados
 */

if (!defined('ABSPATH')) {
    exit;
}

class EA_Database {
    
    /**
     * Ativação do plugin - cria tabelas
     */
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabela de ativos
        $table_ativos = $wpdb->prefix . 'ea_ativos';
        $sql_ativos = "CREATE TABLE $table_ativos (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            nome varchar(255) NOT NULL,
            tipo varchar(100) NOT NULL,
            descricao text,
            numero_serie varchar(100),
            etiqueta_patrimonial varchar(100),
            imagem_id bigint(20) UNSIGNED,
            status enum('ativo','inativo') DEFAULT 'ativo',
            condicao varchar(100) DEFAULT 'bom',
            disponibilidade enum('disponivel','emprestado','reservado','manutencao') DEFAULT 'disponivel',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_disponibilidade (disponibilidade),
            KEY idx_tipo (tipo)
        ) $charset_collate;";
        
        // Tabela de setores
        $table_setores = $wpdb->prefix . 'ea_setores';
        $sql_setores = "CREATE TABLE $table_setores (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            nome varchar(255) NOT NULL,
            descricao text,
            status enum('ativo','inativo') DEFAULT 'ativo',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Tabela de funcionários
        $table_funcionarios = $wpdb->prefix . 'ea_funcionarios';
        $sql_funcionarios = "CREATE TABLE $table_funcionarios (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED,
            nome varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            whatsapp varchar(20) NOT NULL,
            setor_id bigint(20) UNSIGNED,
            status enum('ativo','inativo','bloqueado') DEFAULT 'ativo',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_email (email),
            KEY idx_setor (setor_id),
            KEY idx_user (user_id)
        ) $charset_collate;";
        
        // Tabela de empréstimos
        $table_emprestimos = $wpdb->prefix . 'ea_emprestimos';
        $sql_emprestimos = "CREATE TABLE $table_emprestimos (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            codigo varchar(20) NOT NULL,
            ativo_id bigint(20) UNSIGNED NOT NULL,
            funcionario_id bigint(20) UNSIGNED NOT NULL,
            setor_id bigint(20) UNSIGNED,
            data_emprestimo date NOT NULL,
            hora_retirada time NOT NULL,
            hora_devolucao_prevista time NOT NULL,
            data_devolucao_prevista date NOT NULL,
            data_devolucao_efetiva datetime,
            observacao text,
            observacao_devolucao text,
            condicao_devolucao varchar(100),
            status enum('pendente','ativo','concluido','cancelado','atrasado') DEFAULT 'pendente',
            assinatura_eletronica text,
            notificacao_30min_enviada tinyint(1) DEFAULT 0,
            notificacao_atraso_enviada tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) UNSIGNED,
            returned_by bigint(20) UNSIGNED,
            PRIMARY KEY (id),
            UNIQUE KEY idx_codigo (codigo),
            KEY idx_ativo (ativo_id),
            KEY idx_funcionario (funcionario_id),
            KEY idx_status (status),
            KEY idx_data (data_emprestimo),
            KEY idx_devolucao (data_devolucao_prevista, hora_devolucao_prevista)
        ) $charset_collate;";
        
        // Tabela de histórico de movimentações
        $table_historico = $wpdb->prefix . 'ea_historico';
        $sql_historico = "CREATE TABLE $table_historico (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ativo_id bigint(20) UNSIGNED NOT NULL,
            emprestimo_id bigint(20) UNSIGNED,
            tipo_movimentacao varchar(50) NOT NULL,
            descricao text,
            user_id bigint(20) UNSIGNED,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_ativo (ativo_id),
            KEY idx_emprestimo (emprestimo_id)
        ) $charset_collate;";
        
        // Tabela de configurações
        $table_config = $wpdb->prefix . 'ea_config';
        $sql_config = "CREATE TABLE $table_config (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            config_key varchar(100) NOT NULL,
            config_value text,
            PRIMARY KEY (id),
            UNIQUE KEY idx_key (config_key)
        ) $charset_collate;";
        
        // Tabela de notificações enviadas
        $table_notificacoes = $wpdb->prefix . 'ea_notificacoes';
        $sql_notificacoes = "CREATE TABLE $table_notificacoes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            emprestimo_id bigint(20) UNSIGNED NOT NULL,
            tipo varchar(50) NOT NULL,
            destinatario varchar(255) NOT NULL,
            mensagem text,
            status enum('enviada','erro','pendente') DEFAULT 'pendente',
            erro_mensagem text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_emprestimo (emprestimo_id),
            KEY idx_tipo (tipo)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_ativos);
        dbDelta($sql_setores);
        dbDelta($sql_funcionarios);
        dbDelta($sql_emprestimos);
        dbDelta($sql_historico);
        dbDelta($sql_config);
        dbDelta($sql_notificacoes);
        
        // Inserir configurações padrão
        self::insert_default_config();
        
        // Inserir tipos de ativos padrão
        self::insert_default_types();
        
        // Criar página do catálogo
        self::create_catalog_page();
        
        // Atualizar versão
        update_option('ea_version', EA_VERSION);
        
        // Limpar cache de rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Desativação do plugin
     */
    public static function deactivate() {
        wp_clear_scheduled_hook('ea_check_notifications');
        flush_rewrite_rules();
    }
    
    /**
     * Inserir configurações padrão
     */
    private static function insert_default_config() {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_config';
        
        $configs = array(
            'whatsapp_api_type' => 'whapi',
            'whatsapp_api_token' => '',
            'whatsapp_instance_id' => '',
            'tempo_aviso_devolucao' => '30',
            'permitir_cancelamento' => '1',
            'tempo_limite_cancelamento' => '60',
            'mensagem_confirmacao' => 'Olá {nome}! Seu empréstimo foi confirmado. Equipamento: {ativo}. Retirada: {data_retirada} às {hora_retirada}. Devolução prevista: {data_devolucao} às {hora_devolucao}. Código: {codigo}',
            'mensagem_aviso_devolucao' => 'Olá {nome}! Lembrete: você precisa devolver o equipamento {ativo} às {hora_devolucao}. Código: {codigo}',
            'mensagem_atraso' => 'Olá {nome}! O equipamento {ativo} está com devolução atrasada. Horário previsto: {hora_devolucao}. Por favor, devolva o mais rápido possível. Código: {codigo}',
            'mensagem_devolucao' => 'Olá {nome}! A devolução do equipamento {ativo} foi registrada com sucesso. Obrigado! Código: {codigo}',
            'tipos_ativos' => 'camera,audio,iluminacao,tripe,acessorio,drone,lente,monitor,gravador,celular,outro'
        );
        
        foreach ($configs as $key => $value) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE config_key = %s",
                $key
            ));
            
            if (!$exists) {
                $wpdb->insert($table, array(
                    'config_key' => $key,
                    'config_value' => $value
                ));
            }
        }
    }
    
    /**
     * Inserir setores padrão
     */
    private static function insert_default_types() {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_setores';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        
        if ($count == 0) {
            $setores = array(
                'Produção',
                'Edição',
                'Jornalismo',
                'Marketing',
                'TI',
                'Administrativo'
            );
            
            foreach ($setores as $setor) {
                $wpdb->insert($table, array(
                    'nome' => $setor,
                    'status' => 'ativo'
                ));
            }
        }
    }
    
    /**
     * Criar página do catálogo
     */
    private static function create_catalog_page() {
        $page_exists = get_page_by_path('catalogo-equipamentos');
        
        if (!$page_exists) {
            $page_data = array(
                'post_title'    => 'Catálogo de Equipamentos',
                'post_content'  => '[ea_catalogo]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => 'catalogo-equipamentos'
            );
            
            $page_id = wp_insert_post($page_data);
            update_option('ea_catalog_page_id', $page_id);
        }
    }
    
    /**
     * Obter configuração
     */
    public static function get_config($key, $default = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_config';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT config_value FROM $table WHERE config_key = %s",
            $key
        ));
        
        return $value !== null ? $value : $default;
    }
    
    /**
     * Atualizar configuração
     */
    public static function update_config($key, $value) {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_config';
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE config_key = %s",
            $key
        ));
        
        if ($exists) {
            return $wpdb->update(
                $table,
                array('config_value' => $value),
                array('config_key' => $key)
            );
        } else {
            return $wpdb->insert($table, array(
                'config_key' => $key,
                'config_value' => $value
            ));
        }
    }
    
    /**
     * Gerar código único para empréstimo
     */
    public static function generate_loan_code() {
        $prefix = 'EMP';
        $date = date('ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return $prefix . $date . $random;
    }
    
    /**
     * Verificar conflito de horário
     */
   /**
 * Verificar conflito de horário
 * IGNORA empréstimos cancelados/concluídos
 * SUPORTA múltiplos dias corretamente
 * IGNORA o próprio ID em edição
 */
public static function check_schedule_conflict($ativo_id, $data_emprestimo, $hora_retirada, $hora_devolucao, $data_devolucao = null, $ignore_id = 0) {

    global $wpdb;

    $table = $wpdb->prefix . 'ea_emprestimos';

    // Se não vier data devolução, usa a mesma data
    if (empty($data_devolucao)) {
        $data_devolucao = $data_emprestimo;
    }

    // Intervalo NOVO
    $novo_inicio = strtotime("$data_emprestimo $hora_retirada");
    $novo_fim    = strtotime("$data_devolucao $hora_devolucao");

    // Segurança
    if ($novo_fim <= $novo_inicio) {
        return true;
    }

    // Busca somente empréstimos abertos
    $sql = "
        SELECT *
        FROM $table
        WHERE ativo_id = %d
        AND status IN ('pendente', 'ativo', 'atrasado')
    ";

    $params = array($ativo_id);

    // Ignora o próprio registro na edição
    if (!empty($ignore_id)) {
        $sql .= " AND id != %d";
        $params[] = $ignore_id;
    }

    $emprestimos = $wpdb->get_results(
        $wpdb->prepare($sql, $params)
    );

    if (!$emprestimos) {
        return false;
    }

    foreach ($emprestimos as $emp) {

        $existente_inicio = strtotime(
            $emp->data_emprestimo . ' ' . $emp->hora_retirada
        );

        $existente_fim = strtotime(
            $emp->data_devolucao_prevista . ' ' . $emp->hora_devolucao_prevista
        );

        // REGRA REAL DE SOBREPOSIÇÃO
        $conflito = (
            $novo_inicio < $existente_fim &&
            $novo_fim > $existente_inicio
        );

        if ($conflito) {
            return true;
        }
    }

    return false;
}
    
    /**
     * Verificar se funcionário tem atraso
     */
    public static function check_employee_has_delay($funcionario_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_emprestimos';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE funcionario_id = %d 
             AND status = 'atrasado'",
            $funcionario_id
        ));
        
        return $count > 0;
    }
    
    /**
     * Atualizar empréstimos atrasados
     */
    public static function update_delayed_loans() {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_emprestimos';
        
        $now = current_time('mysql');
        $today = current_time('Y-m-d');
        $current_time = current_time('H:i:s');
        
        // Atualizar empréstimos que passaram do horário
        $wpdb->query($wpdb->prepare(
            "UPDATE $table 
             SET status = 'atrasado' 
             WHERE status = 'ativo' 
             AND (
                 data_devolucao_prevista < %s 
                 OR (data_devolucao_prevista = %s AND hora_devolucao_prevista < %s)
             )",
            $today, $today, $current_time
        ));
    }
    
    /**
     * Registrar histórico
     */
    public static function log_history($ativo_id, $tipo, $descricao, $emprestimo_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_historico';
        
        return $wpdb->insert($table, array(
            'ativo_id' => $ativo_id,
            'emprestimo_id' => $emprestimo_id,
            'tipo_movimentacao' => $tipo,
            'descricao' => $descricao,
            'user_id' => get_current_user_id()
        ));
    }
}