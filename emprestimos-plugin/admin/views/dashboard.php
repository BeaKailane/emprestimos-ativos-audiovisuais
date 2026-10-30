<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap magma-style">

    <div class="magma-header">
        <div class="magma-title-section">
            <span class="magma-icon-bg">
                <span class="dashicons dashicons-chart-area"></span>
            </span>

            <div>
                <h1><?php _e('Visão Geral do Inventário', 'emprestimos-ativos'); ?></h1>

                <p>
                    <?php _e('Indicadores de desempenho e controle em tempo real', 'emprestimos-ativos'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- CARDS -->
    <div class="magma-stats-grid" id="ea-stats-container">

        <div class="magma-stat-card">
            <div class="magma-stat-icon icon-total">
                <span class="dashicons dashicons-archive"></span>
            </div>

            <div class="magma-stat-data">
                <span class="magma-stat-number" id="stat-total">-</span>

                <span class="magma-stat-label">
                    <?php _e('Total de Ativos', 'emprestimos-ativos'); ?>
                </span>
            </div>
        </div>

        <div class="magma-stat-card">
            <div class="magma-stat-icon icon-available">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>

            <div class="magma-stat-data">
                <span class="magma-stat-number" id="stat-disponiveis">-</span>

                <span class="magma-stat-label">
                    <?php _e('Disponíveis', 'emprestimos-ativos'); ?>
                </span>
            </div>
        </div>

        <div class="magma-stat-card">
            <div class="magma-stat-icon icon-loaned">
                <span class="dashicons dashicons-clock"></span>
            </div>

            <div class="magma-stat-data">
                <span class="magma-stat-number" id="stat-emprestados">-</span>

                <span class="magma-stat-label">
                    <?php _e('Emprestados', 'emprestimos-ativos'); ?>
                </span>
            </div>
        </div>

        <div class="magma-stat-card">
            <div class="magma-stat-icon icon-delayed">
                <span class="dashicons dashicons-warning"></span>
            </div>

            <div class="magma-stat-data">
                <span class="magma-stat-number" id="stat-atrasados">-</span>

                <span class="magma-stat-label">
                    <?php _e('Atrasados', 'emprestimos-ativos'); ?>
                </span>
            </div>
        </div>

        <div class="magma-stat-card">
            <div class="magma-stat-icon icon-cancelled">
                <span class="dashicons dashicons-dismiss"></span>
            </div>

            <div class="magma-stat-data">
                <span class="magma-stat-number" id="stat-cancelados">-</span>

                <span class="magma-stat-label">
                    <?php _e('Cancelados', 'emprestimos-ativos'); ?>
                </span>
            </div>
        </div>

    </div>

    <!-- DASHBOARD -->
    <div class="magma-dashboard-row">

        <div class="magma-col-8">

            <div class="magma-card">

                <div class="magma-card-header">
                    <h3>
                        <?php _e('Volume de Empréstimos (30 dias)', 'emprestimos-ativos'); ?>
                    </h3>
                </div>

                <div class="magma-chart-box">
                    <canvas id="ea-chart-loans"></canvas>
                </div>

            </div>

        </div>

        <div class="magma-col-4">

            <div class="magma-card">

                <div class="magma-card-header">
                    <h3>
                        <?php _e('Ativos Mais Utilizados', 'emprestimos-ativos'); ?>
                    </h3>
                </div>

                <div id="ea-top-assets" class="magma-top-list"></div>

            </div>

        </div>

    </div>

    <!-- TABELA -->
    <div class="magma-card magma-mt-20">

        <div class="magma-card-header">
            <h3>
                <?php _e('Monitoramento de Empréstimos Ativos', 'emprestimos-ativos'); ?>
            </h3>
        </div>

        <div class="magma-table-container">

            <?php

            global $wpdb;

            $table_emp      = $wpdb->prefix . 'ea_emprestimos';
            $table_func     = $wpdb->prefix . 'ea_funcionarios';
            $table_ativos   = $wpdb->prefix . 'ea_ativos';
            $table_setores  = $wpdb->prefix . 'ea_setores';

            $emprestimos = $wpdb->get_results(
                "
                SELECT 
                    e.*, 
                    f.nome as funcionario_nome, 
                    a.nome as ativo_nome, 
                    a.tipo as ativo_tipo,
                    s.nome as setor_nome

                FROM $table_emp e

                JOIN $table_func f 
                    ON e.funcionario_id = f.id

                JOIN $table_ativos a 
                    ON e.ativo_id = a.id

                LEFT JOIN $table_setores s 
                    ON e.setor_id = s.id

                WHERE e.status IN ('ativo', 'atrasado')

                ORDER BY 
                    e.status DESC,
                    e.data_devolucao_prevista ASC
                "
            );

            ?>

            <?php if ($emprestimos): ?>

                <table class="magma-table">

                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>EQUIPAMENTO</th>
                            <th>COLABORADOR</th>
                            <th>DEVOLUÇÃO</th>
                            <th>STATUS</th>
                            <th style="text-align:right">AÇÃO</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($emprestimos as $emp): ?>

                            <tr>

                                <td>
                                    #<?php echo esc_html($emp->codigo); ?>
                                </td>

                                <td>
                                    <?php echo esc_html($emp->ativo_nome); ?>
                                </td>

                                <td>
                                    <?php echo esc_html($emp->funcionario_nome); ?>
                                </td>

                                <td>
                                    <?php echo date('d/m/Y', strtotime($emp->data_devolucao_prevista)); ?>
                                </td>

                                <td>
                                    <span class="magma-status status-<?php echo esc_attr($emp->status); ?>">
                                        <?php echo ucfirst($emp->status); ?>
                                    </span>
                                </td>

                                <td style="text-align:right">

                                    <button
                                        class="magma-btn-return-action ea-btn-return"
                                        data-id="<?php echo esc_attr($emp->id); ?>"
                                        data-ativo="<?php echo esc_attr($emp->ativo_nome); ?>"
                                    >
                                        Receber
                                    </button>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="magma-empty">
                    Nenhum empréstimo ativo encontrado.
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<style>

.magma-style{
    background:#f4f7fa;
    padding:20px;
    font-family:'Segoe UI',sans-serif;
}

.magma-header{
    display:flex;
    align-items:center;
    margin-bottom:30px;
}

.magma-title-section{
    display:flex;
    align-items:center;
    gap:15px;
}

.magma-icon-bg{
    background:#0046ad;
    color:#fff;
    padding:12px;
    border-radius:12px;
    display:flex;
}

.magma-stats-grid{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:15px;
    margin-bottom:30px;
}

.magma-stat-card{
    background:#fff;
    padding:15px;
    border-radius:12px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:0 4px 10px rgba(0,0,0,.03);
}

.magma-stat-icon{
    width:40px;
    height:40px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.icon-total{
    background:#eef2ff;
    color:#4f46e5;
}

.icon-available{
    background:#ecfdf5;
    color:#10b981;
}

.icon-loaned{
    background:#fffbeb;
    color:#f59e0b;
}

.icon-delayed{
    background:#fef2f2;
    color:#ef4444;
}

.icon-cancelled{
    background:#f1f5f9;
    color:#64748b;
}

.magma-stat-number{
    font-size:20px;
    font-weight:700;
    display:block;
}

.magma-stat-label{
    font-size:11px;
    color:#7a8ba1;
    text-transform:uppercase;
}

.magma-dashboard-row{
    display:flex;
    gap:20px;
}

.magma-col-8{
    flex:2;
}

.magma-col-4{
    flex:1;
}

.magma-card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,.05);
}

