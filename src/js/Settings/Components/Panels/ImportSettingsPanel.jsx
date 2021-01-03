const { __ } = wp.i18n;
const { PanelBody, FormFileUpload, BaseControl } = wp.components;

const ImportSettingsPanel = (props) => {

    const importSettings = (e) => {

        let file = e.target.files[0]
        if(file) {
            const reader = new FileReader()
            reader.readAsText(file)
            reader.onload = function(event) {
                let contents = event.target.result.toString()
                let obj = JSON.parse(contents)
                Object.keys(obj).forEach(function (k) {
                    if(obj[k] !== '') {
                        props.handleInputChange(k, obj[k])
                    }
                })
                props.updateAll()
            }
        }
    };


    return (
        <PanelBody
            title={ __( 'Import settings', 'kudos-donations' ) }
            initialOpen={ false }
        >
            <BaseControl
                id="import-1"
                help={__( 'Warning: this will overwrite your current settings.', 'kudos-donations' )}
            >
                <FormFileUpload
                    accept="text/*"
                    className={ "is-primary" }
                    onChange={ (e) => importSettings(e) }
                >
                    Upload
                </FormFileUpload>
            </BaseControl>
        </PanelBody>
    );
};

export { ImportSettingsPanel };
