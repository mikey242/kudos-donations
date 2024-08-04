import React from 'react';
import Render from '../common/Render';
import { useCampaignContext } from '../common/contexts/CampaignContext';
import { DonateButton } from '../common/DonateButton';
import { Spinner } from '../common/Spinner';

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
