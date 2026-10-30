<?php 
if (!defined('ABSPATH')) exit;

wp_enqueue_style('dashicons');

global $wpdb;
$table_emp    = $wpdb->prefix . 'ea_emprestimos';
$table_func   = $wpdb->prefix . 'ea_funcionarios';
$table_ativos = $wpdb->prefix . 'ea_ativos';
$table_setores = $wpdb->prefix . 'ea_setores';

$agora = new DateTime(current_time('mysql'));

$emprestimos_all = $wpdb->get_results(
    "SELECT * FROM $table_emp WHERE status IN ('ativo','atrasado')"
);

$total_em_uso = 0;
$total_reservas = 0;
$total_atrasados = 0;

foreach ($emprestimos_all as $emp) {

    $inicio = new DateTime($emp->data_emprestimo . ' ' . $emp->hora_retirada);
    $fim = new DateTime($emp->data_devolucao_prevista . ' ' . $emp->hora_devolucao_prevista);

    if ($agora < $inicio) {
        $total_reservas++;
    } elseif ($agora > $fim) {
        $total_atrasados++;
    } else {
        $total_em_uso++;
    }
}
$emprestimos = $wpdb->get_results(
    "SELECT e.*, f.nome as funcionario_nome, a.nome as ativo_nome, s.nome as setor_nome
     FROM $table_emp e
     JOIN $table_func f ON e.funcionario_id = f.id
     JOIN $table_ativos a ON e.ativo_id = a.id
     LEFT JOIN $table_setores s ON f.setor_id = s.id
     WHERE e.status IN ('ativo', 'atrasado')
     ORDER BY e.status DESC, e.data_devolucao_prevista ASC"
);
?>

