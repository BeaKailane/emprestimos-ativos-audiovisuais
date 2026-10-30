<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap ea-admin">
    <h1 class="ea-page-title">
        <span class="dashicons dashicons-admin-generic"></span>
        <?php _e('Configurações', 'emprestimos-ativos'); ?>
    </h1>
    
    <form id="ea-form-settings">
        <div class="ea-settings-tabs">
            <button type="button" class="ea-tab-btn active" data-tab="whatsapp"><?php _e('WhatsApp API', 'emprestimos-ativos'); ?></button>
            <button type="button" class="ea-tab-btn" data-tab="mensagens"><?php _e('Mensagens', 'emprestimos-ativos'); ?></button>
            <button type="button" class="ea-tab-btn" data-tab="regras"><?php _e('Regras', 'emprestimos-ativos'); ?></button>
        </div>
        
        <div id="tab-whatsapp" class="ea-tab-content active">
            <div class="ea-card">
                <h2><?php _e('Configuração do WhatsApp', 'emprestimos-ativos'); ?></h2>
                <div class="ea-form-group">
                    <label for="whatsapp_api_type"><?php _e('Tipo de API', 'emprestimos-ativos'); ?></label>
                    <select name="whatsapp_api_type" id="whatsapp_api_type" class="ea-select">
                        <option value="whapi" <?php selected(EA_Database::get_config('whatsapp_api_type'), 'whapi'); ?>>WHAPI</option>
                        <option value="meta" <?php selected(EA_Database::get_config('whatsapp_api_type'), 'meta'); ?>>Meta (WhatsApp Business)</option>
                        <option value="evolution" <?php selected(EA_Database::get_config('whatsapp_api_type'), 'evolution'); ?>>Evolution API</option>
                    </select>
                </div>
                <div class="ea-form-group">
                    <label for="whatsapp_api_token"><?php _e('Token da API', 'emprestimos-ativos'); ?></label>
                    <input type="text" name="whatsapp_api_token" id="whatsapp_api_token" class="ea-input" value="<?php echo esc_attr(EA_Database::get_config('whatsapp_api_token')); ?>">
                </div>
                <div class="ea-form-group">
                    <label for="whatsapp_instance_id"><?php _e('Instance ID / Phone Number ID', 'emprestimos-ativos'); ?></label>
                    <input type="text" name="whatsapp_instance_id" id="whatsapp_instance_id" class="ea-input" value="<?php echo esc_attr(EA_Database::get_config('whatsapp_instance_id')); ?>">
                </div>
                <div class="ea-form-group" id="evolution-url-group" style="display:none;">
                    <label for="evolution_api_url"><?php _e('URL da Evolution API', 'emprestimos-ativos'); ?></label>
                    <input type="url" name="evolution_api_url" id="evolution_api_url" class="ea-input" value="<?php echo esc_attr(EA_Database::get_config('evolution_api_url', 'http://localhost:8080')); ?>">
                </div>
                <button type="button" id="ea-btn-test-whatsapp" class="button"><?php _e('Testar Conexão', 'emprestimos-ativos'); ?></button>
                <span id="ea-test-result"></span>
            </div>
        </div>
        
        <div id="tab-mensagens" class="ea-tab-content">
            <div class="ea-card">
                <h2><?php _e('Templates de Mensagens', 'emprestimos-ativos'); ?></h2>
                <p class="description"><?php _e('Variáveis disponíveis: {nome}, {ativo}, {codigo}, {data_retirada}, {hora_retirada}, {data_devolucao}, {hora_devolucao}', 'emprestimos-ativos'); ?></p>
                
                <div class="ea-form-group">
                    <label for="mensagem_confirmacao"><?php _e('Confirmação de Empréstimo', 'emprestimos-ativos'); ?></label>
                    <textarea name="mensagem_confirmacao" id="mensagem_confirmacao" class="ea-textarea" rows="4"><?php echo esc_textarea(EA_Database::get_config('mensagem_confirmacao')); ?></textarea>
                </div>
                <div class="ea-form-group">
                    <label for="mensagem_aviso_devolucao"><?php _e('Aviso de Devolução (30 min antes)', 'emprestimos-ativos'); ?></label>
                    <textarea name="mensagem_aviso_devolucao" id="mensagem_aviso_devolucao" class="ea-textarea" rows="4"><?php echo esc_textarea(EA_Database::get_config('mensagem_aviso_devolucao')); ?></textarea>
                </div>
                <div class="ea-form-group">
                    <label for="mensagem_atraso"><?php _e('Aviso de Atraso', 'emprestimos-ativos'); ?></label>
                    <textarea name="mensagem_atraso" id="mensagem_atraso" class="ea-textarea" rows="4"><?php echo esc_textarea(EA_Database::get_config('mensagem_atraso')); ?></textarea>
                </div>
                <div class="ea-form-group">
                    <label for="mensagem_devolucao"><?php _e('Confirmação de Devolução', 'emprestimos-ativos'); ?></label>
                    <textarea name="mensagem_devolucao" id="mensagem_devolucao" class="ea-textarea" rows="4"><?php echo esc_textarea(EA_Database::get_config('mensagem_devolucao')); ?></textarea>
                </div>
            </div>
        </div>
        
        <div id="tab-regras" class="ea-tab-content">
            <div class="ea-card">
                <h2><?php _e('Regras do Sistema', 'emprestimos-ativos'); ?></h2>
                <div class="ea-form-group">
                    <label for="tempo_aviso_devolucao"><?php _e('Tempo de aviso antes da devolução (minutos)', 'emprestimos-ativos'); ?></label>
                    <input type="number" name="tempo_aviso_devolucao" id="tempo_aviso_devolucao" class="ea-input" value="<?php echo esc_attr(EA_Database::get_config('tempo_aviso_devolucao', '30')); ?>" min="5" max="120">
                </div>
                <div class="ea-form-group">
                    <label>
                        <input type="checkbox" name="permitir_cancelamento" id="permitir_cancelamento" value="1" <?php checked(EA_Database::get_config('permitir_cancelamento', '1'), '1'); ?>>
                        <?php _e('Permitir cancelamento pelo funcionário', 'emprestimos-ativos'); ?>
                    </label>
                </div>
                <div class="ea-form-group">
                    <label for="tempo_limite_cancelamento"><?php _e('Tempo mínimo antes da retirada para cancelar (minutos)', 'emprestimos-ativos'); ?></label>
                    <input type="number" name="tempo_limite_cancelamento" id="tempo_limite_cancelamento" class="ea-input" value="<?php echo esc_attr(EA_Database::get_config('tempo_limite_cancelamento', '60')); ?>" min="0" max="1440">
                </div>
            </div>
        </div>
        
        <div class="ea-form-actions">
            <button type="submit" class="button button-primary button-large"><?php _e('Salvar Configurações', 'emprestimos-ativos'); ?></button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Tabs
    $('.ea-tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        $('.ea-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.ea-tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });
    
    // Mostrar/esconder URL Evolution
    $('#whatsapp_api_type').on('change', function() {
        $('#evolution-url-group').toggle($(this).val() === 'evolution');
    }).trigger('change');
    
    // Testar conexão
    $('#ea-btn-test-whatsapp').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true);
        $('#ea-test-result').text('<?php _e('Testando...', 'emprestimos-ativos'); ?>');
        
        $.ajax({
            url: ea_admin.ajax_url,
            type: 'POST',
            data: { action: 'ea_admin_test_whatsapp', nonce: ea_admin.nonce },
            success: function(r) {
                btn.prop('disabled', false);
                if (r.success) {
                    $('#ea-test-result').html('<span style="color:green;">✓ ' + r.data.message + '</span>');
                } else {
                    $('#ea-test-result').html('<span style="color:red;">✗ ' + (r.data.error || r.data.message) + '</span>');
                }
            }
        });
    });
    
    // Salvar configurações
    $('#ea-form-settings').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ea_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'ea_admin_save_settings',
                nonce: ea_admin.nonce,
                whatsapp_api_type: $('#whatsapp_api_type').val(),
                whatsapp_api_token: $('#whatsapp_api_token').val(),
                whatsapp_instance_id: $('#whatsapp_instance_id').val(),
                evolution_api_url: $('#evolution_api_url').val(),
                tempo_aviso_devolucao: $('#tempo_aviso_devolucao').val(),
                permitir_cancelamento: $('#permitir_cancelamento').is(':checked') ? '1' : '0',
                tempo_limite_cancelamento: $('#tempo_limite_cancelamento').val(),
                mensagem_confirmacao: $('#mensagem_confirmacao').val(),
                mensagem_aviso_devolucao: $('#mensagem_aviso_devolucao').val(),
                mensagem_atraso: $('#mensagem_atraso').val(),
                mensagem_devolucao: $('#mensagem_devolucao').val()
            },
            success: function(r) {
                alert(r.data.message);
            }
        });
    });
});
</script>
