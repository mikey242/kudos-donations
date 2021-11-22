import {Component} from "@wordpress/element"
import Modal from "react-modal"
import {KudosButton} from "./KudosButton"
import KudosFrame from "./KudosFrame"

Modal.setAppElement('#kudos-donations')

class KudosModal extends Component {

    constructor() {
        super()

        this.state = {
            showModal: false,
            color: {
                primary: '#ff9f1c',
                primaryDark: '#f0961b',
                secondary: '#2ec4b6',
                secondaryDark: '#2cb9ac'
            }
        }

        this.handleOpenModal = this.handleOpenModal.bind(this)
        this.handleCloseModal = this.handleCloseModal.bind(this)
    }

    resize(obj) {
        obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px'
    }

    componentDidMount() {
        console.log('Component mounted')
    }

    handleOpenModal() {
        this.setState({showModal: true})
    }

    handleCloseModal() {
        this.setState({showModal: false})
    }

    render() {
        return (
            <KudosFrame
                color={this.state.color}
            >
                <KudosButton
                    onClick={this.handleOpenModal}>
                    Donate Now!
                </KudosButton>
                <Modal
                    isOpen={this.state.showModal}
                    contentLabel="Donate Modal"
                >
                    <button onClick={this.handleCloseModal}>Close Modal</button>
                </Modal>
            </KudosFrame>
        )
    }

}

export default KudosModal