// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { Header } from './Header';
import { PlusIcon } from '@heroicons/react/outline';
import React from 'react';
import CampaignTable from './CampaignTable';
import CampaignEdit from './CampaignEdit';
import { __ } from '@wordpress/i18n';
import { Button } from '../../common/components/controls';
import {
	getQueryVar,
	removeQueryParameters,
	updateQueryParameter,
} from '../../common/helpers/util';
import EmptyCampaigns from './EmptyCampaigns';
import { Spinner } from '../../common/components/Spinner';
import { useSettingsContext } from '../contexts/SettingsContext';
import { useNotificationContext } from '../contexts/NotificationContext';

const CampaignsPage = () => {
	const [campaigns, setCampaigns] = useState(null);
	const [isApiBusy, setIsApiBusy] = useState(false);
	const [currentCampaign, setCurrentCampaign] = useState(null);
	const [campaignsReady, setCampaignsReady] = useState(false);
	const { settings, settingsReady } = useSettingsContext();
	const { createNotification } = useNotificationContext();

	useEffect(() => {
		getData();
	}, []);

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
		setCurrentCampaign({
			status: 'draft',
			title: {
				rendered: __('New campaign', 'kudos-donations'),
			},
			meta: {
				initial_title: __('Support us!', 'kudos-donations'),
				initial_description: __(
					'Your support is greatly appreciated and will help to keep us going.',
					'kudos-donations'
				),
				donation_type: 'oneoff',
				amount_type: 'both',
				minimum_donation: 1,
				fixed_amounts: '5,10,20,50',
				theme_color: '#ff9f1c',
				completed_payment: 'message',
				return_message_title: __('Thank you!', 'kudos-donations'),
				return_message_text: __(
					'Many thanks for your donation. We appreciate your support.',
					'kudos-donations'
				),
			},
		});
	};

	const updateCampaign = (id, data = {}) => {
		setIsApiBusy(true);
		apiFetch({
			path: `wp/v2/kudos_campaign/${id ?? ''}`,
			method: 'POST',
			data: {
				...data,
				status: 'publish',
			},
		})
			.then((response) => {
				setCurrentCampaign(response);
				createNotification(
					data.status === 'draft'
						? __('Campaign created', 'kudos-donations')
						: __('Campaign updated', 'kudos-donations'),
					true
				);
				return getCampaigns();
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
		updateCampaign(null, data);
	};

	const getData = () => {
		Promise.all([getCampaigns()])
			.then(() => setCampaignsReady(true))
			.catch((error) => {
				createNotification(error.message, false);
			});
	};

	const getCampaigns = () => {
		return apiFetch({
			path: 'wp/v2/kudos_campaign/',
			method: 'GET',
		})
			.then(async (response) => {
				await Promise.all(
					response.map((campaign) => {
						return apiFetch({
							path: `kudos/v1/transaction/campaign/total/${campaign.id}`,
							method: 'GET',
						}).then((total) => {
							campaign.total = total;
						});
					})
				);
				return response;
			})
			.then((response) => {
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
								isDisabled={isApiBusy}
							>
								{currentCampaign.status === 'draft'
									? __('Create', 'kudos-donations')
									: __('Save', 'kudos-donations')}
							</Button>
						)}
					</Header>
					<div className="max-w-3xl w-full mx-auto">
						{!currentCampaign ? (
							<>
								{campaigns?.length >= 1 ? (
									<CampaignTable
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
