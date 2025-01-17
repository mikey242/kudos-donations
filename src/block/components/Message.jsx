import { useEffect, useState } from '@wordpress/element';
import React from 'react';
import { KudosModal } from './KudosModal';
import Render from './Render';
import { Button } from './controls';
import { __ } from '@wordpress/i18n';

function Message({ color, title, body }) {
	const [ready, setReady] = useState(false);
	const [modalOpen, setModalOpen] = useState(true);

	const closeModal = () => {
		setModalOpen(!modalOpen);
	};

	useEffect(() => {
		setReady(true);
	}, []);

	return (
		<>
			{ready && (
				<Render themeColor={color}>
					<KudosModal toggleModal={closeModal} isOpen={modalOpen}>
						<>
							<h2 className="font-bold font-heading text-4xl/4 m-0 mb-2 block text-center">
								{title}
							</h2>
							<p className="text-lg text-center block font-normal mb-4">
								{body}
							</p>
							<Button
								type="button"
								className="text-base block ml-auto"
								ariaLabel={__('Close', 'kudos-donations')}
								onClick={closeModal}
							>
								<span className="mx-2">OK</span>
							</Button>
						</>
					</KudosModal>
				</Render>
			)}
		</>
	);
}

export default Message;
