import { RadioImage } from "../FormElements/RadioImage";
import { TextInput } from "../FormElements/TextInput"

const { __ } = wp.i18n;
const { PanelBody } = wp.components;
const { Fragment } = wp.element;

const DonationAmountPanel = ( props ) => {

    if(!props.settings._kudos_subscription_level >= 1) {
        return null;
    }

    return (
        <PanelBody
            title={ __( 'Donation Amount', 'kudos-donations' ) }
            initialOpen={ false }
        >
            <RadioImage
                isPrimary
                className="components-kudos-toggle"
                id="_kudos_amount_type"
                value={ props.settings._kudos_amount_type }
                label={ __( 'Type', 'kudos-donations' ) }
                help={ __(
                    'Use either fixed amounts or leave the field open to allow donors to choose how much to donate.',
                    'kudos-donations'
                ) }
                onClick={ props.handleInputChange }
            >
                { [
                    { value: 'fixed', content: 'Fixed' },
                    { value: 'open', content: 'Open' },
                ] }
            </RadioImage>

            { props.settings._kudos_amount_type === 'fixed'
                ? [
                    <Fragment key="_kudos_fixed_amounts">
                        <TextInput
                            id="_kudos_fixed_amounts"
                            label={ __(
                                'Amounts:',
                                'kudos-donations'
                            ) }
                            help={ __( 'Enter a comma separated list of values to use.', 'kudos-donations' ) }
                            value={ props.settings._kudos_fixed_amounts }
                            disabled={ props.isSaving }
                            onChange={ props.handleInputChange }
                        />
                    </Fragment>,
                ]
                : '' }

        </PanelBody>
    );
};

export { DonationAmountPanel };
