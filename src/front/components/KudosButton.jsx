import React from 'react';
import Render from './Render';
import { useCampaignContext } from '../contexts/CampaignContext';
import { DonateButton } from './DonateButton';

const KudosButton = ({ children, className, targetId = null }) => {
	const { campaign, campaignErrors } = useCampaignContext();

	const triggerModal = () => {
		const target = document.getElementById(targetId);
		if (target) {
			const root = target.shadowRoot;
			const modal = root.querySelector('[data-toggle]');
			if (modal) {
				modal.dataset.toggle = 'true';
			}
		}
	};

	return (
		<>
			<Render
				themeColor={campaign?.meta?.theme_color}
				errors={campaignErrors}
				style={campaign?.meta?.custom_styles}
			>
				<DonateButton
					className={className}
					children={children}
					onClick={triggerModal}
				/>
			</Render>
		</>
	);
};

export { KudosButton };
