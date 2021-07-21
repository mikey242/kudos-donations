// Defining the edit interface
import {Component} from "@wordpress/element"
import {AlignmentToolbar, BlockControls, InspectorControls, RichText, useBlockProps} from "@wordpress/block-editor"
import {PanelBody, RadioControl, SelectControl} from "@wordpress/components"
import {__} from "@wordpress/i18n"
import api from '@wordpress/api';
import {KudosButton} from "../Components/KudosButton"

class ButtonEdit extends Component {

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
        this.props.setAttributes({type: newValue})
    }

    getCampaignName(value) {
        let campaign = this.state.campaigns.find(campaign => campaign.value === value)
        return campaign ? campaign.value ? campaign.label : '' : 'Unknown (' + value + ')'
    }

    getCampaigns() {
        api.loadPromise.then(() => {
            new api.models.Settings().fetch().then((response) => {
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
                        allowedFormats={[
                            'core/bold',
                            'core/italic',
                            'core/text-color',
                            'core/strikethrough',
                        ]}
                        onChange={this.onChangeButtonLabel}
                        value={this.props.attributes.button_label}
                    />
                </KudosButton>
            </div>

        )
    }
}

export default function Edit(props) {
    return(
        <div {...useBlockProps()}>
            <ButtonEdit {...props}/>
        </div>
    )
}