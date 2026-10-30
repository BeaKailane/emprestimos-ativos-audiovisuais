<?php if (!defined('ABSPATH')) exit; 
wp_enqueue_style('dashicons');
?>

<div class="magma-style ea-userpanel-outer">
    <div class="ea-userpanel-container">
        
        <div class="magma-page-header">
            <h1>Consultar Empréstimos</h1>
            <p>Insira seu email abaixo para acessar seu histórico de empréstimos</p>
        </div>

        <div class="magma-search-container-box">
            <div class="magma-input-group">
            <input type="email" id="ea-my-email" placeholder="ex: nome@empresa.com" class="magma-input" required>                <button type="button" id="ea-btn-search-loans" class="magma-btn-dark">
                    Buscar
                </button>
            </div>
        </div>

        <div id="ea-search-placeholder" class="magma-card magma-empty-state">
            <div class="magma-empty-icon">
                <span class="dashicons dashicons-archive"></span>
            </div>
            <h2>Digite seu e-mail corporativo</h2>
            <p>Insira o email cadastrado acima para consultar seus empréstimos</p>
        </div>

        <div id="ea-profile-step" style="display:none;" class="animate-in">
            
            <div class="magma-user-info-banner">
                <div class="ea-up-name" id="ea-display-email-profile">Carregando...</div>
                <div class="ea-up-since">Acesso em: <?php echo date('d/m/Y'); ?></div>
            </div>

            <div class="magma-tabs-navigation">
                <button class="magma-tab-link active" data-tab="ativos">
                    <span class="dashicons dashicons-clock"></span> Ativos <span id="count-active" class="tab-badge">0</span>
                </button>
                <button class="magma-tab-link" data-tab="historico">
                    <span class="dashicons dashicons-history"></span> Histórico <span id="count-history" class="tab-badge">0</span>
                </button>
            </div>

            <div id="ea-tab-ativos" class="ea-tab-panel active">
                <div id="ea-loans-active" class="magma-results-grid"></div>
            </div>
            
            <div id="ea-tab-historico" class="ea-tab-panel">
                <div id="ea-loans-history" class="magma-results-grid"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* --- DESIGN SYSTEM REFINADO --- */
.magma-style.ea-userpanel-outer { 
    background: #f8fafc; 
    min-height: 100vh; 
    padding: 40px 20px; 
    font-family: 'Segoe UI', system-ui, sans-serif; 
}
.ea-userpanel-container { max-width: 1000px; margin: 0 auto; }

