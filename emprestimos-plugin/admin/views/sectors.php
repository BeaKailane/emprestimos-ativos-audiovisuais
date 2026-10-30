<?php if (!defined('ABSPATH')) exit;
global $wpdb;
$table = $wpdb->prefix . 'ea_setores';
$setores = $wpdb->get_results("SELECT * FROM $table ORDER BY nome ASC");
?>

<div class="wrap magma-style">
    <div class="magma-header">
        <div class="magma-title-section">
            <span class="magma-icon-bg"><span class="dashicons dashicons-building"></span></span>
            <div>
                <h1><?php _e('Gerenciar Setores', 'emprestimos-ativos'); ?></h1>
                <p><?php _e('Organize sua estrutura organizacional', 'emprestimos-ativos'); ?></p>
            </div>
        </div>
        <button class="magma-btn-primary" id="ea-btn-add-setor">
            <span class="dashicons dashicons-plus"></span> <?php _e('Novo Setor', 'emprestimos-ativos'); ?>
        </button>
    </div>

    <div class="magma-table-container">
        <table class="magma-table">
            <thead>
                <tr>
                    <th><?php _e('Nome do Setor', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Descrição', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Status', 'emprestimos-ativos'); ?></th>
                    <th style="text-align:right;"><?php _e('Ações', 'emprestimos-ativos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($setores): foreach ($setores as $setor): ?>
                <tr>
                    <td><span class="magma-setor-name"><?php echo esc_html($setor->nome); ?></span></td>
                    <td><span class="magma-col-desc"><?php echo esc_html($setor->descricao ?: '-'); ?></span></td>
                    <td>
                        <span class="magma-status status-<?php echo $setor->status; ?>">
                            <?php echo ucfirst($setor->status); ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <button class="magma-action edit ea-btn-edit-setor" 
                                data-id="<?php echo $setor->id; ?>" 
                                data-nome="<?php echo esc_attr($setor->nome); ?>" 
                                data-descricao="<?php echo esc_attr($setor->descricao); ?>" 
                                data-status="<?php echo esc_attr($setor->status); ?>"
                                title="Editar">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button class="magma-action delete ea-btn-delete-setor" 
                                data-id="<?php echo $setor->id; ?>"
                                title="Excluir">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4" class="magma-no-data"><?php _e('Nenhum setor cadastrado.', 'emprestimos-ativos'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="ea-modal-setor" class="magma-modal" style="display:none;">
    <div class="magma-modal-content animate-in">
        <div class="magma-modal-header">
            <h2 id="ea-modal-setor-title"><?php _e('Novo Setor', 'emprestimos-ativos'); ?></h2>
            <span class="ea-modal-close dashicons dashicons-no-alt"></span>
        </div>
        <form id="ea-form-setor">
            <input type="hidden" name="id" id="setor-id">
            <div class="magma-modal-body">
                <div class="magma-form-group">
                    <label><?php _e('Nome do Setor *', 'emprestimos-ativos'); ?></label>
                    <input type="text" name="nome" id="setor-nome" class="magma-input" placeholder="Ex: Tecnologia" required>
                </div>
                <div class="magma-form-group">
                    <label><?php _e('Descrição', 'emprestimos-ativos'); ?></label>
                    <textarea name="descricao" id="setor-descricao" class="magma-input" rows="3" placeholder="Breve descrição do setor..."></textarea>
                </div>
                <div class="magma-form-group">
                    <label><?php _e('Status Disponível', 'emprestimos-ativos'); ?></label>
                    <select name="status" id="setor-status" class="magma-input">
                        <option value="ativo">🟢 Ativo</option>
                        <option value="inativo">🔴 Inativo</option>
                    </select>
                </div>
            </div>
            <div class="magma-modal-footer">
                <button type="button" class="magma-btn-cancel ea-modal-cancel"><?php _e('Cancelar', 'emprestimos-ativos'); ?></button>
                <button type="submit" class="magma-btn-save"><?php _e('Salvar Setor', 'emprestimos-ativos'); ?></button>
            </div>
        </form>
    </div>
</div>

<style>
/* --- DESIGN SYSTEM MAGMA3 (Setores) --- */
.magma-style { background: #f4f7fa; padding: 20px; font-family: 'Segoe UI', Roboto, sans-serif; }

/* Header */
.magma-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.magma-title-section { display: flex; align-items: center; gap: 15px; }
.magma-icon-bg { background: #0046ad; color: white; padding: 12px; border-radius: 12px; display: flex; }
.magma-header h1 { margin: 0 !important; font-size: 22px !important; color: #1a2b4a; font-weight: 700; }
.magma-header p { margin: 0; color: #7a8ba1; font-size: 13px; }

/* Botão Primário Alinhado */
.magma-btn-primary { 
    background: #0052cc; color: white; border: none; padding: 10px 20px; 
    border-radius: 8px; font-weight: 600; cursor: pointer; 
    display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; 
}
.magma-btn-primary:hover { background: #0046ad; box-shadow: 0 4px 12px rgba(0,82,204,0.3); }
.magma-btn-primary .dashicons { font-size: 18px; width: 18px; height: 18px; display: flex; align-items: center; }

/* Tabela */
.magma-table-container { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.magma-table { width: 100%; border-collapse: collapse; margin: 0; }
.magma-table th { background: #fcfdfe; text-align: left; padding: 15px 20px; font-size: 11px; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #f1f5f9; }
.magma-table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }

.magma-setor-name { font-weight: 600; color: #1e293b; }
.magma-col-desc { color: #64748b; font-size: 13px; }

/* Status Pill */
.magma-status { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
.status-ativo { background: #e0f9f1; color: #059669; }
.status-inativo { background: #fef2f2; color: #dc2626; }

/* Ações Alinhadas */
.magma-action { 
    background: transparent; border: 1px solid #e1e8f0; border-radius: 6px; 
    cursor: pointer; color: #94a3b8; width: 32px; height: 32px; 
    display: inline-flex; align-items: center; justify-content: center; 
    transition: 0.2s; margin-left: 5px; 
}
.magma-action .dashicons { font-size: 18px; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; }
.magma-action:hover { border-color: #0052cc; color: #0052cc; background: #f8fafc; }
.magma-action.delete:hover { color: #ef4444; border-color: #fee2e2; }

/* Modal */
.magma-modal { position: fixed; z-index: 99999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
.magma-modal-content { background: white; border-radius: 15px; width: 100%; max-width: 450px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
.magma-modal-header { padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
.magma-modal-body { padding: 20px; }
.magma-input { width: 100%; border: 1px solid #e1e8f0; border-radius: 8px; padding: 10px; margin-top: 5px; font-size: 14px; box-sizing: border-box; }
.magma-modal-footer { padding: 15px 20px; background: #f9fafc; display: flex; justify-content: flex-end; gap: 10px; }
.magma-btn-save { background: #0052cc; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
.magma-btn-cancel { background: white; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 8px; cursor: pointer; }

.magma-no-data { text-align: center; padding: 40px !important; color: #94a3b8; }
.animate-in { animation: modalIn 0.25s ease-out; }
@keyframes modalIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
jQuery(document).ready(function($) {
    const modal = $('#ea-modal-setor');

    // Novo Setor
    $('#ea-btn-add-setor').on('click', function() {
        $('#ea-form-setor')[0].reset();
        $('#setor-id').val('');
        $('#ea-modal-setor-title').text('<?php _e('Novo Setor', 'emprestimos-ativos'); ?>');
        modal.fadeIn(200).css('display', 'flex');
    });
    
    // Editar
    $('.ea-btn-edit-setor').on('click', function() {
        $('#setor-id').val($(this).data('id'));
        $('#setor-nome').val($(this).data('nome'));
        $('#setor-descricao').val($(this).data('descricao'));
        $('#setor-status').val($(this).data('status'));
        $('#ea-modal-setor-title').text('<?php _e('Editar Setor', 'emprestimos-ativos'); ?>');
        modal.fadeIn(200).css('display', 'flex');
    });
    
    $('.ea-modal-close, .ea-modal-cancel').on('click', function() { modal.fadeOut(200); });

    // AJAX Salvar
    $('#ea-form-setor').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: ea_admin.ajax_url, type: 'POST',
            data: { 
                action: 'ea_admin_save_setor', nonce: ea_admin.nonce, 
                id: $('#setor-id').val(), nome: $('#setor-nome').val(), 
                descricao: $('#setor-descricao').val(), status: $('#setor-status').val() 
            },
            success: function(r) { if(r.success) location.reload(); else alert(r.data.message); }
        });
    });
    
    // AJAX Excluir
    $('.ea-btn-delete-setor').on('click', function() {
        if (!confirm('<?php _e('Tem certeza que deseja excluir este setor?', 'emprestimos-ativos'); ?>')) return;
        $.ajax({
            url: ea_admin.ajax_url, type: 'POST',
            data: { action: 'ea_admin_delete_setor', nonce: ea_admin.nonce, id: $(this).data('id') },
            success: function(r) { if(r.success) location.reload(); else alert(r.data.message); }
        });
    });
});
</script>