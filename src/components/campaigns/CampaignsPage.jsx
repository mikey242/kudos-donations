// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { Header } from '../admin/Header';
import React from 'react';
import CampaignTable from './CampaignTable';
import CampaignEdit from './CampaignEdit';
import { __ } from '@wordpress/i18n';
import { Button } from '../controls';
import EmptyCampaigns from './EmptyCampaigns';
import { Spinner } from '../Spinner';
import { useSettingsContext } from '../../contexts/SettingsContext';
import { useNotificationContext } from '../../contexts/NotificationContext';
import {
	ArrowDownTrayIcon,
	PlusCircleIcon,
	PlusIcon,
} from '@heroicons/react/24/outline';
import {
	NumberParam,
	StringParam,
	useQueryParam,
	useQueryParams,
	withDefault,
} from 'use-query-params';
import { removeQueryParameters } from '../../helpers/util';

const CampaignsPage = () => {
	const [campaigns, setCampaigns] = useState(null);
	const [isApiBusy, setIsApiBusy] = useState(false);
	const [currentCampaign, setCurrentCampaign] = useState(null);
	const { settings, settingsReady } = useSettingsContext();
	const { createNotification } = useNotificationContext();
	const [didLoad, setDidLoad] = useState(false);
	const [campaignId, setCampaignId] = useQueryParam('campaign', NumberParam);
	const [sortQuery, setSortQuery] = useQueryParams({
		order: withDefault(StringParam, 'asc'),
		orderby: withDefault(StringParam, 'title'),
	});

	const getCampaigns = useCallback(() => {
		return apiFetch({
			path: `wp/v2/kudos_campaign?orderby=${sortQuery.orderby}&order=${sortQuery.order}`,
			method: 'GET',
		})
			.then((response) => {
				setCampaigns(response);
				return response;
			})
			.catch((error) => {
				createNotification(error.message, false);
			});
	}, [createNotification, sortQuery]);

	useEffect(() => {
		if (!didLoad) {
			getCampaigns().then(() => {
				setDidLoad(true);
			});
		}
	}, [didLoad, getCampaigns]);

	useEffect(() => {
		if (campaigns) {
			if (campaignId) {
				setCurrentCampaign(
					campaigns.filter((c) => c.id === campaignId)[0]
				);
			} else {
				clearCurrentCampaign();
			}
		}
	}, [campaignId, campaigns]);

	const clearCurrentCampaign = () => {
		removeQueryParameters(['campaign', 'tab']);
		setCurrentCampaign(null);
	};

	const newCampaign = () => {
		updateCampaign(null, {
			title: __('New campaign', 'kudos-donations'),
			status: 'draft',
		}).then((response) => {
			setCampaignId(response.id);
		});
	};

	const updateCampaign = (id = null, data = {}, notification = true) => {
		setIsApiBusy(true);
		return apiFetch({
			path: `wp/v2/kudos_campaign/${id ?? ''}`,
			method: 'POST',
			data: {
				...data,
				status: 'publish',
			},
		})
			.then((response) => {
				getCampaigns().then(() => {
					if (notification) {
						createNotification(
							data.status === 'draft'
								? __('Campaign created', 'kudos-donations')
								: __('Campaign updated', 'kudos-donations'),
							true
						);
					}
				});
				return response;
			})
			.catch((error) => {
				createNotification(error.message, false);
			})
			.finally(() => {
				setIsApiBusy(false);
			});
	};

	const removeCampaign = (id) => {
		apiFetch({
			path: `wp/v2/kudos_campaign/${id}?force=true`,
			method: 'DELETE',
		}).then(() => {
			createNotification(__('Campaign deleted', 'kudos-donations'), true);
			return getCampaigns();
		});
	};

	useEffect(() => {
		getCampaigns();
	}, [getCampaigns, sortQuery]);

	const duplicateCampaign = (campaign) => {
		const data = {
			...campaign,
			id: null,
			title: campaign.title.rendered,
			status: 'draft',
		};
		return updateCampaign(null, data);
	};

	return (
		<>
			{!campaigns && !settingsReady ? (
				<div className="absolute inset-0 flex items-center justify-center">
					<Spinner />
				</div>
			) : (
				<>
					<Header>
						{currentCampaign && (
							<Button
								form="settings-form"
								type="submit"
								isBusy={isApiBusy}
								icon={
									currentCampaign.status === 'draft' ? (
										<PlusCircleIcon className="mr-2 w-5 h-5" />
									) : (
										<ArrowDownTrayIcon className="mr-2 w-5 h-5" />
									)
								}
							>
								{__('Save', 'kudos-donations')}
							</Button>
						)}
					</Header>
					{!currentCampaign ? (
						<div className="max-w-5xl w-full mx-auto">
							{campaigns?.length >= 1 ? (
								<CampaignTable
									updateCampaign={updateCampaign}
									deleteClick={removeCampaign}
									duplicateClick={duplicateCampaign}
									editClick={setCampaignId}
									campaigns={campaigns}
									sortQuery={sortQuery}
									setSortQuery={setSortQuery}
								/>
							) : (
								<EmptyCampaigns />
							)}
							<button
								title={__('Add campaign', 'kudos-donations')}
								className="rounded-full mx-auto p-2 flex justify-center items-center bg-white mt-5 shadow-md border-0 cursor-pointer"
								onClick={newCampaign}
							>
								<PlusIcon className={'w-5 h-5'} />
							</button>
						</div>
					) : (
						<div className="max-w-4xl w-full mx-auto">
							<CampaignEdit
								updateCampaign={updateCampaign}
								recurringAllowed={
									settings?.[
										'_kudos_vendor_' +
											settings._kudos_vendor
									].recurring
								}
								clearCurrentCampaign={clearCurrentCampaign}
								campaign={currentCampaign}
							/>
						</div>
					)}
				</>
			)}
		</>
	);
};

export { CampaignsPage };
