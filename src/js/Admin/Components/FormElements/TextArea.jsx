import _uniqueId from 'lodash/uniqueId';

const { PanelRow, BaseControl, TextareaControl } = wp.components;

const TextArea = ( props ) => {
	return (
		<PanelRow>
			<BaseControl
				label={ props.label }
				id={ _uniqueId( props.label ) }
				help={ props.help }
			>
				<TextareaControl
					key={ 'key_' + props.id }
					id={ props.id }
					type={ props.type || 'text' }
					value={ props.value }
					placeholder={ props.placeholder }
					disabled={ props.disabled }
					onChange={ ( value ) => props.onChange( props.id, value ) }
				/>
			</BaseControl>
		</PanelRow>
	);
};

export { TextArea };
