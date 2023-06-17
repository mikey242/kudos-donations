import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import logo from '../../../assets/images/full-logo-green.svg';
import React from 'react';
import { Button, ToggleControl } from '../controls';
import Divider from '../Divider';
import { HeartIcon } from '@heroicons/react/20/solid';
import {
	InformationCircleIcon,
	PencilIcon,
	QuestionMarkCircleIcon,
	UserGroupIcon,
} from '@heroicons/react/24/outline';

const HelpTab = ({ setShowIntro }) => {
	return (
		<Fragment>
			<h2>{__('Share the love', 'kudos-donations')}</h2>
			<div>
				<p className="mb-2">
					{__(
						'Do you like using Kudos? Please let us know your thoughts.',
						'kudos-donations'
					)}
				</p>
				<div className="flex flex-wrap">
					<Button
						isOutline
						isExternal
						className="mr-2"
						href="https://kudosdonations.com/donate/"
					>
						<HeartIcon className="hidden sm:block w-5 h-5 mr-2" />
						{__('Donate to Kudos Donations', 'kudos-donations')}
					</Button>
					<Button
						isOutline
						isExternal
						className="mr-2"
						href="https://wordpress.org/support/plugin/kudos-donations/reviews/#new-post"
					>
						<PencilIcon className="hidden sm:block w-5 h-5 mr-2" />
						{__('Leave a review', 'kudos-donations')}
					</Button>
				</div>
			</div>
			<Divider />
			<h2>{__('Need some assistance?', 'kudos-donations')}</h2>
			<div>
				<p>
					{__(
						"Don't hesitate to get in touch if you need any help or have a suggestion.",
						'kudos-donations'
					)}
				</p>
				<div className="flex flex-wrap sm:flex-nowrap mt-2">
					<div className="flex flex-wrap flex-grow">
						<Button
							isOutline
							className="mr-2"
							href="https://wordpress.org/support/plugin/kudos-donations/"
							isExternal
						>
							<UserGroupIcon className="hidden sm:block w-5 h-5 mr-2" />
							{__('Support forums', 'kudos-donations')}
						</Button>
						<Button
							isOutline
							className={'mr-2'}
							onClick={() => setShowIntro(true)}
						>
							<InformationCircleIcon className="hidden sm:block w-5 h-5 mr-2" />
							{__('Show welcome guide', 'kudos-donations')}
						</Button>
						<Button
							isOutline
							className={'mr-2'}
							isExternal
							href="https://kudosdonations.com/faq/"
						>
							<QuestionMarkCircleIcon className="hidden sm:block w-5 h-5 mr-2" />
							{__('Visit our F.A.Q', 'kudos-donations')}
						</Button>
					</div>
					<div className="mt-2 sm:mt-0">
						<a
							target="_blank"
							title={__(
								'Visit Kudos Donations',
								'kudos-donations'
							)}
							className="block"
							href="https://kudosdonations.com"
							rel="noreferrer"
						>
							<img
								width="140"
								src={logo}
								className="mr-4"
								alt="Kudos Logo"
							/>
						</a>
					</div>
				</div>
			</div>
			<Divider />
			<ToggleControl
				name="_kudos_always_load_assets"
				label={__('Always load assets', 'kudos-donations')}
				help={__(
					'This will ensure that the JavaScript for Kudos is loaded on every page.',
					'kudos-donations'
				)}
			/>
			<ToggleControl
				name="_kudos_debug_mode"
				label={__('Debug mode', 'kudos-donations')}
				help={__(
					'Enables an actions tab under tools and adds more detail to logs.',
					'kudos-donations'
				)}
			/>
		</Fragment>
	);
};

export { HelpTab };
