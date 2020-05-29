const { __ } = wp.i18n;

const KudosButton = props => {

    return (
        <div className={props.className + " has-text-align-" + props.alignment}>
            <button
                className={'kudos_button kudos_button_donate ' + props.style }
                data-custom-header={ props.header }
                data-custom-text={ props.text }>
                {props.label}
            </button>
        </div>
    )
}

export {KudosButton}