export interface WPResponse {
	message: string;
}

export interface WPErrorResponse extends WPResponse {
	code: string;
	data?: {
		status?: number;
		[key: string]: any;
	};
}
