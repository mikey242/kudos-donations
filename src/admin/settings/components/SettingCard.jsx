import { CardBody } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';

const SettingCard = (props) => {
	return applyFilters(
		'kudos.settings.settingCard.' + props.id,
		<Fragment>
			<CardBody size="medium">
				<header className={'mb-6'}>
					<h3 className="mb-5">{props.title}</h3>
					{/*<p className={"mb-6 text-lg text-gray-500"}>{props.description}</p>*/}
				</header>
				{props.children}
			</CardBody>
		</Fragment>,
		props
	);
};

export { SettingCard };
