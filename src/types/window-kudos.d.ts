export interface KudosGlobal {
	styles?: string;
	stylesheets?: string[];
	baseFontSize?: string;
	currencies: Record<string, string>;
	[key: string]: unknown;
}

declare global {
	interface Window {
		kudos?: KudosGlobal;
	}
}