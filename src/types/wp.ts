export interface Post {
	id: number;
	slug: string;
	title: { rendered: string; raw: string };
	content: { rendered: string };
	excerpt: { rendered: string };
	status: string;
	type: string;
	link: string;
	meta?: Record<string, unknown>;
	[key: string]: unknown;
}
