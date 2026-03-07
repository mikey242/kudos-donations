import CodeMirror from 'codemirror';
export interface KudosGlobal {
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
		Controls: typeof import('../admin/components/controls');
		Components: {
			Panel: typeof import('../admin/components/Panel').Panel;
		};
		Hooks: {
			useSettingsContext: typeof import('../admin/contexts/settings-context').useSettingsContext;
			useFormContext: typeof import('react-hook-form').useFormContext;
		};
	}
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
