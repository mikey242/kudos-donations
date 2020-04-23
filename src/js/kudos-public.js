import bootbox from 'bootbox';
import 'bootstrap';

(function( $ ) {
    'use strict';

    $(function () {

        const $body = $('body');

        // Setup button action
        $('.kudos_button').each(function() {
            $(this).click(function () {
                bootbox.confirm("<form id='kudos_form' action=''>\
                        <input type='email' class='form-control' name='email_address' placeholder='E-mail adres' /><br/>\
                        <input type='number' class='form-control' name='value' placeholder='Bedrag (5, 10, 20)' />\
                    </form>", function(result) {
                    if(result)
                        $('#kudos_form').submit();
                });
            })
        })

        // Submit donation form action
        $body.on('submit', 'form#kudos_form', function (e) {
            e.preventDefault();
            $.ajax({
                method: 'post',
                dataType: 'json',
                url: wp_ajax.ajaxurl,
                data:  {
                    action: 'create_payment',
                    form: $(e.currentTarget).serialize()
                },
                success: function (result) {
                    if(result.success) {
                        $(location).attr('href', result.data);
                        console.log('success', result);
                    }
                },
                error: function (error) {
                    console.log('error', error)
                }
            })
        })
    })

})( jQuery );
