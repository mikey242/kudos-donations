export interface LicenceStatus {
    valid: boolean;
    expires_at: string;
}

export type LicenceStatusString = 'active' | 'expired' | 'not-set';

export interface LicenceSettings {
    _kudos_licence_key: string;
    _kudos_licence_status: LicenceStatus | Record<string, never>;
}