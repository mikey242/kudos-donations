import React from 'react';
import almaSvg from '../../images/payment-logos/alma.svg';
import applepaysvg from '../../images/payment-logos/applepay.svg';
import bacsSvg from '../../images/payment-logos/bacs.svg';
import bancomatpaySvg from '../../images/payment-logos/bancomatpay.svg';
import bancontactSvg from '../../images/payment-logos/bancontact.svg';
import banktransferSvg from '../../images/payment-logos/banktransfer.svg';
import belfiusSvg from '../../images/payment-logos/belfius.svg';
import billieSvg from '../../images/payment-logos/billie.svg';
import blikSvg from '../../images/payment-logos/blik.svg';
import creditCardSvg from '../../images/payment-logos/credit-card.svg';
import directDebitSvg from '../../images/payment-logos/directdebit.svg';
import epsSvg from '../../images/payment-logos/eps.svg';
import fallbackSvg from '../../images/payment-logos/fallback.svg';
import giftcardSvg from '../../images/payment-logos/giftcard.svg';
import giropaySvg from '../../images/payment-logos/giropay.svg';
import googlepaySvg from '../../images/payment-logos/googlepay.svg';
import idealSvg from '../../images/payment-logos/ideal.svg';
import in3Svg from '../../images/payment-logos/in3.svg';
import kbcSvg from '../../images/payment-logos/kbc.svg';
import klarnaSvg from '../../images/payment-logos/klarna.svg';
import mbwaySvg from '../../images/payment-logos/mbway.svg';
import multibancoSvg from '../../images/payment-logos/multibanco.svg';
import mybankSvg from '../../images/payment-logos/mybank.svg';
import paybybankSvg from '../../images/payment-logos/paybybank.svg';
import payconiqSvg from '../../images/payment-logos/payconiq.svg';
import paypalSvg from '../../images/payment-logos/paypal.svg';
import paysafecardSvg from '../../images/payment-logos/paysafecard.svg';
import przelewy24Svg from '../../images/payment-logos/przelewy24.svg';
import rivertySvg from '../../images/payment-logos/riverty.svg';
import satispaySvg from '../../images/payment-logos/satispay.svg';
import swishSvg from '../../images/payment-logos/swish.svg';
import trustlySvg from '../../images/payment-logos/trustly.svg';
import twintSvg from '../../images/payment-logos/twint.svg';

const ICON_MAP: Record<string, string> = {
	// Mollie
	alma: almaSvg,
	applepay: applepaysvg,
	bacs: bacsSvg,
	bancomatpay: bancomatpaySvg,
	bancontact: bancontactSvg,
	banktransfer: banktransferSvg,
	belfius: belfiusSvg,
	billie: billieSvg,
	blik: blikSvg,
	creditcard: creditCardSvg,
	directdebit: directDebitSvg,
	eps: epsSvg,
	giftcard: giftcardSvg,
	giropay: giropaySvg,
	googlepay: googlepaySvg,
	ideal: idealSvg,
	in3: in3Svg,
	kbc: kbcSvg,
	klarna: klarnaSvg,
	klarnapaylater: klarnaSvg,
	klarnapaynow: klarnaSvg,
	klarnasliceit: klarnaSvg,
	mbway: mbwaySvg,
	multibanco: multibancoSvg,
	mybank: mybankSvg,
	paybybank: paybybankSvg,
	payconiq: payconiqSvg,
	paypal: paypalSvg,
	paysafecard: paysafecardSvg,
	przelewy24: przelewy24Svg,
	riverty: rivertySvg,
	satispay: satispaySvg,
	sofort: klarnaSvg,
	swish: swishSvg,
	trustly: trustlySvg,
	twint: twintSvg,

	// Stripe capability IDs
	acss_debit_payments: fallbackSvg,
	afterpay_clearpay_payments: fallbackSvg,
	alipay_payments: fallbackSvg,
	amazon_pay_payments: fallbackSvg,
	au_becs_debit_payments: fallbackSvg,
	bacs_debit_payments: bacsSvg,
	bancontact_payments: bancontactSvg,
	blik_payments: blikSvg,
	boleto_payments: fallbackSvg,
	card_payments: creditCardSvg,
	eps_payments: epsSvg,
	fpx_payments: fallbackSvg,
	giropay_payments: giropaySvg,
	grabpay_payments: fallbackSvg,
	ideal_payments: idealSvg,
	klarna_payments: klarnaSvg,
	konbini_payments: fallbackSvg,
	link_payments: fallbackSvg,
	oxxo_payments: fallbackSvg,
	p24_payments: przelewy24Svg,
	paynow_payments: fallbackSvg,
	pix_payments: fallbackSvg,
	promptpay_payments: fallbackSvg,
	revolut_pay_payments: fallbackSvg,
	sepa_debit_payments: directDebitSvg,
	twint_payments: twintSvg,
	transfers: paybybankSvg,
	sofort_payments: klarnaSvg,
};

interface PaymentIconProps {
	id: string;
	style?: React.CSSProperties;
}

export const PaymentIcon = ({ id, style }: PaymentIconProps) => {
	const src = ICON_MAP[id] ?? fallbackSvg;
	return (
		<img
			src={src}
			alt={id}
			style={{ width: 40, display: 'block', ...style }}
		/>
	);
};
