<?php
/**
 * Classe para handlers AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

class EA_Ajax {
    
    public static function init() {
        // Frontend AJAX
        add_action('wp_ajax_ea_admin_get_loan_detail', array(__CLASS__, 'admin_get_loan_detail'));
        add_action('wp_ajax_ea_get_assets', array(__CLASS__, 'get_assets'));
        add_action('wp_ajax_nopriv_ea_get_assets', array(__CLASS__, 'get_assets'));
        
        add_action('wp_ajax_ea_get_asset_detail', array(__CLASS__, 'get_asset_detail'));
        add_action('wp_ajax_nopriv_ea_get_asset_detail', array(__CLASS__, 'get_asset_detail'));
        
        add_action('wp_ajax_ea_get_asset_schedule', array(__CLASS__, 'get_asset_schedule'));
        add_action('wp_ajax_nopriv_ea_get_asset_schedule', array(__CLASS__, 'get_asset_schedule'));
        
        add_action('wp_ajax_ea_create_loan', array(__CLASS__, 'create_loan'));
        add_action('wp_ajax_nopriv_ea_create_loan', array(__CLASS__, 'create_loan'));
        
        add_action('wp_ajax_ea_cancel_loan', array(__CLASS__, 'cancel_loan'));
        add_action('wp_ajax_nopriv_ea_cancel_loan', array(__CLASS__, 'cancel_loan'));
        
        
    	add_action('wp_ajax_ea_get_my_loans', array(__CLASS__, 'get_my_loans'));
		add_action('wp_ajax_nopriv_ea_get_my_loans', array(__CLASS__, 'get_my_loans'));
    
        
        add_action('wp_ajax_ea_get_setores', array(__CLASS__, 'get_setores'));
        add_action('wp_ajax_nopriv_ea_get_setores', array(__CLASS__, 'get_setores'));
        
        add_action('wp_ajax_ea_check_employee', array(__CLASS__, 'check_employee'));
        add_action('wp_ajax_nopriv_ea_check_employee', array(__CLASS__, 'check_employee'));
        
        // Admin AJAX
        add_action('wp_ajax_ea_admin_save_asset', array(__CLASS__, 'admin_save_asset'));
        add_action('wp_ajax_ea_admin_delete_asset', array(__CLASS__, 'admin_delete_asset'));
        add_action('wp_ajax_ea_admin_register_return', array(__CLASS__, 'admin_register_return'));
        add_action('wp_ajax_ea_admin_update_loan', array(__CLASS__, 'admin_update_loan'));
        add_action('wp_ajax_ea_admin_save_settings', array(__CLASS__, 'admin_save_settings'));
        add_action('wp_ajax_ea_admin_test_whatsapp', array(__CLASS__, 'admin_test_whatsapp'));
        add_action('wp_ajax_ea_admin_save_setor', array(__CLASS__, 'admin_save_setor'));
        add_action('wp_ajax_ea_admin_delete_setor', array(__CLASS__, 'admin_delete_setor'));
        add_action('wp_ajax_ea_admin_get_stats', array(__CLASS__, 'admin_get_stats'));
        add_action('wp_ajax_ea_admin_cancel_loan', array(__CLASS__, 'admin_cancel_loan'));

        add_action('wp_ajax_ea_admin_save_reserve',     array(__CLASS__, 'admin_save_reserve'));
        add_action('wp_ajax_ea_admin_activate_reserve', array(__CLASS__, 'admin_activate_reserve'));
    }
    
    /**
     * Obter lista de ativos
     */
    public static function get_assets() {
    check_ajax_referer('ea_nonce', 'nonce');

    EA_Database::update_delayed_loans();

    global $wpdb;
    $table = $wpdb->prefix . 'ea_ativos';

    $tipo   = isset($_POST['tipo']) ? sanitize_text_field($_POST['tipo']) : '';
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $busca  = isset($_POST['busca']) ? sanitize_text_field($_POST['busca']) : '';

    $where = "WHERE status = 'ativo'";
    $params = array();

    if ($tipo) {
        $where .= " AND tipo = %s";
        $params[] = $tipo;
    }

    if ($busca) {
        $where .= " AND (nome LIKE %s OR descricao LIKE %s OR numero_serie LIKE %s)";
        $search = '%' . $wpdb->esc_like($busca) . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    $sql = "SELECT * FROM $table $where ORDER BY nome ASC";

    $ativos = $params 
        ? $wpdb->get_results($wpdb->prepare($sql, $params))
        : $wpdb->get_results($sql);

    foreach ($ativos as &$ativo) {

        $ativo->imagem_url = $ativo->imagem_id
            ? wp_get_attachment_url($ativo->imagem_id)
            : EA_PLUGIN_URL . 'assets/images/placeholder.png';

        // 🔥 CALCULA STATUS REAL DINÂMICO
        $ativo->disponibilidade = self::calculate_real_status($ativo->id);

        $ativo->proximo_disponivel = self::get_next_available_time($ativo->id);
    }

    // 🔎 FILTRO POR STATUS AGORA É DINÂMICO
    if ($status) {
        $ativos = array_filter($ativos, function($a) use ($status) {
            return $a->disponibilidade === $status;
        });
        $ativos = array_values($ativos);
    }

    wp_send_json_success($ativos);
  }
    /**
     * Obter próximo horário disponível
     */
    private static function get_next_available_time($ativo_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ea_emprestimos';
        
        $proximo = $wpdb->get_row($wpdb->prepare(
            "SELECT data_devolucao_prevista, hora_devolucao_prevista 
             FROM $table 
             WHERE ativo_id = %d 
             AND status IN ('ativo', 'atrasado', 'pendente')
             ORDER BY data_devolucao_prevista ASC, hora_devolucao_prevista ASC
             LIMIT 1",
            $ativo_id
        ));
        
        if ($proximo) {
            return date('d/m/Y H:i', strtotime($proximo->data_devolucao_prevista . ' ' . $proximo->hora_devolucao_prevista));
        }
        
        return null;
    }
    private static function calculate_real_status($ativo_id) {
    global $wpdb;

    $table = $wpdb->prefix . 'ea_emprestimos';

    $hoje  = current_time('Y-m-d');
    $agora = current_time('H:i:s');

    $emprestimo = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table
         WHERE ativo_id = %d
         AND status IN ('pendente','ativo','atrasado')
         ORDER BY data_emprestimo ASC, hora_retirada ASC
         LIMIT 1",
        $ativo_id
    ));

    if (!$emprestimo) {
        return 'disponivel';
    }

    $inicio_data = $emprestimo->data_emprestimo;
    $inicio_hora = $emprestimo->hora_retirada;

    $fim_data = $emprestimo->data_devolucao_prevista;
    $fim_hora = $emprestimo->hora_devolucao_prevista;

    $inicio = strtotime("$inicio_data $inicio_hora");
    $fim    = strtotime("$fim_data $fim_hora");
    $agora_timestamp = current_time('timestamp');

    if ($agora_timestamp < $inicio) {
        return 'reservado';
    }

    if ($agora_timestamp >= $inicio && $agora_timestamp <= $fim) {
        return 'emprestado';
    }

    if ($agora_timestamp > $fim) {
        return 'atrasado';
    }

    return 'disponivel';
 }

  public static function admin_activate_reserve() {
    check_ajax_referer('ea_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Sem permissão.'));
    }
    global $wpdb;
    $id = intval($_POST['id']);
    $table = $wpdb->prefix . 'ea_emprestimos';

    $emp = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    if (!$emp || $emp->status !== 'reservado') {
        wp_send_json_error(array('message' => 'Reserva não encontrada ou já ativa.'));
    }

    $wpdb->update($table, array('status' => 'ativo'), array('id' => $id));
    EA_Database::log_history($emp->ativo_id, 'ativacao', "Reserva ativada - Código: {$emp->codigo}", $id);
    wp_send_json_success(array('message' => 'Empréstimo ativado com sucesso!'));
  }

    /**
     * Obter detalhes do ativo
     */
    public static function get_asset_detail() {
        check_ajax_referer('ea_nonce', 'nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'ea_ativos';
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error(array('message' => 'ID inválido.'));
        }
        
        $ativo = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
        
        if (!$ativo) {
            wp_send_json_error(array('message' => 'Ativo não encontrado.'));
        }
        
        $ativo->imagem_url = $ativo->imagem_id ? wp_get_attachment_url($ativo->imagem_id) : EA_PLUGIN_URL . 'assets/images/placeholder.png';
        
        wp_send_json_success($ativo);
    }
    
    public static function admin_get_loan_detail() {
    check_ajax_referer('ea_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Sem permissão.'));
    }

    global $wpdb;
    $id = intval($_POST['id']);

    $emp = $wpdb->get_row($wpdb->prepare(
        "SELECT e.*, 
                f.nome as funcionario_nome, 
                f.email as funcionario_email, 
                f.whatsapp,
                a.nome as ativo_nome, 
                a.tipo as ativo_tipo,
                s.nome as setor_nome
         FROM {$wpdb->prefix}ea_emprestimos e
         JOIN {$wpdb->prefix}ea_funcionarios f ON e.funcionario_id = f.id
         JOIN {$wpdb->prefix}ea_ativos a ON e.ativo_id = a.id
         LEFT JOIN {$wpdb->prefix}ea_setores s ON e.setor_id = s.id
         WHERE e.id = %d",
        $id
    ));

    if (!$emp) {
        wp_send_json_error(array('message' => 'Empréstimo não encontrado.'));
    }

    wp_send_json_success($emp);
}

    /**
     * Obter agenda do ativo
     */
    public static function get_asset_schedule() {
        check_ajax_referer('ea_nonce', 'nonce');
        
        global $wpdb;
        $table_emp = $wpdb->prefix . 'ea_emprestimos';
        $table_func = $wpdb->prefix . 'ea_funcionarios';
        
        $ativo_id = isset($_POST['ativo_id']) ? intval($_POST['ativo_id']) : 0;
        $data_inicio = isset($_POST['data_inicio']) ? sanitize_text_field($_POST['data_inicio']) : date('Y-m-d');
        $data_fim = isset($_POST['data_fim']) ? sanitize_text_field($_POST['data_fim']) : date('Y-m-d', strtotime('+30 days'));
        
        $emprestimos = $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, f.nome as funcionario_nome
             FROM $table_emp e
             JOIN $table_func f ON e.funcionario_id = f.id
             WHERE e.ativo_id = %d
             AND e.status IN ('pendente', 'ativo', 'atrasado')
             AND e.data_emprestimo >= %s
             AND e.data_emprestimo <= %s
             ORDER BY e.data_emprestimo ASC, e.hora_retirada ASC",
            $ativo_id, $data_inicio, $data_fim
        ));
        
        $eventos = array();
        foreach ($emprestimos as $emp) {
            $eventos[] = array(
                'id' => $emp->id,
                'title' => $emp->funcionario_nome,
                'start' => $emp->data_emprestimo . 'T' . $emp->hora_retirada,
                'end' => $emp->data_devolucao_prevista . 'T' . $emp->hora_devolucao_prevista,
                'status' => $emp->status,
                'color' => self::get_status_color($emp->status)
            );
        }
        
        wp_send_json_success($eventos);
    }
    
    /**
     * Cor por status
     */
    private static function get_status_color($status) {
        $colors = array(
            'pendente' => '#f39c12',
            'ativo' => '#3498db',
            'atrasado' => '#e74c3c',
            'concluido' => '#27ae60',
            'cancelado' => '#95a5a6'
        );
        return isset($colors[$status]) ? $colors[$status] : '#95a5a6';
    }
    
    /**
     * Criar empréstimo
     */
    public static function create_loan() {
    check_ajax_referer('ea_nonce', 'nonce');
    
    global $wpdb;
    
    // Suporta ativo_ids[] (múltiplos) OU ativo_id (legado singular)
    if (!empty($_POST['ativo_ids']) && is_array($_POST['ativo_ids'])) {
        $ativo_ids = array_map('intval', $_POST['ativo_ids']);
    } elseif (!empty($_POST['ativo_id'])) {
        $ativo_ids = array(intval($_POST['ativo_id']));
    } else {
        wp_send_json_error(array('message' => 'Nenhum equipamento selecionado.'));
        return;
    }

    // Validar campos obrigatórios (sem ativo_id aqui)
    $required = array('nome', 'email', 'whatsapp', 'setor_id', 'data_emprestimo', 'hora_retirada', 'hora_devolucao');
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(array('message' => 'Preencha todos os campos obrigatórios.'));
            return;
        }
    }

    $nome            = sanitize_text_field($_POST['nome']);
    $email           = sanitize_email($_POST['email']);
    $whatsapp        = sanitize_text_field($_POST['whatsapp']);
    $setor_id        = intval($_POST['setor_id']);
    $data_emprestimo = sanitize_text_field($_POST['data_emprestimo']);
    $hora_retirada   = sanitize_text_field($_POST['hora_retirada']);
    $hora_devolucao  = sanitize_text_field($_POST['hora_devolucao']);
    $data_devolucao  = !empty($_POST['data_devolucao']) ? sanitize_text_field($_POST['data_devolucao']) : $data_emprestimo;
    $observacao      = isset($_POST['observacao']) ? sanitize_textarea_field($_POST['observacao']) : '';
    $assinatura      = isset($_POST['assinatura']) ? sanitize_textarea_field($_POST['assinatura']) : '';

    // Validar período
    $timestamp_inicio = strtotime("$data_emprestimo $hora_retirada");
    $timestamp_fim    = strtotime("$data_devolucao $hora_devolucao");
    if ($timestamp_fim <= $timestamp_inicio) {
        wp_send_json_error(array('message' => 'A data/hora de devolução deve ser superior à de retirada.'));
        return;
    }

    // Validar WhatsApp
    if (!EA_WhatsApp::validate_phone($whatsapp)) {
        wp_send_json_error(array('message' => 'Número de WhatsApp inválido.'));
        return;
    }

    // Buscar ou criar funcionário (feito uma vez só, fora do loop)
    $table_func = $wpdb->prefix . 'ea_funcionarios';
    $funcionario = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_func WHERE email = %s", $email
    ));

    if ($funcionario) {
        $funcionario_id = $funcionario->id;

        if ($funcionario->status === 'bloqueado') {
            wp_send_json_error(array('message' => 'Seu cadastro está bloqueado. Entre em contato com o administrador.'));
            return;
        }

        if (EA_Database::check_employee_has_delay($funcionario_id)) {
            wp_send_json_error(array('message' => 'Você possui devolução atrasada. Regularize antes de solicitar novo empréstimo.'));
            return;
        }

        $wpdb->update($table_func, array(
            'nome'     => $nome,
            'whatsapp' => $whatsapp,
            'setor_id' => $setor_id
        ), array('id' => $funcionario_id));

    } else {
        $wpdb->insert($table_func, array(
            'nome'     => $nome,
            'email'    => $email,
            'whatsapp' => $whatsapp,
            'setor_id' => $setor_id,
            'user_id'  => get_current_user_id() ?: null
        ));
        $funcionario_id = $wpdb->insert_id;
    }

    // Loop: cria um empréstimo por ativo
    $table_emp = $wpdb->prefix . 'ea_emprestimos';
    $codigos = array();
    $erros   = array();

    foreach ($ativo_ids as $ativo_id) {

        // Verificar conflito de horário para cada ativo
        if (EA_Database::check_schedule_conflict($ativo_id, $data_emprestimo, $hora_retirada, $hora_devolucao, $data_devolucao)) {
            $erros[] = "Equipamento ID $ativo_id possui conflito de horário.";
            continue;
        }

        $codigo = EA_Database::generate_loan_code();

        $result = $wpdb->insert($table_emp, array(
            'codigo'                  => $codigo,
            'ativo_id'                => $ativo_id,
            'funcionario_id'          => $funcionario_id,
            'setor_id'                => $setor_id,
            'data_emprestimo'         => $data_emprestimo,
            'hora_retirada'           => $hora_retirada,
            'hora_devolucao_prevista' => $hora_devolucao,
            'data_devolucao_prevista' => $data_devolucao,
            'observacao'              => $observacao,
            'assinatura_eletronica'   => $assinatura,
            'status'                  => 'ativo',
            'created_by'              => get_current_user_id() ?: null
        ));

        if ($result) {
            $emprestimo_id = $wpdb->insert_id;
            $codigos[] = $codigo;
            EA_Database::log_history($ativo_id, 'emprestimo', "Emprestado para $nome - Código: $codigo", $emprestimo_id);
            EA_Notifications::send_loan_confirmation($emprestimo_id);
        } else {
            $erros[] = "Erro ao registrar equipamento ID $ativo_id.";
        }
    }

    // Retorno
    if (empty($codigos)) {
        $msg = !empty($erros) ? implode(' ', $erros) : 'Nenhum empréstimo pôde ser registrado.';
        wp_send_json_error(array('message' => $msg));
        return;
    }

    $aviso = !empty($erros) ? ' Atenção: ' . implode(' ', $erros) : '';

    wp_send_json_success(array(
        'message'  => count($codigos) . ' empréstimo(s) registrado(s) com sucesso!' . $aviso,
        'codigos'  => $codigos,           // array — já tratado no JS
        'codigo'   => $codigos[0],        // legado singular
        'emprestimo_id' => null
    ));
}

    /**
     * Cancelar empréstimo (admin) - SUPORTA MÚLTIPLOS IDS
     */
    public static function admin_cancel_loan() {
        check_ajax_referer('ea_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão.'));
        }

        global $wpdb;
        $table_emp = $wpdb->prefix . 'ea_emprestimos';
        $table_ativos = $wpdb->prefix . 'ea_ativos';

        // Captura o ID vindo do AJAX (pode ser um número único ou um array)
        $ids_input = isset($_POST['id']) ? $_POST['id'] : 0;
        
        // Normaliza para sempre ser um array, facilitando o loop
        $ids = is_array($ids_input) ? $ids_input : array($ids_input);
        
        $sucesso_count = 0;
        $erros = array();

        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;

            $emprestimo = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_emp WHERE id = %d", $id)
            );

            if (!$emprestimo) {
                $erros[] = "ID $id não encontrado.";
                continue;
            }

            // Verifica se o status permite cancelamento
            if (in_array($emprestimo->status, array('concluido', 'cancelado'))) {
                $erros[] = "Empréstimo #{$emprestimo->codigo} já está concluído ou cancelado.";
                continue;
            }

            // Executa a atualização
            $result = $wpdb->update(
                $table_emp,
                array('status' => 'cancelado'),
                array('id' => $id),
                array('%s'),
                array('%d')
            );

            if ($result !== false) {
                // Notificação de cancelamento
                EA_Notifications::send_loan_cancellation($id);
                
                // Registrar histórico
                EA_Database::log_history(
                    $emprestimo->ativo_id,
                    'cancelamento_admin',
                    "Cancelado pelo administrador - Código: {$emprestimo->codigo}",
                    $id
                );

                // IMPORTANTE: Como seu sistema usa cálculo dinâmico (calculate_real_status), 
                // o cancelamento do empréstimo já libera o ativo automaticamente na listagem.
                
                $sucesso_count++;
            }
        }

        if ($sucesso_count > 0) {
            $msg = ($sucesso_count === 1) 
                ? 'Empréstimo cancelado com sucesso.' 
                : $sucesso_count . ' empréstimos cancelados com sucesso.';
            
            wp_send_json_success(array('message' => $msg));
        } else {
            $erro_msg = !empty($erros) ? implode(' ', $erros) : 'Nenhum empréstimo pôde ser cancelado.';
            wp_send_json_error(array('message' => $erro_msg));
        }
    }

    public static function admin_save_reserve() {
    check_ajax_referer('ea_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Sem permissão.'));
    }

    global $wpdb;

    $funcionario_id  = intval($_POST['funcionario_id']);
    $ativo_id        = intval($_POST['ativo_id']);
    $setor_id        = !empty($_POST['setor_id']) ? intval($_POST['setor_id']) : null;
    $data_emprestimo = sanitize_text_field($_POST['data_emprestimo']);
    $hora_retirada   = sanitize_text_field($_POST['hora_retirada']);
    $data_devolucao  = sanitize_text_field($_POST['data_devolucao']);
    $hora_devolucao  = sanitize_text_field($_POST['hora_devolucao']);
    $observacao      = sanitize_textarea_field($_POST['observacao'] ?? '');
    $tipo_registro   = sanitize_text_field($_POST['tipo_registro'] ?? 'reservado');
    $status          = in_array($tipo_registro, array('ativo', 'reservado')) ? $tipo_registro : 'reservado';

    if (!$funcionario_id || !$ativo_id || !$data_emprestimo || !$hora_retirada || !$data_devolucao || !$hora_devolucao) {
        wp_send_json_error(array('message' => 'Preencha todos os campos obrigatórios.'));
    }

    $timestamp_inicio = strtotime("$data_emprestimo $hora_retirada");
    $timestamp_fim    = strtotime("$data_devolucao $hora_devolucao");

    if ($timestamp_fim <= $timestamp_inicio) {
        wp_send_json_error(array('message' => 'A data/hora de devolução deve ser superior à de retirada.'));
    }

    if (EA_Database::check_schedule_conflict($ativo_id, $data_emprestimo, $hora_retirada, $hora_devolucao, $data_devolucao)) {
        wp_send_json_error(array('message' => 'Conflito de horário com outro agendamento.'));
    }

    $codigo = EA_Database::generate_loan_code();
    $table  = $wpdb->prefix . 'ea_emprestimos';

    $result = $wpdb->insert($table, array(
        'codigo'                  => $codigo,
        'ativo_id'                => $ativo_id,
        'funcionario_id'          => $funcionario_id,
        'setor_id'                => $setor_id,
        'data_emprestimo'         => $data_emprestimo,
        'hora_retirada'           => $hora_retirada,
        'data_devolucao_prevista' => $data_devolucao,
        'hora_devolucao_prevista' => $hora_devolucao,
        'observacao'              => $observacao,
        'status'                  => $status,
        'created_by'              => get_current_user_id()
    ));

    if (!$result) {
        wp_send_json_error(array('message' => 'Erro ao salvar no banco de dados.'));
    }

    $emprestimo_id = $wpdb->insert_id;
    EA_Database::log_history($ativo_id, $status, "Criado pelo admin - Código: $codigo", $emprestimo_id);

    if ($status === 'ativo') {
        EA_Notifications::send_loan_confirmation($emprestimo_id);
    }

    wp_send_json_success(array(
        'message' => $status === 'ativo' ? 'Empréstimo registrado com sucesso!' : 'Reserva criada com sucesso!',
        'codigo'  => $codigo
    ));
  }


    /**
     * Cancelar empréstimo (pelo próprio usuário/funcionário)
     */
    public static function cancel_loan() {
        check_ajax_referer('ea_nonce', 'nonce');
        
        global $wpdb;
        
        $emprestimo_id = isset($_POST['emprestimo_id']) ? intval($_POST['emprestimo_id']) : 0;
        $email = isset($_POST['email']) ? sanitize_email(trim($_POST['email'])) : '';
        
        if (!$emprestimo_id) {
            wp_send_json_error(array('message' => 'ID inválido.'));
        }
        
        if (!$email) {
            wp_send_json_error(array('message' => 'E-mail não informado.'));
        }
        
        $table_emp = $wpdb->prefix . 'ea_emprestimos';
        $table_func = $wpdb->prefix . 'ea_funcionarios';
        
        // Identifica o funcionário pelo e-mail informado
        $funcionario = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_func WHERE email = %s",
            $email
        ));
        
        if (!$funcionario) {
            wp_send_json_error(array('message' => 'Funcionário não encontrado.'));
        }
        
        $emprestimo = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_emp WHERE id = %d",
            $emprestimo_id
        ));
        
        if (!$emprestimo) {
            wp_send_json_error(array('message' => 'Empréstimo não encontrado.'));
        }
        
        // Garante que o empréstimo pertence a quem está pedindo o cancelamento
        if ((int) $emprestimo->funcionario_id !== (int) $funcionario->id) {
            wp_send_json_error(array('message' => 'Você não pode cancelar este empréstimo.'));
        }
        
        // Só é possível cancelar empréstimos ainda em aberto
        if (!in_array($emprestimo->status, array('pendente', 'ativo', 'atrasado'), true)) {
            wp_send_json_error(array('message' => 'Este empréstimo não pode mais ser cancelado.'));
        }
        
        // Verificar se pode cancelar (configuração geral do plugin)
        $permitir = EA_Database::get_config('permitir_cancelamento', '1');
        if (!$permitir) {
            wp_send_json_error(array('message' => 'Cancelamento não permitido.'));
        }
        
        // Verificar tempo limite (padrão: 60 minutos = 1h antes da retirada)
        $tempo_limite = (int) EA_Database::get_config('tempo_limite_cancelamento', 60);
        $data_hora_retirada = strtotime($emprestimo->data_emprestimo . ' ' . $emprestimo->hora_retirada);
        $agora = current_time('timestamp');
        $diferenca = ($data_hora_retirada - $agora) / 60; // em minutos
        
        if ($diferenca < $tempo_limite) {
            wp_send_json_error(array('message' => "Cancelamento permitido apenas até {$tempo_limite} minutos antes da retirada."));
        }
        
        // Cancelar
        $wpdb->update($table_emp, array('status' => 'cancelado'), array('id' => $emprestimo_id));
        
        // Verificar se há outros empréstimos ativos para este ativo
        $outros = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_emp WHERE ativo_id = %d AND status IN ('ativo', 'atrasado', 'pendente') AND id != %d",
            $emprestimo->ativo_id, $emprestimo_id
        ));
        
        // Registrar histórico
        EA_Database::log_history($emprestimo->ativo_id, 'cancelamento', "Empréstimo cancelado - Código: {$emprestimo->codigo}", $emprestimo_id);
        
        wp_send_json_success(array('message' => 'Empréstimo cancelado com sucesso.'));
    }
    
    /**
     * Obter meus empréstimos
     */
    public static function get_my_loans() {
        check_ajax_referer('ea_nonce', 'nonce');
        
        global $wpdb;
        
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        
        if (!$email) {
            wp_send_json_error(array('message' => 'E-mail não informado.'));
        }
        
        $table_emp = $wpdb->prefix . 'ea_emprestimos';
        $table_func = $wpdb->prefix . 'ea_funcionarios';
        $table_ativos = $wpdb->prefix . 'ea_ativos';
        
        $funcionario = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_func WHERE email = %s",
            $email
        ));
        
        if (!$funcionario) {
            wp_send_json_success(array('emprestimos' => array()));
            return;
        }
        
        $emprestimos = $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, a.nome as ativo_nome, a.tipo as ativo_tipo
             FROM $table_emp e
             JOIN $table_ativos a ON e.ativo_id = a.id
             WHERE e.funcionario_id = %d
             ORDER BY e.created_at DESC
             LIMIT 50",
            $funcionario->id
        ));
        
        wp_send_json_success(array('emprestimos' => $emprestimos));
    }
    
    /**
     * Obter setores
     */
    public static function get_setores() {
        check_ajax_referer('ea_nonce', 'nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'ea_setores';
        
        $setores = $wpdb->get_results(
            "SELECT id, nome FROM $table WHERE status = 'ativo' ORDER BY nome ASC"
        );
        
        wp_send_json_success($setores);
    }
    
    /**
     * Verificar funcionário existente
     */
    public static function check_employee() {
        check_ajax_referer('ea_nonce', 'nonce');
        
        global $wpdb;
        
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        
        if (!$email) {
            wp_send_json_error(array('message' => 'E-mail não informado.'));
        }
        
        $table = $wpdb->prefix . 'ea_funcionarios';
        $table_setores = $wpdb->prefix . 'ea_setores';
        
        $funcionario = $wpdb->get_row($wpdb->prepare(
            "SELECT f.*, s.nome as setor_nome 
             FROM $table f
             LEFT JOIN $table_setores s ON f.setor_id = s.id
             WHERE f.email = %s",
            $email
        ));
        
        if ($funcionario) {
            wp_send_json_success(array(
                'exists' => true,
                'funcionario' => $funcionario
            ));
        } else {
            wp_send_json_success(array('exists' => false));
        }
    }
    
    // ============ ADMIN AJAX ============
    
    /**
     * Salvar ativo (admin)
     */
   public static function admin_save_asset() {
    check_ajax_referer('ea_admin_nonce', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'ea_ativos';

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    $data = array(
        'nome' => sanitize_text_field($_POST['nome']),
        'tipo' => sanitize_text_field($_POST['tipo']),
        'descricao' => sanitize_textarea_field($_POST['descricao']),
        'numero_serie' => sanitize_text_field($_POST['numero_serie']),
        'etiqueta_patrimonial' => sanitize_text_field($_POST['etiqueta_patrimonial']),
        'condicao' => sanitize_text_field($_POST['condicao']),
        'imagem_id' => !empty($_POST['imagem_id']) ? intval($_POST['imagem_id']) : null,
        'disponibilidade' => sanitize_text_field($_POST['disponibilidade']),
        'status' => sanitize_text_field($_POST['status'])
    );

    if ($id > 0) {
        // FORÇA O UPDATE SE O ID EXISTIR
        $wpdb->update($table, $data, array('id' => $id), null, array('%d'));
        wp_send_json_success(array('message' => 'Ativo atualizado!'));
    } else {
        // SÓ CRIA NOVO SE O ID FOR ZERO
        $wpdb->insert($table, $data);
        wp_send_json_success(array('message' => 'Ativo criado!'));
    }
}
    /**
     * Excluir ativo (admin)
     */
    public static function admin_delete_asset() {
        check_ajax_referer('ea_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão.'));
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        // Verificar se tem empréstimos ativos
        $table_emp = $wpdb->prefix . 'ea_emprestimos';
        $ativos = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_emp WHERE ativo_id = %d AND status IN ('ativo', 'atrasado', 'pendente')",
            $id
        ));
        
        if ($ativos > 0) {
            wp_send_json_error(array('message' => 'Este ativo possui empréstimos ativos e não pode ser excluído.'));
        }
        
        $table = $wpdb->prefix . 'ea_ativos';
        $wpdb->delete($table, array('id' => $id));
        
        wp_send_json_success(array('message' => 'Ativo excluído com sucesso.'));
    }
    
    /**
     * Registrar devolução (admin)
     */
    public static function admin_register_return() {
        check_ajax_referer('ea_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão.'));
        }
        
        global $wpdb;
        
        $emprestimo_id = intval($_POST['emprestimo_id']);
        $condicao = sanitize_text_field($_POST['condicao'] ?? 'bom');
        $observacao = sanitize_textarea_field($_POST['observacao'] ?? '');
        
        $table_emp = $wpdb->prefix . 'ea_emprestimos';
        $emprestimo = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_emp WHERE id = %d",
            $emprestimo_id
        ));
        
        if (!$emprestimo) {
            wp_send_json_error(array('message' => 'Empréstimo não encontrado.'));
        }
        
        // Atualizar empréstimo
        $wpdb->update($table_emp, array(
            'status' => 'concluido',
            'data_devolucao_efetiva' => current_time('mysql'),
            'condicao_devolucao' => $condicao,
            'observacao_devolucao' => $observacao,
            'returned_by' => get_current_user_id()
        ), array('id' => $emprestimo_id));
        
        // Verificar outros empréstimos ativos
        $outros = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_emp WHERE ativo_id = %d AND status IN ('ativo', 'atrasado', 'pendente') AND id != %d",
            $emprestimo->ativo_id, $emprestimo_id
        ));
        
        if (!$outros) {
            $table_ativos = $wpdb->prefix . 'ea_ativos';
            $wpdb->update($table_ativos, array(
                'condicao' => $condicao
            ), array('id' => $emprestimo->ativo_id));
        }
        
        // Registrar histórico
        EA_Database::log_history($emprestimo->ativo_id, 'devolucao', "Devolvido - Condição: $condicao - Código: {$emprestimo->codigo}", $emprestimo_id);
        
        // Enviar notificação
        EA_Notifications::send_return_confirmation($emprestimo_id);
        
        wp_send_json_success(array('message' => 'Devolução registrada com sucesso!'));
    }
    
    /**
     * Atualizar empréstimo (admin)
     */
