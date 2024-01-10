import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';

const settings = getSetting( 'blockonomics_data', {} );

const defaultLabel = __( 'Bitcoin', 'blockonomics-bitcoin-payments' );
const label = decodeEntities( settings.title ) || defaultLabel;

const getIcons = () => {
	return Object.entries( settings?.icons ?? {} ).map(
		( [ id, { src, alt } ] ) => {
			return {
				id,
				src,
				alt,
			};
		}
	);
};

const Label = ( props ) => {
	const { PaymentMethodLabel, PaymentMethodIcons } = props.components;
	return (
		<>
			<PaymentMethodLabel text={ label } />
			<PaymentMethodIcons icons={getIcons()} />
		</>
	);
};

const Content = () => {
	return decodeEntities( settings.description || '' );
};

const canMakePayment = () => {
	return true;
};



const blockonomicsPaymentMethod = {
    name: 'blockonomics',
    label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment,
	ariaLabel: label,
	icons: getIcons(),
	placeOrderButtonLabel: __( 'Pay with Bitcoin', 'blockonomics-bitcoin-payments' )
}

registerPaymentMethod( blockonomicsPaymentMethod );