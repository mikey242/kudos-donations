import CodeMirror from 'codemirror';
import type { LicenceStatusString } from './licence';

export interface KudosNotice {
	id: string;
	status: 'success' | 'error' | 'info' | 'warning';
	content: string;
	isDismissible: boolean;
	type: 'default' | 'snackbar';
}

export interface KudosAdminData {
	notices?: KudosNotice[];
	needsUpgrade?: boolean;
	version?: string;
	codeEditor?: CodeEditorSettings;
	api?: {
		Controls: typeof import('../admin/controls');
		Components: typeof import('../admin/components');
		Contexts: typeof import('../admin/contexts');
		getLicenceStatus: () => Promise<LicenceStatusString>;
	};
	[key: string]: unknown;
}

export interface KudosFrontData {
	stylesheets?: string[];
	customStyles?: string;
	baseFontSize?: string;
	api?: {
		Controls: typeof import('../block/controls');
		Components: typeof import('../block/components');
		Contexts: typeof import('../block/contexts');
	};
	[key: string]: unknown;
}

export interface KudosGlobal {
	currencies: Record<string, string>;
	countries: Record<string, string>;
	env?: string;
	isLicenceActive: boolean;
	isAddonInstalled: boolean;
	debug?: boolean;
	admin?: KudosAdminData;
	front?: KudosFrontData;
	screencastMode?: boolean;
	[key: string]: unknown;
}

export interface CodeEditorSettings {
	codemirror: CodeMirror.EditorConfiguration;
}

export interface CodeEditorInstance {
	codemirror: CodeMirror.Editor;
}

declare global {
	interface Window {
		kudos?: KudosGlobal;
		wp: {
			codeEditor: {
				initialize: (
					id: string,
					settings: CodeEditorSettings
				) => CodeEditorInstance;
			};
		};
	}
}
