import {Button} from "./form/Button"
import {Initial} from "./form/Initial"
import {PaymentFrequency} from "./form/PaymentFrequency"
import {Form} from "./form/Form"
import {Address} from "./form/Address"

const KudosForm = (props) => {

    const {step, title, description, campaign, formData} = props
    const {next, prev, handleInput} = props

    const getStep = (step) => {
        switch (step) {
            case 1:
                return (
                    <Initial
                        title={title}
                        description={description}
                        next={next}
                        handleInput={handleInput}
                        values={formData}
                    />
                )
            case 2:
                return (
                    <PaymentFrequency
                        title={title}
                        description={description}
                        next={next}
                        handleInput={handleInput}
                        values={formData}
                    />
                )
            case 3:
                return (
                    <Address
                        title={title}
                        description={description}
                        next={next}
                        handleInput={handleInput}
                        values={formData}
                    />
                )
            case 4:
                return (
                    <p>Step 4</p>
                )
            default:
                return ('')
            // do nothing
        }
    }

    return (
        <>
            {getStep(step)}
            {step > 1 &&
                <Button
                    type="button"
                    onClick={prev}
                >Prev</Button>
            }
        </>
    )

}

export {KudosForm}