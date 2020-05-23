/* eslint-disable camelcase */
/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
    BaseControl,
    Button,
    ExternalLink,
    PanelBody,
    PanelRow,
    Placeholder,
    Spinner,
    Notice,
    ToggleControl,
    TabPanel,
    RadioControl
} = wp.components;

const {
    render,
    Component,
    Fragment
} = wp.element;

/**
 * Internal dependencies
 */
import '../scss/kudos-admin-react.scss';

class App extends Component {
    constructor() {
        super( ...arguments );

        this.changeOptions = this.changeOptions.bind( this );

        this.state = {
            isAPILoaded: false,
            isAPISaving: false,
            kd_mollie_api_mode: '',
            kd_mollie_test_key: '',
            kd_mollie_live_key: ''
        };
    }

    componentDidMount() {
        wp.api.loadPromise.then( () => {
            this.settings = new wp.api.models.Settings();

            if ( false === this.state.isAPILoaded ) {
                this.settings.fetch().then( response => {
                    this.setState({
                        kd_mollie_api_mode: response.kd_mollie_api_mode,
                        kd_mollie_test_key: response.kd_mollie_test_key,
                        kd_mollie_live_key: response.kd_mollie_live_key,
                        isAPILoaded: true
                    });
                });
            }
        });
    }

    changeOptions( option, value ) {
        this.setState({ isAPISaving: true });

        const model = new wp.api.models.Settings({
            // eslint-disable-next-line camelcase
            [option]: value
        });

        model.save().then( response => {
            this.setState({
                [option]: response[option],
                isAPISaving: false
            });
        });
    }

    render() {

        return (
            <Fragment>
                <div className="codeinwp-header">
                    <div className="codeinwp-container">
                        <div className="codeinwp-logo">
                            <h1>{ __( 'Kudos Settings' ) }</h1>
                        </div>
                        <TabPanel className="my-tab-panel"
                                  activeClass="active-tab"

                                  tabs={ [
                                      {
                                          name: 'tab1',
                                          title: 'Tab 1',
                                          className: 'tab-one',
                                      },
                                      {
                                          name: 'tab2',
                                          title: 'Tab 2',
                                          className: 'tab-two',
                                      },
                                  ] }>
                            {
                                ( tab ) => <p>{ tab.title }</p>
                            }
                        </TabPanel>
                    </div>
                </div>

                { ! this.state.isAPILoaded ? [
                    <Placeholder key='kudos-loader'>
                        <Spinner/>
                    </Placeholder>
                ] : [

                    <div className="codeinwp-main" key='kudos-settings'>
                        <PanelBody
                            title={ __( 'Mollie' ) }
                        >
                            <PanelRow>
                                <RadioControl
                                    label={__('Mollie API Mode', 'kudos-donations')}
                                    help={__('When using this plugin for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you\'re ready to receive live payments you can switch the mode to "Live".', 'kudos-donations')}
                                    selected ={this.state.kd_mollie_api_mode}
                                    options={[
                                        { label: 'Test', value: 'test' },
                                        { label: 'Live', value: 'live' }
                                    ]}
                                    onChange={ value => this.changeOptions( 'kd_mollie_api_mode', value ) }
                                />
                            </PanelRow>

                            <PanelRow>
                                <BaseControl
                                    label={ __( 'Mollie Test API Key' ) }
                                    className="codeinwp-text-field"
                                >
                                    <input
                                        type="text"
                                        id="kd_mollie_test_key"
                                        value={ this.state.kd_mollie_test_key }
                                        placeholder={ __( 'Mollie Test API Key' ) }
                                        disabled={ this.state.isAPISaving || this.state.kd_mollie_api_mode !== 'test' }
                                        onChange={ e => this.setState({ kd_mollie_test_key: e.target.value }) }
                                    />
                                </BaseControl>
                            </PanelRow>

                            <PanelRow>
                                <BaseControl
                                    label={ __( 'Mollie Live API Key' ) }
                                    className="codeinwp-text-field"
                                >
                                    <input
                                        type="text"
                                        id="kd_mollie_live_key"
                                        value={ this.state.kd_mollie_live_key }
                                        placeholder={ __( 'Mollie Live API Key' ) }
                                        disabled={ this.state.isAPISaving || this.state.kd_mollie_api_mode !== 'live' }
                                        onChange={ e => this.setState({ kd_mollie_live_key: e.target.value }) }
                                    />

                                    <div className="codeinwp-text-field-button-group">
                                        <Button
                                            isPrimary
                                            isLarge
                                            disabled={ this.state.isAPISaving }
                                            onClick={ () => {
                                                this.changeOptions('kd_mollie_test_key', this.state.kd_mollie_test_key);
                                                this.changeOptions('kd_mollie_live_key', this.state.kd_mollie_live_key);
                                            }}
                                        >
                                            { __( 'Save' ) }
                                        </Button>

                                        <ExternalLink href="#">
                                            { __( 'Get API Key' ) }
                                        </ExternalLink>
                                    </div>
                                </BaseControl>
                            </PanelRow>
                        </PanelBody>

                        <PanelBody>
                            <div className="codeinwp-info">
                                <h2>{ __( 'Got a question for us?' ) }</h2>

                                <p>{ __( 'We would love to help you out if you need any help.' ) }</p>

                                <div className="codeinwp-info-button-group">
                                    <Button
                                        isSecondary
                                        isLarge
                                        target="_blank"
                                        href="#"
                                    >
                                        { __( 'Ask a question' ) }
                                    </Button>

                                    <Button
                                        isSecondary
                                        isLarge
                                        target="_blank"
                                        href="#"
                                    >
                                        { __( 'Leave a review' ) }
                                    </Button>
                                </div>
                            </div>
                        </PanelBody>
                    </div>
                ]}
            </Fragment>
        );
    }
}

render(
    <App/>,
    document.getElementById( 'codeinwp-awesome-plugin' )
);