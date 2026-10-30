<?php if (!defined('ABSPATH')) exit;
global $wpdb;
$table = $wpdb->prefix . 'ea_ativos';
$ativos = $wpdb->get_results("SELECT * FROM $table ORDER BY nome ASC");
$tipos = explode(',', EA_Database::get_config('tipos_ativos'));
?>
<div class="wrap ea-admin">
    <h1 class="ea-page-title">
        <span class="dashicons dashicons-archive"></span>
        <?php _e('Gerenciar Ativos', 'emprestimos-ativos'); ?>
        <button class="button button-primary ea-btn-add" id="ea-btn-add-asset">
            <span class="dashicons dashicons-plus-alt"></span> <?php _e('Novo Ativo', 'emprestimos-ativos'); ?>
        </button>
    </h1>
    
    <div class="ea-filters">
        <select id="filter-tipo" class="ea-select">
            <option value=""><?php _e('Todos os tipos', 'emprestimos-ativos'); ?></option>
            <?php foreach ($tipos as $tipo): ?>
            <option value="<?php echo esc_attr($tipo); ?>"><?php echo esc_html(ucfirst($tipo)); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filter-status" class="ea-select">
            <option value=""><?php _e('Todos os status', 'emprestimos-ativos'); ?></option>
            <option value="disponivel"><?php _e('Disponível', 'emprestimos-ativos'); ?></option>
            <option value="emprestado"><?php _e('Emprestado', 'emprestimos-ativos'); ?></option>
            <option value="manutencao"><?php _e('Em manutenção', 'emprestimos-ativos'); ?></option>
        </select>
        <input type="text" id="filter-busca" class="ea-input" placeholder="<?php _e('Buscar...', 'emprestimos-ativos'); ?>">
    </div>
    
    <div class="ea-card">
        <table class="ea-table" id="ea-assets-table">
            <thead>
                <tr>
                    <th width="80"><?php _e('Imagem', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Nome', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Tipo', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Nº Série', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Etiqueta', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Condição', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Disponibilidade', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Status', 'emprestimos-ativos'); ?></th>
                    <th width="120"><?php _e('Ações', 'emprestimos-ativos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ativos): foreach ($ativos as $ativo):
                    $img_url = $ativo->imagem_id ? wp_get_attachment_thumb_url($ativo->imagem_id) : EA_PLUGIN_URL . 'assets/images/placeholder.png';
                ?>
                <tr data-id="<?php echo $ativo->id; ?>" data-tipo="<?php echo esc_attr($ativo->tipo); ?>" data-status="<?php echo esc_attr($ativo->disponibilidade); ?>">
                    <td><img src="<?php echo esc_url($img_url); ?>" class="ea-thumb" alt=""></td>
                    <td><strong><?php echo esc_html($ativo->nome); ?></strong></td>
                    <td><?php echo esc_html(ucfirst($ativo->tipo)); ?></td>
                    <td><?php echo esc_html($ativo->numero_serie ?: '-'); ?></td>
                    <td><?php echo esc_html($ativo->etiqueta_patrimonial ?: '-'); ?></td>
                    <td><?php echo esc_html(ucfirst($ativo->condicao)); ?></td>
                    <td><span class="ea-badge ea-badge-<?php echo esc_attr($ativo->disponibilidade); ?>"><?php echo esc_html(ucfirst($ativo->disponibilidade)); ?></span></td>
                    <td><span class="ea-badge ea-badge-<?php echo $ativo->status; ?>"><?php echo esc_html(ucfirst($ativo->status)); ?></span></td>
                    <td>
                        <button class="button ea-btn-edit" data-id="<?php echo $ativo->id; ?>" title="<?php _e('Editar', 'emprestimos-ativos'); ?>"><span class="dashicons dashicons-edit"></span></button>
                        <button class="button ea-btn-delete" data-id="<?php echo $ativo->id; ?>" title="<?php _e('Excluir', 'emprestimos-ativos'); ?>"><span class="dashicons dashicons-trash"></span></button>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="9" class="ea-no-data"><?php _e('Nenhum ativo cadastrado.', 'emprestimos-ativos'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Ativo -->
<div id="ea-modal-asset" class="ea-modal" style="display:none;">
    <div class="ea-modal-content ea-modal-large">
        <span class="ea-modal-close">&times;</span>
        <h2 id="ea-modal-asset-title"><?php _e('Novo Ativo', 'emprestimos-ativos'); ?></h2>
        <form id="ea-form-asset">
            <input type="hidden" name="id" id="asset-id">
            <div class="ea-form-row">
                <div class="ea-form-col">
                    <div class="ea-form-group">
                        <label for="asset-nome"><?php _e('Nome *', 'emprestimos-ativos'); ?></label>
                        <input type="text" name="nome" id="asset-nome" class="ea-input" required>
                    </div>
                </div>
                <div class="ea-form-col">
                    <div class="ea-form-group">
                        <label for="asset-tipo"><?php _e('Tipo *', 'emprestimos-ativos'); ?></label>
                        <select name="tipo" id="asset-tipo" class="ea-select" required>
                            <?php foreach ($tipos as $tipo): ?>
                            <option value="<?php echo esc_attr($tipo); ?>"><?php echo esc_html(ucfirst($tipo)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ea-form-group">
                <label for="asset-descricao"><?php _e('Descrição', 'emprestimos-ativos'); ?></label>
                <textarea name="descricao" id="asset-descricao" class="ea-textarea" rows="3"></textarea>
            </div>
            <div class="ea-form-row">
                <div class="ea-form-col">
                    <div class="ea-form-group">
                        <label for="asset-numero_serie"><?php _e('Número de Série', 'emprestimos-ativos'); ?></label>
                        <input type="text" name="numero_serie" id="asset-numero_serie" class="ea-input">
                    </div>
                </div>
                <div class="ea-form-col">
                    <div class="ea-form-group">
                        <label for="asset-etiqueta"><?php _e('Etiqueta Patrimonial', 'emprestimos-ativos'); ?></label>
                        <input type="text" name="etiqueta_patrimonial" id="asset-etiqueta" class="ea-input">
                    </div>
                </div>
            </div>
            <div class="ea-form-row">
                <div class="ea-form-col">
                    <div class="ea-form-group">
                        <label for="asset-condicao"><?php _e('Condição', 'emprestimos-ativos'); ?></label>
                        <select name="condicao" id="asset-condicao" class="ea-select">
                            <option value="bom"><?php _e('Bom', 'emprestimos-ativos'); ?></option>
                            <option value="regular"><?php _e('Regular', 'emprestimos-ativos'); ?></option>
                            <option value="danificado"><?php _e('Danificado', 'emprestimos-ativos'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="ea-form-col">
                    <div class="ea-form-group">
                        <label for="asset-disponibilidade"><?php _e('Disponibilidade', 'emprestimos-ativos'); ?></label>
                        <select name="disponibilidade" id="asset-disponibilidade" class="ea-select">
                            <option value="disponivel"><?php _e('Disponível', 'emprestimos-ativos'); ?></option>
                            <option value="manutencao"><?php _e('Em Manutenção', 'emprestimos-ativos'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="ea-form-col">
                    <div class="ea-form-group">
                        <label for="asset-status"><?php _e('Status', 'emprestimos-ativos'); ?></label>
                        <select name="status" id="asset-status" class="ea-select">
                            <option value="ativo"><?php _e('Ativo', 'emprestimos-ativos'); ?></option>
                            <option value="inativo"><?php _e('Inativo', 'emprestimos-ativos'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ea-form-group">
                <label><?php _e('Imagem', 'emprestimos-ativos'); ?></label>
                <div class="ea-image-upload">
                    <input type="hidden" name="imagem_id" id="asset-imagem-id">
                    <img id="asset-imagem-preview" src="<?php echo EA_PLUGIN_URL; ?>assets/images/placeholder.png" class="ea-image-preview">
                    <div class="ea-image-buttons">
                        <button type="button" class="button" id="ea-btn-select-image"><?php _e('Selecionar Imagem', 'emprestimos-ativos'); ?></button>
                        <button type="button" class="button" id="ea-btn-remove-image"><?php _e('Remover', 'emprestimos-ativos'); ?></button>
                    </div>
                </div>
            </div>
            <div class="ea-form-actions">
                <button type="submit" class="button button-primary"><?php _e('Salvar', 'emprestimos-ativos'); ?></button>
                <button type="button" class="button ea-modal-cancel"><?php _e('Cancelar', 'emprestimos-ativos'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var mediaUploader;
    
    // Filtros
    function filterAssets() {
        var tipo = $('#filter-tipo').val();
        var status = $('#filter-status').val();
        var busca = $('#filter-busca').val().toLowerCase();
        
        $('#ea-assets-table tbody tr').each(function() {
            var row = $(this);
            var show = true;
            
            if (tipo && row.data('tipo') !== tipo) show = false;
            if (status && row.data('status') !== status) show = false;
            if (busca && row.text().toLowerCase().indexOf(busca) === -1) show = false;
            
            row.toggle(show);
        });
    }
    
    $('#filter-tipo, #filter-status').on('change', filterAssets);
    $('#filter-busca').on('keyup', filterAssets);
    
    // Adicionar ativo
    $('#ea-btn-add-asset').on('click', function() {
        $('#ea-form-asset')[0].reset();
        $('#asset-id').val('');
        $('#asset-imagem-preview').attr('src', ea_admin.placeholder || '<?php echo EA_PLUGIN_URL; ?>assets/images/placeholder.png');
        $('#asset-imagem-id').val('');
        $('#ea-modal-asset-title').text('<?php _e('Novo Ativo', 'emprestimos-ativos'); ?>');
        $('#ea-modal-asset').show();
    });
    
    // Editar ativo
    $('.ea-btn-edit').on('click', function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        $.ajax({
            url: ea_admin.ajax_url,
            type: 'POST',
            data: { action: 'ea_get_asset_detail', nonce: ea_admin.nonce.replace('ea_admin_nonce', 'ea_nonce'), id: id },
            beforeSend: function() {
                // Carregar dados da linha
                $('#asset-id').val(id);
                $('#ea-modal-asset-title').text('<?php _e('Editar Ativo', 'emprestimos-ativos'); ?>');
                $('#ea-modal-asset').show();
            },
            success: function(response) {
                if (response.success) {
                    var a = response.data;
                    $('#asset-nome').val(a.nome);
                    $('#asset-tipo').val(a.tipo);
                    $('#asset-descricao').val(a.descricao);
                    $('#asset-numero_serie').val(a.numero_serie);
                    $('#asset-etiqueta').val(a.etiqueta_patrimonial);
                    $('#asset-condicao').val(a.condicao);
                    $('#asset-disponibilidade').val(a.disponibilidade);
                    $('#asset-status').val(a.status);
                    $('#asset-imagem-id').val(a.imagem_id || '');
                    $('#asset-imagem-preview').attr('src', a.imagem_url);
                }
            }
        });
    });
    
    // Upload de imagem
    $('#ea-btn-select-image').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media({
            title: '<?php _e('Selecionar Imagem', 'emprestimos-ativos'); ?>',
            button: { text: '<?php _e('Usar esta imagem', 'emprestimos-ativos'); ?>' },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#asset-imagem-id').val(attachment.id);
            $('#asset-imagem-preview').attr('src', attachment.url);
        });
        mediaUploader.open();
    });
    
    $('#ea-btn-remove-image').on('click', function() {
        $('#asset-imagem-id').val('');
        $('#asset-imagem-preview').attr('src', '<?php echo EA_PLUGIN_URL; ?>assets/images/placeholder.png');
    });
    
    // Salvar ativo
    $('#ea-form-asset').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ea_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'ea_admin_save_asset',
                nonce: ea_admin.nonce,
                id: $('#asset-id').val(),
                nome: $('#asset-nome').val(),
                tipo: $('#asset-tipo').val(),
                descricao: $('#asset-descricao').val(),
                numero_serie: $('#asset-numero_serie').val(),
                etiqueta_patrimonial: $('#asset-etiqueta').val(),
                condicao: $('#asset-condicao').val(),
                disponibilidade: $('#asset-disponibilidade').val(),
                status: $('#asset-status').val(),
                imagem_id: $('#asset-imagem-id').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Excluir ativo
    $('.ea-btn-delete').on('click', function() {
        if (!confirm('<?php _e('Tem certeza que deseja excluir este ativo?', 'emprestimos-ativos'); ?>')) return;
        
        var id = $(this).data('id');
        
        $.ajax({
            url: ea_admin.ajax_url,
            type: 'POST',
            data: { action: 'ea_admin_delete_asset', nonce: ea_admin.nonce, id: id },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Fechar modal
    $('.ea-modal-close, .ea-modal-cancel').on('click', function() {
        $('.ea-modal').hide();
    });
});
</script>