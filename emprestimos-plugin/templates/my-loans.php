<?php if (!defined('ABSPATH')) exit; ?>
<div class="ea-my-loans">
    <h2 class="ea-section-title"><?php _e('Meus Empréstimos', 'emprestimos-ativos'); ?></h2>
    
    <div class="ea-my-loans-form">
        <div class="ea-form-group">
            <label for="ea-my-email"><?php _e('Digite seu e-mail para consultar:', 'emprestimos-ativos'); ?></label>
            <div class="ea-input-group">
                <input type="email" id="ea-my-email" class="ea-input" placeholder="seu.email@empresa.com">
                <button type="button" id="ea-btn-search-loans" class="ea-btn ea-btn-primary"><?php _e('Consultar', 'emprestimos-ativos'); ?></button>
            </div>
        </div>
    </div>
    
    <div id="ea-my-loans-list" class="ea-my-loans-list" style="display:none;">
        <div class="ea-loans-tabs">
            <button class="ea-tab-btn active" data-tab="ativos"><?php _e('Ativos', 'emprestimos-ativos'); ?></button>
            <button class="ea-tab-btn" data-tab="historico"><?php _e('Histórico', 'emprestimos-ativos'); ?></button>
        </div>
        
        <div id="ea-tab-ativos" class="ea-tab-content active">
            <div id="ea-loans-active"></div>
        </div>
        
        <div id="ea-tab-historico" class="ea-tab-content">
            <div id="ea-loans-history"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var currentEmail = '';

    $('#ea-btn-search-loans').on('click', function() {
        var email = $('#ea-my-email').val();
        if (!email) {
            alert('<?php _e('Digite seu e-mail.', 'emprestimos-ativos'); ?>');
            return;
        }
        currentEmail = email;
        
        $.ajax({
            url: ea_vars.ajax_url,
            type: 'POST',
            data: { action: 'ea_get_my_loans', nonce: ea_vars.nonce, email: email },
            success: function(response) {
                if (response.success) {
                    var loans = response.data.emprestimos;
                    var activeHtml = '';
                    var historyHtml = '';
                    
                    if (loans.length === 0) {
                        activeHtml = '<p class="ea-no-data"><?php _e('Nenhum empréstimo encontrado.', 'emprestimos-ativos'); ?></p>';
                        historyHtml = activeHtml;
                    } else {
                        loans.forEach(function(loan) {
                            var card = '<div class="ea-loan-card ea-loan-' + loan.status + '">';
                            card += '<div class="ea-loan-header">';
                            card += '<strong>' + loan.ativo_nome + '</strong>';
                            card += '<span class="ea-loan-code">' + loan.codigo + '</span>';
                            card += '</div>';
                            card += '<div class="ea-loan-body">';
                            card += '<p><strong><?php _e('Retirada:', 'emprestimos-ativos'); ?></strong> ' + formatDate(loan.data_emprestimo) + ' às ' + loan.hora_retirada.substring(0,5) + '</p>';
                            card += '<p><strong><?php _e('Devolução:', 'emprestimos-ativos'); ?></strong> ' + formatDate(loan.data_devolucao_prevista) + ' às ' + loan.hora_devolucao_prevista.substring(0,5) + '</p>';
                            card += '<span class="ea-loan-status ea-status-' + loan.status + '">' + loan.status.toUpperCase() + '</span>';
                            card += '</div>';
                            
                            if (loan.status === 'em uso' || loan.status === 'pendente') {

                             // Monta data/hora da retirada
                            var retiradaDateTime = new Date(
                            loan.data_emprestimo + 'T' + loan.hora_retirada
                            );

                            var now = new Date();

                            // diferença em minutos
                            var diffMs = retiradaDateTime - now;
                            var diffMinutes = diffMs / (1000 * 60);

                            if (diffMinutes >= 60) {
                            card += '<div class="ea-loan-actions">';
                            card += '<button class="ea-btn ea-btn-cancel" data-id="' + loan.id + '"><?php _e('Cancelar', 'emprestimos-ativos'); ?></button>';
                            card += '</div>';
                         }
                      }
                            
                            card += '</div>';
                            
                            if (loan.status === 'ativo' || loan.status === 'pendente' || loan.status === 'atrasado') {
                                activeHtml += card;
                            } else {
                                historyHtml += card;
                            }
                        });
                        
                        if (!activeHtml) activeHtml = '<p class="ea-no-data"><?php _e('Nenhum empréstimo ativo.', 'emprestimos-ativos'); ?></p>';
                        if (!historyHtml) historyHtml = '<p class="ea-no-data"><?php _e('Nenhum histórico.', 'emprestimos-ativos'); ?></p>';
                    }
                    
                    $('#ea-loans-active').html(activeHtml);
                    $('#ea-loans-history').html(historyHtml);
                    $('#ea-my-loans-list').show();
                }
            }
        });
    });
    
    function formatDate(dateStr) {
        var parts = dateStr.split('-');
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }
    
    // Tabs
    $('.ea-tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        $('.ea-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.ea-tab-content').removeClass('active');
        $('#ea-tab-' + tab).addClass('active');
    });
    
    // Cancelar
    $(document).on('click', '.ea-btn-cancel', function() {
        if (!confirm('<?php _e('Tem certeza que deseja cancelar este empréstimo?', 'emprestimos-ativos'); ?>')) return;
        
        var id = $(this).data('id');
        var btn = $(this);
        
        $.ajax({
            url: ea_vars.ajax_url,
            type: 'POST',
            data: { action: 'ea_cancel_loan', nonce: ea_vars.nonce, emprestimo_id: id, email: currentEmail },
            success: function(response) {
                alert(response.data.message);
                if (response.success) {
                    $('#ea-btn-search-loans').click();
                }
            }
        });
    });
});
</script>