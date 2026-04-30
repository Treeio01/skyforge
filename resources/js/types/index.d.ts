export interface User {
    id: number;
    username: string;
    avatar_url: string | null;
    steam_id: string;
    balance: number;
    trade_url: string | null;
    is_admin: boolean;
}

export interface Skin {
    id: number;
    market_hash_name: string;
    weapon_type: string | null;
    skin_name: string | null;
    exterior: string | null;
    rarity_color: string | null;
    category: string | null;
    image_url: string | null;
    price: number;
}

export interface UserSkin {
    id: number;
    skin_id: number;
    skin: Skin;
    price_at_acquisition: number;
    status: string;
    source: string;
}

export interface Upgrade {
    id: number;
    bet_amount: number;
    target_price: number;
    chance: number;
    result: 'win' | 'lose';
    roll_value: number;
    created_at: string;
}

export interface FeedItem {
    id: number;
    username: string;
    avatar_url: string | null;
    target_skin_name: string;
    target_skin_image: string | null;
    rarity_color: string | null;
    chance: number;
    result: string;
    is_fake?: boolean;
    created_at: string;
}

export interface Flash {
    error: string | null;
    success: string | null;
}

export interface SiteStats {
    online_real: number;
    online_fake_initial: number;
    online_enabled: boolean;
    total_upgrades: number;
}

export interface UpgradeSettings {
    houseEdge: number;
    minChance: number;
    maxChance: number;
    minBetAmount: number;
    maxBetAmount: number;
    cooldownSeconds: number;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: { user: User | null };
    flash: Flash;
    stats?: SiteStats;
    socials?: Record<string, string>;
    upgradeSettings?: UpgradeSettings;
};
