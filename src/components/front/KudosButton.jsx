import React from 'react';
import Render from '../Render';
import { useCampaignContext } from '../../contexts/CampaignContext';
import { DonateButton } from '../DonateButton';
import { Spinner } from '../Spinner';

const KudosButton = ({ children, className, targetId = null }) => {
	const { campaignRequest, campaignErrors } = useCampaignContext();
	const { campaign } = campaignRequest;

	const triggerModal = () => {
		const target = document.getElementById(targetId);
		if (target) {
			const root = target.shadowRoot;
			const modal = root.querySelector('[data-toggle]');
			modal.dataset.toggle = 'true';
		}
	};

	return (
		<>
			<Render
				themeColor={campaign?.meta?.theme_color}
				errors={campaignErrors}
				style={campaign?.meta?.custom_styles}
			>
				{campaignRequest.ready ? (
					<DonateButton
						className={className}
						children={children}
						onClick={triggerModal}
					/>
				) : (
					<Spinner />
				)}
			</Render>
		</>
	);
};

export { KudosButton };
