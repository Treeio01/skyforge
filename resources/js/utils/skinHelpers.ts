import { Skin } from '@/types';
import { SkinRarity } from '@/Components/Upgrade/LiveFeedItem';
import { SkinEntry } from '@/Components/Upgrade/SkinCard';

const RARITY_COLOR_MAP: Record<string, SkinRarity> = {
    '#878f9d': 'cons',
    '#356c9d': 'indus',
    '#2d4ffa': 'mils',
    '#50318d': 'restr',
    '#a64bb5': 'class',
    '#ea2f2f': 'covrt',
    '#d4af37': 'contra',
};

export function mapRarityColor(hex: string | null): SkinRarity {
    if (!hex) {
        return 'cons';
    }

    const normalized = hex.toLowerCase();
    return RARITY_COLOR_MAP[normalized] ?? 'cons';
}

export function formatKopecks(kopecks: number): string {
    const rubles = kopecks / 100;
    const formatted = rubles
        .toLocaleString('ru-RU', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 1,
        })
        .replace(/\s/g, '\u2009'); // тонкий пробел вместо обычного, не ломает layout

    return `${formatted}₽`;
}

export function parseSkinName(marketHashName: string): { weapon: string; name: string } {
    const separatorIndex = marketHashName.indexOf(' | ');

    if (separatorIndex === -1) {
        return { weapon: marketHashName, name: '' };
    }

    const weapon = marketHashName.slice(0, separatorIndex);
    const name = marketHashName.slice(separatorIndex + 3);

    return { weapon, name };
}

export function apiSkinToEntry(skin: Skin, idPrefix?: string): SkinEntry {
    const { weapon, name } = parseSkinName(skin.market_hash_name);

    return {
        id: idPrefix ? `${idPrefix}-${skin.id}` : skin.id,
        rarity: mapRarityColor(skin.rarity_color),
        weapon,
        name,
        price: formatKopecks(skin.price),
        priceKopecks: skin.price,
        image: skin.image_url ?? '',
        backendSkinId: skin.id,
    };
}

export function inventoryItemToEntry(item: {
    id: number;
    skin: Skin;
    price_at_acquisition: number;
}): SkinEntry {
    const { weapon, name } = parseSkinName(item.skin.market_hash_name);
    // Display the current market price, not price_at_acquisition — otherwise
    // identical skins acquired at different times appear with mismatched prices,
    // and the bet/target chance calculation looks confusing to the user.
    const currentPrice = item.skin.price > 0 ? item.skin.price : item.price_at_acquisition;

    return {
        id: item.id,
        rarity: mapRarityColor(item.skin.rarity_color),
        weapon,
        name,
        price: formatKopecks(currentPrice),
        priceKopecks: currentPrice,
        image: item.skin.image_url ?? '',
        backendSkinId: item.skin.id,
        backendUserSkinId: item.id,
    };
}