.magma-page-header h1 { font-size: 28px !important; color: #1a2b4a !important; font-weight: 800 !important; margin: 0 !important; }
.magma-page-header p { color: #64748b; margin: 5px 0 25px; }

.magma-search-container-box { margin-bottom: 30px; }
.magma-input-group { display: flex; gap: 12px; }
.magma-input { 
    flex: 1; 
    border: 1px solid #e2e8f0; 
    border-radius: 8px; 
    padding: 12px 18px; 
    font-size: 15px; 
    outline: none; 
    background: white;
}
.magma-btn-dark { 
    background: #111827; 
    color: white !important; 
    border: none; 
    padding: 0 30px; 
    border-radius: 8px; 
    font-weight: 700; 
    cursor: pointer;
}

.magma-empty-state {
    padding: 80px 40px;
    text-align: center;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 12px;
}
.magma-empty-icon { color: #cbd5e1; margin-bottom: 15px; }
.magma-empty-icon span { font-size: 64px; width: 64px; height: 64px; }
.magma-empty-state h2 { font-size: 20px; color: #1e293b; margin: 0; }
.magma-empty-state p { color: #64748b; font-size: 15px; }

.magma-user-info-banner {
    background: #f1f5f9;
    padding: 20px 25px;
    border-radius: 10px;
    margin-bottom: 25px;
    border-left: 4px solid #0052cc;
}
.ea-up-name { font-size: 18px; color: #1e293b; font-weight: 700; }
.ea-up-since { font-size: 13px; color: #64748b; margin-top: 4px; }

/* Botão Cancelar mais estilizado */
.ea-btn-cancel {
    background: #dc2626; /* vermelho vivo */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.ea-btn-cancel:hover {
    background: #b91c1c; /* vermelho mais escuro ao passar o mouse */
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.ea-btn-cancel:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.magma-tabs-navigation { display: flex; gap: 10px; margin-bottom: 25px; }
.magma-tab-link {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    color: #475569;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}
.magma-tab-link.active {
    background: #0052cc !important;
    color: white !important;
    border-color: #0052cc !important;
}
.tab-badge {
    font-size: 11px;
    background: rgba(0, 0, 0, 0.05);
    padding: 2px 8px;
    border-radius: 12px;
}
.magma-tab-link.active .tab-badge { background: rgba(255, 255, 255, 0.2); }

.magma-results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }

.ea-loan-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    padding: 25px;
}

.status-tag {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    display: inline-block;
}

.st-ativo { background: #e0f9f1; color: #059669; }
.st-atrasado { background: #fef2f2; color: #dc2626; animation: magmaPulse 2s infinite; }
.st-concluido { background: #f1f5f9; color: #475569; }

.asset-title { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }

.date-footer {
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: #f8fafc;
    padding: 12px;
    border-radius: 10px;
    gap: 15px;
}
.date-footer label { display: block; font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
.date-footer span { font-size: 13px; color: #334155; font-weight: 600; display: flex; align-items: center; gap: 5px; }

.ea-tab-panel { display: none; }
.ea-tab-panel.active { display: block; }

@keyframes magmaPulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
.animate-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
jQuery(document).ready(function ($) {
    function formatDate(dateStr) {
        if (!dateStr || dateStr.includes('0000')) return '-';
        const parts = dateStr.split(' ')[0].split('-');
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function renderCard(loan) {
    const st = String(loan.status).toLowerCase().trim();
    let statusLabel = loan.status === 'ativo' ? 'Em Uso' : loan.status;

    let cancelButton = '';

    // Permitir cancelar apenas se estiver pendente
   if (['pendente', 'ativo', 'em uso', 'reservado'].includes(st)) {

        if (loan.data_emprestimo && loan.hora_retirada) {

            const retiradaDateTime = new Date(
                loan.data_emprestimo + ' ' + loan.hora_retirada
            );

            const now = new Date();
            const diffMinutes = (retiradaDateTime - now) / (1000 * 60);

            if (diffMinutes >= 60) {
                cancelButton = `
                    <div style="margin-top:15px;">
                        <button class="magma-btn-dark ea-btn-cancel" data-id="${loan.id}">
                            Cancelar
                        </button>
                    </div>`;
            }
        }
    }

    return `
        <div class="ea-loan-card">
            <span class="status-tag st-${st}">${statusLabel}</span>
            <div class="asset-title">${loan.ativo_nome}</div>
            <div class="date-footer">
                <div>
                    <label>Retirada</label>
                    <span><span class="dashicons dashicons-calendar-alt"></span> ${formatDate(loan.data_emprestimo)}</span>
                </div>
                <div>
                    <label>Devolução Prevista</label>
                    <span><span class="dashicons dashicons-clock"></span> ${loan.hora_devolucao_prevista ? loan.hora_devolucao_prevista.substring(0,5) : '--:--'}</span>
                </div>
            </div>
            ${cancelButton}
        </div>`;
}


$(document).on('click', '.ea-btn-cancel', function() {

    if (!confirm('Tem certeza que deseja cancelar este empréstimo?')) return;

    const id = $(this).data('id');

    $.ajax({
        url: ea_vars.ajax_url,
        type: 'POST',
        data: { 
            action: 'ea_cancel_loan', 
            nonce: ea_vars.nonce, 
            emprestimo_id: id 
        },
        success: function(response) {
            alert(response.data.message);
            if (response.success) {
                $('#ea-btn-search-loans').click();
            }
        }
    });
});
    $('#ea-btn-search-loans').on('click', function() {
        const email = $('#ea-my-email').val().trim();
        if(!email) {
            alert('Por favor, digite um e-mail.');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('Buscando...');

        $.ajax({
            url: ea_vars.ajax_url,
            type: 'POST',
            data: { action: 'ea_get_my_loans', nonce: ea_vars.nonce, email: email },
            success: function(r) {
                if (r.success) {
                    $('#ea-search-placeholder').hide();
                    $('#ea-profile-step').fadeIn();
                    
                    // AQUI A MÁGICA: Injeta o e-mail no banner
                    $('#ea-display-email-profile').text(email);

                    const loans = r.data.emprestimos || [];
                    let activeHtml = '', histHtml = '', cA = 0, cH = 0;

                    loans.forEach(loan => {
                        const st = String(loan.status).toLowerCase();
                        if (['ativo', 'pendente', 'atrasado'].includes(st)) {
                            activeHtml += renderCard(loan); cA++;
                        } else {
                            histHtml += renderCard(loan); cH++;
                        }
                    });

                    $('#ea-loans-active').html(activeHtml || '<p>Nenhum item ativo.</p>');
                    $('#ea-loans-history').html(histHtml || '<p>Nenhum histórico.</p>');
                    $('#count-active').text(cA);
                    $('#count-history').text(cH);
                } else {
                    alert('Nenhum empréstimo encontrado para este e-mail.');
                }
            },
            complete: function() { $btn.prop('disabled', false).text('Buscar'); }
        });
    });

    $('.magma-tab-link').on('click', function() {
        $('.magma-tab-link').removeClass('active');
        $(this).addClass('active');
        $('.ea-tab-panel').removeClass('active');
        $('#ea-tab-' + $(this).data('tab')).addClass('active');
    });
});
</script>