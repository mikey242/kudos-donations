import { KudosLogo } from './KudosLogo';

export const Message = ({ message }) => {
	return (
		<p
			style={{
				display: 'flex',
				alignItems: 'center',
				justifyContent: 'flex-start',
				padding: '0.5em',
				fontStyle: 'italic',
			}}
		>
			<KudosLogo style={{ maxWidth: '32px', marginRight: '0.5em' }} />
			<span>{message}</span>
		</p>
	);
};
