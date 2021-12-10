import {Button} from "./Button"

const Form = (props) => {

    const {title, description, children} = props
    const {onSubmit, next} = props

    return (

            <form
                onSubmit={onSubmit}
                className="form-tab block w-full relative m-0 p-0 border-0">
                <legend className="block m-auto">
                    <h2 className="kudos_modal_title font-normal font-serif text-4xl m-0 mb-2 text-gray-900 block text-center">
                        {title}
                    </h2>
                </legend>

                <p className="text-lg text-gray-900 text-center block font-normal mb-4">
                    {description}
                </p>
                {children}
                <div className="kudos-modal-buttons mt-8 flex justify-between relative">
                    <Button
                        type="submit"
                        onClick={next}
                        className="ml-auto bg-next bg-no-repeat bg-8 bg-right-2 pr-[2.5em]"
                    >Next</Button>
                </div>
            </form>
    )
}

export {Form}