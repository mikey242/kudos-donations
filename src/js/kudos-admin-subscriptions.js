import $ from "jquery"

console.log('subscriptions');

$(() => {
    'use strict';
    $('.row-actions .cancel').click(function (e) {
        if(!confirm(window.kudos.confirmation)) {
            e.preventDefault();
        }
    })
})
