declare module '@kudos/admin-controls' {
	export * from '../admin/components/controls';
}

declare module '@kudos/admin-components' {
	export { Panel, PanelRow } from '../admin/components/Panel';
}

declare module '@kudos/admin-hooks' {
	export { useSettingsContext } from '../admin/contexts/settings-context';
	export { useFormContext } from 'react-hook-form';
}