import MicroModal from "micromodal"

// Handles the messages by showing the modals in order
export function handleMessages(messages) {

    let showMessage = () => {
        MicroModal.show(messages[0].id, {
            onClose: () => {
                messages.shift()
                if (messages.length) {
                    showMessage()
                }
            },
            awaitCloseAnimation: true,
            awaitOpenAnimation: true,
        })
    }

    showMessage()
}