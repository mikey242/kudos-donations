class KudosModal_OLD {

    constructor(modal, options = {}) {
        // Get the modal element
        this.modal = modal

        // Assign options and their defaults
        this.options = {
            timeOut: options.timeOut ?? 300,
            triggerElement: options.triggerElement,
            escapeClose: options.escapeClose ?? true,
            overlayClose: options.overlayClose ?? false,
            openClass: options.openClass ?? 'is-open',
            overlayClass: options.overlayClass ?? 'kudos-modal-overlay',
            onOpen: options.onOpen,
            onOpened: options.onOpened,
            onClose: options.onClose,
            onClosed: options.onClosed
        }

        // Initial state is closed
        this.isOpen = false

        // Get close elements
        this.closeModal = this.modal.querySelectorAll('[data-modal-close]')

        // Elements in modal that are focusable
        this.focusableElements = [
            'a[href]',
            'area[href]',
            'input:not([disabled]):not([type="hidden"]):not([aria-hidden])',
            'select:not([disabled]):not([aria-hidden])',
            'textarea:not([disabled]):not([aria-hidden])',
            'button:not([disabled]):not([aria-hidden])',
            'iframe',
            'object',
            'embed',
            '[contenteditable]',
            '[tabindex]:not([tabindex^="-"])'
        ]

        // Bind event handlers
        this.handleKeyPress = this.handleKeyPress.bind(this)
        this.handleClick = this.handleClick.bind(this)

        // Click event listener for trigger element
        if (this.options.triggerElement) {
            this.options.triggerElement.addEventListener("click", () => {
                if (this.isOpen) {
                    return this.close()
                }
                return this.open()
            })
        }

        // Close modal button click events
        this.closeModal.forEach(item => {
            item.addEventListener("click", () => {
                this.close()
            })
        })

    }

    /**
     * Opens modal.
     */
    open = () => {
        this.modal.setAttribute('aria-hidden', 'false')
        this.modal.classList.add(this.options.openClass)
        this.modal.querySelector('.kudos-modal-container').focus()

        // Add event listeners
        this.addEventListeners()

        // Create and dispatch event
        window.dispatchEvent(new CustomEvent('kudosModalOpen', {detail: this}))

        // Fire onOpen function
        if (typeof this.options.onOpen === 'function') {
            this.options.onOpen(this.modal)
        }

        setTimeout(() => {
            // Fire onOpened function
            if (typeof this.options.onOpened === 'function') {
                this.options.onOpened(this.modal)
            }
        }, this.options.timeOut)
    }

    /**
     * Closes modal.
     */
    close = () => {
        this.modal.setAttribute('aria-hidden', 'true')

        // Remove event listeners
        this.removeEventListeners()

        // Create and dispatch event
        window.dispatchEvent(new CustomEvent('kudosModalClose', {detail: this}))

        // Fire onClose function
        if (typeof this.options.onClose === 'function') {
            this.options.onClose(this.modal)
        }

        setTimeout(() => {
            this.modal.classList.remove(this.options.openClass)

            // Fire onClose function
            if (typeof this.options.onClosed === 'function') {
                this.options.onClosed(this.modal)
            }
        }, this.options.timeOut)
    }

    /**
     * Registers event listeners.
     */
    addEventListeners = () => {
        window.addEventListener('keydown', this.handleKeyPress)
        window.addEventListener('click', this.handleClick)
    }

    /**
     * Remove registered event listeners.
     */
    removeEventListeners = () => {
        window.removeEventListener('keydown', this.handleKeyPress)
        window.removeEventListener('click', this.handleClick)
    }

    /**
     * Gets all focusable content specified in this.focusableElements
     * and removes non-visible elements.
     *
     * @returns {array}
     */
    getFocusableContent = () => Array.from(this.modal.querySelectorAll(this.focusableElements)).filter((e) => {
        return (e.offsetParent !== null)
    })

    /**
     * Handles click event and calls relevant function.
     *
     * @param {Event} e
     */
    handleClick = e => {
        if (e.target.classList.contains(this.options.overlayClass)) this.handleOverlayClose()
    }

    /**
     * Handles key press event and calls relevant function.
     *
     * @param {KeyboardEvent} e
     */
    handleKeyPress = e => {
        if (e.key === 'Escape' || e.keyCode === 27) this.handleEscapeClose()
        if (e.key === 'Tab' || e.keyCode === 9) this.handleTab(e)
    }

    /**
     * Handles click event on overlay by closing modal.
     */
    handleOverlayClose = () => {
        if (this.options.overlayClose) {
            this.close()
        }
    }

    /**
     * Handles escape button press by closing modal.
     */
    handleEscapeClose = () => {
        if (this.options.escapeClose) {
            this.close()
        }
    }

    /**
     * Handles tab presses and contains focus within modal.
     *
     * @param {KeyboardEvent} e
     */
    handleTab = e => {
        const focusableContent = this.getFocusableContent()

        // Bail if no focusable content
        if (focusableContent.length === 0) return

        const firstFocusableElement = focusableContent[0]
        const lastFocusableElement = focusableContent[focusableContent.length - 1]

        // Check if using shift (reverse) and on first element
        if (e.shiftKey) {
            if (document.activeElement === firstFocusableElement) {
                lastFocusableElement.focus()
                e.preventDefault()
            }

            // Check if on last element
        } else {
            if (document.activeElement === lastFocusableElement) {
                firstFocusableElement.focus()
                e.preventDefault()
            }
        }
    }

}

export default KudosModal_OLD
