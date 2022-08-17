import React from 'react';
import Render from '../common/components/Render';
import { useCampaignContext } from '../admin/contexts/CampaignContext';
import { DonateButton } from '../common/components/DonateButton';

const KudosButton = ({ children, className, targetId = null }) => {
	const { campaign } = useCampaignContext();

	const triggerModal = () => {
		const target = document.getElementById(targetId);
		if (target) {
			const root = target.shadowRoot;
			const modal = root.querySelector('[data-toggle]');
			modal.dataset.toggle = 'true';
		}
	};

	return (
		<Render themeColor={campaign?.theme_color}>
			<DonateButton
				className={className}
				children={children}
				onClick={triggerModal}
			/>
		</Render>
	);
};

export { KudosButton };
