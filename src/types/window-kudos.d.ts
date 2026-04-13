import CodeMirror from 'codemirror';
import type { LicenceStatusString } from './licence';
export interface KudosNotice {
	id: string;
	status: 'success' | 'error' | 'info' | 'warning';
	content: string;
	isDismissible: boolean;
	type: 'default' | 'snackbar';
}

export interface KudosGlobal {
	notices?: KudosNotice[];
	isLicenceActive: boolean;
	isAddonInstalled: boolean;
	getLicenceStatus: () => Promise<LicenceStatusString>;
	customStyles?: string;
	stylesheets?: string[];
	baseFontSize?: string;
	currencies: Record<string, string>;
	version?: string;
	codeEditor: CodeEditorSettings;
	needsUpgrade: boolean;
	countries: Record<string, string>;
	env?: string;
	admin: {
		Controls: typeof import('../admin/controls');
		Components: typeof import('../admin/components');
		Contexts: typeof import('../admin/contexts');
	};
	front: {
		Controls: typeof import('../block/controls');
		Components: typeof import('../block/components');
		Contexts: typeof import('../block/contexts');
	};
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