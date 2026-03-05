import type { BaseSettings } from './settings';
import type { LicenceSettings } from './licence';
import type { MollieSettings } from './mollie';

export interface AllSettings extends BaseSettings, LicenceSettings, MollieSettings {}