const { Guide } = wp.components;
const { useState } = wp.element;
const { __ } = wp.i18n;

const IntroGuide = ( props ) => {

    const [ isOpen, setIsOpen ] = useState( props.open );

    if ( ! isOpen ) {
        return null;
    }

    const handleClose = () => {
        setIsOpen(false)
        if(props.saveSetting) {
            props.saveSetting('_kudos_show_intro', false)
        }
    }

    return (
        <Guide
            onFinish={ () => handleClose() }
            pages={[
                {
                    image: <img alt="" width="600" src="https://gitlab.iseard.media/michael/kudos-donations/-/raw/master/assets/demo-1.gif" />,
                    content: <h1 className="intro-guide__heading">Welcome!</h1>
                }
            ]}
        >
        </Guide>
    );

};

export { IntroGuide };
