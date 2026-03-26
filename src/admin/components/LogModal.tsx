import React from 'react';
import {
	Modal,
	Button,
	Flex,
	Spinner,
	SelectControl as WPSelectControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { useCallback, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

interface LogEntry {
	datetime: string;
	channel: string;
	level: string;
	message: string;
	context: Record<string, unknown>;
	extra: Record<string, unknown>;
}

interface LogResponse {
	log_files: string[];
	log_content: LogEntry[];
}

const LEVEL_OPTIONS = [
	{ label: __('ALL', 'kudos-donations'), value: 'ALL' },
	{ label: 'DEBUG', value: 'DEBUG' },
	{ label: 'INFO', value: 'INFO' },
	{ label: 'NOTICE', value: 'NOTICE' },
	{ label: 'WARNING', value: 'WARNING' },
	{ label: 'ERROR', value: 'ERROR' },
	{ label: 'CRITICAL', value: 'CRITICAL' },
];

const LEVEL_COLORS: Record<string, string> = {
	DEBUG: '#9ca3af',
	INFO: '#3b82f6',
	NOTICE: '#06b6d4',
	WARNING: '#f59e0b',
	ERROR: '#ef4444',
	CRITICAL: '#b91c1c',
	ALERT: '#b91c1c',
	EMERGENCY: '#b91c1c',
};

const LevelBadge = ({ level }: { level: string }) => (
	<span
		style={{
			display: 'inline-block',
			padding: '1px 6px',
			borderRadius: '3px',
			color: '#fff',
			backgroundColor: LEVEL_COLORS[level] ?? '#6b7280',
			fontSize: '11px',
			fontWeight: 600,
		}}
	>
		{level}
	</span>
);

const LogModal = () => {
	const [isOpen, setOpen] = useState(false);
	const [logFiles, setLogFiles] = useState<string[]>([]);
	const [logContent, setLogContent] = useState<LogEntry[]>([]);
	const [selectedFile, setSelectedFile] = useState('');
	const [selectedLevel, setSelectedLevel] = useState('ALL');
	const [isLoading, setIsLoading] = useState(false);

	const fetchLog = useCallback(async (file: string, level: string) => {
		setIsLoading(true);
		try {
			const params = new URLSearchParams({ level });
			if (file) {
				params.set('file', file);
			}
			const data = await apiFetch<LogResponse>({
				path: `kudos/v1/log/?${params}`,
				method: 'GET',
			});
			setLogFiles(data.log_files ?? []);
			setLogContent(data.log_content ?? []);
		} finally {
			setIsLoading(false);
		}
	}, []);

	const openModal = () => {
		setOpen(true);
		void fetchLog('', 'ALL');
	};

	return (
		<>
			<Button variant="secondary" icon="text" onClick={openModal}>
				{__('View Logs', 'kudos-donations')}
			</Button>
			{isOpen && (
				<Modal
					title={__('Log', 'kudos-donations')}
					onRequestClose={() => setOpen(false)}
					size={'fill'}
				>
					<VStack spacing={5}>
						<Flex justify="flex-start">
							<WPSelectControl
								label={__('File', 'kudos-donations')}
								value={selectedFile}
								options={[
									{
										label: __('Latest', 'kudos-donations'),
										value: '',
									},
									...logFiles.map((f) => ({
										label: f,
										value: f,
									})),
								]}
								onChange={(value: string) => {
									setSelectedFile(value);
									void fetchLog(value, selectedLevel);
								}}
								__next40pxDefaultSize
								__nextHasNoMarginBottom
							/>
							<WPSelectControl
								label={__('Level', 'kudos-donations')}
								value={selectedLevel}
								options={LEVEL_OPTIONS}
								onChange={(value: string) => {
									setSelectedLevel(value);
									void fetchLog(selectedFile, value);
								}}
								__next40pxDefaultSize
								__nextHasNoMarginBottom
							/>
						</Flex>
						{isLoading ? (
							<Flex justify="center">
								<Spinner style={{ margin: 0 }} />
							</Flex>
						) : (
							<div
								style={{
									overflowY: 'auto',
									fontSize: '12px',
								}}
							>
								<table className="widefat striped">
									<thead>
										<tr>
											<th style={{ width: '160px' }}>
												{__('Time', 'kudos-donations')}
											</th>
											<th style={{ width: '90px' }}>
												{__('Level', 'kudos-donations')}
											</th>
											<th>
												{__(
													'Message',
													'kudos-donations'
												)}
											</th>
											<th>
												{__(
													'Context',
													'kudos-donations'
												)}
											</th>
											<th>
												{__('Extra', 'kudos-donations')}
											</th>
										</tr>
									</thead>
									<tbody>
										{logContent.length === 0 ? (
											<tr>
												<td colSpan={5}>
													<Flex justify="center">
														<p>
															{__(
																'No log entries found.',
																'kudos-donations'
															)}
														</p>
													</Flex>
												</td>
											</tr>
										) : (
											logContent.map((entry, i) => (
												<tr key={i}>
													<td
														style={{
															whiteSpace:
																'nowrap',
														}}
													>
														{new Date(
															entry.datetime
														).toLocaleString()}
													</td>
													<td>
														<LevelBadge
															level={entry.level}
														/>
													</td>
													<td>{entry.message}</td>
													<td>
														{Object.keys(
															entry.context
														).length > 0 && (
															<details>
																<summary
																	style={{
																		cursor: 'pointer',
																	}}
																>
																	{__(
																		'View',
																		'kudos-donations'
																	)}
																</summary>
																<pre
																	style={{
																		margin: '4px 0 0',
																		whiteSpace:
																			'pre-wrap',
																	}}
																>
																	{JSON.stringify(
																		entry.context,
																		null,
																		2
																	)}
																</pre>
															</details>
														)}
													</td>
													<td>
														{Object.keys(
															entry.extra
														).length > 0 && (
															<details>
																<summary
																	style={{
																		cursor: 'pointer',
																	}}
																>
																	{__(
																		'View',
																		'kudos-donations'
																	)}
																</summary>
																<pre
																	style={{
																		margin: '4px 0 0',
																		whiteSpace:
																			'pre-wrap',
																	}}
																>
																	{JSON.stringify(
																		entry.extra,
																		null,
																		2
																	)}
																</pre>
															</details>
														)}
													</td>
												</tr>
											))
										)}
									</tbody>
								</table>
							</div>
						)}
					</VStack>
				</Modal>
			)}
		</>
	);
};

export { LogModal };
