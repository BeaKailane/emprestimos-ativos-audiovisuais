/**
 * Sistema de Empréstimos - Admin JS
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Fechar modal com ESC
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.ea-modal').hide();
            }
        });
        
        // Confirmação de exclusão genérica
        $(document).on('click', '.ea-confirm-delete', function(e) {
            if (!confirm(ea_admin.messages.confirm_delete)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
})(jQuery);
