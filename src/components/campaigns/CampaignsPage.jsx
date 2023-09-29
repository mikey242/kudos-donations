// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { Header } from '../admin/Header';
import React from 'react';
import CampaignTable from './CampaignTable';
import CampaignEdit from './CampaignEdit';
import { __ } from '@wordpress/i18n';
import { Button } from '../controls';
import {
	getQueryVar,
	removeQueryParameters,
	updateQueryParameter,
} from '../../helpers/util';
import EmptyCampaigns from './EmptyCampaigns';
import { Spinner } from '../Spinner';
import { useSettingsContext } from '../../contexts/SettingsContext';
import { useNotificationContext } from '../../contexts/NotificationContext';
import {
	ArrowDownTrayIcon,
	PlusCircleIcon,
	PlusIcon,
} from '@heroicons/react/24/outline';

const CampaignsPage = () => {
	const [campaigns, setCampaigns] = useState(null);
	const [isApiBusy, setIsApiBusy] = useState(false);
	const [currentCampaign, setCurrentCampaign] = useState(null);
	const [campaignsReady, setCampaignsReady] = useState(false);
	const { settings, settingsReady } = useSettingsContext();
	const { createNotification } = useNotificationContext();
	const [didLoad, setDidLoad] = useState(false);

	const getCampaigns = useCallback(() => {
		return apiFetch({
			path: 'wp/v2/kudos_campaign/',
			method: 'GET',
		}).then((response) => {
			setCampaigns(response.reverse());
			const currentId = getQueryVar('campaign');
			if (currentId) {
				const campaign = response.filter(
					(res) => res.id === parseInt(currentId)
				);
				if (campaign && currentCampaign === null) {
					setCurrentCampaign(campaign[0]);
				}
			}
		});
	}, [currentCampaign]);

	const getData = useCallback(() => {
		Promise.all([getCampaigns()])
			.then(() => setCampaignsReady(true))
			.catch((error) => {
				createNotification(error.message, false);
			});
	}, [createNotification, getCampaigns]);

	useEffect(() => {
		if (!didLoad) {
			getData();
			setDidLoad(true);
		}
	}, [didLoad, getData]);

	useEffect(() => {
		if (currentCampaign?.id) {
			updateQueryParameter('campaign', currentCampaign.id);
		}
	}, [currentCampaign]);

	const clearCurrentCampaign = () => {
		removeQueryParameters(['campaign', 'tab']);
		setCurrentCampaign(null);
	};

	const newCampaign = () => {
		updateCampaign(null, {
			title: __('New campaign', 'kudos-donations'),
			status: 'draft',
		}).then((response) => {
			setCurrentCampaign(response);
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
			{!campaignsReady && !settingsReady ? (
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
					<div className="max-w-4xl w-full mx-auto">
						{!currentCampaign ? (
							<>
								{campaigns?.length >= 1 ? (
									<CampaignTable
										updateCampaign={updateCampaign}
										deleteClick={removeCampaign}
										duplicateClick={duplicateCampaign}
										editClick={setCurrentCampaign}
										campaigns={campaigns}
									/>
								) : (
									<EmptyCampaigns />
								)}
								<button
									title={__(
										'Add campaign',
										'kudos-donations'
									)}
									className="rounded-full mx-auto p-2 flex justify-center items-center bg-white mt-5 shadow-md border-0 cursor-pointer"
									onClick={newCampaign}
								>
									<PlusIcon className={'w-5 h-5'} />
								</button>
							</>
						) : (
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
						)}
					</div>
				</>
			)}
		</>
	);
};

export { CampaignsPage };
