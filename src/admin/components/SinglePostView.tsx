import React from 'react';
import { useCallback, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useAdminContext, usePostsContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import type { Post } from '../../types/posts';
import {
	Button,
	Flex,
	FlexItem,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { Panel } from './Panel';
const NavigationButtons = ({ onBack }): React.ReactNode => (
	<>
		<Button
			variant="secondary"
			icon="arrow-left"
			onClick={onBack}
			type="button"
		>
			{__('Back', 'kudos-donations')}
		</Button>
	</>
);

const Row = ({
	label,
	value,
}: {
	label: string;
	value: React.ReactNode;
}): React.ReactNode => (
	<Flex justify="space-between" align="center">
		<FlexItem
			style={{
				width: '160px',
				fontWeight: 600,
				flexShrink: 0,
			}}
		>
			{label}
		</FlexItem>
		<FlexItem
			style={{
				flexGrow: 1,
				overflowWrap: 'break-word',
				wordBreak: 'break-word',
				maxWidth: '100%',
			}}
		>
			{value}
		</FlexItem>
	</Flex>
);

interface PostEditProps {
	post: Post;
}

const SinglePostView = ({ post }: PostEditProps): React.ReactNode => {
	const { setPageTitle, setHeaderContent } = useAdminContext();
	const { singularName } = usePostsContext();
	const { setParams } = useAdminQueryParams();

	const clearParams = useCallback(() => {
		void setParams({ post: null, tab: null });
	}, [setParams]);

	useEffect(() => {
		setHeaderContent(
			<NavigationButtons
				onBack={() => {
					void clearParams();
				}}
			/>
		);
		return () => {
			setHeaderContent(null);
			void clearParams();
		};
	}, [clearParams, setHeaderContent]);

	useEffect(() => {
		if (post) {
			setPageTitle(singularName + ': ' + post.id);
		}
	}, [post, singularName, setPageTitle]);

	if (!post) {
		return null;
	}

	return (
		<VStack spacing={4}>
			<Panel header={__('Post details', 'kudos-donations')}>
				<PostDetails post={post} />
			</Panel>
			<Panel header={__('Meta details', 'kudos-donations')}>
				<PostMeta post={post} />
			</Panel>
		</VStack>
	);
};

export const PostDetails = ({ post }) => (
	<>
		<Row label={__('ID', 'kudos-donations')} value={post.id} />
		<Row
			label={__('Date', 'kudos-donations')}
			value={new Date(post.date).toLocaleDateString()}
		/>
		<Row
			label={__('Description', 'kudos-donations')}
			value={post.title.raw}
		/>
	</>
);

export const PostMeta = ({ post }) => (
	<>
		{Object.entries(post.meta)
			.sort(([a], [b]) => a.localeCompare(b))
			.map(([key, value]) => (
				<Row
					key={key}
					label={key.replace(/_/g, ' ')}
					value={String(value)}
				/>
			))}
	</>
);

export default SinglePostView;
