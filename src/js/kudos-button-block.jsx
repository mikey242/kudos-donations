import logo from '../images/logo-colour.svg'
import {KudosButton} from "./Settings/Components/KudosButton"

/**
 * Internal block libraries
 */
const {__} = wp.i18n
const {Component} = wp.element
const {registerBlockType} = wp.blocks
const {
    PanelBody,
    SelectControl,
    RadioControl
} = wp.components
const {
    RichText,
    BlockControls,
    AlignmentToolbar,
    InspectorControls,
} = wp.blockEditor

/**
 * Register block
 */
export default registerBlockType('iseardmedia/kudos-button', {
    // Block Title
    title: __('Kudos Button', 'kudos-donations'),
    // Block Description
    description: __(
        'Adds a Kudos donate button to your post or page.',
        'kudos-donations'
    ),
    // Block Category
    category: 'widgets',
    // Block Icon
    icon: <img width="30" src={logo} alt="Kudos Logo"/>,
    // Block Keywords
    keywords: [
        __('Kudos', 'kudos-donations'),
        __('Button', 'kudos-donations'),
        __('Donate', 'kudos-donations'),
    ],
    example: {
        attributes: {
            label: __('Donate now!', 'kudos-donations'),
            alignment: 'center',
        },
    },

    // Defining the edit interface
    edit: class extends Component {

        constructor() {
            super()
            this.onChangeButtonLabel = this.onChangeButtonLabel.bind(this)
            this.onChangeAlignment = this.onChangeAlignment.bind(this)
            this.onChangeCampaign = this.onChangeCampaign.bind(this)
            this.onChangeType = this.onChangeType.bind(this)
            this.state = {
                campaigns:
                    [{
                        value: '',
                        label: '',
                        disabled: true
                    }]

                ,
                selectedCampaign: ''
            }
        }

        componentDidMount() {
            this.getCampaigns()
        }

        onChangeButtonLabel(newValue) {
            this.props.setAttributes({button_label: newValue})
        };

        onChangeAlignment(newValue) {
            this.props.setAttributes({
                alignment: newValue === undefined ? 'none' : newValue,
            })
        };

        onChangeCampaign(newValue) {
            this.props.setAttributes({campaign_id: newValue})
            this.setState({
                selectedCampaign: newValue
            })
        };

        onChangeType(newValue) {
            console.log(newValue)
            this.props.setAttributes({type: newValue})
        }

        getCampaignName(value) {
            let campaign = this.state.campaigns.find(campaign => campaign.value === value)
            return campaign ? campaign.value ? campaign.label : '' : 'Unknown (' + value + ')'
        }

        getCampaigns() {
            wp.api.loadPromise.then(() => {
                new wp.api.models.Settings().fetch().then((response) => {
                    let options = response._kudos_campaigns.map(campaign => ({
                        value: campaign.id,
                        label: campaign.name
                    }))
                    this.setState({
                        campaigns: [...this.state.campaigns, ...options],
                    })
                })
            })
        };

        render() {

            return (

                <div>
                    <InspectorControls>

                        <PanelBody
                            title={__('Campaign', 'kudos-donations')}
                            initialOpen={false}
                        >
                            <p><strong>Current
                                campaign: {this.getCampaignName(this.props.attributes.campaign_id)}</strong></p>
                            <SelectControl
                                label={__('Select a campaign', 'kudos-donations')}
                                value={this.state.selectedCampaign}
                                onChange={this.onChangeCampaign}
                                options={this.state.campaigns}
                            />
                            <a href="admin.php?page=kudos-settings&tab_name=campaigns">{__('Create a new campaign here', 'kudos-donations')}</a>

                        </PanelBody>

                        <PanelBody
                            title={__('Options', 'kudos-donations')}
                            initialOpen={false}
                        >
                            <RadioControl
                                label={__('Display type', 'kudos-donations')}
                                selected={this.props.attributes.type}
                                options={[
                                    {label: __('Button', 'kudos-donations'), value: 'button'},
                                    {label: __('Form', 'kudos-donations'), value: 'form'}
                                ]}
                                onChange={this.onChangeType}
                            />

                        </PanelBody>

                    </InspectorControls>

                    <BlockControls>
                        <AlignmentToolbar
                            value={this.props.attributes.alignment}
                            onChange={this.onChangeAlignment}
                        />
                    </BlockControls>

                    <KudosButton
                        className={(this.props.attributes.className ?? '') + ' has-text-align-' + this.props.attributes.alignment}
                    >
                        <RichText
                            formattingControls={[
                                'bold',
                                'italic',
                                'text-color',
                                'strikethrough',
                            ]}
                            onChange={this.onChangeButtonLabel}
                            value={this.props.attributes.button_label}
                        />
                    </KudosButton>

                </div>
            )
        }
    },

    // Defining the front-end interface
    save: () => {
        return null
    },
})
