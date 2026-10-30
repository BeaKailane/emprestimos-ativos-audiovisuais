<?php
if (!defined('ABSPATH')) exit;
 
$tipos_str = EA_Database::get_config('tipos_ativos');
$tipos = array_filter(array_map('trim', explode(',', $tipos_str)));
?>
 
<div class="ea-catalog-container">
    <div class="ea-catalog-header">
        <h2 class="ea-catalog-title"><?php _e('Catálogo de Equipamentos', 'emprestimos-ativos'); ?></h2>
        <div class="ea-catalog-filters">
            <div class="ea-filter-field">
                <label for="ea-filter-tipo"><?php _e('Tipo', 'emprestimos-ativos'); ?></label>
                <select id="ea-filter-tipo" class="ea-select">
                    <option value=""><?php _e('Todos', 'emprestimos-ativos'); ?></option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?php echo esc_attr($tipo); ?>"><?php echo esc_html($tipo); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ea-filter-field">
                <label for="ea-filter-status"><?php _e('Status', 'emprestimos-ativos'); ?></label>
               <select id="ea-filter-status" class="ea-select">
                <option value=""><?php _e('Todos', 'emprestimos-ativos'); ?></option>
                <option value="disponivel"><?php _e('Disponível', 'emprestimos-ativos'); ?></option>
                <option value="emprestado"><?php _e('Emprestado', 'emprestimos-ativos'); ?></option>
                <option value="atrasado"><?php _e('Atrasado', 'emprestimos-ativos'); ?></option> 
                </select>
            </div>
            <div class="ea-filter-field">
                <label for="ea-filter-data"><?php _e('Data', 'emprestimos-ativos'); ?></label>
                <input type="date" id="ea-filter-data" class="ea-input">
            </div>
            <div class="ea-filter-field">
                <label for="ea-filter-hora"><?php _e('Hora', 'emprestimos-ativos'); ?></label>
                <input type="time" id="ea-filter-hora" class="ea-input">
            </div>
            <div class="ea-filter-field">
                <label for="ea-filter-busca"><?php _e('Equipamento', 'emprestimos-ativos'); ?></label>
                <input type="text" id="ea-filter-busca" class="ea-input" placeholder="<?php _e('Buscar...', 'emprestimos-ativos'); ?>">
            </div>
        </div>
    </div>
 
    <div id="ea-catalog-grid" class="ea-catalog-grid"></div>
</div>
 
<!-- CARRINHO FLUTUANTE -->
<div id="ea-cart-bar" class="ea-cart-bar" style="display:none;">
    <div class="ea-cart-bar-inner">
        <div class="ea-cart-bar-left">
            <span class="ea-cart-icon">🛒</span>
            <span id="ea-cart-count" class="ea-cart-count">0</span>
            <span class="ea-cart-label"><?php _e('equipamento(s) selecionado(s)', 'emprestimos-ativos'); ?></span>
            <div id="ea-cart-chips" class="ea-cart-chips"></div>
        </div>
        <div class="ea-cart-bar-right">
            <button type="button" id="ea-btn-clear-cart" class="ea-btn ea-btn-secondary"><?php _e('Limpar', 'emprestimos-ativos'); ?></button>
            <button type="button" id="ea-btn-open-loan" class="ea-btn ea-btn-primary"><?php _e('Solicitar Empréstimo', 'emprestimos-ativos'); ?></button>
        </div>
    </div>
</div>
 
<!-- MODAL DETALHE -->
<div id="ea-modal-detail" class="ea-modal" style="display:none;">
    <div class="ea-modal-content ea-modal-large">
        <span class="ea-modal-close">&times;</span>
        
        <div class="ea-modal-header-new">
            <h2 id="ea-detail-nome"></h2>
            <div class="ea-detail-tags">
                <span id="ea-detail-status" class="ea-tag-status"></span>
                <span id="ea-detail-tipo" class="ea-tag-type"></span>
            </div>
        </div>
 
        <div class="ea-modal-body-grid">
            <div class="ea-modal-column-left">
                <div class="ea-asset-detail-image-box">
                    <img id="ea-detail-image" src="" alt="Equipamento">
                </div>
                
                <div class="ea-detail-specs-grid">
                    <div class="ea-spec-item">
                        <span class="ea-spec-label">Patrimônio</span>
                        <span id="ea-detail-patrimonio" class="ea-spec-value"></span>
                    </div>
                    <div class="ea-spec-item">
                        <span class="ea-spec-label">Condição</span>
                        <span id="ea-detail-condicao" class="ea-spec-value"></span>
                    </div>
                </div>
 
                <div class="ea-description-container">
                    <h3>Descrição do Equipamento</h3>
                    <p id="ea-detail-descricao" class="ea-detail-description-text"></p>
                </div>
            </div>
 
            <div class="ea-modal-column-right">
                <div class="ea-calendar-card">
                    <div class="ea-calendar-header">
                        <h3>Disponibilidade</h3>
                        <div class="ea-calendar-nav">
                            <button type="button" id="ea-prev-month">&lt;</button>
                            <span id="ea-current-month-display"></span>
                            <button type="button" id="ea-next-month">&gt;</button>
                        </div>
                    </div>
                    <div class="ea-calendar-wrapper">
                        <div class="ea-calendar-weekdays">
                            <span>Dom</span><span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span>
                        </div>
                        <div id="ea-calendar-days" class="ea-calendar-days-grid"></div>
                        <div class="ea-calendar-legend">
                            <span class="ea-legend-dot"></span> Reservado/Emprestado
                        </div>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="ea-modal-footer-actions">
            <button type="button" class="ea-btn ea-btn-secondary ea-modal-cancel">Fechar</button>
            <button id="ea-btn-add-to-cart" class="ea-btn ea-btn-outline-primary"><?php _e('+ Adicionar ao Carrinho', 'emprestimos-ativos'); ?></button>
            <button id="ea-btn-solicitar-detail" class="ea-btn ea-btn-primary"><?php _e('Realizar Empréstimo', 'emprestimos-ativos'); ?></button>
        </div>
    </div>
</div>
 