<div class="magma-style ea-panel-wrapper">
    <div class="magma-header">
        <div class="magma-title-section">
            <div class="magma-icon-bg"><span class="dashicons dashicons-chart-area"></span></div>
            <div>
                <h1><?php _e('Painel de Monitoramento', 'emprestimos-ativos'); ?></h1>
                <p><?php _e('Status em tempo real das alocações', 'emprestimos-ativos'); ?></p>
            </div>
        </div>
        <button type="button" onclick="location.reload();" class="magma-btn-refresh">
            <span class="dashicons dashicons-update"></span>
            <span class="btn-text"><?php _e('Atualizar', 'emprestimos-ativos'); ?></span>
        </button>
    </div>

    <div class="magma-stats-grid">
        <div class="magma-stat-card card-blue">
            <div class="magma-stat-icon"><span class="dashicons dashicons-desktop"></span></div>
            <div class="magma-stat-data">
                <span class="magma-stat-number"><?php echo $total_em_uso; ?></span>
                <span class="magma-stat-label"><?php _e('Em uso', 'emprestimos-ativos'); ?></span>
            </div>
        </div>
        <div class="magma-stat-card card-orange">
            <div class="magma-stat-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
            <div class="magma-stat-data">
                <span class="magma-stat-number"><?php echo $total_reservas; ?></span>                <span class="magma-stat-label"><?php _e('Reservas', 'emprestimos-ativos'); ?></span>
            </div>
        </div>
        <div class="magma-stat-card card-red">
            <div class="magma-stat-icon"><span class="dashicons dashicons-warning"></span></div>
            <div class="magma-stat-data">
                <span class="magma-stat-number"><?php echo $total_atrasados; ?></span>
                <span class="magma-stat-label"><?php _e('Atrasados', 'emprestimos-ativos'); ?></span>
            </div>
        </div>
    </div>

    <div class="magma-card magma-filter-bar">
        <div class="magma-search-box">
            <span class="dashicons dashicons-search"></span>
            <input type="text" id="filter-busca-panel" placeholder="Pesquisar...">
        </div>
    </div>

    <div class="magma-card magma-table-card">
        <div class="magma-table-container">
            <table class="magma-table" id="ea-panel-table">
                <thead>
                    <tr>
                        <th><?php _e('Equipamento', 'emprestimos-ativos'); ?></th>
                        <th><?php _e('Colaborador', 'emprestimos-ativos'); ?></th>
                        <th><?php _e('Setor', 'emprestimos-ativos'); ?></th>
                        <th><?php _e('Prazo', 'emprestimos-ativos'); ?></th>
                        <th style="text-align: center;"><?php _e('Status', 'emprestimos-ativos'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($emprestimos): foreach ($emprestimos as $emp): 
                        $agora = new DateTime(current_time('mysql'));
                        $inicio = new DateTime($emp->data_emprestimo . ' ' . $emp->hora_retirada);
                        $fim = new DateTime($emp->data_devolucao_prevista . ' ' . $emp->hora_devolucao_prevista);
                        $status_visual = 'ativo';
                        $status_label = 'Em uso';

                        if ($agora < $inicio) {
                        $status_visual = 'reservado';
                        $status_label = 'Reservado';
                        } elseif ($agora > $fim) {
                        $status_visual = 'atrasado';
                        $status_label = 'Atrasado';
                        }
                    ?>
                    <tr class="<?php echo ($status_visual === 'atrasado') ? 'row-delayed' : ''; ?>">                        <td data-label="Equipamento">
                            <div class="magma-asset-info">
                                <div class="magma-asset-icon"><span class="dashicons dashicons-admin-generic"></span></div>
                                <span class="magma-name"><?php echo esc_html($emp->ativo_nome); ?></span>
                            </div>
                        </td>
                        <td data-label="Colaborador">
                            <div class="magma-user-info">
                                <span class="dashicons dashicons-admin-users"></span>
                                <?php echo esc_html($emp->funcionario_nome); ?>
                            </div>
                        </td>
                        <td data-label="Setor">
                            <div class="magma-sector-info">
                                <span class="dashicons dashicons-building"></span>
                                <?php echo esc_html($emp->setor_nome ?: '-'); ?>
                            </div>
                        </td>
                      <td data-label="Prazo">
             <div class="magma-date-group <?php echo ($status_visual === 'atrasado') ? 'date-danger' : ''; ?>">
                <span class="dashicons dashicons-clock"></span>

               <?php if ($status_visual === 'reservado'): ?>

               <div>
                <strong>Retirada:</strong><br>
                <?php echo date('d/m H:i', strtotime($emp->data_emprestimo . ' ' . $emp->hora_retirada)); ?>
              </div>

            <?php else: ?>

            <div>
                <strong>Devolução:</strong><br>
                <?php echo date('d/m H:i', strtotime($emp->data_devolucao_prevista . ' ' . $emp->hora_devolucao_prevista)); ?>
              </div>

                  <?php endif; ?>

                    </div>
                        </td>
                        <td data-label="Status" style="text-align: center;">
                            <span class="magma-status-pill status-<?php echo $status_visual; ?>">
                                <?php echo $status_label; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="magma-no-data">Nenhum equipamento em uso.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.status-reservado {
    background: #fef3c7;
    color: #92400e;
}

.ea-panel-wrapper { background: #f4f7fa; padding: 20px; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; max-width: 100%; box-sizing: border-box; }

.magma-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; }
.magma-title-section { display: flex; align-items: center; gap: 12px; }
.magma-icon-bg { background: #0046ad; color: white; padding: 10px; border-radius: 12px; display: flex; }
.magma-header h1 { margin: 0 !important; font-size: 20px !important; color: #1a2b4a; font-weight: 800; }
.magma-header p { margin: 2px 0 0; color: #64748b; font-size: 13px; }

.magma-btn-refresh:hover {
    background: #967d45; /* Substitua por uma cor um pouco mais escura ou clara */
    transform: translateY(-2px); /* Efeito opcional de subir levemente */
    box-shadow: 0 6px 12px rgba(177, 148, 83, 0.3); /* Aumenta a sombra no hover */
}

.magma-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
.magma-stat-card { background: white; padding: 20px; border-radius: 16px; display: flex; align-items: center; gap: 15px; border: 1px solid #eef2f6; }
.magma-stat-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.magma-stat-icon .dashicons { font-size: 24px; width: 24px; height: 24px; }
.card-blue .magma-stat-icon { background: #e0f2fe; color: #0369a1; }
.card-orange .magma-stat-icon { background: #ffedd5; color: #c2410c; }
.card-red .magma-stat-icon { background: #fee2e2; color: #b91c1c; }
.magma-stat-number { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1; }
.magma-stat-label { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; }

.magma-filter-bar { padding: 12px 20px; margin-bottom: 20px; border-radius: 12px; }
.magma-search-box { position: relative; display: flex; align-items: center; width: 100%; }
.magma-search-box .dashicons { position: absolute; left: 10px; color: #94a3b8; }
.magma-search-box input { width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; background: #f8fafc; }

.magma-table-card { border-radius: 16px; overflow: hidden; border: 1px solid #eef2f6; }
.magma-table { width: 100%; border-collapse: collapse; background: white; }
.magma-table th { background: #fcfdfe; text-align: left; padding: 15px 20px; font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; }
.magma-table td { padding: 15px 20px; border-bottom: 1px solid #f8fafc; font-size: 14px; }
.magma-asset-info { display: flex; align-items: center; gap: 10px; }
.magma-asset-icon { background: #f1f5f9; color: #64748b; padding: 6px; border-radius: 8px; display: flex; }
.magma-name { font-weight: 700; color: #1e293b; }
.magma-status-pill { padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.status-ativo { background: #dcfce7; color: #166534; }
.status-atrasado { background: #fee2e2; color: #b91c1c; }

@media (max-width: 768px) {
    .magma-header { flex-direction: row; align-items: center; }
    .btn-text { display: none; } 
    .magma-btn-refresh { padding: 8px; }
    
    .magma-table, .magma-table thead, .magma-table tbody, .magma-table th, .magma-table td, .magma-table tr { 
        display: block; 
    }
    .magma-table thead { display: none; } 
    .magma-table tr { margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding: 10px 0; }
    .magma-table td { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 8px 20px; 
        border: none;
        text-align: right;
    }
    .magma-table td::before { 
        content: attr(data-label); 
        font-weight: 700; 
        color: #94a3b8; 
        font-size: 11px; 
        text-transform: uppercase;
        text-align: left;
    }
    .magma-asset-info, .magma-user-info, .magma-sector-info, .magma-date-group { justify-content: flex-end; }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#filter-busca-panel').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#ea-panel-table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>