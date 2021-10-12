class KudosModal {

    constructor(modal, trigger = null, options = []) {
        this.isOpen = false
        this.modal = document.getElementById(modal)
        this.trigger = trigger
        this.closeModal = this.modal.querySelectorAll('[data-modal-close]')
        this.options = {
            timeOut: options.timeOut ?? 300,
            escapeClose: options.escapeClose ?? true,
            overlayClose: options.overlayClose ?? false,
            openClass: options.openClass ?? 'is-open',
            overlayClass: options.overlayClass ?? 'kudos-modal-overlay',
            onOpen: options.onOpen,
            onOpened: options.onOpened,
            onClose: options.onClose,
            onClosed: options.onClosed
        }
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
        if (this.trigger) {
            this.trigger.addEventListener("click", () => {
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

    open() {
        this.modal.setAttribute('aria-hidden', 'false')
        this.modal.classList.add(this.options.openClass)
        this.modal.querySelector('.kudos-modal-container').focus()

        // Add event listeners
        this.addEventListeners()

        // Create and dispatch event
        window.dispatchEvent(new CustomEvent('kudosOpenModal', {detail: this}))

        // Fire onOpen function
        if (typeof this.options.onOpen === 'function') {
            this.options.onOpen(this.modal)
        }

        // Timout
        setTimeout(() => {
            // Fire onOpened function
            if (typeof this.options.onOpened === 'function') {
                this.options.onOpened(this.modal)
            }
        }, this.options.timeOut)
    }

    close() {
        this.modal.setAttribute('aria-hidden', 'true')

        // Remove event listeners
        this.removeEventListeners()

        // Create and dispatch event
        window.dispatchEvent(new CustomEvent('kudosCloseModal', {detail: this}))

        // Fire onClose function
        if (typeof this.options.onClose === 'function') {
            this.options.onClose(this.modal)
        }

        // Timout
        setTimeout(() => {
            this.modal.classList.remove(this.options.openClass)

            // Fire onClose function
            if (typeof this.options.onClosed === 'function') {
                this.options.onClosed(this.modal)
            }
        }, this.options.timeOut)
    }

    addEventListeners() {
        window.addEventListener('keydown', this.handleKeyPress)
        window.addEventListener('click', this.handleClick)
    }

    removeEventListeners() {
        window.removeEventListener('keydown', this.handleKeyPress)
        window.removeEventListener('click', this.handleClick)
    }

    getFocusableContent() {
        return Array.from(this.modal.querySelectorAll(this.focusableElements)).filter((e) => {
            return (e.offsetParent !== null)
        })
    }

    handleClick(e) {
        if (e.target.classList.contains(this.options.overlayClass)) this.handleOverlayClose()
    }

    handleKeyPress(e) {
        if (e.key === 'Escape' || e.keyCode === 27) this.handleEscapeClose()
        if (e.key === 'Tab' || e.keyCode === 9) this.handleTab(e)
    }

    handleOverlayClose() {
        if (this.options.overlayClose) {
            this.close()
        }
    }

    handleEscapeClose() {
        if (this.options.escapeClose) {
            this.close()
        }
    }

    handleTab(e) {
        const focusableContent = this.getFocusableContent()

        // Bail if no focusable content
        if (focusableContent.length === 0) return

        const firstFocusableElement = focusableContent[0]
        const lastFocusableElement = focusableContent[focusableContent.length - 1]

        if (e.shiftKey) {
            if (document.activeElement === firstFocusableElement) {
                lastFocusableElement.focus()
                e.preventDefault()
            }
        } else {
            if (document.activeElement === lastFocusableElement) {
                firstFocusableElement.focus()
                e.preventDefault()
            }
        }
    }

}

export default KudosModal