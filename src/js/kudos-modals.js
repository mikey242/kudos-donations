import $ from "jquery";
import logo from "../img/logo-colour.svg";

export function init() {

    let modalLogo = new Image(40);
    modalLogo.src = logo;

    let $modal = $('\
        <div id="kudos_modal" aria-hidden="true">\
            <div class="kudos_modal_overlay flex justify-center items-center fixed top-0 left-0 w-full h-full bg-modal z-50" tabindex="-1" data-micromodal-close>\
                <div class="kudos_modal_container bg-white py-4 px-8 rounded-lg max-h-screen max-w-lg relative overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="kudos_modal-title">\
                    <header class="kudos_modal_header flex items-center justify-between">\
                        <div class="kudos_modal_title mt-0 mb-0" id="kudos_modal-title">\
                        '+modalLogo.outerHTML+'\
                        </div>\
                        <button class="kudos_modal_close text-black p-0" aria-hidden="true" aria-label="Close modal" data-micromodal-close></button>\
                    </header>\
                    <div id="kudos_modal_content" class="kudos_modal_content">\
                    </div>\
                </div>\
            </div>\
        </div>\
    ');

    $modal.appendTo('body');
    return $modal;
}

export function messageModal(header, message) {

    return $('\
        <div class="top-content text-center">\
            <h2 class="font-normal">' + header + '</h2><p>' + message + '</p>\
        </div>\
        <footer class="kudos_modal_footer mt-4 text-right">\
            <button class="kudos_btn kudos_btn_primary" type="button" data-micromodal-close aria-label="Close this dialog window">Ok</button>\
        </footer>\
    ');
}

export function donateModal(header, text) {

    return ($('\
        <div class="top-content text-center">\
            <h2 class="font-normal">' + header + '</h2>\
            <p>' + text + '</p>\
        </div>\
        <form id="kudos_form" action="">\
            <input type="text" name="name" placeholder="Naam (optioneel)" />\
            <input type="email" class="mt-3" name="email_address" placeholder="E-mailadres (optioneel)" />\
            <input required type="text" min="1" class="mt-3" name="value" placeholder="Bedrag (in euro\'s) *" />\
            <div class="payment_by mt-3 text-muted text-right"><small class="text-gray-600">\
                <span class="fa-stack fa-xs align-middle">\
                    <i class="fas fa-circle fa-stack-2x"></i>\
                    <i class="fas fa-lock fa-stack-1x fa-inverse"></i>\
                </span>\
                Beveiligde betaling via\
            </small></div>\
            <footer class="kudos_modal_footer mt-4 text-center">\
                <button class="kudos_btn kudos_btn_primary_outline mr-3" type="button" data-micromodal-close aria-label="Close this dialog window">Annuleren</button>\
                <button id="kudos_submit" class="kudos_btn kudos_btn_primary" type="submit">Doneeren</button>\
            </footer>\
        </form>\
        <i class="kudos_spinner"></i> \
    '));

}