import React from "react";

export type PayMethod = "card" | "crypto" | "skins";
export type Currency = "RUB" | "UAH" | "EUR" | "USD" | "KZT" | "BYN";
export type CryptoNetwork =
    | "USDTTRC"
    | "USDTBSC"
    | "USDTTON"
    | "USDTERC"
    | "TON"
    | "TRX";
export type PaySystem = "qr_sbp_a" | "qr_sbp_b" | "visa";

export const FALLBACK_RATES: Record<Currency, number> = {
    RUB: 1,
    USD: 96,
    EUR: 105,
    UAH: 2.2,
    KZT: 0.19,
    BYN: 29,
};

export const CURRENCIES: { label: string; value: Currency; prefix?: string }[] =
    [
        { label: "RUB", value: "RUB", prefix: "₽" },
        { label: "UAH", value: "UAH" },
        { label: "EUR", value: "EUR" },
        { label: "USD", value: "USD" },
        { label: "KZT", value: "KZT" },
        { label: "BYN", value: "BYN" },
    ];

export const CRYPTO_NETWORKS: {
    label: string;
    value: CryptoNetwork;
    rateKey: string;
    symbol: string;
    min: number;
}[] = [
    {
        label: "USDTTRC",
        value: "USDTTRC",
        rateKey: "USDT",
        symbol: "$",
        min: 2,
    },
    {
        label: "USDTBSC",
        value: "USDTBSC",
        rateKey: "USDT",
        symbol: "$",
        min: 2,
    },
    {
        label: "USDTTON",
        value: "USDTTON",
        rateKey: "USDT",
        symbol: "$",
        min: 2,
    },
    {
        label: "USDTERC",
        value: "USDTERC",
        rateKey: "USDT",
        symbol: "$",
        min: 10,
    },
    { label: "TON", value: "TON", rateKey: "TON", symbol: "TON", min: 1 },
    { label: "TRX", value: "TRX", rateKey: "TRX", symbol: "TRX", min: 10 },
];

export const SBP_SYSTEMS: { label: string; value: PaySystem }[] = [
    { label: "QR | СБП (A)", value: "qr_sbp_a" },
    { label: "QR | СБП (B)", value: "qr_sbp_b" },
];

export const CURRENCY_SYMBOLS: Record<Currency, string> = {
    RUB: "₽",
    UAH: "₴",
    EUR: "€",
    USD: "$",
    KZT: "₸",
    BYN: "Br",
};

export const MIN_AMOUNTS: Record<Currency, number> = {
    RUB: 50,
    UAH: 25,
    EUR: 1,
    USD: 2,
    KZT: 250,
    BYN: 2,
};
