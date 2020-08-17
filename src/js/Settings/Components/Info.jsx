const { PanelRow, Dashicon } = wp.components;

const Info = ( props ) => {

    return (

        <PanelRow>

            <div className="kudos-admin-info">
                <Dashicon icon={ props.icon ?? "info" } />
                    <i>{ props.children }</i>
            </div>

        </PanelRow>

    );

};

export { Info };
