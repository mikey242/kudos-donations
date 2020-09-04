const { Guide, GuidePage } = wp.components;
const { useState } = wp.element;
const { __ } = wp.i18n;

const IntroGuide = ( props ) => {

        const [ isOpen, setIsOpen ] = useState( props.open );

        const handleClose = () => {
            setIsOpen(false)
            props.saveSetting('_kudos_show_intro', false)
        }

        if ( ! isOpen ) {
            return null;
        }

        return (
            <Guide
                onFinish={ () => handleClose() }
            >
                <GuidePage>
                    <img alt="" width="600" src="https://gitlab.iseard.media/michael/kudos-donations/-/raw/master/assets/demo-1.gif" />

                </GuidePage>
            </Guide>
        );

};

export { IntroGuide };
