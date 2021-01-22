import $ from 'jquery'

$(() => {
    'use strict'
    $('.row-actions .cancel').click(function (e) {
        // eslint-disable-next-line no-alert,no-undef
        if (!confirm(window.kudos.confirmationCancel)) {
            e.preventDefault()
        }
    })

    $('.row-actions .delete').click(function (e) {
        // eslint-disable-next-line no-alert,no-undef
        if (!confirm(window.kudos.confirmationDelete)) {
            e.preventDefault()
        }
    })
})
