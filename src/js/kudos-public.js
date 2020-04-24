import bootbox from 'bootbox';
import 'bootstrap';

(function( $ ) {
    'use strict';

    $(function () {

        const $body = $('body');
        let order_id = new URLSearchParams(location.search).get('kudos_order_id');

        // Check order status if query var exists
        if(order_id) {
            $.ajax({
                method: 'post',
                dataType: 'json',
                url: wp_ajax.ajaxurl,
                data: {
                    action: 'check_transaction',
                    order_id: order_id
                },
                success: function (result) {
                    if(result.success) {
                        if (result.data.status === 'paid')
                        bootbox.alert({
                            message: 'Thanks for your donation of €' + result.data.value
                        })
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            })
        }

        // Setup button action
        $('.kudos_button a').each(function() {
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
                    redirectUrl: $(location).attr('href').split('?kudos_order_id')[0],
                    form: $(e.currentTarget).serialize()
                },
                success: function (result) {
                    if(result.success) {
                        $(location).attr('href', result.data);
                    }
                },
                error: function (error) {
                    console.log('error', error)
                }
            })
        })
    })

})( jQuery );