<!-- MODAL EMPRÉSTIMO (agora suporta múltiplos equipamentos) -->
<div id="ea-modal-loan" class="ea-modal" style="display:none;">
    <div class="ea-modal-content ea-modal-large">
        <span class="ea-modal-close">&times;</span>
        <h2><?php _e('Solicitar Empréstimo', 'emprestimos-ativos'); ?></h2>
 
       <!-- Lista de equipamentos selecionados -->
        <div class="ea-loan-assets-section">
             <div class="ea-loan-assets-header">
                <strong><?php _e('Equipamentos:', 'emprestimos-ativos'); ?></strong>
                <button type="button" id="ea-btn-add-more" class="ea-btn-add-more">
             ＋ <?php _e('Adicionar mais equipamentos', 'emprestimos-ativos'); ?>
                </button>
        </div>
             <div id="ea-loan-ativos-list" class="ea-loan-ativos-list"></div>
        </div>

        <form id="ea-form-loan">
            <!-- IDs dos ativos serão injetados como campos hidden via JS -->
            <div id="ea-loan-ativos-inputs"></div>
 
            <div class="ea-form-section">
                <h3><?php _e('Seus Dados', 'emprestimos-ativos'); ?></h3>
                <div class="ea-form-row ea-align-end"> 
                    <div class="ea-form-col-70">
                        <div class="ea-form-group">
                            <label for="ea-loan-email"><?php _e('E-mail Corporativo *', 'emprestimos-ativos'); ?></label>
                            <input type="email" name="email" id="ea-loan-email" class="ea-input" required>
                            <small><?php _e('Digite seu e-mail para verificar cadastro', 'emprestimos-ativos'); ?></small>
                        </div>
                    </div>
                    <div class="ea-form-col-30">
                        <div class="ea-form-group">
                            <label class="ea-hide-mobile">&nbsp;</label>
                            <button type="button" id="ea-btn-check-email" class="ea-btn ea-btn-secondary ea-btn-full"><?php _e('Verificar', 'emprestimos-ativos'); ?></button>
                        </div>
                    </div>
                </div>
                
                <div id="ea-employee-fields">
                    <div class="ea-form-row">
                        <div class="ea-form-col">
                            <div class="ea-form-group">
                                <label for="ea-loan-nome"><?php _e('Nome Completo *', 'emprestimos-ativos'); ?></label>
                                <input type="text" name="nome" id="ea-loan-nome" class="ea-input" required>
                            </div>
                        </div>
                        <div class="ea-form-col">
                            <div class="ea-form-group">
                                <label for="ea-loan-whatsapp"><?php _e('WhatsApp *', 'emprestimos-ativos'); ?></label>
                                <input type="tel" name="whatsapp" id="ea-loan-whatsapp" class="ea-input" placeholder="(11) 99999-9999" required>
                            </div>
                        </div>
                    </div>
                    <div class="ea-form-group">
                        <label for="ea-loan-setor"><?php _e('Setor *', 'emprestimos-ativos'); ?></label>
                        <select name="setor_id" id="ea-loan-setor" class="ea-select" required>
                            <option value=""><?php _e('Selecione o setor', 'emprestimos-ativos'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="ea-form-section">
                <h3><?php _e('Período do Empréstimo', 'emprestimos-ativos'); ?></h3>
                <p class="ea-period-info"><?php _e('O mesmo período será aplicado a todos os equipamentos selecionados.', 'emprestimos-ativos'); ?></p>
                <div class="ea-form-row">
                    <div class="ea-form-col">
                        <div class="ea-form-group">
                            <label for="ea-loan-data"><?php _e('Início *', 'emprestimos-ativos'); ?></label>
                            <input type="date" name="data_emprestimo" id="ea-loan-data" class="ea-input" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="ea-form-col">
                        <div class="ea-form-group">
                            <label for="ea-loan-hora-ret"><?php _e('Hora Retirada *', 'emprestimos-ativos'); ?></label>
                            <input type="time" name="hora_retirada" id="ea-loan-hora-ret" class="ea-input" required>
                        </div>
                    </div>
                </div>
                <div class="ea-form-row">
                    <div class="ea-form-col">
                        <div class="ea-form-group">
                            <label for="ea-loan-data-dev"><?php _e('Devolução *', 'emprestimos-ativos'); ?></label>
                            <input type="date" name="data_devolucao" id="ea-loan-data-dev" class="ea-input" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="ea-form-col">
                        <div class="ea-form-group">
                            <label for="ea-loan-hora-dev"><?php _e('Hora Devolução *', 'emprestimos-ativos'); ?></label>
                            <input type="time" name="hora_devolucao" id="ea-loan-hora-dev" class="ea-input" required>
                        </div>
                    </div>
                </div>
            </div>
 
            <div class="ea-form-section">
                <div class="ea-form-group">
                    <label for="ea-loan-obs"><?php _e('Observações (opcional)', 'emprestimos-ativos'); ?></label>
                    <textarea name="observacao" id="ea-loan-obs" class="ea-textarea" rows="3"></textarea>
                </div>
            </div>
 
            <div class="ea-form-section ea-signature-section">
    <h3><?php _e('Termos de Compromisso e Deveres', 'emprestimos-ativos'); ?></h3>
 
    <div class="ea-terms-box" style="font-size: 12px;">
 
        <p><strong><?php _e('1. Cláusula de Pontualidade e Agendamento', 'emprestimos-ativos'); ?></strong></p>
        <ul style="margin-left: 15px;">
            <li><?php _e('Gestão de Agenda: O locatário declara estar ciente de que o equipamento pode ser reservado para outros colaboradores imediatamente após o seu horário de devolução.', 'emprestimos-ativos'); ?></li>
            <li><?php _e('Comunicação de Atrasos: Caso ocorra qualquer imprevisto que impossibilite a devolução no horário estipulado, o locatário deverá informar o responsável pelo inventário com, no mínimo, 2 (duas) horas de antecedência.', 'emprestimos-ativos'); ?></li>
            <li><?php _e('Impacto Operacional: O descumprimento injustificado do horário de devolução prejudica a escala de trabalho de demais colegas e será tratado como falta de zelo com os processos da empresa.', 'emprestimos-ativos'); ?></li>
        </ul>
 
        <p><strong><?php _e('2. Responsabilidades pelo Manuseio', 'emprestimos-ativos'); ?></strong></p>
        <ul style="margin-left: 15px;">
            <li><?php _e('Zelo Absoluto: O colaborador é o único responsável pelo equipamento durante o período acima descrito.', 'emprestimos-ativos'); ?></li>
            <li><?php _e('Configuração de Uso: O equipamento deve ser utilizado exclusivamente para fins profissionais, sendo proibida a cessão a terceiros, mesmo que sejam outros membros da equipe, sem o devido registro de transferência junto à administração.', 'emprestimos-ativos'); ?></li>
        </ul>
 
       <div style="background-color: #ffe6e6; border-left: 5px solid #ff0000; padding: 10px; margin-top: 10px; border-radius: 5px;">
    
      <p style="margin: 0; font-weight: bold; color: #b30000;">
        ⚠️ <?php _e('ATENÇÃO:', 'emprestimos-ativos'); ?>
      </p>
      <p style="margin: 5px 0; font-weight: bold;">
        <?php _e('Em caso de danos causados por uso inadequado, queda ou derramamento de líquidos, o colaborador será responsável pelos custos de reparo.', 'emprestimos-ativos'); ?>
      </p>
      <p style="margin: 5px 0;">
        <?php _e('A conferência do estado físico do equipamento será realizada no ato da entrega e na devolução.', 'emprestimos-ativos'); ?>
     </p>
       </div>
        </div>
                <div class="ea-form-group">
                    <label>
                        <input type="checkbox" name="aceite_termos" id="ea-loan-termos" required>
                        <?php _e('Li e aceito os termos de responsabilidade', 'emprestimos-ativos'); ?>
                    </label>
                </div>
            </div>
 
            <div class="ea-form-actions">
                <button type="submit" class="ea-btn ea-btn-primary ea-btn-large"><?php _e('Confirmar Empréstimo', 'emprestimos-ativos'); ?></button>
                <button type="button" class="ea-btn ea-btn-secondary ea-modal-cancel"><?php _e('Cancelar', 'emprestimos-ativos'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL MINI-CATÁLOGO (adicionar mais equipamentos) -->
<div id="ea-modal-add-more" class="ea-modal" style="display:none;">
    <div class="ea-modal-content ea-modal-medium">
        <span class="ea-modal-close">&times;</span>
        <h2><?php _e('Adicionar Equipamentos', 'emprestimos-ativos'); ?></h2>

        <div class="ea-add-more-search">
            <input type="text" id="ea-add-more-busca" class="ea-input" placeholder="<?php _e('Buscar equipamento...', 'emprestimos-ativos'); ?>">
            <select id="ea-add-more-tipo" class="ea-select">
                <option value=""><?php _e('Todos os tipos', 'emprestimos-ativos'); ?></option>
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?php echo esc_attr($tipo); ?>"><?php echo esc_html($tipo); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="ea-add-more-grid" class="ea-add-more-grid"></div>

        <div class="ea-modal-footer-actions">
            <button type="button" class="ea-btn ea-btn-secondary ea-modal-close-add-more">
                <?php _e('Fechar', 'emprestimos-ativos'); ?>
            </button>
            <button type="button" id="ea-btn-confirm-add-more" class="ea-btn ea-btn-primary">
                <?php _e('Confirmar Seleção', 'emprestimos-ativos'); ?>
            </button>
        </div>
    </div>
</div>
 
<!-- MODAL SUCESSO -->
<div id="ea-modal-success" class="ea-modal" style="display:none;">
    <div class="ea-modal-content ea-modal-small ea-modal-success-styled">
        <div class="ea-success-header">
            <div class="ea-success-icon-circle">
                <svg class="ea-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="ea-checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="ea-checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <h2><?php _e('Solicitação Confirmada!', 'emprestimos-ativos'); ?></h2>
            <p id="ea-success-summary"><?php _e('Seu empréstimo foi registrado com sucesso.', 'emprestimos-ativos'); ?></p>
        </div>
 
        <div class="ea-success-body">
            <div class="ea-protocol-card">
                <span class="ea-protocol-label"><?php _e('CÓDIGO(S) DE PROTOCOLO', 'emprestimos-ativos'); ?></span>
                <div class="ea-loan-code" id="ea-success-code"></div>
                <small><?php _e('Apresente este(s) código(s) na retirada dos equipamentos.', 'emprestimos-ativos'); ?></small>
            </div>
        </div>
 
        <div class="ea-success-footer">
            <button class="ea-btn ea-btn-primary ea-btn-full" onclick="location.reload();">
                <?php _e('Entendido', 'emprestimos-ativos'); ?>
            </button>
        </div>
    </div>
</div>
 
<style>

/* =============================================
   MINI-CATÁLOGO (adicionar mais equipamentos)
   ============================================= */
.ea-loan-assets-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 8px;
}

.ea-btn-add-more {
    background: transparent;
    border: 1.5px dashed #b19453;
    color: #b19453;
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.ea-btn-add-more:hover {
    background: #b19453;
    color: #fff;
}

.ea-add-more-search {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}
.ea-add-more-search .ea-input,
.ea-add-more-search .ea-select {
    flex: 1;
    min-width: 140px;
}

.ea-add-more-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    max-height: 380px;
    overflow-y: auto;
    padding: 4px 2px;
}

.ea-mini-card {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    background: #fff;
    text-align: center;
}
.ea-mini-card:hover {
    border-color: #b19453;
    box-shadow: 0 2px 8px rgba(177,148,83,0.15);
}
.ea-mini-card.ea-mini-selected {
    border-color: #b19453;
    background: #fdf8ee;
}
.ea-mini-card.ea-mini-selected::after {
    content: '✓';
    position: absolute;
    top: 6px;
    right: 8px;
    background: #b19453;
    color: #fff;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.ea-mini-card.ea-mini-unavailable {
    opacity: 0.45;
    cursor: not-allowed;
    border-color: #e2e8f0 !important;
    background: #f8fafc !important;
}
.ea-mini-card img {
    width: 60px;
    height: 60px;
    object-fit: contain;
    margin-bottom: 8px;
}
.ea-mini-card-name {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
    line-height: 1.3;
}
.ea-mini-card-status {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
    font-weight: 600;
}

.ea-modal-medium {
    max-width: 680px !important;
}

@media (max-width: 600px) {
    .ea-add-more-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        max-height: 300px;
    }
}
 
