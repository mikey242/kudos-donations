const { __ } = wp.i18n;

const KudosButton = props => {

    return (
        <div className={props.className + " has-text-align-" + props.alignment}>
            <button
                style={{backgroundColor: props.style}}
                className={'kudos_button kudos_button_donate'}
                data-custom-header={ props.header }
                data-custom-text={ props.body }>
                {props.label}
            </button>
        </div>
    )
}

export {KudosButton}