import CodeMirror from 'codemirror';
export interface KudosGlobal {
	styles?: string;
	stylesheets?: string[];
	baseFontSize?: string;
	currencies: Record<string, string>;
	version: string;
	codeEditor: CodeEditorSettings;
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
				initialize: (id: string, settings: CodeEditorSettings) => CodeEditorInstance;
			}
		};
	}
}