.magma-card-header{
    padding:15px;
    border-bottom:1px solid #f1f5f9;
}

.magma-chart-box{
    padding:20px;
    height:300px;
}

.magma-status{
    padding:4px 10px;
    border-radius:10px;
    font-size:11px;
    font-weight:bold;
}

.status-ativo{
    background:#dcfce7;
    color:#166534;
}

.status-atrasado{
    background:#fee2e2;
    color:#991b1b;
}

.magma-top-list{
    padding:10px;
}

.ea-top-assets-list{
    list-style:none;
    margin:0;
    padding:0;
}

.ea-top-assets-list li{
    display:flex;
    justify-content:space-between;
    padding:10px;
    border-bottom:1px solid #f8fafc;
}

.magma-btn-return-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #16a34a;
    color: #ffffff !important;
    border: none;
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background .18s ease, transform .1s ease;
    line-height: 1;
    text-shadow: none;
    box-shadow: none;
}

.magma-btn-return-action:hover {
    background: #15803d;
    color: #ffffff !important;
}

.magma-btn-return-action:active {
    transform: scale(0.96);
}

.magma-btn-return-action:disabled {
    background: #94a3b8;
    color: #ffffff !important;
    cursor: not-allowed;
}

</style>

<script>

jQuery(document).ready(function($){

    let loansChart = null;

    function loadStats(){

        $.ajax({

            url: ea_admin.ajax_url,

            type: 'POST',

            data: {
                action: 'ea_admin_get_stats',
                nonce: ea_admin.nonce
            },

            success: function(response){

                if(response.success){

                    let s = response.data;

                    $('#stat-total').text(s.total_ativos);
                    $('#stat-disponiveis').text(s.ativos_disponiveis);
                    $('#stat-emprestados').text(s.ativos_emprestados);
                    $('#stat-atrasados').text(s.emprestimos_atrasados);
                    $('#stat-cancelados').text(s.total_cancelados);

                    // CHART
                    if(s.por_dia && s.por_dia.length > 0){

                        if(loansChart){
                            loansChart.destroy();
                        }

                        loansChart = new Chart(
                            document.getElementById('ea-chart-loans'),
                            {
                                type:'line',

                                data:{
                                    labels:s.por_dia.map(d => d.dia),

                                    datasets:[{
                                        label:'Empréstimos',
                                        data:s.por_dia.map(d => d.total),
                                        borderColor:'#0052cc',
                                        backgroundColor:'rgba(0,82,204,0.05)',
                                        fill:true,
                                        tension:0.3
                                    }]
                                },

                                options:{
                                    responsive:true,
                                    maintainAspectRatio:false
                                }
                            }
                        );
                    }

                    // TOP ASSETS
                    if(s.mais_emprestados && s.mais_emprestados.length > 0){

                        let html = '<ul class="ea-top-assets-list">';

                        s.mais_emprestados.forEach(function(item){

                            html += `
                                <li>
                                    <span>${item.nome}</span>
                                    <strong>${item.total}</strong>
                                </li>
                            `;
                        });

                        html += '</ul>';

                        $('#ea-top-assets').html(html);
                    }

                }

            }

        });

    }

    // DEVOLUÇÃO
    $(document).on('click', '.ea-btn-return', function(){

        let btn       = $(this);
        let id        = btn.data('id');
        let nomeAtivo = btn.data('ativo');

        if(!confirm('Confirmar devolução de: ' + nomeAtivo + '?')){
            return;
        }

        btn.prop('disabled', true).text('Processando...');

        $.ajax({

            url: ea_admin.ajax_url,

            type: 'POST',

            data: {
                action:        'ea_admin_register_return',
                nonce:         ea_admin.nonce,
                emprestimo_id: id,
                condicao:      'bom',
                observacao:    ''
            },

            success: function(response){

                if(response.success){

                    btn.closest('tr').fadeOut(400, function(){
                        $(this).remove();
                    });

                    loadStats();

                } else {

                    alert(
                        'Erro: ' +
                        (
                            response.data?.message ||
                            'Não foi possível processar.'
                        )
                    );

                    btn.prop('disabled', false).text('Receber');
                }

            },

            error: function(){

                alert('Erro de conexão. Tente novamente.');

                btn.prop('disabled', false).text('Receber');
            }

        });

    });

    loadStats();

});

</script>