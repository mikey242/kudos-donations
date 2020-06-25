import $ from "jquery"

console.log('subscriptions');

$(() => {
    'use strict';
    console.log('do it live');
    $('.row-actions .cancel').click(function (e) {
        if(!confirm(window.kudos.confirmation)) {
            e.preventDefault();
        }
    })
})
