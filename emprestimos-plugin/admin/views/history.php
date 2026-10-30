<?php if (!defined('ABSPATH')) exit;
global $wpdb;
$table_hist = $wpdb->prefix . 'ea_historico';
$table_ativos = $wpdb->prefix . 'ea_ativos';

if (isset($_GET['action']) && $_GET['action'] === 'clear_history') {
    check_admin_referer('ea_clear_history_action');
    $wpdb->query("TRUNCATE TABLE $table_hist");
    echo '<div class="updated"><p>' . __('Todo o histórico foi apagado com sucesso!', 'emprestimos-ativos') . '</p></div>';
}

$historico = $wpdb->get_results(
    "SELECT h.*, a.nome as ativo_nome, u.display_name as usuario_nome
     FROM $table_hist h
     JOIN $table_ativos a ON h.ativo_id = a.id
     LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
     ORDER BY h.created_at DESC
     LIMIT 200"
);
?>
<div class="wrap ea-admin">
    <h1 class="ea-page-title">
        <span class="dashicons dashicons-backup"></span>
        <?php _e('Histórico de Movimentações', 'emprestimos-ativos'); ?>
        
        <?php if ($historico): ?>
            <?php 
            $clear_url = wp_nonce_url(
                admin_url('admin.php?page=' . $_GET['page'] . '&action=clear_history'),
                'ea_clear_history_action'
            );
            ?>
            <a href="<?php echo $clear_url; ?>" 
               class="page-title-action ea-btn-danger" 
               onclick="return confirm('<?php _e('ATENÇÃO: Deseja realmente APAGAR TODO o histórico? Esta ação não pode ser desfeita.', 'emprestimos-ativos'); ?>');">
                <span class="dashicons dashicons-trash"></span>
                <?php _e('Limpar Histórico', 'emprestimos-ativos'); ?>
            </a>
        <?php endif; ?>
    </h1>
    
    <div class="ea-card">
        <table class="ea-table">
            <thead>
                <tr>
                    <th><?php _e('Data/Hora', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Ativo', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Tipo', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Descrição', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Usuário', 'emprestimos-ativos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($historico): foreach ($historico as $h): ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($h->created_at)); ?></td>
                    <td><?php echo esc_html($h->ativo_nome); ?></td>
                    <td><span class="ea-badge ea-badge-<?php echo sanitize_title($h->tipo_movimentacao); ?>"><?php echo esc_html(ucfirst($h->tipo_movimentacao)); ?></span></td>
                    <td><?php echo esc_html($h->descricao); ?></td>
                    <td><?php echo esc_html($h->usuario_nome ?: 'Sistema'); ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5" class="ea-no-data"><?php _e('Nenhum registro encontrado.', 'emprestimos-ativos'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Estilo do Botão com Lixeira Alinhada */
    .ea-btn-danger {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px;
        color: #d63638 !important;
        border-color: #d63638 !important;
        text-decoration: none;
    }

    .ea-btn-danger .dashicons {
        font-size: 18px;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        margin: 0; /* Remove desalinhamento */
    }

    .ea-btn-danger:hover {
        background: #d63638 !important;
        color: #fff !important;
    }

    .ea-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .ea-badge-saida { background: #fbeae5; color: #d63638; }
    .ea-badge-entrada { background: #e7f4e9; color: #1e7e34; }
</style>