import KudosModal from "../../public/KudosModal"

// Handles the messages by showing the modals in order
export function handleMessages(messages) {

    let message = new KudosModal(messages[0].id, null,{
        onHide: () => {
            messages.shift()
            if (messages.length) {
                message.open()
            }
        },
    })

    message.open()
}