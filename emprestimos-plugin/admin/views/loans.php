<?php 
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_emp     = $wpdb->prefix . 'ea_emprestimos';
$table_func    = $wpdb->prefix . 'ea_funcionarios';
$table_ativos  = $wpdb->prefix . 'ea_ativos';
$table_setores = $wpdb->prefix . 'ea_setores';

$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$where = $status_filter ? $wpdb->prepare("WHERE e.status = %s", $status_filter) : "";

$emprestimos = $wpdb->get_results(
    "SELECT e.*, f.nome as funcionario_nome, f.email as funcionario_email, f.whatsapp, a.nome as ativo_nome, a.tipo as ativo_tipo, s.nome as setor_nome
     FROM $table_emp e
     JOIN $table_func f ON e.funcionario_id = f.id
     JOIN $table_ativos a ON e.ativo_id = a.id
     LEFT JOIN $table_setores s ON e.setor_id = s.id
     $where
     ORDER BY e.created_at DESC
     LIMIT 100"
);

// Buscar dados para o modal de reserva
$funcionarios = $wpdb->get_results("SELECT id, nome, email FROM $table_func WHERE status = 'ativo' ORDER BY nome ASC");
$setores      = $wpdb->get_results("SELECT id, nome FROM $table_setores ORDER BY nome ASC");

$agora_data = current_time('Y-m-d');
$agora_hora = current_time('H:i:s');

$ativos_disponiveis = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT a.id, a.nome, a.tipo
         FROM $table_ativos a
         WHERE a.status = 'ativo'
           AND a.id NOT IN (
               SELECT e.ativo_id
               FROM $table_emp e
               WHERE e.status IN ('ativo', 'atrasado', 'pendente')
                 AND (
                     /* empréstimo já iniciado e ainda não encerrado */
                     (CONCAT(e.data_emprestimo,   ' ', e.hora_retirada)          <= %s)
                     AND
                     (CONCAT(e.data_devolucao_prevista, ' ', e.hora_devolucao_prevista) >= %s)
                 )
           )
         ORDER BY a.nome ASC",
        "$agora_data $agora_hora",
        "$agora_data $agora_hora"
    )
);
?>

