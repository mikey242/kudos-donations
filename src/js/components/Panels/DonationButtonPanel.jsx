const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const {
    useState
} = wp.element;

import {TextInput} from "../FormElements/TextInput"
import {RadioImage} from "../FormElements/RadioImage"
import {PrimaryButton} from "../FormElements/PrimaryButton"

const DonationButtonPanel = props => {

    const [isEdited, setIsEdited] = useState(false);

    const handleChange = (option, value) => {
        setIsEdited(true)
        props.handleInputChange(option, value);
    }

    return (
        <PanelBody
            title={__('Donation Button', 'kudos-donations')}
            initialOpen={false}
        >
            <RadioImage
                id="_kudos_button_style"
                className={"component-kudos-images"}
                value={props._kudos_button_style}
                label={__('Button Style', 'kudos-donations')}
                onClick={props.updateSetting}
            >
                { [
                    { value: 'style-orange', content: <img alt={__('Orange', 'kudos-donations')} src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAAAyCAIAAACCil8SAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kb9LQlEUxz9pYpRhUUNDg4g1ZZhB1NKglAXVoAZZLfr8Fag93jMiWoNWoSBq6ddQf0GtQXMQFEUQTQ3NRS0lr/M0MCLP5dzzud97z+Hec8ESzSl5vdEH+UJRC4cCrrnYvMv+jA0L7XhxxxVdnY6MR6lrH3c0mPHGa9aqf+5fa0mmdAUamoRHFVUrCk8IT60WVZO3hTuVbDwpfCrcp8kFhW9NPVHlF5MzVf4yWYuGg2BpE3ZlfnHiFytZLS8sL8eTz60oP/cxX+JIFWYjEt3i3eiECRHAxSRjBBligBGZh6Q7fvplRZ18XyV/hmXJVWRWWUNjiQxZivSJuiLVUxLToqdk5Fgz+/+3r3p60F+t7giA7ckw3nrAvgXlkmF8HhpG+Qisj3BRqOUvH8Dwu+ilmubZB+cGnF3WtMQOnG9C14Ma1+IVySpuSafh9QRaY9BxDc0L1Z797HN8D9F1+aor2N2DXjnvXPwGce5n6w3W25IAAAAJcEhZcwAACxMAAAsTAQCanBgAAAJESURBVGiB7Zo/aBNRHMc/edaKkiIEhBRidMggKUKhUyQu7RAnF2lGF7s5GRFUENzEwXTRrcHFyTh1alGiYjTFQYKSgBARbZQDIfjnaiFGzyVGk5Le72xf3wXyWcLL+/K7D+HH+12OCziOQw9vF6k/5PNrvtRorffuGmWka1UvsHIF+70hGXf+0X15kxfXzZmIUO3PV7f87woEHMfhwyPun4YNTew/FK01nl4YCFdAsfqA75ZpDSmKd8umHTygaFRMO3hAsf7JtIMHFD++mXbwgHKP+Imhrk4GTHfEPbIJp54wdvjvsrXGnSOb5UNxYrOEE4QmABoVKgu8uSe/4NZ05QQjJLOEE11fhiY4Ps94guJ5YZkdaYZoipNLva4dYum+WxvQrxubZXqB0f0uGRmadaMpkln3WPCgsJ5O3WCE5I3tLalTdzrn0gMd7FVhSW26kxlCcWm4lhcG9egGI0yek4arOaySMKtHNzkvTVZzPL8qL2x6CI+OeYrr0S2KOyGW9nR66NG165TF/eCLqVbO0qhKw76YaoUzNL+Kkr6Yanadwtz2ltR8Mlglihn3mPmp1qGWZynt0hWGp1oPVonFVF+nWl4+1bb2b+Jnk1/NrmU/7DrFDOUs8TnCifbthLVC7a78pwUCzu3If9vuPKaHsEeGujoZ6upEsdvbHadZFHsPmHbwgGo/rhoQFIdSph08oIieYN+4aQ0pil17OHbNtIYUBRCZYeqiaRMRf87do2eZumTURESg6/WLj495dtnP7zP8Bq4Aja10468CAAAAAElFTkSuQmCC"/> },
                    { value: 'style-green', content: <img alt={__('Green', 'kudos-donations')} src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAAAyCAIAAACCil8SAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kb9LQlEUxz9pYpRhUUNDg4g1ZZhB1NKglAXVoAZZLfr8Fag93jMiWoNWoSBq6ddQf0GtQXMQFEUQTQ3NRS0lr/M0MCLP5dzzud97z+Hec8ESzSl5vdEH+UJRC4cCrrnYvMv+jA0L7XhxxxVdnY6MR6lrH3c0mPHGa9aqf+5fa0mmdAUamoRHFVUrCk8IT60WVZO3hTuVbDwpfCrcp8kFhW9NPVHlF5MzVf4yWYuGg2BpE3ZlfnHiFytZLS8sL8eTz60oP/cxX+JIFWYjEt3i3eiECRHAxSRjBBligBGZh6Q7fvplRZ18XyV/hmXJVWRWWUNjiQxZivSJuiLVUxLToqdk5Fgz+/+3r3p60F+t7giA7ckw3nrAvgXlkmF8HhpG+Qisj3BRqOUvH8Dwu+ilmubZB+cGnF3WtMQOnG9C14Ma1+IVySpuSafh9QRaY9BxDc0L1Z797HN8D9F1+aor2N2DXjnvXPwGce5n6w3W25IAAAAJcEhZcwAACxMAAAsTAQCanBgAAAKRSURBVGiB7ZrPb9JgGMfLK+GHjBFLbJPaqTFDtkRJdOxgYjTE+7h58raLXnR/gBizLTHxtGSHedCbu3gbGmO8GCMaTVhMthm2lSmbULOOwQC7DrTgAcMQhT4NvntLwucCL3zz8CG87dOH1FSpVKg/eZH+FsluxeVCQpGVskoZCXP94k12697ap1RRIWWjyb7uw+Ta9PoqQRUIqPrwKPnZ+K5UVfftTnp6fYW0CQi0q6p3hcXGw82ooNdZSSrtkdaAgl5tb5J20AFalvOkHXSAtktF0g46QN/Vn6QddIBIC+ijq4uTDtM1a0ea82zocp/tcG25q6oX3r9skfc6nCMM73fRA45eiqKW5fxjMfFUSsE/sS1dOJzVPu7xDbvo+hcHHL2THt+wi74jLALrHMRmCNDsk3MXG1xrBBne3+Stv8GuO8Icmxo87zzU6mcMMjywGl7dAM1OeHyaMc5mBxbEqMtZ7ROntV11gVF3anCo9R6oIe5Bp0NcujeOe7wOJzA8JyWBSSy6nNV+va8fGJ4VE9FcBhjGogvfsrNi4v6XGLwy4SbcY9bXp7DohlYXgMkgw497zsIrY9EVi8qDr3Fg2BBdbWZDWJELwLAhutpYbL4Am6wM0dXEojIWm/+/NfGeGaK5TEjQPuzId7UaYSk1uvSh9a4g3NUaiOYyVz9Gwk2mhrCUgne1tqaJUrn8o1LeX9Y9b0AsKiFhYWZDuMad9Lvc1cuJaD4zt5ls9jX+ickXed6O8QHTYZNwVxcnXV2coB7YOGUQkNtiJe2gA1T9u6pTQAE3S9pBB+iKm2UtNtIaUJDFhG73nyGtAQVRFHXpyNGbJ7ykTUD8Pu+O8qdudYKxqf72i3c76cn4kpHvZ/gFXzjMlNqW1W8AAAAASUVORK5CYII="/> },
                ] }
            </RadioImage>

            <TextInput
                id='_kudos_button_label'
                label="Button label"
                value={props._kudos_button_label}
                placeHolder='Button label'
                disabled={props.isSaving}
                onChange={handleChange}
            />

            <PrimaryButton
                label="Save"
                disabled={!isEdited || props.isSaving}
                isBusy={props.isSaving}
                onClick={()=> {
                    props.updateSetting('_kudos_button_label', props._kudos_button_label)
                    setIsEdited(false)
                }}
            />

        </PanelBody>
    )
}

export {DonationButtonPanel}