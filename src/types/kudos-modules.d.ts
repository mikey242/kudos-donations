declare module '@kudos/admin-controls' {
	export {BaseControl} from '../admin/components/controls'
	export {CheckboxControl} from '../admin/components/controls'
	export {ColorPicker} from '../admin/components/controls'
	export {FormTokenField} from '../admin/components/controls'
	export {RadioControl} from '../admin/components/controls'
	export {RadioGroupControl} from '../admin/components/controls'
	export {SelectControl} from '../admin/components/controls'
	export {TextAreaControl} from '../admin/components/controls'
	export {TextControl} from '../admin/components/controls'
	export {ToggleControl} from '../admin/components/controls'
}

declare module '@kudos/admin-components' {
	export { Panel, PanelRow } from '../admin/components/Panel';
	export {
		SLOT_HEADER_ACTIONS,
		SLOT_HEADER_ACTIONS_EXTRA,
		SLOT_PAGE_TITLE,
		HeaderActionsFillProps,
	} from '../admin/components/AdminHeader';
}

declare module '@kudos/admin-hooks' {
	export { useAdminContext } from '../admin/contexts/admin-context';
	export { useSettingsContext } from '../admin/contexts/settings-context';
	export { useEntitiesContext } from '../admin/contexts/entities-context';
	export { useFormContext } from 'react-hook-form';
}