<div class="wrap magma-style">
   <div class="magma-header">
    <div class="magma-title-section">
        <span class="magma-icon-bg"><span class="dashicons dashicons-list-view"></span></span>
        <div>
            <h1><?php _e('Gerenciar Empréstimos', 'emprestimos-ativos'); ?></h1>
            <p><?php _e('Controle total e histórico de movimentações', 'emprestimos-ativos'); ?></p>
        </div>
    </div>

    <!-- Bulk actions: aparece no meio quando ativo, empurrando "Nova Reserva" para a direita -->
    <div id="bulk-actions-container" style="margin-left: auto; display: none; align-items: center; gap: 10px;">
        <span class="magma-sub-text"><strong id="selected-count">0</strong> selecionados</span>
        <button class="magma-btn-bulk-return" id="ea-btn-bulk-return">
            <span class="dashicons dashicons-yes-alt"></span> <?php _e('Devolver Selecionados', 'emprestimos-ativos'); ?>
        </button>
        <button class="magma-btn-bulk-cancel" id="ea-btn-bulk-cancel">
            <span class="dashicons dashicons-trash"></span> <?php _e('Cancelar Selecionados', 'emprestimos-ativos'); ?>
        </button>
    </div>

    <!-- Botão Nova Reserva: sem margin-left:auto próprio, deixa o bulk-actions empurrar -->
   <div id="nova-reserva-container">
      <button class="magma-btn-reserve" id="ea-btn-open-reserve">
        <span class="dashicons dashicons-calendar-alt"></span>
        <?php _e('Nova Reserva', 'emprestimos-ativos'); ?>
      </button>
      </div>
   </div>

    <div class="magma-tabs-wrapper">
        <div class="magma-tabs">
            <a href="?page=ea-emprestimos" class="magma-tab <?php echo !$status_filter ? 'is-active' : ''; ?>"><?php _e('Todos', 'emprestimos-ativos'); ?></a>
            <a href="?page=ea-emprestimos&status=ativo" class="magma-tab <?php echo $status_filter === 'ativo' ? 'is-active' : ''; ?>"><?php _e('Ativos', 'emprestimos-ativos'); ?></a>
            <a href="?page=ea-emprestimos&status=atrasado" class="magma-tab <?php echo $status_filter === 'atrasado' ? 'is-active' : ''; ?>"><?php _e('Atrasados', 'emprestimos-ativos'); ?></a>
            <a href="?page=ea-emprestimos&status=reservado" class="magma-tab <?php echo $status_filter === 'reservado' ? 'is-active' : ''; ?>"><?php _e('Reservados', 'emprestimos-ativos'); ?></a>
            <a href="?page=ea-emprestimos&status=concluido" class="magma-tab <?php echo $status_filter === 'concluido' ? 'is-active' : ''; ?>"><?php _e('Concluídos', 'emprestimos-ativos'); ?></a>
            <a href="?page=ea-emprestimos&status=cancelado" class="magma-tab <?php echo $status_filter === 'cancelado' ? 'is-active' : ''; ?>"><?php _e('Cancelados', 'emprestimos-ativos'); ?></a>
        </div>
    </div>

    <div class="magma-table-container">
        <table class="magma-table">
            <thead>
                <tr>
                    <th style="width: 30px;"><input type="checkbox" id="select-all-loans"></th>
                    <th>Código</th>
                    <th>Equipamento</th>
                    <th>Colaborador</th>
                    <th>Setor</th>
                    <th>Retirada</th>
                    <th>Previsão</th>
                    <th>Status</th>
                    <th style="text-align:right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($emprestimos): foreach ($emprestimos as $emp): ?>
                <tr>
                    <td>
                        <?php if (in_array($emp->status, array('ativo', 'atrasado'))): ?>
                            <input type="checkbox" class="loan-checkbox" value="<?php echo $emp->id; ?>">
                        <?php endif; ?>
                    </td>
                    <td><span class="magma-id-badge">#<?php echo esc_html($emp->codigo); ?></span></td>
                    <td>
                        <div class="magma-cell-info">
                            <span class="magma-main-text"><?php echo esc_html($emp->ativo_nome); ?></span>
                            <span class="magma-sub-text"><?php echo esc_html($emp->ativo_tipo); ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="magma-cell-info">
                            <span class="magma-main-text"><?php echo esc_html($emp->funcionario_nome); ?></span>
                            <span class="magma-sub-text"><?php echo esc_html($emp->funcionario_email); ?></span>
                        </div>
                    </td>
                    <td><?php echo esc_html($emp->setor_nome ?: '-'); ?></td>
                    <td>
                        <div class="magma-date-cell">
                            <span><?php echo date('d/m/Y', strtotime($emp->data_emprestimo)); ?></span>
                            <small><?php echo date('H:i', strtotime($emp->hora_retirada)); ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="magma-date-cell">
                            <span><?php echo date('d/m/Y', strtotime($emp->data_devolucao_prevista)); ?></span>
                            <small><?php echo date('H:i', strtotime($emp->hora_devolucao_prevista)); ?></small>
                        </div>
                    </td>
                    <td><span class="magma-status status-<?php echo esc_attr($emp->status); ?>"><?php echo ucfirst($emp->status); ?></span></td>
                    <td style="text-align:right">
                        <div class="magma-actions-group">
                            <button class="magma-action-btn view ea-btn-view" data-id="<?php echo $emp->id; ?>" title="Ver Detalhes"><span class="dashicons dashicons-visibility"></span></button>
                             
                            <?php if (in_array($emp->status, array('ativo', 'atrasado'))): ?>
                                <button class="magma-action-btn check ea-btn-return" data-id="<?php echo $emp->id; ?>" data-ativo="<?php echo esc_attr($emp->ativo_nome); ?>" title="Devolver"><span class="dashicons dashicons-yes-alt"></span></button>
                                <button class="magma-action-btn edit ea-btn-edit-loan" 
                                    data-id="<?php echo $emp->id; ?>" 
                                    data-data="<?php echo esc_attr($emp->data_emprestimo); ?>" 
                                    data-hora-ret="<?php echo esc_attr($emp->hora_retirada); ?>" 
                                    data-hora-dev="<?php echo esc_attr($emp->hora_devolucao_prevista); ?>" 
                                    data-data-dev="<?php echo esc_attr($emp->data_devolucao_prevista); ?>" 
                                    data-obs="<?php echo esc_attr($emp->observacao); ?>" title="Editar">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button class="magma-action-btn cancel ea-btn-cancel-loan"
                                    data-id="<?php echo $emp->id; ?>"
                                    data-ativo="<?php echo esc_attr($emp->ativo_nome); ?>"
                                    title="Cancelar Empréstimo">
                                    <span class="dashicons dashicons-dismiss"></span>
                                </button>
                            <?php endif; ?>

                            <?php if ($emp->status === 'reservado'): ?>
                                <button class="magma-action-btn activate ea-btn-activate-reserve"
                                    data-id="<?php echo $emp->id; ?>"
                                    data-ativo="<?php echo esc_attr($emp->ativo_nome); ?>"
                                    title="Ativar Empréstimo">
                                    <span class="dashicons dashicons-controls-play"></span>
                                </button>
                                <button class="magma-action-btn cancel ea-btn-cancel-loan"
                                    data-id="<?php echo $emp->id; ?>"
                                    data-ativo="<?php echo esc_attr($emp->ativo_nome); ?>"
                                    title="Cancelar Reserva">
                                    <span class="dashicons dashicons-dismiss"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="9" class="magma-no-data"><?php _e('Nenhum empréstimo encontrado.', 'emprestimos-ativos'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Devolução -->
<div id="ea-modal-return" class="magma-modal" style="display:none;">
    <div class="magma-modal-content animate-in">
        <div class="magma-modal-header">
            <h2><?php _e('Registrar Devolução', 'emprestimos-ativos'); ?></h2>
            <span class="ea-modal-close dashicons dashicons-no-alt"></span>
        </div>
        <form id="ea-form-return">
            <input type="hidden" name="emprestimo_id" id="return-emprestimo-id">
            <div class="magma-modal-body">
                <p><strong>Equipamento:</strong> <span id="return-ativo-nome"></span></p>
                <div class="magma-form-group">
                    <label><?php _e('Condição:', 'emprestimos-ativos'); ?></label>
                    <select name="condicao" id="return-condicao" class="magma-input">
                        <option value="bom">Bom</option>
                        <option value="regular">Regular</option>
                        <option value="danificado">Danificado</option>
                        <option value="manutencao">Requer Manutenção</option>
                    </select>
                </div>
                <div class="magma-form-group">
                    <label><?php _e('Observações:', 'emprestimos-ativos'); ?></label>
                    <textarea name="observacao" id="return-observacao" class="magma-input" rows="3"></textarea>
                </div>
            </div>
            <div class="magma-modal-footer">
                <button type="button" class="magma-btn-cancel ea-modal-cancel">Cancelar</button>
                <button type="submit" class="magma-btn-save">Confirmar Devolução</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Empréstimo -->
<div id="ea-modal-edit-loan" class="magma-modal" style="display:none;">
    <div class="magma-modal-content animate-in">
        <div class="magma-modal-header">
            <h2><?php _e('Editar Empréstimo', 'emprestimos-ativos'); ?></h2>
            <span class="ea-modal-close dashicons dashicons-no-alt"></span>
        </div>
        <form id="ea-form-edit-loan">
            <input type="hidden" name="id" id="edit-loan-id">
            <div class="magma-modal-body">
                <div class="magma-form-grid">
                    <div class="magma-field-half">
                        <label>Data Empréstimo:</label>
                        <input type="date" name="data_emprestimo" id="edit-data" class="magma-input" required>
                    </div>
                    <div class="magma-field-half">
                        <label>Hora Retirada:</label>
                        <input type="time" name="hora_retirada" id="edit-hora-ret" class="magma-input" required>
                    </div>
                    <div class="magma-field-half">
                        <label>Data Devolução:</label>
                        <input type="date" name="data_devolucao" id="edit-data-dev" class="magma-input" required>
                    </div>
                    <div class="magma-field-half">
                        <label>Hora Devolução:</label>
                        <input type="time" name="hora_devolucao" id="edit-hora-dev" class="magma-input" required>
                    </div>
                </div>
                <div class="magma-form-group" style="margin-top:15px;">
                    <label>Observações:</label>
                    <textarea name="observacao" id="edit-obs" class="magma-input" rows="3"></textarea>
                </div>
            </div>
            <div class="magma-modal-footer">
                <button type="button" class="magma-btn-cancel ea-modal-cancel">Cancelar</button>
                <button type="submit" class="magma-btn-save">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Visualizar Detalhes -->
<div id="ea-modal-view" class="magma-modal" style="display:none;">
    <div class="magma-modal-content animate-in" style="width:560px;">
        <div class="magma-modal-header">
            <h2>Detalhes do Empréstimo</h2>
            <span class="ea-modal-close dashicons dashicons-no-alt"></span>
        </div>
        <div class="magma-modal-body" id="ea-modal-view-body">
            <p style="color:#94a3b8; text-align:center;">Carregando...</p>
        </div>
        <div class="magma-modal-footer">
            <button type="button" class="magma-btn-cancel ea-modal-cancel">Fechar</button>
        </div>
    </div>
</div>

<!-- Modal: Nova Reserva pelo Admin -->
<div id="ea-modal-reserve" class="magma-modal" style="display:none;">
    <div class="magma-modal-content animate-in" style="width:580px;">
        <div class="magma-modal-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="background:#f0f7ff;color:#0052cc;padding:8px;border-radius:8px;display:flex;">
                    <span class="dashicons dashicons-calendar-alt"></span>
                </span>
                <div>
                    <h2 style="margin:0"><?php _e('Nova Reserva pelo Admin', 'emprestimos-ativos'); ?></h2>
                    <p style="margin:0;font-size:12px;color:#94a3b8;"><?php _e('Reservar equipamento para um colaborador', 'emprestimos-ativos'); ?></p>
                </div>
            </div>
            <span class="ea-modal-close dashicons dashicons-no-alt"></span>
        </div>
        <form id="ea-form-reserve">
            <div class="magma-modal-body">

                <!-- Colaborador -->
                <div class="magma-reserve-section">
                    <div class="magma-section-label">
                        <span class="dashicons dashicons-admin-users"></span>
                        <?php _e('Colaborador', 'emprestimos-ativos'); ?>
                    </div>
                    <div class="magma-form-group">
                        <label><?php _e('Selecionar Colaborador: *', 'emprestimos-ativos'); ?></label>
                        <select name="funcionario_id" id="reserve-funcionario" class="magma-input magma-select" required>
                            <option value="">— Escolha um colaborador —</option>
                            <?php foreach ($funcionarios as $func): ?>
                                <option value="<?php echo $func->id; ?>" data-email="<?php echo esc_attr($func->email); ?>">
                                    <?php echo esc_html($func->nome); ?> — <?php echo esc_html($func->email); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="magma-form-group">
                        <label><?php _e('Setor:', 'emprestimos-ativos'); ?></label>
                        <select name="setor_id" id="reserve-setor" class="magma-input magma-select">
                            <option value="">— Sem setor —</option>
                            <?php foreach ($setores as $setor): ?>
                                <option value="<?php echo $setor->id; ?>"><?php echo esc_html($setor->nome); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Equipamento -->
                <div class="magma-reserve-section">
                    <div class="magma-section-label">
                        <span class="dashicons dashicons-desktop"></span>
                        <?php _e('Equipamento', 'emprestimos-ativos'); ?>
                    </div>
                    <div class="magma-form-group">
                        <label><?php _e('Selecionar Ativo Disponível: *', 'emprestimos-ativos'); ?></label>
                        <select name="ativo_id" id="reserve-ativo" class="magma-input magma-select" required>
                            <option value="">— Escolha um equipamento —</option>
                            <?php foreach ($ativos_disponiveis as $ativo): ?>
                                <option value="<?php echo $ativo->id; ?>">
                                    <?php echo esc_html($ativo->nome); ?> (<?php echo esc_html($ativo->tipo); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($ativos_disponiveis)): ?>
                            <p class="magma-warning-text">
                                <span class="dashicons dashicons-warning"></span>
                                <?php _e('Nenhum ativo disponível no momento.', 'emprestimos-ativos'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Período -->
                <div class="magma-reserve-section">
                    <div class="magma-section-label">
                        <span class="dashicons dashicons-clock"></span>
                        <?php _e('Período', 'emprestimos-ativos'); ?>
                    </div>
                    <div class="magma-form-grid">
                        <div class="magma-field-half">
                            <label><?php _e('Data de Retirada: *', 'emprestimos-ativos'); ?></label>
                            <input type="date" name="data_emprestimo" id="reserve-data-ret" class="magma-input" required>
                        </div>
                        <div class="magma-field-half">
                            <label><?php _e('Hora de Retirada: *', 'emprestimos-ativos'); ?></label>
                            <input type="time" name="hora_retirada" id="reserve-hora-ret" class="magma-input" value="08:00" required>
                        </div>
                        <div class="magma-field-half">
                            <label><?php _e('Data de Devolução: *', 'emprestimos-ativos'); ?></label>
                            <input type="date" name="data_devolucao" id="reserve-data-dev" class="magma-input" required>
                        </div>
                        <div class="magma-field-half">
                            <label><?php _e('Hora de Devolução: *', 'emprestimos-ativos'); ?></label>
                            <input type="time" name="hora_devolucao" id="reserve-hora-dev" class="magma-input" value="18:00" required>
                        </div>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="magma-reserve-section" style="border-bottom:none;padding-bottom:0;">
                    <div class="magma-section-label">
                        <span class="dashicons dashicons-info-outline"></span>
                        <?php _e('Informações Adicionais', 'emprestimos-ativos'); ?>
                    </div>
                    <div class="magma-form-group">
                        <label><?php _e('Tipo de Registro:', 'emprestimos-ativos'); ?></label>
                        <div class="magma-radio-group">
                            <label class="magma-radio-option">
                                <input type="radio" name="tipo_registro" value="reservado" checked>
                                <span class="magma-radio-box">
                                    <span class="dashicons dashicons-calendar-alt"></span>
                                    <strong><?php _e('Reserva Futura', 'emprestimos-ativos'); ?></strong>
                                    <small><?php _e('Reservar para data futura', 'emprestimos-ativos'); ?></small>
                                </span>
                            </label>
                            <label class="magma-radio-option">
                                <input type="radio" name="tipo_registro" value="ativo">
                                <span class="magma-radio-box">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <strong><?php _e('Empréstimo Imediato', 'emprestimos-ativos'); ?></strong>
                                    <small><?php _e('Já saiu agora com o colaborador', 'emprestimos-ativos'); ?></small>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="magma-form-group">
                        <label><?php _e('Observações:', 'emprestimos-ativos'); ?></label>
                        <textarea name="observacao" id="reserve-obs" class="magma-input" rows="3" 
                            placeholder="<?php _e('Motivo da reserva, instruções especiais...', 'emprestimos-ativos'); ?>"></textarea>
                    </div>
                </div>

            </div>
            <div class="magma-modal-footer">
                <button type="button" class="magma-btn-cancel ea-modal-cancel">
                    <?php _e('Cancelar', 'emprestimos-ativos'); ?>
                </button>
                <button type="submit" class="magma-btn-save" id="reserve-submit-btn">
                    <span class="dashicons dashicons-calendar-alt" style="margin-right:5px;"></span>
                    <?php _e('Confirmar Reserva', 'emprestimos-ativos'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* --- DESIGN SYSTEM MAGMA3 --- */
.magma-style { background: #f4f7fa; padding: 20px; font-family: 'Segoe UI', sans-serif; }
.magma-header { display: flex; align-items: center; margin-bottom: 25px; min-height: 60px; gap: 10px; }
.magma-title-section { display: flex; align-items: center; gap: 15px; }
.magma-icon-bg { background: #0046ad; color: white; padding: 12px; border-radius: 12px; display: flex; }
.magma-header h1 { margin: 0 !important; font-size: 22px !important; color: #1a2b4a; font-weight: 700; }

.magma-btn-bulk-cancel { 
    background: #fee2e2; color: #991b1b; border: 1px solid #f87171; 
    padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600;
    display: flex; align-items: center; gap: 8px; transition: 0.2s;
}
.magma-btn-bulk-cancel:hover { background: #fecaca; }

.magma-btn-bulk-return {
    background: #dcfce7; color: #166534; border: 1px solid #86efac;
    padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600;
    display: flex; align-items: center; gap: 8px; transition: 0.2s;
}
.magma-btn-bulk-return:hover { background: #bbf7d0; }

.magma-btn-reserve {
    background: #0052cc; color: white; border: none;
    padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 600;
    display: flex; align-items: center; gap: 8px; font-size: 13px;
    transition: background 0.2s; box-shadow: 0 2px 8px rgba(0,82,204,0.25);
}
.magma-btn-reserve:hover { background: #003d99; }

#nova-reserva-container { margin-left: auto; }

.magma-tabs-wrapper { margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
.magma-tabs { display: flex; gap: 10px; }
.magma-tab { text-decoration: none; padding: 10px 20px; color: #64748b; font-weight: 600; font-size: 14px; border-bottom: 3px solid transparent; }
.magma-tab.is-active { color: #0052cc; border-bottom-color: #0052cc; }

.magma-table-container { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
.magma-table { width: 100%; border-collapse: collapse; }
.magma-table th { background: #fcfdfe; text-align: left; padding: 15px 20px; font-size: 11px; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #f1f5f9; }
.magma-table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 13px; }

.magma-main-text { font-weight: 600; color: #1e293b; display: block; }
.magma-sub-text { font-size: 11px; color: #94a3b8; display: block; }
.magma-id-badge { background: #f1f5f9; padding: 3px 8px; border-radius: 6px; font-family: monospace; font-weight: 600; color: #475569; }

.magma-status { padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
.status-ativo    { background: #dcfce7; color: #166534; }
.status-atrasado { background: #fee2e2; color: #991b1b; }
.status-reservado{ background: #eff6ff; color: #1d4ed8; }
.status-concluido{ background: #f1f5f9; color: #64748b; }
.status-cancelado{ background: #f1f5f9; color: #64748b; }

.magma-actions-group { display: flex; gap: 5px; justify-content: flex-end; }
.magma-action-btn { background: white; border: 1px solid #e2e8f0; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #64748b; transition: 0.2s; }
.magma-action-btn:hover        { border-color: #0052cc; color: #0052cc; background: #f8fafc; }
.magma-action-btn.cancel:hover { border-color: #dc2626; color: #dc2626; background: #fef2f2; }
.magma-action-btn.activate:hover { border-color: #16a34a; color: #16a34a; background: #f0fdf4; }

/* Modais */
.magma-modal { position: fixed; z-index: 99999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
.magma-modal-content { background: white; border-radius: 15px; width: 480px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
.magma-modal-header { padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 1; }
.magma-modal-body { padding: 20px; }
.magma-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.magma-input { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-top: 5px; box-sizing: border-box; font-size: 13px; }
.magma-input:focus { outline: none; border-color: #0052cc; box-shadow: 0 0 0 3px rgba(0,82,204,0.1); }
.magma-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
.magma-modal-footer { padding: 15px 20px; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px; position: sticky; bottom: 0; }
.magma-btn-save { background: #0052cc; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 5px; }
.magma-btn-save:hover { background: #003d99; }
.magma-btn-cancel { background: white; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 8px; cursor: pointer; }

.magma-reserve-section { padding: 15px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 5px; }
.magma-section-label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em; margin-bottom: 12px; }
.magma-form-group { margin-bottom: 12px; }
.magma-form-group label { font-size: 12px; font-weight: 600; color: #475569; }
.magma-warning-text { color: #b45309; font-size: 12px; margin: 5px 0 0; display: flex; align-items: center; gap: 5px; }

.magma-radio-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 8px; }
.magma-radio-option { cursor: pointer; }
.magma-radio-option input[type="radio"] { display: none; }
.magma-radio-box { display: flex; flex-direction: column; gap: 2px; border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px; transition: 0.2s; }
.magma-radio-box .dashicons { color: #94a3b8; font-size: 18px; margin-bottom: 4px; }
.magma-radio-box strong { font-size: 12px; color: #1e293b; }
.magma-radio-box small { font-size: 11px; color: #94a3b8; }
.magma-radio-option input[type="radio"]:checked + .magma-radio-box { border-color: #0052cc; background: #eff6ff; }
.magma-radio-option input[type="radio"]:checked + .magma-radio-box .dashicons { color: #0052cc; }
.magma-radio-option input[type="radio"]:checked + .magma-radio-box strong { color: #0052cc; }

.magma-no-data { text-align: center; padding: 40px !important; color: #94a3b8; }
.animate-in { animation: modalIn 0.25s ease-out; }
@keyframes modalIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
jQuery(document).ready(function($) {

    // --- Seleção Múltipla ---
    function toggleBulkActions() {
        const selectedCount = $('.loan-checkbox:checked').length;
        if (selectedCount > 0) {
            $('#bulk-actions-container').css('display', 'flex');
            $('#selected-count').text(selectedCount);
        } else {
            $('#bulk-actions-container').hide();
        }
    }

    $('#select-all-loans').on('change', function() {
        $('.loan-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkActions();
    });

    $(document).on('change', '.loan-checkbox', function() {
        toggleBulkActions();
    });

    // --- Controle Manual Globais de Modais (Abrir / Fechar) ---
    $('#ea-btn-open-reserve').on('click', function() {
        $('#ea-modal-reserve').show();
    });

    $('.ea-modal-close, .ea-modal-cancel').on('click', function() {
        $(this).closest('.magma-modal').hide();
    });

    $('.magma-modal').on('click', function(e) {
        if ($(e.target).hasClass('magma-modal')) {
            $(this).hide();
        }
    });

    // --- Cancelamento em Massa ---
    $('#ea-btn-bulk-cancel').on('click', function() {
        const ids = $('.loan-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (confirm('Deseja realmente cancelar os ' + ids.length + ' empréstimos selecionados?')) {
            $.ajax({
                url: ea_admin.ajax_url, 
                type: 'POST',
                data: { action: 'ea_admin_cancel_loan', nonce: ea_admin.nonce, id: ids },
                success: function(r) { 
                    alert(r.data.message); 
                    if (r.success) location.reload(); 
                }
            });
        }
    });

    // --- Cancelamento Individual ---
    $(document).on('click', '.ea-btn-cancel-loan', function() {
        const id = $(this).data('id');
        const ativo = $(this).data('ativo');
        if (confirm('Deseja realmente cancelar o empréstimo do equipamento: ' + ativo + '?')) {
            $.ajax({
                url: ea_admin.ajax_url,
                type: 'POST',
                data: { action: 'ea_admin_cancel_loan', nonce: ea_admin.nonce, id: [id] },
                success: function(r) { 
                    alert(r.data.message); 
                    if (r.success) location.reload(); 
                }
            });
        }
    });

   // --- Visualizar Detalhes ---
    $(document).on('click', '.ea-btn-view', function() {
      const id = $(this).data('id');
    $('#ea-modal-view-body').html('<p style="text-align:center;color:#94a3b8;">Carregando...</p>');
    $('#ea-modal-view').show();

    $.ajax({
        url: ea_admin.ajax_url,
        type: 'POST',
        data: { action: 'ea_admin_get_loan_detail', nonce: ea_admin.nonce, id: id },
        success: function(r) {
            if (r.success) {
                const d = r.data;
                $('#ea-modal-view-body').html(`
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <tr><td style="padding:8px 0;color:#94a3b8;width:40%">Código</td><td><strong>#${d.codigo}</strong></td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">Equipamento</td><td>${d.ativo_nome} <small style="color:#94a3b8">(${d.ativo_tipo})</small></td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">Colaborador</td><td>${d.funcionario_nome}</td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">E-mail</td><td>${d.funcionario_email}</td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">WhatsApp</td><td>${d.whatsapp || '-'}</td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">Setor</td><td>${d.setor_nome || '-'}</td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">Retirada</td><td>${d.data_emprestimo} às ${d.hora_retirada}</td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">Devolução Prevista</td><td>${d.data_devolucao_prevista} às ${d.hora_devolucao_prevista}</td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">Status</td><td><span class="magma-status status-${d.status}">${d.status}</span></td></tr>
                        <tr><td style="padding:8px 0;color:#94a3b8;">Observação</td><td>${d.observacao || '-'}</td></tr>
                    </table>
                `);
            } else {
                $('#ea-modal-view-body').html('<p style="color:red;">' + r.data.message + '</p>');
            }
        }
    });
   });

    // --- Devolução de Equipamento ---
    $(document).on('click', '.ea-btn-return', function() {
        const id = $(this).data('id');
        const ativo = $(this).data('ativo');
        $('#return-emprestimo-id').val(id);
        $('#return-ativo-nome').text(ativo);
        $('#ea-modal-return').show();
    });

    $('#ea-form-return').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=ea_admin_register_return&nonce=' + ea_admin.nonce;
        $.ajax({
            url: ea_admin.ajax_url,
            type: 'POST',
            data: formData,
            success: function(r) {
                alert(r.data.message);
                if (r.success) location.reload();
            }
        });
    });

    // --- Edita*r Período do Empréstimo ---
    $(document).on('click', '.ea-btn-edit-loan', function() {
    $('#edit-loan-id').val($(this).data('id'));
    $('#edit-data').val($(this).data('data'));
    $('#edit-hora-ret').val($(this).data('horaRet'));   // era 'hora-ret'
    $('#edit-data-dev').val($(this).data('dataDev'));   // era 'data-dev'
    $('#edit-hora-dev').val($(this).data('horaDev'));   // era 'hora-dev'
    $('#edit-obs').val($(this).data('obs'));
    $('#ea-modal-edit-loan').show();
   });

    $('#ea-form-edit-loan').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=ea_admin_update_loan&nonce=' + ea_admin.nonce;
        $.ajax({
            url: ea_admin.ajax_url,
            type: 'POST',
            data: formData,
            success: function(r) {
                alert(r.data.message);
                if (r.success) location.reload();
            }
        });
    });

    // --- Ativar uma Reserva Pendente ---
    $(document).on('click', '.ea-btn-activate-reserve', function() {
        const id = $(this).data('id');
        const ativo = $(this).data('ativo');
        if (confirm('Deseja realmente ativar e iniciar o empréstimo para: ' + ativo + '?')) {
            $.ajax({
                url: ea_admin.ajax_url,
                type: 'POST',
                data: { action: 'ea_admin_activate_reserve', nonce: ea_admin.nonce, id: id },
                success: function(r) {
                    alert(r.data.message);
                    if (r.success) location.reload();
                }
            });
        }
    });

    // --- Formulário de Criação de Reserva / Novo Empréstimo ---
    $('#ea-form-reserve').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=ea_admin_save_reserve&nonce=' + ea_admin.nonce;
        $.ajax({
            url: ea_admin.ajax_url,
            type: 'POST',
            data: formData,
            success: function(r) {
                alert(r.data.message);
                if (r.success) location.reload();
            }
        });
    });
});
</script>