/**
     * Atualizar empréstimo (admin) - CORRIGIDO PARA NÃO DUPLICAR
     */
    public static function admin_update_loan() {
        check_ajax_referer('ea_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão.'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'ea_emprestimos';
        
        // Captura o ID vindo do AJAX
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // SE O ID FOR 0, É POR ISSO QUE CRIA UM NOVO. PRECISAMOS PARAR AQUI.
        if ($id <= 0) {
            wp_send_json_error(array('message' => 'Erro crítico: ID do empréstimo não foi enviado pelo formulário.'));
        }

        $data_emp = sanitize_text_field($_POST['data_emprestimo']);
        $hora_ret = sanitize_text_field($_POST['hora_retirada']);
        $hora_dev = sanitize_text_field($_POST['hora_devolucao']);
        $data_dev = !empty($_POST['data_devolucao']) ? sanitize_text_field($_POST['data_devolucao']) : $data_emp;
        $obs      = sanitize_textarea_field($_POST['observacao'] ?? '');

        // 1. Busca o registro atual para garantir que ele existe
        $registro_existente = $wpdb->get_row($wpdb->prepare("SELECT id, ativo_id FROM $table WHERE id = %d", $id));

        //  NOVA VALIDAÇÃO: Fim não pode ser menor que o início
        $timestamp_inicio = strtotime("$data_emp $hora_ret");
        $timestamp_fim    = strtotime("$data_dev $hora_dev");

        if ($timestamp_fim <= $timestamp_inicio) {
            wp_send_json_error(array('message' => 'A data/hora de devolução deve ser superior à data/hora de retirada.'));
        }
        
        if (!$registro_existente) {
            wp_send_json_error(array('message' => 'O empréstimo ID ' . $id . ' não existe no banco de dados para ser editado.'));
        }

        // 2. Verifica conflitos (passando o ID atual para ele ignorar a si mesmo na checagem)
        if (EA_Database::check_schedule_conflict($registro_existente->ativo_id, $data_emp, $hora_ret, $hora_dev, $data_dev, $id)) {
            wp_send_json_error(array('message' => 'Conflito de horário detectado com outro agendamento.'));
        }
        
        
        // 3. EXECUTA O UPDATE NO ID ESPECÍFICO
        $resultado = $wpdb->update(
            $table, 
            array(
                'data_emprestimo'         => $data_emp,
                'hora_retirada'           => $hora_ret,
                'data_devolucao_prevista' => $data_dev,
                'hora_devolucao_prevista' => $hora_dev,
                'observacao'              => $obs
            ), 
            array('id' => $id), // FILTRO OBRIGATÓRIO PARA NÃO CRIAR NOVO
            array('%s', '%s', '%s', '%s', '%s'), 
            array('%d') 
        );
        
        if ($resultado !== false) {
            EA_Database::update_delayed_loans();
            wp_send_json_success(array('message' => 'Empréstimo #' . $id . ' atualizado com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao salvar alterações ou nenhuma mudança detectada.'));
        }
    }
    
    /**
     * Salvar configurações (admin)
     */
    public static function admin_save_settings() {
        check_ajax_referer('ea_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão.'));
        }
        
        $settings = array(
            'whatsapp_api_type',
            'whatsapp_api_token',
            'whatsapp_instance_id',
            'evolution_api_url',
            'tempo_aviso_devolucao',
            'permitir_cancelamento',
            'tempo_limite_cancelamento',
            'mensagem_confirmacao',
            'mensagem_aviso_devolucao',
            'mensagem_atraso',
            'mensagem_devolucao'
        );
        
        foreach ($settings as $key) {
            if (isset($_POST[$key])) {
                EA_Database::update_config($key, sanitize_textarea_field($_POST[$key]));
            }
        }
        
        wp_send_json_success(array('message' => 'Configurações salvas com sucesso!'));
    }
    
    /**
     * Testar conexão WhatsApp (admin)
     */
    public static function admin_test_whatsapp() {
        check_ajax_referer('ea_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão.'));
        }
        
        $result = EA_WhatsApp::test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    

    public static function admin_save_setor() {
        check_ajax_referer('ea_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão.'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'ea_setores';
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nome = sanitize_text_field($_POST['nome']);
        $descricao = sanitize_textarea_field($_POST['descricao'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'ativo');
        
        if ($id) {
            $wpdb->update($table, array(
                'nome' => $nome,
                'descricao' => $descricao,
                'status' => $status
            ), array('id' => $id));
        } else {
            $wpdb->insert($table, array(
                'nome' => $nome,
                'descricao' => $descricao,
                'status' => $status
            ));
        }
        
        wp_send_json_success(array('message' => 'Setor salvo com sucesso!'));
    }
    
    public static function admin_delete_setor() {
        check_ajax_referer('ea_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão.'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'ea_setores';
        $id = intval($_POST['id']);
        
        $wpdb->delete($table, array('id' => $id));
        
        wp_send_json_success(array('message' => 'Setor excluído com sucesso.'));
    }
    
  public static function admin_get_stats() {
    check_ajax_referer('ea_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Sem permissão.'));
    }

    global $wpdb;
    $table_ativos = $wpdb->prefix . 'ea_ativos';
    $table_emp    = $wpdb->prefix . 'ea_emprestimos';
    $agora = current_time('Y-m-d H:i:s');

    // 1. Consulta Principal (Cards) - Uso de IS NULL para identificar ativos sem empréstimos ativos
    $stats = $wpdb->get_row($wpdb->prepare("
        SELECT 
            COUNT(DISTINCT a.id) as total_ativos,
            -- Ativos que não possuem registros vinculados em status aberto (Disponíveis)
            SUM(CASE WHEN e.id IS NULL THEN 1 ELSE 0 END) as ativos_disponiveis,
            -- Ativos em posse agora
            SUM(CASE WHEN %s BETWEEN CONCAT(e.data_emprestimo, ' ', e.hora_retirada) 
                AND CONCAT(e.data_devolucao_prevista, ' ', e.hora_devolucao_prevista) THEN 1 ELSE 0 END) as ativos_emprestados,
            -- Atrasados (Data prevista menor que agora e não concluído)
            SUM(CASE WHEN CONCAT(e.data_devolucao_prevista, ' ', e.hora_devolucao_prevista) < %s 
                AND e.status != 'concluido' THEN 1 ELSE 0 END) as emprestimos_atrasados,
            -- Contador de Cancelados Total
            (SELECT COUNT(*) FROM $table_emp WHERE status = 'cancelado') as total_cancelados
        FROM $table_ativos a
        LEFT JOIN $table_emp e ON a.id = e.ativo_id AND e.status IN ('pendente', 'ativo', 'atrasado')
        WHERE a.status = 'ativo'
    ", $agora, $agora));

    // 2. Gráfico: Volume últimos 30 dias
    $por_dia = $wpdb->get_results("
        SELECT DATE_FORMAT(data_emprestimo, '%d/%m') as dia, COUNT(*) as total 
        FROM $table_emp 
        WHERE data_emprestimo >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY data_emprestimo ORDER BY data_emprestimo ASC
    ");

    // 3. Ranking: Top 5 mais utilizados
    $mais_emprestados = $wpdb->get_results("
        SELECT a.nome, COUNT(e.id) as total
        FROM $table_ativos a
        JOIN $table_emp e ON a.id = e.ativo_id
        GROUP BY a.id ORDER BY total DESC LIMIT 5
    ");

    wp_send_json_success(array(
        'total_ativos'          => (int) $stats->total_ativos,
        'ativos_disponiveis'    => (int) $stats->ativos_disponiveis,
        'ativos_emprestados'    => (int) $stats->ativos_emprestados,
        'emprestimos_atrasados' => (int) $stats->emprestimos_atrasados,
        'total_cancelados'      => (int) $stats->total_cancelados,
        'por_dia'               => $por_dia,
        'mais_emprestados'      => $mais_emprestados
    ));
}
}