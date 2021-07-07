const Notice = (props) => {

    const {Snackbar} = wp.components

    if (props.showNotice) {
        return (
            <div className="components-snackbar-list components-editor-notices__snackbar">
                <Snackbar
                    className={
                        props.showNotice
                            ? 'notification-shown'
                            : 'notification-hidden'
                    }
                    onRemove={() => props.hideNotice()}
                >
                    {props.message}
                </Snackbar>
            </div>
        )
    }
    return null
}

export {Notice}
