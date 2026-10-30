/**
 * Sistema de Empréstimos - Frontend JS
 */
(function($) {
    'use strict';
    
    // Máscara de telefone
    function maskPhone(input) {
        var value = input.value.replace(/\D/g, '');
        if (value.length > 11) value = value.substring(0, 11);
        
        if (value.length > 6) {
            value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7);
        } else if (value.length > 2) {
            value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
        } else if (value.length > 0) {
            value = '(' + value;
        }
        
        input.value = value;
    }
    
    $(document).ready(function() {
        // Aplicar máscara de telefone
        $(document).on('input', 'input[name="whatsapp"]', function() {
            maskPhone(this);
        });
        
        // Fechar modal com ESC
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.ea-modal').hide();
            }
        });
    });
    
})(jQuery);
