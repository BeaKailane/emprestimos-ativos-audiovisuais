<?php if (!defined('ABSPATH')) exit;
global $wpdb;
$table_func = $wpdb->prefix . 'ea_funcionarios';
$table_setores = $wpdb->prefix . 'ea_setores';
$table_emp = $wpdb->prefix . 'ea_emprestimos';

// --- LÓGICA DE EXCLUSÃO ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Verifica segurança para evitar ataques de links externos
    check_admin_referer('delete_funcionario_' . $_GET['id']);
    
    $id = intval($_GET['id']);
    
    // Deleta o funcionário
    $wpdb->delete($table_func, array('id' => $id), array('%d'));
    
    echo '<div class="updated"><p>' . __('Funcionário removido com sucesso!', 'emprestimos-ativos') . '</p></div>';
}

$funcionarios = $wpdb->get_results(
    "SELECT f.*, s.nome as setor_nome,
     (SELECT COUNT(*) FROM $table_emp WHERE funcionario_id = f.id) as total_emprestimos,
     (SELECT COUNT(*) FROM $table_emp WHERE funcionario_id = f.id AND status = 'atrasado') as atrasados
     FROM $table_func f
     LEFT JOIN $table_setores s ON f.setor_id = s.id
     ORDER BY f.nome ASC"
);
?>

<div class="wrap ea-admin">
    <h1 class="ea-page-title">
        <span class="dashicons dashicons-groups"></span>
        <?php _e('Funcionários', 'emprestimos-ativos'); ?>
    </h1>
    
    <div class="ea-card">
        <table class="ea-table">
            <thead>
                <tr>
                    <th><?php _e('Nome', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('E-mail', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('WhatsApp', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Setor', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Total Empréstimos', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Atrasados', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Status', 'emprestimos-ativos'); ?></th>
                    <th><?php _e('Ações', 'emprestimos-ativos'); ?></th> </tr>
            </thead>
            <tbody>
                <?php if ($funcionarios): foreach ($funcionarios as $func): ?>
                <tr>
                    <td><strong><?php echo esc_html($func->nome); ?></strong></td>
                    <td><?php echo esc_html($func->email); ?></td>
                    <td><?php echo esc_html($func->whatsapp); ?></td>
                    <td><?php echo esc_html($func->setor_nome ?: '-'); ?></td>
                    <td><?php echo $func->total_emprestimos; ?></td>
                    <td><?php if ($func->atrasados > 0): ?><span class="ea-badge ea-badge-atrasado"><?php echo $func->atrasados; ?></span><?php else: ?>0<?php endif; ?></td>
                    <td><span class="ea-badge ea-badge-<?php echo $func->status; ?>"><?php echo ucfirst($func->status); ?></span></td>
                    <td>
                        <?php 
                        // Cria o link de exclusão seguro
                        $delete_url = wp_nonce_url(
                            admin_url('admin.php?page=' . $_GET['page'] . '&action=delete&id=' . $func->id),
                            'delete_funcionario_' . $func->id
                        );
                        ?>
                        <a href="<?php echo $delete_url; ?>" 
                           class="ea-btn-delete" 
                           onclick="return confirm('<?php _e('Tem certeza que deseja excluir este funcionário?', 'emprestimos-ativos'); ?>');"
                           style="color: #d63638; text-decoration: none;">
                            <span class="dashicons dashicons-trash"></span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="8" class="ea-no-data"><?php _e('Nenhum funcionário cadastrado.', 'emprestimos-ativos'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>