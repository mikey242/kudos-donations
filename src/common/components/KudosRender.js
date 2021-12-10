import {getStyle} from "../helpers/util"
import apiFetch from "@wordpress/api-fetch"
import {Component} from "@wordpress/element"
import {KudosButton} from "./KudosButton"
import {KudosModal} from "./KudosModal"
import ReactShadowRoot from 'react-shadow-root'
import {KudosForm} from "./KudosForm"
import React from "react";

let screenSize = getStyle('--kudos-screen')

class KudosRender extends Component {

    constructor(props) {
        super()

        this.state = {
            modalOpen: false,
            modalClosing: false,
            currentStep: 1,
            skipSteps: [],
            formData: {
                value: "",
                name: "",
                email: "",
                payment_frequency: "oneoff"
            }
        }

        this.label = props.label

        this.toggleModal = this.toggleModal.bind(this)
        this.handleInput = this.handleInput.bind(this)
    }

    toggleModal() {
        const {modalOpen} = this.state

        // Open modal
        if (!modalOpen) {
            this.createEventListeners()
            this.setState({
                modalOpen: true,
            })
        } else {
            // Close modal
            this.removeEventListeners()
            this.setState({
                modalOpen: false,
                modalClosing: true,
            })
            setTimeout(() => {
                this.setState({
                    modalClosing: false,
                    step: 1
                })
            }, 300)
        }
    }

    componentDidMount() {
        this.getCampaign()
    }

    getCampaign() {
        apiFetch({
            path: 'kudos/v1/campaign/get?' + new URLSearchParams({id: 'default'}),
        }).then((response) => {
            this.setState({
                campaign: response,
                ready: true
            })
        })
    }

    createEventListeners() {
        document.addEventListener("keydown", this.handleKeyPress, false)
    }

    removeEventListeners() {
        document.removeEventListener("keydown", this.handleKeyPress, false)
    }

    prevStep = () => {
        const {currentStep, skipSteps} = this.state
        const step = skipSteps.includes(currentStep - 1) ? currentStep - 2 : currentStep - 1
        this.setState({currentStep: step})
    }

    nextStep = () => {
        const {currentStep, skipSteps} = this.state
        const step = skipSteps.includes(currentStep + 1) ? currentStep + 2 : currentStep + 1
        this.setState({currentStep: step})
    }

    addSkip = (step) => {
        const {skipSteps} = this.state
        if(skipSteps.indexOf(step) === -1) {
            skipSteps.push(step)
            this.setState({ skipSteps: skipSteps })
        }
    }

    removeSkip = (step) => {
        const {skipSteps} = this.state
        const index = skipSteps.indexOf(step)
        if(index > -1) {
            console.log('removing')
            skipSteps.splice(index, 1)
            this.setState({
                skipSteps: skipSteps
            })
        }
    }

    handleInput = (data) => {

        data.payment_frequency === "recurring" ?
            this.removeSkip(2) :
            this.addSkip(2)

        this.setState(
            {
                formData: {
                    ...this.state.formData,
                    ...data
                }
            }
        )
        console.log(this.state)
    }

    handleKeyPress = e => {
        if (e.key === 'Escape' || e.keyCode === 27) this.toggleModal()
    }

    render() {

        const style = `
            :host { all: initial }
        `

        return (
            <ReactShadowRoot>
                <link rel="stylesheet" href="/wp-content/plugins/kudos-donations/dist/public/kudos-public.css"/>
                <style>{style}</style>
                <KudosButton onClick={this.toggleModal}>
                    {this.label}
                </KudosButton>
                {this.state.ready &&
                    <KudosModal
                        toggle={this.toggleModal}
                        isOpen={this.state.modalOpen}
                        isClosing={this.state.modalClosing}
                    >
                        <KudosForm
                            step={this.state.currentStep}
                            next={this.nextStep}
                            prev={this.prevStep}
                            campaign={this.state.campaign}
                            handleInput={this.handleInput}
                            formData={this.state.formData}
                            title={this.state.campaign.modal_title}
                            description={this.state.campaign.welcome_text}
                        />
                    </KudosModal>
                }
            </ReactShadowRoot>
        )
    }
}

export default KudosRender