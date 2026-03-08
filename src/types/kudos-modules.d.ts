declare module '@kudos/admin-controls' {
	export * from '../admin/components/controls';
}

declare module '@kudos/admin-components' {
	export { Panel, PanelRow } from '../admin/components/Panel';
	export {
		SLOT_HEADER_ACTIONS,
		HeaderActionsFillProps,
	} from '../admin/components/AdminHeader';
}

declare module '@kudos/admin-hooks' {
	export { useAdminContext } from '../admin/contexts/admin-context';
	export { useSettingsContext } from '../admin/contexts/settings-context';
	export { useEntitiesContext } from '../admin/contexts/entities-context';
	export { useFormContext } from 'react-hook-form';
}