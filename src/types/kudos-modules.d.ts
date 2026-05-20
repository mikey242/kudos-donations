declare module '@kudos/types' {
	export type {
		BaseEntity,
		Transaction,
		Subscription,
		Donor,
		Campaign,
	} from '../types/entity';
	export type { BaseSettings } from '../types/settings';
	export type { LicenceStatus, LicenceStatusString } from '../types/licence';
	export type { WPResponse, WPErrorResponse } from '../types/wp';
}

declare module '@kudos/admin-controls' {
	export {
		ControlProps,
		BaseControl,
		CheckboxControl,
		ColorPicker,
		FormTokenField,
		RadioOption,
		RadioControl,
		RadioGroupOption,
		RadioGroupControl,
		RadioGroupControlBase,
		SelectControl,
		TextAreaControl,
		TextControl,
		ToggleControl,
	} from '../admin/controls';
}

declare module '@kudos/admin-components' {
	export {
		Panel,
		PanelRow,
		Spacer,
		DetailsModal,
		StatusConfig,
		StatusIcon,
		Pagination,
		Search,
		HeaderItem,
		Table,
		TableControls,
		ProviderSelector,
	} from '../admin/components';
	export type { Provider } from '../admin/components';
}

declare module '@kudos/admin-utils' {
	export {
		queryEntities,
		getLicenceStatus,
		isLicenceActive,
	} from '../admin/utils';
	export type { QueryArgs, EntityRestResponse } from '../admin/utils';
}

declare module '@kudos/admin-contexts' {
	export {
		AdminProvider,
		useAdminContext,
		EntitiesProvider,
		useEntitiesContext,
		SettingsProvider,
		useSettingsContext,
	} from '../admin/contexts';
	export { useFormContext, useWatch } from 'react-hook-form';
	export { useOnSettingsSaved } from '../admin/hooks';
}

declare module '@kudos/front-controls' {
	export {
		BaseController,
		Button,
		CheckboxControl,
		RadioGroupOption,
		RadioGroupControl,
		SelectOption,
		SelectControl,
		TextAreaControl,
		TextControl,
		ToggleControl,
	} from '../block/controls';
}

declare module '@kudos/front-components' {
	export {
		Message,
		KudosLogo,
		KudosLogoFullScreenAnimated,
		ProgressBar,
		Render,
	} from '../block/components';
}

declare module '@kudos/front-contexts' {
	export { CampaignProvider, useCampaignContext } from '../block/contexts';
}
