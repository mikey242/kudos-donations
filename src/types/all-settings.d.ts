import type { BaseSettings } from './settings';
import type { LicenceSettings } from './licence';
import type { MollieSettings } from './mollie';
import type { StripeSettings } from './stripe';

export interface AllSettings
	extends BaseSettings, LicenceSettings, MollieSettings, StripeSettings {}
