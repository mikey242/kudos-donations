import $ from "jquery"

$(() => {
    'use strict';
    $('.row-actions .cancel').click(function (e) {
        if(!confirm(window.kudos.confirmation)) {
            e.preventDefault();
        }
    })
})