/* =============================================
   CARRINHO FLUTUANTE
   ============================================= */
.ea-cart-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #1e293b;
    color: #fff;
    z-index: 9999;
    padding: 12px 20px;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.2);
    animation: slideUp 0.3s ease;
}
 
@keyframes slideUp {
    from { transform: translateY(100%); }
    to   { transform: translateY(0); }
}
 
.ea-cart-bar-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
}
 
.ea-cart-bar-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    flex: 1;
}
 
.ea-cart-bar-right {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}
 
.ea-cart-count {
    background: #b19453;
    color: #fff;
    font-weight: bold;
    border-radius: 50%;
    width: 26px;
    height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}
 
.ea-cart-label {
    font-size: 14px;
    opacity: 0.85;
}
 
.ea-cart-chips {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
 
.ea-cart-chip {
    background: rgba(255,255,255,0.15);
    border-radius: 20px;
    padding: 3px 10px 3px 8px;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
 
.ea-cart-chip-remove {
    cursor: pointer;
    opacity: 0.7;
    font-size: 14px;
    line-height: 1;
}
.ea-cart-chip-remove:hover { opacity: 1; }
 
/* Botão "Adicionar ao Carrinho" na modal */
.ea-btn-outline-primary {
    background: transparent !important;
    border: 2px solid #b19453 !important;
    color: #b19453 !important;
}
.ea-btn-outline-primary:hover {
    background: #b19453 !important;
    color: #fff !important;
}
.ea-btn-outline-primary.ea-in-cart {
    background: #b19453 !important;
    color: #fff !important;
}
 
/* =============================================
   LISTA DE EQUIPAMENTOS NO FORMULÁRIO
   ============================================= */
.ea-loan-assets-section {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}
 
.ea-loan-ativos-list {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
 
.ea-loan-ativo-tag {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 20px;
    padding: 4px 12px 4px 10px;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
 
.ea-loan-ativo-tag-remove {
    cursor: pointer;
    color: #ef4444;
    font-size: 15px;
    line-height: 1;
}
.ea-loan-ativo-tag-remove:hover { color: #b91c1c; }
 
.ea-period-info {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 12px;
    font-style: italic;
}
 
/* Indicador no card de que está no carrinho */
.ea-asset-card.ea-card-in-cart {
    outline: 2px solid #b19453;
    position: relative;
}
.ea-asset-card.ea-card-in-cart::before {
    content: '✓ No carrinho';
    position: absolute;
    top: 8px;
    right: 8px;
    background: #b19453;
    color: #fff;
    font-size: 11px;
    font-weight: bold;
    padding: 2px 8px;
    border-radius: 10px;
    z-index: 2;
}
 
/* =============================================
   ESTILOS ORIGINAIS MANTIDOS
   ============================================= */
.ea-day-past {
    color: #cbd5e1 !important;
    cursor: not-allowed !important;
    background-color: #f8fafc !important;
}
.ea-day-past:hover { background-color: #f8fafc !important; }
 
.ea-status-disponivel { background-color: #10b981 !important; color: white !important; }
.ea-status-emprestado { background-color: #0736a2ff !important; color: white !important; }
.ea-status-manutencao { background-color: #6b7280 !important; color: white !important; }
.ea-status-atrasado   { background-color: #e3200aff !important; color: white !important; font-weight: bold; }
 
.ea-modal-body-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 20px; }
.ea-asset-detail-image-box { background: #f8fafc; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #e2e8f0; }
.ea-asset-detail-image-box img { max-height: 200px; object-fit: contain; }
.ea-tag-status { background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; }
.ea-tag-type { background: #f3f4f6; color: #374151; padding: 4px 10px; border-radius: 6px; font-size: 12px; }
 
.ea-asset-status.ea-status-atrasado {
    background-color: #ef4444 !important;
    color: white !important;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    display: inline-block;
}
.ea-tag-status.ea-status-atrasado {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
    border: 1px solid #fecaca;
}
.ea-status-atrasado { animation: ea-pulse-red 2s infinite; }
@keyframes ea-pulse-red { 0%{opacity:1} 50%{opacity:.6} 100%{opacity:1} }
 
@media (max-width: 768px) {
    .ea-modal-body-grid { grid-template-columns: 1fr !important; gap: 15px !important; }
    .ea-modal-content { width: 95% !important; margin: 10px auto !important; padding: 15px !important; max-height: 90vh; overflow-y: auto; }
    .ea-asset-detail-image-box img { max-height: 150px !important; }
    .ea-calendar-header h3 { font-size: 16px; }
    .ea-cart-bar-inner { flex-direction: column; align-items: flex-start; }
    .ea-cart-bar-right { width: 100%; }
    .ea-cart-bar-right .ea-btn { flex: 1; }
}
 
.ea-calendar-wrapper { width: 100%; box-sizing: border-box; overflow: hidden; }
.ea-calendar-days-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
.ea-day { aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; font-size: clamp(10px,3vw,14px); background-color: #f9fafb; border-radius: 4px; cursor: pointer; min-height: 35px; }
.ea-calendar-weekdays { display: grid; grid-template-columns: repeat(7,1fr); text-align: center; font-size: clamp(9px,2.5vw,11px); font-weight: bold; color: #94a3b8; margin-bottom: 8px; }
.ea-calendar-legend { font-size: 12px; margin-top: 10px; display: flex; align-items: center; gap: 5px; }
.ea-calendar-nav button { border: 1px solid #d1d5db; background: #ffffff; color: #B89044; border-radius: 6px; padding: 5px 12px; font-weight: bold; transition: all 0.2s; }
 
@media (max-width: 380px) {
    .ea-day { font-size: 11px; }
    .ea-calendar-weekdays { font-size: 9px; }
}
 
.ea-day:hover:not(.ea-day-booked):not(.ea-day-empty) { background: #e2e8f0; }
.ea-day-booked { background-color: #fee2e2 !important; color: #ef4444 !important; text-decoration: line-through; cursor: not-allowed; }
.ea-day-booked::after { content:''; position:absolute; bottom:4px; width:4px; height:4px; background:#f57c0bff; border-radius:50%; }
.ea-day-selected { background: #b19453 !important; color:white !important; font-weight:bold; }
.ea-legend-dot { display:inline-block; width:8px; height:8px; background:#f59e0b; border-radius:50%; margin-right:5px; }
 
.ea-modal-footer-actions {
    display: flex !important;
    justify-content: flex-end;
    gap: 12px !important;
    padding: 20px 0 0 0 !important;
    margin-top: 15px;
    border-top: 1px solid #eee;
    flex-wrap: wrap;
}
.ea-modal-footer-actions .ea-btn { margin:0 !important; width:auto !important; white-space:nowrap; }
 
@media (max-width: 600px) {
    .ea-modal-footer-actions { justify-content:center; flex-wrap: wrap; }
    .ea-form-row.ea-align-end { flex-direction:column; gap:0; }
    .ea-form-col-70,.ea-form-col-30 { width:100%; }
    .ea-hide-mobile { display:none; }
    #ea-btn-check-email { margin-top:10px; margin-bottom:15px; }
    .ea-detail-arrow { width:35px; height:35px; }
}
 
.ea-loan-status { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; margin-top:8px; }
.ea-loan-actions { padding:12px 16px; border-top:1px solid var(--ea-gray-200); }
@keyframes pulse { 0%{opacity:1} 50%{opacity:.7} 100%{opacity:1} }
 
.ea-detail-arrow { position:absolute; top:50%; transform:translateY(-50%); background:#b19453; color:#ffffff; border:2px solid #ffffff; width:45px; height:45px; border-radius:50%; cursor:pointer; z-index:999; font-size:20px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,.2); transition:all .3s ease; }
.ea-detail-arrow:hover { background:#967d45; transform:translateY(-50%) scale(1.1); box-shadow:0 6px 15px rgba(0,0,0,.3); }
.ea-detail-arrow-left { left:10px; }
.ea-detail-arrow-right { right:10px; }
 
#ea-btn-check-email { background-color:#b19453 !important; color:#fff !important; border:none !important; }
#ea-btn-check-email:hover { background-color:#967d45 !important; }
 
.ea-modal-success-styled { text-align:center; padding:40px 30px !important; border-radius:20px !important; box-shadow:0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04) !important; }
.ea-success-header h2 { color:#111827; margin:20px 0 10px !important; font-size:24px !important; }
.ea-success-header p { color:#6b7280; font-size:15px; margin-bottom:25px; }
.ea-success-icon-circle { width:80px; height:80px; border-radius:50%; display:block; stroke-width:2; stroke:#10b981; stroke-miterlimit:10; margin:0 auto; box-shadow:inset 0px 0px 0px #10b981; animation:fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; }
.ea-checkmark { width:80px; height:80px; border-radius:50%; display:block; stroke-width:4; stroke:#fff; stroke-miterlimit:10; box-shadow:inset 0px 0px 0px #10b981; animation:fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; }
.ea-checkmark-circle { stroke-dasharray:166; stroke-dashoffset:166; stroke-width:2; stroke-miterlimit:10; stroke:#10b981; fill:none; animation:stroke .6s cubic-bezier(.65,0,.45,1) forwards; }
.ea-checkmark-check { transform-origin:50% 50%; stroke-dasharray:48; stroke-dashoffset:48; animation:stroke .3s cubic-bezier(.65,0,.45,1) .8s forwards; }
@keyframes stroke { 100%{stroke-dashoffset:0} }
@keyframes scale { 0%,100%{transform:none} 50%{transform:scale3d(1.1,1.1,1)} }
@keyframes fill { 100%{box-shadow:inset 0px 0px 0px 40px #10b981} }
 
.ea-protocol-card { background:#f8fafc; border:2px dashed #e2e8f0; border-radius:12px; padding:20px; margin:20px 0 30px; position:relative; }
.ea-protocol-label { display:block; font-size:11px; font-weight:700; color:#94a3b8; letter-spacing:1px; margin-bottom:8px; }
#ea-success-code { font-family:'Courier New',monospace; font-size:24px; font-weight:800; color:#b19453; letter-spacing:3px; margin:10px 0; line-height:1.6; }
.ea-protocol-card small { color:#64748b; font-size:12px; line-height:1.4; }
.ea-btn-full { width:100% !important; }
 
.ea-input,.ea-select,.ea-textarea,input[type="date"].ea-input,input[type="time"].ea-input,input[type="email"].ea-input,input[type="text"].ea-input { border:1px solid #ddd !important; border-radius:8px !important; padding:10px 12px !important; height:42px !important; box-sizing:border-box !important; font-size:14px !important; background-color:#fff !important; appearance:none; -webkit-appearance:none; }
.ea-btn { border-radius:8px !important; height:42px !important; display:flex; align-items:center; justify-content:center; }
input[type="date"]::-webkit-calendar-picker-indicator,input[type="time"]::-webkit-calendar-picker-indicator { cursor:pointer; }
.ea-input:focus,.ea-select:focus { border-color:#b19453 !important; outline:none !important; box-shadow:0 0 0 2px rgba(177,148,83,.1) !important; }
.ea-terms-box { border-radius:8px !important; border:1px solid #e5e7eb !important; }
.ea-no-results { display:flex; align-items:center; justify-content:center; gap:6px; white-space:nowrap; text-align:center; width:100%; font-size:14px; color:#6b7280; padding:20px 10px; }
.ea-btn-secondary { background-color:#f3f4f6 !important; color:#374151 !important; border:1px solid #d1d5db !important; transition:all .2s ease; }
.ea-btn-secondary:hover { background-color:#e5e7eb !important; color:#111827 !important; border-color:#9ca3af !important; }
.ea-form-actions { display:flex !important; gap:12px !important; align-items:center !important; margin-top:20px !important; }
.ea-btn-secondary,.ea-modal-cancel { width:auto !important; margin-top:0 !important; }
@media (max-width:600px) { .ea-form-actions { flex-direction:row; } }
 
.ea-description-container { margin-top:20px; padding:15px; background:#f8fafc; border-radius:10px; border-left:4px solid #b19453; }
.ea-description-container h3 { font-size:14px; text-transform:uppercase; color:#64748b; margin-bottom:8px; letter-spacing:.5px; }
.ea-detail-description-text { font-size:14px; line-height:1.6; color:#334155; margin:0; white-space:pre-line; }
.ea-detail-specs-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:15px; }
.ea-spec-item { background:#fff; border:1px solid #e2e8f0; padding:10px; border-radius:8px; display:flex; flex-direction:column; }
.ea-spec-label { font-size:11px; color:#94a3b8; text-transform:uppercase; font-weight:bold; }
.ea-spec-value { font-size:14px; color:#1e293b; font-weight:600; }
.ea-calendar-card { border:1px solid #e2e8f0; border-radius:12px; padding:15px; background:#fff; height:100%; }
.ea-modal-header-new { border-bottom:1px solid #f1f5f9; padding-bottom:15px; margin-bottom:10px; }
.ea-detail-tags { display:flex; gap:8px; margin-top:5px; }
.ea-terms-box { background:#f3f4f6; padding:12px; border-radius:6px; border:1px solid #e5e7eb; margin-bottom:10px; }
.ea-schedule-item { display:flex; gap:10px; align-items:center; background:#f9fafb; padding:10px; border-bottom:1px solid #eee; }
.ea-schedule-user { font-weight:bold; margin-left:auto; }
 
.ea-form-row.ea-align-end { display:flex; align-items:flex-start; gap:15px; }
.ea-form-col-70 { flex:0 0 70%; }
.ea-form-col-30 { flex:0 0 calc(30% - 15px); }
 
.magma-form-alert { display:none; background-color:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:500; align-items:center; gap:10px; box-shadow:0 2px 4px rgba(0,0,0,.05); animation:alertShake .4s ease-in-out; }
.magma-form-alert .dashicons { color:#dc2626; font-size:20px; width:20px; height:20px; }
@keyframes alertShake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-4px)} 75%{transform:translateX(4px)} }
</style>
 
<script>
jQuery(document).ready(function($) {
    let ativos = [];
    let ativoAtualIndex = -1;
    let dataCalendario = new Date();
    let ativoAtualId = null;
    let reservasAtivo = [];
 
    // ============================================
    // CARRINHO — estado global
    // ============================================
    let carrinho = []; // Array de objetos { id, nome }
 
    function carrinhoContem(id) {
        return carrinho.some(a => a.id == id);
    }
 
    function adicionarAoCarrinho(id, nome) {
        if (carrinhoContem(id)) return;
        carrinho.push({ id: id, nome: nome });
        atualizarCarrinhoUI();
    }
 
    function removerDoCarrinho(id) {
        carrinho = carrinho.filter(a => a.id != id);
        atualizarCarrinhoUI();
    }
 
    function limparCarrinho() {
        carrinho = [];
        atualizarCarrinhoUI();
    }
 
    function atualizarCarrinhoUI() {
        const qtd = carrinho.length;
 
        // Barra flutuante
        if (qtd === 0) {
            $('#ea-cart-bar').hide();
        } else {
            $('#ea-cart-bar').show();
            $('#ea-cart-count').text(qtd);
 
            let chips = '';
            carrinho.forEach(a => {
                chips += `<span class="ea-cart-chip">
                    ${a.nome}
                    <span class="ea-cart-chip-remove" data-id="${a.id}" title="Remover">×</span>
                </span>`;
            });
            $('#ea-cart-chips').html(chips);
        }
 
        // Destaque nos cards do catálogo
        $('.ea-asset-card').each(function() {
            const id = $(this).data('id');
            $(this).toggleClass('ea-card-in-cart', carrinhoContem(id));
        });
 
        // Botão "Adicionar ao Carrinho" na modal de detalhe
        if (ativoAtualId) {
            const noCarrinho = carrinhoContem(ativoAtualId);
            $('#ea-btn-add-to-cart')
                .toggleClass('ea-in-cart', noCarrinho)
                .text(noCarrinho ? '✓ No Carrinho' : '+ Adicionar ao Carrinho');
        }
    }
 
    // Remover item pelo chip na barra
    $(document).on('click', '.ea-cart-chip-remove', function(e) {
        e.stopPropagation();
        removerDoCarrinho($(this).data('id'));
    });
 
    // Remover item pela tag no formulário
    $(document).on('click', '.ea-loan-ativo-tag-remove', function() {
        removerDoCarrinho($(this).data('id'));
        renderizarListaFormulario();
        if (carrinho.length === 0) {
            $('#ea-modal-loan').hide();
        }
    });
 
    // Limpar carrinho
    $('#ea-btn-clear-cart').on('click', limparCarrinho);
 
    // Abrir formulário a partir da barra
    $('#ea-btn-open-loan').on('click', function() {
        abrirFormulario();
    });
 
    // ============================================
    // ABRIR FORMULÁRIO COM OS ITENS DO CARRINHO
    // ============================================
    function abrirFormulario() {
        if (carrinho.length === 0) return;
        renderizarListaFormulario();
        $('#ea-modal-loan').fadeIn();
    }

 function renderizarListaFormulario() {
    // Tags visuais
    let html = '';
    carrinho.forEach(a => {
        html += `<span class="ea-loan-ativo-tag">
            ${a.nome}
            <span class="ea-loan-ativo-tag-remove" data-id="${a.id}" title="Remover">×</span>
        </span>`;
    });
    $('#ea-loan-ativos-list').html(html);

    // LIMPA antes de reinjetar os campos hidden
    $('#ea-loan-ativos-inputs').empty();
    
    let inputs = '';
    carrinho.forEach(a => {
        inputs += `<input type="hidden" name="ativo_ids[]" value="${a.id}">`;
    });
    $('#ea-loan-ativos-inputs').html(inputs);
}

// ============================================
// MINI-CATÁLOGO — ADICIONAR MAIS EQUIPAMENTOS
// ============================================
let todosAtivos = []; // cache completo para o mini-catálogo

function renderMiniCatalogo(lista) {
    let html = '';
    if (!lista.length) {
        html = '<div class="ea-no-results">Nenhum equipamento disponível 🔎</div>';
    } else {
        lista.forEach(a => {
            const indisponivel = a.disponibilidade === 'atrasado' || a.disponibilidade === 'manutencao';
            const noCarrinho   = carrinhoContem(a.id);

            let statusText  = { disponivel:'Disponível', emprestado:'Emprestado', atrasado:'Atrasado', manutencao:'Manutenção' }[a.disponibilidade] || a.disponibilidade;
            let statusColor = { disponivel:'#10b981', emprestado:'#0736a2', atrasado:'#e3200a', manutencao:'#6b7280' }[a.disponibilidade] || '#6b7280';

            const classes = [
                'ea-mini-card',
                noCarrinho    ? 'ea-mini-selected'    : '',
                indisponivel  ? 'ea-mini-unavailable' : ''
            ].filter(Boolean).join(' ');

            html += `
            <div class="${classes}" data-id="${a.id}" data-nome="${a.nome}" data-disponivel="${indisponivel ? '0' : '1'}">
                <img src="${a.imagem_url}" alt="${a.nome}">
                <div class="ea-mini-card-name">${a.nome}</div>
                <span class="ea-mini-card-status" style="background:${statusColor}20;color:${statusColor};">${statusText}</span>
            </div>`;
        });
    }
    $('#ea-add-more-grid').html(html);
}

function filtrarMiniCatalogo() {
    const busca = $('#ea-add-more-busca').val().toLowerCase().trim();
    const tipo  = $('#ea-add-more-tipo').val();

    const filtrado = todosAtivos.filter(a => {
        const matchBusca = !busca || a.nome.toLowerCase().includes(busca);
        const matchTipo  = !tipo  || a.tipo === tipo;
        return matchBusca && matchTipo;
    });
    renderMiniCatalogo(filtrado);
}

// Abrir mini-catálogo
$('#ea-btn-add-more').on('click', function() {
    $('#ea-add-more-busca').val('');
    $('#ea-add-more-tipo').val('');

    if (todosAtivos.length) {
        renderMiniCatalogo(todosAtivos);
        $('#ea-modal-add-more').fadeIn();
        return;
    }

    // Carrega todos os ativos sem filtro de status
    $.ajax({
        url: ea_vars.ajax_url,
        type: 'POST',
        data: { action: 'ea_get_assets', nonce: ea_vars.nonce },
        success: function(r) {
            if (r.success) {
                todosAtivos = r.data;
                renderMiniCatalogo(todosAtivos);
                $('#ea-modal-add-more').fadeIn();
            }
        }
    });
});

// Invalidar cache quando o catálogo principal recarregar
function loadAssets() {
    todosAtivos = []; // reseta cache do mini-catálogo
    $('#ea-catalog-grid').css('opacity','0.5');
    $.ajax({
        url: ea_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'ea_get_assets',
            nonce: ea_vars.nonce,
            tipo: $('#ea-filter-tipo').val(),
            status: $('#ea-filter-status').val(),
            busca: $('#ea-filter-busca').val()
        },
        success: function(r) {
            $('#ea-catalog-grid').css('opacity','1');
            if (r.success) {
                ativos = r.data;
                renderAssets(ativos);
            }
        }
    });
}

// Selecionar/deselecionar card no mini-catálogo
$(document).on('click', '.ea-mini-card', function() {
    if ($(this).data('disponivel') == '0') return; // bloqueado

    const id   = $(this).data('id');
    const nome = $(this).data('nome');

    if (carrinhoContem(id)) {
        removerDoCarrinho(id);
        $(this).removeClass('ea-mini-selected');
    } else {
        adicionarAoCarrinho(id, nome);
        $(this).addClass('ea-mini-selected');
    }
});

// Busca e filtro no mini-catálogo
$('#ea-add-more-busca').on('keyup', function() {
    clearTimeout(window.miniSearchT);
    window.miniSearchT = setTimeout(filtrarMiniCatalogo, 300);
});
$('#ea-add-more-tipo').on('change', filtrarMiniCatalogo);

// Fechar mini-catálogo e confirmar
$('#ea-btn-confirm-add-more, .ea-modal-close-add-more').on('click', function() {
    renderizarListaFormulario(); // sincroniza hidden inputs
    $('#ea-modal-add-more').fadeOut();
});

    // ============================================
    // CARREGAR CATÁLOGO
    // ============================================
    function loadAssets() {
        $('#ea-catalog-grid').css('opacity','0.5');
        $.ajax({
            url: ea_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'ea_get_assets',
                nonce: ea_vars.nonce,
                tipo: $('#ea-filter-tipo').val(),
                status: $('#ea-filter-status').val(),
                busca: $('#ea-filter-busca').val()
            },
            success: function(r) {
                $('#ea-catalog-grid').css('opacity','1');
                if (r.success) {
                    ativos = r.data;
                    renderAssets(ativos);
                }
            }
        });
    }
 
    function renderAssets(assets) {
        let html = '';
        if (assets.length === 0) {
            html = '<div class="ea-no-results"><?php _e("Nenhum equipamento encontrado no momento 🔎.", "emprestimos-ativos"); ?></div>';
        } else {
            assets.forEach(asset => {
                let statusClass = 'ea-status-' + asset.disponibilidade;
                let statusText = asset.disponibilidade;
                if (asset.disponibilidade === 'disponivel') statusText = 'Disponível';
                if (asset.disponibilidade === 'emprestado') statusText = 'Emprestado';
                if (asset.disponibilidade === 'atrasado')  statusText = 'Atrasado';
                if (asset.disponibilidade === 'manutencao') statusText = 'Manutenção';
 
                html += `
                <div class="ea-asset-card${carrinhoContem(asset.id) ? ' ea-card-in-cart' : ''}" data-id="${asset.id}">
                    <div class="ea-asset-image"><img src="${asset.imagem_url}" alt="${asset.nome}"></div>
                    <div class="ea-asset-info">
                        <h3 class="ea-asset-name">${asset.nome}</h3>
                        <span class="ea-asset-type">${asset.tipo}</span>
                        <span class="ea-asset-status ${statusClass}">${statusText}</span>
                        ${asset.proximo_disponivel && asset.disponibilidade !== 'disponivel' ?
                          `<small class="ea-next-available" style="display:block;margin-top:5px;font-size:11px;color:#6b7280;">
                            <strong><?php _e("Previsão de devolução:", "emprestimos-ativos"); ?></strong> ${asset.proximo_disponivel}
                          </small>` : ''}
                    </div>
                    <button type="button" class="ea-btn-view-asset"><?php _e("Ver Detalhes", "emprestimos-ativos"); ?></button>
                </div>`;
            });
        }
        $('#ea-catalog-grid').html(html);
    }
 
    // ============================================
    // MODAL DE DETALHE
    // ============================================
    function abrirDetalhe(index) {
        if (index < 0 || index >= ativos.length) return;
 
        ativoAtualIndex = index;
        let item = ativos[index];
        ativoAtualId = item.id;
 
        $.ajax({
            url: ea_vars.ajax_url,
            type: 'POST',
            data: { action: 'ea_get_asset_detail', nonce: ea_vars.nonce, id: item.id },
            success: function(r) {
                if (r.success) {
                    let d = r.data;
                    $('#ea-detail-image').attr('src', d.imagem_url);
                    $('#ea-detail-nome').text(d.nome);
                    $('#ea-detail-tipo').text(d.tipo);
 
                    let statusText = d.disponibilidade;
                    if (d.disponibilidade === 'disponivel') statusText = 'Disponível';
                    if (d.disponibilidade === 'emprestado') statusText = 'Emprestado';
                    if (d.disponibilidade === 'atrasado')  statusText = 'Atrasado';
                    if (d.disponibilidade === 'manutencao') statusText = 'Manutenção';
 
                    $('#ea-detail-status')
                        .text(statusText)
                        .attr('class', 'ea-tag-status ea-status-' + d.disponibilidade);
 
                    $('#ea-detail-descricao').text(d.descricao || '');
                    $('#ea-detail-patrimonio').text(d.etiqueta_patrimonial || '-');
                    $('#ea-detail-condicao').text(d.condicao || '-');
 
                    const indisponivel = d.disponibilidade === 'atrasado' || d.disponibilidade === 'manutencao';
 
                    // Botão empréstimo direto
                    $('#ea-btn-solicitar-detail')
                        .prop('disabled', indisponivel)
                        .text(indisponivel ? 'Indisponível' : 'Realizar Empréstimo');
 
                    // Botão adicionar ao carrinho
                    if (indisponivel) {
                        $('#ea-btn-add-to-cart').hide();
                    } else {
                        $('#ea-btn-add-to-cart').show();
                        const noCarrinho = carrinhoContem(d.id);
                        $('#ea-btn-add-to-cart')
                            .toggleClass('ea-in-cart', noCarrinho)
                            .text(noCarrinho ? '✓ No Carrinho' : '+ Adicionar ao Carrinho');
                    }
 
                    $.post(ea_vars.ajax_url, { action: 'ea_get_asset_schedule', ativo_id: d.id, nonce: ea_vars.nonce }, function(res) {
                        reservasAtivo = res.success ? res.data : [];
                        renderCalendar(dataCalendario);
                        $('#ea-modal-detail').fadeIn();
                    });
                }
            }
        });
    }
 
    $(document).on('click', '.ea-asset-card', function(e) {
        const id = $(this).data('id');
        const idx = ativos.findIndex(a => a.id == id);
        if (idx !== -1) abrirDetalhe(idx);
    });
 
    // ============================================
    // BOTÃO "ADICIONAR AO CARRINHO" NA MODAL
    // ============================================
    $('#ea-btn-add-to-cart').on('click', function() {
        const item = ativos.find(a => a.id == ativoAtualId);
        if (!item) return;
 
        if (carrinhoContem(ativoAtualId)) {
            removerDoCarrinho(ativoAtualId);
        } else {
            adicionarAoCarrinho(item.id, item.nome);
        }
    });
 
    // ============================================
    // BOTÃO "REALIZAR EMPRÉSTIMO" (empréstimo direto — apenas este item)
    // ============================================
    $('#ea-btn-solicitar-detail').on('click', function() {
        const item = ativos.find(a => a.id == ativoAtualId);
        if (!item) return;
 
        // Garante que só este item esteja no carrinho (empréstimo único direto)
        if (!carrinhoContem(item.id)) {
            // Não limpa o carrinho — apenas abre o formulário com o carrinho atual + este item
            adicionarAoCarrinho(item.id, item.nome);
        }
 
        $('#ea-modal-detail').hide();
        abrirFormulario();
    });
 
    // ============================================
    // CALENDÁRIO
    // ============================================
    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();
 
        const today = new Date();
        today.setHours(0, 0, 0, 0);
 
        const names = ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"];
        $('#ea-current-month-display').text(`${names[month]} ${year}`);
 
        let html = '';
        for (let i = 0; i < firstDay; i++) html += '<div class="ea-day ea-day-empty"></div>';
 
        for (let d = 1; d <= lastDate; d++) {
            const currentIterationDate = new Date(year, month, d, 0, 0, 0);
            const fullDate = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const isPast = currentIterationDate < today;
 
            const booked = reservasAtivo.some(res => {
                const startDate = new Date(res.start.split('T')[0] + 'T00:00:00');
                const endDate   = new Date(res.end.split('T')[0]   + 'T00:00:00');
                return currentIterationDate >= startDate && currentIterationDate <= endDate;
            });
 
            let dayClass = 'ea-day';
            if (isPast)  dayClass += ' ea-day-past';
            if (booked)  dayClass += ' ea-day-booked';
 
            html += `<div class="${dayClass}" data-date="${fullDate}">${d}</div>`;
        }
        $('#ea-calendar-days').html(html);
    }
 
    $(document).on('click', '.ea-day:not(.ea-day-booked):not(.ea-day-empty):not(.ea-day-past)', function() {
        const dataSelecionada = $(this).data('date');
        $('#ea-loan-data, #ea-loan-data-dev').val(dataSelecionada);
        // O item atual já deve estar no carrinho neste ponto
        const item = ativos.find(a => a.id == ativoAtualId);
        if (item && !carrinhoContem(ativoAtualId)) {
            adicionarAoCarrinho(item.id, item.nome);
        }
        renderizarListaFormulario();
        $('#ea-modal-detail').hide();
        $('#ea-modal-loan').fadeIn();
    });
 
    $('#ea-prev-month').click(() => { dataCalendario.setMonth(dataCalendario.getMonth()-1); renderCalendar(dataCalendario); });
    $('#ea-next-month').click(() => { dataCalendario.setMonth(dataCalendario.getMonth()+1); renderCalendar(dataCalendario); });
 
    // ============================================
    // FECHAR MODAIS
    // ============================================
    $('.ea-modal-close, .ea-modal-cancel').click(function() {
        $(this).closest('.ea-modal').fadeOut();
    });
 
    $('#ea-modal-loan .ea-modal-cancel').click(function(e) {
        e.preventDefault();
        $('#ea-modal-loan').hide();
        // Volta para o detalhe apenas se havia um item sendo visualizado
        if (ativoAtualId) $('#ea-modal-detail').fadeIn();
    });
 
    // ============================================
    // SUBMIT DO FORMULÁRIO
    // ============================================

 $('#ea-form-loan').on('submit', function(e) {
    e.preventDefault();

    if (carrinho.length === 0) {
        alert('Nenhum equipamento selecionado.');
        return;
    }

    // Garante que os inputs hidden estão atualizados ANTES de serializar
    renderizarListaFormulario();

    // Verifica se os campos obrigatórios estão preenchidos
    const camposObrigatorios = [
        { id: '#ea-loan-nome',     label: 'Nome Completo' },
        { id: '#ea-loan-email',    label: 'E-mail' },
        { id: '#ea-loan-whatsapp', label: 'WhatsApp' },
        { id: '#ea-loan-setor',    label: 'Setor' },
        { id: '#ea-loan-data',     label: 'Data de Início' },
        { id: '#ea-loan-hora-ret', label: 'Hora de Retirada' },
        { id: '#ea-loan-data-dev', label: 'Data de Devolução' },
        { id: '#ea-loan-hora-dev', label: 'Hora de Devolução' },
    ];

    let faltando = [];
    camposObrigatorios.forEach(c => {
        if (!$(c.id).val() || $(c.id).val().trim() === '') {
            faltando.push(c.label);
            $(c.id).css('border-color', '#ef4444');
        } else {
            $(c.id).css('border-color', '');
        }
    });

    if (!$('#ea-loan-termos').is(':checked')) {
        faltando.push('Aceite dos Termos');
        $('#ea-loan-termos').closest('label').css('color', '#ef4444');
    } else {
        $('#ea-loan-termos').closest('label').css('color', '');
    }

    if (faltando.length > 0) {
        alert('Preencha os campos obrigatórios:\n• ' + faltando.join('\n• '));
        return;
    }

    let btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).text('Enviando...');

    // Serializa o form (que já inclui os ativo_ids[] dos hidden atualizados)
    const formData = $(this).serialize() + '&action=ea_create_loan&nonce=' + ea_vars.nonce;

    $.ajax({
        url: ea_vars.ajax_url,
        type: 'POST',
        data: formData,
        success: function(r) {
            if (r.success) {
                $('#ea-modal-loan').hide();

                let codigosHtml = '';
                if (Array.isArray(r.data.codigos)) {
                    codigosHtml = r.data.codigos.join('<br>');
                    $('#ea-success-summary').text(
                        r.data.codigos.length > 1
                            ? `${r.data.codigos.length} empréstimos registrados com sucesso.`
                            : 'Seu empréstimo foi registrado com sucesso.'
                    );
                } else {
                    codigosHtml = r.data.codigo || '';
                }

                $('#ea-success-code').html(codigosHtml);
                $('#ea-modal-success').show();
                limparCarrinho();
            } else {
                alert(r.data.message);
                btn.prop('disabled', false).text('Confirmar Empréstimo');
            }
        },
        error: function() {
            alert('Erro de comunicação. Tente novamente.');
            btn.prop('disabled', false).text('Confirmar Empréstimo');
        }
    });
});
    // ============================================
    // VERIFICAR E-MAIL + SETORES
    // ============================================
    $('#ea-btn-check-email').on('click', function() {
        let email = $('#ea-loan-email').val();
        if (!email) return alert('Informe o e-mail');
        $.post(ea_vars.ajax_url, { action:'ea_check_employee', email:email, nonce:ea_vars.nonce }, function(r) {
            if (r.success && r.data.exists) {
                $('#ea-loan-nome').val(r.data.funcionario.nome);
                $('#ea-loan-whatsapp').val(r.data.funcionario.whatsapp);
                $('#ea-loan-setor').val(r.data.funcionario.setor_id);
            } else {
                alert('E-mail não encontrado. Continue o cadastro.');
            }
        });
    });
 
    $.post(ea_vars.ajax_url, { action:'ea_get_setores', nonce:ea_vars.nonce }, function(r) {
        if (r.success) {
            let opt = '<option value="">Selecione o setor</option>';
            r.data.forEach(s => opt += `<option value="${s.id}">${s.nome}</option>`);
            $('#ea-loan-setor').html(opt);
        }
    });
 
    // ============================================
    // FILTROS
    // ============================================
    $('#ea-filter-tipo, #ea-filter-status').on('change', loadAssets);
    $('#ea-filter-busca').on('keyup', function() {
        clearTimeout(window.searchT);
        window.searchT = setTimeout(loadAssets, 400);
    });
 
    loadAssets();
});
</script>