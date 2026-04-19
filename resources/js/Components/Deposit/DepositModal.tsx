import Modal from "@/Components/UI/Modal";
import React, { useEffect, useMemo, useState } from "react";
import { router } from "@inertiajs/react";
import axios from "axios";

type PayMethod = "card" | "crypto" | "skins";
type Currency = "RUB" | "UAH" | "EUR" | "USD" | "KZT" | "BYN";
type CryptoNetwork =
    | "USDTTRC"
    | "USDTBSC"
    | "USDTTON"
    | "USDTERC"
    | "TON"
    | "TRX";
type PaySystem = "qr_sbp_a" | "qr_sbp_b" | "visa";

const FALLBACK_RATES: Record<Currency, number> = {
    RUB: 1,
    USD: 96,
    EUR: 105,
    UAH: 2.2,
    KZT: 0.19,
    BYN: 29,
};

const CardIcon = () => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="11"
        height="11"
        viewBox="0 0 11 11"
        fill="none"
    >
        <mask
            id="mask0_214_5002"
            maskUnits="userSpaceOnUse"
            x="0"
            y="1"
            width="11"
            height="9"
        >
            <path
                d="M0.916016 2.29165C0.916016 2.17009 0.964304 2.05351 1.05026 1.96756C1.13621 1.8816 1.25279 1.83331 1.37435 1.83331H9.62435C9.74591 1.83331 9.86249 1.8816 9.94844 1.96756C10.0344 2.05351 10.0827 2.17009 10.0827 2.29165V8.70831C10.0827 8.82987 10.0344 8.94645 9.94844 9.0324C9.86249 9.11836 9.74591 9.16665 9.62435 9.16665H1.37435C1.25279 9.16665 1.13621 9.11836 1.05026 9.0324C0.964304 8.94645 0.916016 8.82987 0.916016 8.70831V2.29165Z"
                fill="white"
                stroke="white"
                strokeWidth="0.916667"
                strokeLinejoin="round"
            />
            <path
                d="M0.916016 3.66663H10.0827"
                stroke="black"
                strokeWidth="0.916667"
                strokeLinecap="square"
                strokeLinejoin="round"
            />
            <path
                d="M6.18677 7.33331H8.24927"
                stroke="black"
                strokeWidth="0.916667"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <path
                d="M10.0827 2.29163V5.95829M0.916016 2.29163V5.95829"
                stroke="white"
                strokeWidth="0.916667"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </mask>
        <g mask="url(#mask0_214_5002)">
            <path
                d="M-0.000244141 0H10.9998V11H-0.000244141V0Z"
                fill="currentColor"
            />
        </g>
    </svg>
);

const CryptoIcon = () => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="9"
        height="11"
        viewBox="0 0 9 11"
        fill="none"
    >
        <g clipPath="url(#clip0_214_5636)">
            <path
                d="M6.66424 5.21293C7.26 4.90828 7.63913 4.36666 7.55111 3.46625C7.436 2.2341 6.42409 1.82112 5.08363 1.70605V0H4.04104V1.65866C3.77023 1.65866 3.49265 1.66543 3.21509 1.67219V0H2.1725V1.70605C1.78918 1.71763 1.34273 1.71198 0.0805664 1.70605V2.81632C0.903719 2.80176 1.33562 2.74886 1.43458 3.27667V7.94799C1.37173 8.36677 1.0366 8.30646 0.290426 8.29327L0.0805879 9.53219C1.98155 9.53219 2.17252 9.53895 2.17252 9.53895V11H3.21511V9.55926C3.49944 9.56603 3.77702 9.56603 4.04106 9.56603V11H5.08365V9.53895C6.83031 9.44417 7.99794 9.00412 8.15366 7.359C8.2755 6.03887 7.65265 5.44986 6.66424 5.21293ZM3.23542 2.89079C3.82441 2.89079 5.66586 2.70802 5.66586 3.93338C5.66586 5.1046 3.82443 4.96919 3.23542 4.96919V2.89079ZM3.23542 8.30004V6.01178C3.9395 6.01178 6.09557 5.81545 6.09557 7.15593C6.09555 8.44899 3.9395 8.30004 3.23542 8.30004Z"
                fill="currentColor"
            />
        </g>
        <defs>
            <clipPath id="clip0_214_5636">
                <rect width="8.25" height="11" fill="white" />
            </clipPath>
        </defs>
    </svg>
);

const SkinsIcon = () => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="11"
        height="11"
        viewBox="0 0 11 11"
        fill="none"
    >
        <path
            d="M3.2083 2.29165H10.5416V4.12498H10.0833V4.58331H7.3333C7.21174 4.58331 7.09516 4.6316 7.00921 4.71756C6.92325 4.80351 6.87496 4.92009 6.87496 5.04165V5.49998C6.87496 5.74309 6.77839 5.97625 6.60648 6.14816C6.43457 6.32007 6.20141 6.41665 5.9583 6.41665H4.40913C4.23496 6.41665 4.07455 6.51748 3.99663 6.67331L2.87371 8.91456C2.7958 9.0704 2.63996 9.16665 2.4658 9.16665H0.916631C0.916631 9.16665 -0.458369 9.16665 1.37496 6.41665C1.37496 6.41665 2.74996 4.58331 0.916631 4.58331V2.29165H1.37496L1.60413 1.83331H2.97913L3.2083 2.29165ZM6.41663 5.49998V5.04165C6.41663 4.92009 6.36834 4.80351 6.28239 4.71756C6.19643 4.6316 6.07985 4.58331 5.9583 4.58331H5.49996C5.49996 4.58331 5.04163 5.04165 5.49996 5.49998C5.25685 5.49998 5.02369 5.4034 4.85178 5.23149C4.67987 5.05959 4.5833 4.82643 4.5833 4.58331C4.46174 4.58331 4.34516 4.6316 4.25921 4.71756C4.17325 4.80351 4.12496 4.92009 4.12496 5.04165V5.49998C4.12496 5.62154 4.17325 5.73812 4.25921 5.82407C4.34516 5.91002 4.46174 5.95831 4.5833 5.95831H5.9583C6.07985 5.95831 6.19643 5.91002 6.28239 5.82407C6.36834 5.73812 6.41663 5.62154 6.41663 5.49998Z"
            fill="currentColor"
        />
    </svg>
);

const SBPIcon = () => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="10"
        height="13"
        viewBox="0 0 10 13"
        fill="none"
    >
        <g clipPath="url(#clip0_214_4982)">
            <path
                d="M0 2.69275L1.49814 5.37058V7.00399L0.00175258 9.67656L0 2.69275Z"
                fill="#5B57A2"
            />
            <path
                d="M5.75195 4.39617L7.15577 3.53576L10.0288 3.53308L5.75195 6.15308V4.39617Z"
                fill="#D90751"
            />
            <path
                d="M5.7442 2.67701L5.75214 6.22237L4.25049 5.29969V0L5.7442 2.67701Z"
                fill="#FAB718"
            />
            <path
                d="M10.0289 3.53309L7.15585 3.53577L5.7442 2.67701L4.25049 0L10.0289 3.53309Z"
                fill="#ED6F26"
            />
            <path
                d="M5.75214 9.69142V7.97132L4.25049 7.06616L4.25131 12.3711L5.75214 9.69142Z"
                fill="#63B22F"
            />
            <path
                d="M7.15258 8.83883L1.49804 5.37058L0 2.69275L10.023 8.83533L7.15258 8.83883Z"
                fill="#1487C9"
            />
            <path
                d="M4.25122 12.3711L5.75184 9.69141L7.15215 8.83883L10.0226 8.83533L4.25122 12.3711Z"
                fill="#017F36"
            />
            <path
                d="M0.00170898 9.6766L4.26284 7.06629L2.83027 6.18732L1.4981 7.00402L0.00170898 9.6766Z"
                fill="#984995"
            />
        </g>
        <defs>
            <clipPath id="clip0_214_4982">
                <rect width="10" height="12.3711" fill="white" />
            </clipPath>
        </defs>
    </svg>
);

const METHODS: { label: string; value: PayMethod; icon: React.ReactNode }[] = [
    { label: "Карты", value: "card", icon: <CardIcon /> },
    { label: "Crypto", value: "crypto", icon: <CryptoIcon /> },
    { label: "Skins", value: "skins", icon: <SkinsIcon /> },
];

const CURRENCIES: { label: string; value: Currency; prefix?: string }[] = [
    { label: "RUB", value: "RUB", prefix: "₽" },
    { label: "UAH", value: "UAH" },
    { label: "EUR", value: "EUR" },
    { label: "USD", value: "USD" },
    { label: "KZT", value: "KZT" },
    { label: "BYN", value: "BYN" },
];

const CRYPTO_NETWORKS: {
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

const SBP_SYSTEMS: { label: string; value: PaySystem }[] = [
    { label: "QR | СБП (A)", value: "qr_sbp_a" },
    { label: "QR | СБП (B)", value: "qr_sbp_b" },
];

const CURRENCY_SYMBOLS: Record<Currency, string> = {
    RUB: "₽",
    UAH: "₴",
    EUR: "€",
    USD: "$",
    KZT: "₸",
    BYN: "Br",
};

const MIN_AMOUNTS: Record<Currency, number> = {
    RUB: 50,
    UAH: 25,
    EUR: 1,
    USD: 2,
    KZT: 250,
    BYN: 2,
};

interface DepositModalProps {
    visible: boolean;
    onClose: () => void;
}

export default function DepositModal({ visible, onClose }: DepositModalProps) {
    const [method, setMethod] = useState<PayMethod>("card");
    const [currency, setCurrency] = useState<Currency>("RUB");
    const [cryptoNetwork, setCryptoNetwork] =
        useState<CryptoNetwork>("USDTTRC");
    const [paySystem, setPaySystem] = useState<PaySystem>("qr_sbp_a");

    // При смене валюты — автопереключение платёжной системы
    useEffect(() => {
        if (currency === "RUB") {
            if (paySystem === "visa") setPaySystem("qr_sbp_a");
        } else {
            setPaySystem("visa");
        }
    }, [currency]);
    const [amount, setAmount] = useState("1000");
    const [processing, setProcessing] = useState(false);
    const [rates, setRates] = useState<Record<string, number>>(FALLBACK_RATES);
    const [bonus, setBonus] = useState<{
        code: string;
        percent: number;
    } | null>(null);
    const [configLoaded, setConfigLoaded] = useState(false);

    useEffect(() => {
        if (visible && !configLoaded) {
            axios
                .get("/deposit/config")
                .then((res) => {
                    console.log('[DepositConfig] response:', res.data);
                    if (res.data.rates) setRates(res.data.rates);
                    if (res.data.bonus) setBonus(res.data.bonus);
                    setConfigLoaded(true);
                })
                .catch((err) => {
                    console.error('[DepositConfig] error:', err.response?.status, err.response?.data);
                    setConfigLoaded(true);
                });
        }
    }, [visible, configLoaded]);

    const numericAmount = parseInt(amount, 10) || 0;

    const activeNetwork =
        CRYPTO_NETWORKS.find((n) => n.value === cryptoNetwork) ??
        CRYPTO_NETWORKS[0];

    const rate =
        method === "crypto"
            ? (rates[activeNetwork.rateKey] ?? rates["USD"] ?? 96)
            : (rates[currency] ?? 1);

    const minAmount =
        method === "crypto" ? activeNetwork.min : MIN_AMOUNTS[currency];
    const currencySymbol =
        method === "crypto" ? activeNetwork.symbol : CURRENCY_SYMBOLS[currency];

    const credited = useMemo(() => {
        const base = Math.floor(numericAmount * rate);
        if (bonus) {
            return Math.floor(base * (1 + bonus.percent / 100));
        }
        return base;
    }, [numericAmount, rate, bonus]);

    const canDeposit = numericAmount >= minAmount;

    const handleDeposit = () => {
        if (!canDeposit) return;
        setProcessing(true);
        router.post(
            "/deposit",
            {
                amount: credited,
                method,
                currency,
                pay_system: method === "card" ? paySystem : undefined,
                crypto_network: method === "crypto" ? cryptoNetwork : undefined,
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <Modal visible={visible} onClose={onClose} maxWidth="max-w-[490px]">
            <span className="text-white font-gotham font-medium text-xl leading-[100%]">
                Пополнение баланса
            </span>

            {/* Метод */}
            <div className="flex flex-col gap-1.5">
                <span className="text-[#40404A] font-inter text-[14px] leading-[100%]">
                    Выберите метод
                </span>
                <div className="flex gap-1">
                    {METHODS.map((m) => (
                        <ChipButton
                            key={m.value}
                            active={method === m.value}
                            onClick={() => setMethod(m.value)}
                        >
                            <span
                                className={
                                    method === m.value
                                        ? "text-[#F6F0EA]"
                                        : "text-[#52565F]"
                                }
                            >
                                {m.icon}
                            </span>
                            <span className="font-sf-compact text-[13px]">
                                {m.label}
                            </span>
                        </ChipButton>
                    ))}
                </div>
            </div>

            {/* === CARD TAB === */}
            {method === "card" && (
                <>
                    {/* Валюта */}
                    <div className="flex flex-col gap-2">
                        <span className="text-white/40 font-sf-display text-[12px] leading-[120%]">
                            Выберите валюту пополнения
                        </span>
                        <div className="flex gap-1 flex-wrap">
                            {CURRENCIES.map((c) => (
                                <ChipButton
                                    key={c.value}
                                    active={currency === c.value}
                                    onClick={() => setCurrency(c.value)}
                                >
                                    {c.prefix && (
                                        <span
                                            className={`text-[11px] ${currency === c.value ? "text-white/70" : "text-white/20"}`}
                                        >
                                            {c.prefix}
                                        </span>
                                    )}
                                    {c.label}
                                </ChipButton>
                            ))}
                        </div>
                    </div>

                    {/* Платёжная система */}
                    {currency === "RUB" ? (
                        <div className="flex gap-1">
                            {SBP_SYSTEMS.map((ps) => (
                                <ChipButton
                                    key={ps.value}
                                    active={paySystem === ps.value}
                                    onClick={() => setPaySystem(ps.value)}
                                >
                                    <SBPIcon />
                                    <span className="font-sf-compact text-[13px]">
                                        {ps.label}
                                    </span>
                                </ChipButton>
                            ))}
                        </div>
                    ) : (
                        <div className="flex gap-1">
                            <ChipButton active={true} onClick={() => {}}>
                                <span className="font-sf-compact text-[13px]">
                                    VISA / Mastercard
                                </span>
                            </ChipButton>
                        </div>
                    )}

                    {/* Сумма */}
                    <AmountBlock
                        amount={amount}
                        onAmountChange={setAmount}
                        credited={credited}
                        minAmount={minAmount}
                        currencySymbol={currencySymbol}
                        bonus={bonus}
                    />
                </>
            )}

            {/* === CRYPTO TAB === */}
            {method === "crypto" && (
                <>
                    <div className="flex gap-[3px] flex-wrap">
                        {CRYPTO_NETWORKS.map((n) => (
                            <ChipButton
                                className={
                                    "w-full max-w-[144px] justify-center"
                                }
                                key={n.value}
                                active={cryptoNetwork === n.value}
                                onClick={() => setCryptoNetwork(n.value)}
                            >
                                {n.label}
                            </ChipButton>
                        ))}
                    </div>

                    <AmountBlock
                        amount={amount}
                        onAmountChange={setAmount}
                        credited={credited}
                        minAmount={minAmount}
                        currencySymbol={currencySymbol}
                        bonus={bonus}
                    />
                </>
            )}

            {/* === SKINS TAB === */}
            {method === "skins" && (
                <>
                    <div className="flex flex-col items-center gap-1.5">
                        <div className="flex py-[39px] px-[10px] items-center justify-center w-full rounded-[14px] bg-white/1">
                            <svg
                                width="272"
                                height="64"
                                viewBox="0 0 272 64"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M0.402549 45.7818V41.0938H20.2874C21.5291 41.0938 22.4843 40.776 23.1535 40.14C23.4887 39.8114 23.7514 39.4157 23.9245 38.9781C24.0973 38.5402 24.1767 38.0709 24.1573 37.6C24.1573 36.4474 23.8226 35.5824 23.1535 35.005C22.4843 34.4277 21.5291 34.141 20.2874 34.1448H8.10107C6.95243 34.1666 5.80928 33.9797 4.72629 33.5933C3.77523 33.2498 2.90539 32.7109 2.17198 32.0104C1.47403 31.3304 0.926904 30.509 0.566637 29.6002C0.180864 28.6272 -0.0114849 27.5872 0.000530065 26.5393C-0.0092824 25.5148 0.169693 24.4975 0.528352 23.539C0.868947 22.6399 1.4054 21.8295 2.09813 21.1674C2.84155 20.4752 3.71723 19.9431 4.6716 19.6039C5.79659 19.2057 6.98283 19.0125 8.17491 19.033H27.2203V23.721H8.17491C7.10285 23.721 6.27512 24.0087 5.69171 24.5841C5.10827 25.1595 4.81656 25.9565 4.81656 26.9749C4.81656 28.0064 5.11464 28.8032 5.71083 29.3658C6.30701 29.9282 7.11563 30.2106 8.13661 30.2125H20.2874C22.9895 30.2125 25.0397 30.8504 26.4381 32.1261C27.8365 33.4018 28.5357 35.3397 28.5357 37.9392C28.5453 39.0034 28.3723 40.0616 28.0243 41.0664C27.6979 41.9957 27.1755 42.8424 26.4927 43.5482C25.7635 44.276 24.8852 44.8341 23.9193 45.1834C22.7543 45.6024 21.5239 45.805 20.2874 45.7818H0.402549Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M32.5104 45.7803V19.0316H37.1432V45.7803H32.5104ZM54.3669 45.7803L39.5168 34.0053C39.1725 33.7741 38.9005 33.4488 38.732 33.0677C38.6194 32.7427 38.5637 32.4002 38.568 32.0557C38.5594 31.6762 38.6344 31.2997 38.7866 30.9528C39.0173 30.5378 39.3426 30.1842 39.7357 29.9213L53.7816 19.0316H60.8922L43.5288 32.0944L61.4448 45.7803H54.3669Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M64.6953 45.7803V19.0316H69.3281V45.7803H64.6953Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M78.4893 26.2067V45.7856H74.0725V21.4967C74.0314 20.76 74.2581 20.0336 74.7098 19.4533C74.9237 19.2038 75.1906 19.0065 75.4912 18.8761C75.7914 18.7457 76.117 18.6856 76.4437 18.7004C76.784 18.7011 77.1197 18.7764 77.4282 18.921C77.7973 19.1053 78.1309 19.3547 78.4128 19.6573L97.1328 38.3843V18.8107H101.547V43.3149C101.547 44.1973 101.334 44.884 100.909 45.3747C100.705 45.6147 100.449 45.8053 100.162 45.9325C99.8749 46.06 99.5629 46.1211 99.2493 46.1109C98.8482 46.0987 98.4538 46.0043 98.0901 45.8336C97.7261 45.6629 97.4002 45.4197 97.1328 45.1184L78.4893 26.2067Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M106.035 45.784V41.096H125.923C127.164 41.096 128.119 40.7779 128.786 40.1419C129.122 39.8141 129.385 39.4184 129.558 38.9805C129.731 38.5427 129.81 38.0731 129.79 37.6021C129.79 36.4493 129.455 35.5845 128.786 35.0072C128.117 34.4301 127.163 34.1432 125.923 34.1469H113.736C112.587 34.1685 111.443 33.9816 110.359 33.5955C109.409 33.252 108.54 32.7131 107.807 32.0125C107.109 31.3325 106.561 30.5112 106.199 29.6024C105.816 28.6317 105.624 27.5947 105.636 26.5497C105.626 25.5253 105.805 24.5079 106.164 23.5494C106.503 22.6498 107.04 21.8392 107.733 21.1778C108.476 20.485 109.352 19.9528 110.307 19.6143C111.431 19.2164 112.616 19.0231 113.808 19.0435H132.856V23.7314H113.808C112.739 23.7314 111.912 24.0191 111.327 24.5945C110.742 25.17 110.45 25.9641 110.452 26.9771C110.452 28.0085 110.75 28.8053 111.346 29.368C111.942 29.9333 112.749 30.2147 113.772 30.2147H125.923C128.623 30.2147 130.672 30.8525 132.071 32.1283C133.469 33.4043 134.168 35.3419 134.168 37.9413C134.179 39.0056 134.007 40.0635 133.66 41.0685C133.333 41.9984 132.809 42.8453 132.125 43.5504C131.397 44.2787 130.52 44.8371 129.555 45.1856C128.389 45.6013 127.159 45.8013 125.923 45.7757L106.035 45.784Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M138.109 45.781V19.0321H157.526C158.714 19.0186 159.899 19.167 161.049 19.4733C162.028 19.7308 162.952 20.1744 163.767 20.7804C164.516 21.3508 165.117 22.0955 165.517 22.9507C165.946 23.89 166.158 24.9147 166.138 25.9482C166.152 26.7069 166.053 27.4634 165.846 28.1928C165.675 28.7821 165.405 29.3365 165.044 29.8309C164.715 30.2754 164.313 30.6613 163.857 30.9725C163.426 31.2736 162.951 31.5061 162.449 31.6621C163.649 32.0381 164.701 32.7845 165.458 33.7962C166.234 34.8277 166.622 36.1282 166.622 37.6984C166.632 38.8341 166.422 39.961 166.004 41.0157C165.623 41.9824 165.037 42.8544 164.289 43.572C163.515 44.3042 162.595 44.8624 161.59 45.2101C160.459 45.6024 159.269 45.7954 158.073 45.781H138.109ZM146.1 34.144V30.217H157.559C158.871 30.217 159.845 29.9714 160.477 29.4808C161.11 28.9898 161.426 28.1544 161.426 26.9741C161.455 26.3773 161.316 25.7846 161.025 25.2643C160.746 24.826 160.347 24.4798 159.875 24.2688C159.308 24.0184 158.704 23.8638 158.086 23.8111C157.317 23.7333 156.544 23.6965 155.771 23.7007H142.759V41.1453H156.186C156.956 41.1504 157.724 41.0888 158.483 40.9605C159.12 40.8613 159.737 40.6565 160.308 40.3541C160.789 40.1 161.198 39.7261 161.494 39.2674C161.788 38.7677 161.934 38.1933 161.913 37.613C161.913 36.5098 161.573 35.6578 160.893 35.0568C160.213 34.4554 159.211 34.1512 157.888 34.144H146.1Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M198.316 45.7808L194.336 39.0467H181.785L183.974 35.2576H192.112L186.2 25.2504L174.167 45.7808H168.87L184.122 20.1737C184.357 19.7429 184.68 19.367 185.071 19.0706C185.454 18.7967 185.914 18.6554 186.384 18.668C186.848 18.6535 187.303 18.7952 187.679 19.0706C188.058 19.3733 188.375 19.7481 188.61 20.1737L203.897 45.7808H198.316Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M215.321 45.7808C213.557 45.7984 211.809 45.4413 210.192 44.733C208.641 44.0525 207.241 43.065 206.075 41.8293C204.909 40.5936 204.001 39.1344 203.404 37.5384C202.75 35.8128 202.423 33.9784 202.439 32.1306C202.416 30.2941 202.743 28.4701 203.404 26.7589C204.008 25.2047 204.929 23.7956 206.106 22.6224C207.278 21.4693 208.667 20.567 210.192 19.9696C211.827 19.3318 213.568 19.0136 215.321 19.032H229.695V23.72H215.321C214.187 23.7072 213.063 23.9258 212.016 24.3625C211.037 24.7734 210.149 25.3803 209.408 26.1466C208.663 26.9306 208.08 27.8557 207.693 28.8685C207.268 29.984 207.058 31.1704 207.072 32.365C207.062 33.5584 207.272 34.7434 207.693 35.8589C208.075 36.8912 208.659 37.8362 209.408 38.6386C210.147 39.4168 211.034 40.036 212.016 40.4586C213.06 40.9069 214.186 41.1322 215.321 41.1205H229.695V45.8085L215.321 45.7808Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M270.546 45.7653H262.368L244.474 32.0957L262.569 19.0302L269.997 19.0164L252.628 32.0791L270.546 45.7653Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M267.494 19.0212H260.383L245.587 29.9358C245.195 30.1979 244.869 30.5504 244.639 30.9643C244.486 31.3115 244.412 31.688 244.42 32.0675C244.416 32.4118 244.471 32.7544 244.583 33.0795C244.753 33.4606 245.024 33.7856 245.368 34.0171L260.218 45.7894H267.296L267.275 45.7728L249.37 32.1062L267.467 19.0405L267.494 19.0212Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M265.004 19.0212H257.894L243.098 29.9358C242.705 30.1979 242.379 30.5504 242.149 30.9643C241.997 31.3115 241.922 31.688 241.93 32.0675C241.926 32.4118 241.982 32.7544 242.094 33.0795C242.263 33.4606 242.535 33.7856 242.88 34.0171L257.73 45.7894H264.808L264.786 45.7728L246.891 32.1062L264.988 19.0405L265.004 19.0212Z"
                                    fill="#6198EA"
                                />
                                <path
                                    d="M233.456 19.0356V45.7843H238.089V19.0356H233.456ZM262.598 19.0107H255.487L240.681 29.9253C240.288 30.1875 239.962 30.54 239.732 30.9539C239.579 31.3011 239.505 31.6776 239.513 32.0571C239.509 32.4024 239.564 32.7456 239.677 33.0717C239.846 33.4528 240.118 33.7779 240.463 34.0093L255.313 45.7816H262.39L262.368 45.7653L244.474 32.0957L262.57 19.0273L262.598 19.0107Z"
                                    fill="#6198EA"
                                />
                            </svg>
                        </div>
                        <div className="flex px-6 py-3 rounded-[14px] bg-[#3D2D1A]/40 ">
                            <p className="text-[#E6935B] font-sf-display text-[11px] leading-[100%] text-center font-light">
                                Из-за новых правил Steam Trade Protection
                                депозит будет зачислен через 8 дней. В
                                определенных случаях депозит может быть зачислен
                                моментально.
                            </p>
                        </div>
                    </div>
                </>
            )}

            {/* Кнопка */}
            <button
                onClick={handleDeposit}
                disabled={!canDeposit || processing}
                style={{
                    background: canDeposit
                        ? "radial-gradient(80.57% 100% at 50% 100%, #4F86F5 0%, #05F 100%)"
                        : undefined,
                }}
                className={`w-full py-4 rounded-[76px] flex justify-center items-center cursor-pointer transition-all duration-200 ${
                    canDeposit
                        ? "hover:brightness-110 active:scale-[0.98]"
                        : "bg-white/5 opacity-40 cursor-not-allowed"
                }`}
            >
                <span className="text-white font-sf-display text-[14px] font-medium leading-[120%]">
                    {processing ? "Обработка..." : "Пополнить баланс"}
                </span>
            </button>

            <p className="text-white/12 font-sf-display font-medium text-[10px] leading-[120%] text-center">
                Если после оплаты прошло более 30 минут, а баланс на сайте не
                пополнился, то напишите нам в техподдержку
            </p>
        </Modal>
    );
}

function AmountBlock({
    amount,
    onAmountChange,
    credited,
    minAmount,
    currencySymbol,
    bonus,
}: {
    amount: string;
    onAmountChange: (v: string) => void;
    credited: number;
    minAmount: number;
    currencySymbol: string;
    bonus: { code: string; percent: number } | null;
}) {
    return (
        <div className="flex flex-col gap-2">
            <div className="flex gap-2.5">
                <div className="flex flex-col gap-6 flex-1 w-0 min-w-0 p-3.5 rounded-[12px] bg-white/1">
                    <span className="text-white font-inter text-[11px] leading-[100%]">
                        Сумма пополнения
                    </span>
                    <div className="flex items-end gap-1">
                        <input
                            type="text"
                            inputMode="numeric"
                            value={amount}
                            onChange={(e) =>
                                onAmountChange(
                                    e.target.value.replace(/\D/g, ""),
                                )
                            }
                            className="bg-transparent outline-none text-white font-sf-compact text-[40px] font-thin leading-[100%] w-full min-w-0"
                        />
                        <span className="text-white/24 whitespace-nowrap font-sf-display text-[9px] leading-[100%] pb-1">
                            Мин. {minAmount} {currencySymbol}
                        </span>
                    </div>
                </div>

                <div className="flex flex-col gap-6 flex-1 w-0 min-w-0 p-3.5 rounded-[12px] bg-white/1">
                    <span className="text-[#40404A] font-inter text-[11px] leading-[100%]">
                        Будет начислено
                    </span>
                    <div className="flex items-end gap-1 overflow-hidden">
                        <span className="text-[#0055FF] font-sf-compact text-[40px] font-thin leading-[100%] truncate">
                            {credited > 0
                                ? credited.toLocaleString("ru-RU")
                                : "0"}
                        </span>
                    </div>
                </div>
            </div>

            {bonus && (
                <div className="flex items-center gap-1.5 px-3 py-2 rounded-[8px] bg-[#00BF6C]/10 border border-[#00BF6C]/20">
                    <span className="text-[#00BF6C] font-sf-display text-[12px] leading-[120%]">
                        +{bonus.percent}% бонус к пополнению
                    </span>
                    <span className="text-[#00BF6C]/50 font-sf-display text-[10px] leading-[120%]">
                        {bonus.code}
                    </span>
                </div>
            )}
        </div>
    );
}

function ChipButton({
    children,
    active,
    onClick,
    className = "",
}: {
    children: React.ReactNode;
    active: boolean;
    onClick: () => void;
    className?: string | null;
}) {
    return (
        <button
            onClick={onClick}
            className={`${className ?? ""} flex items-center gap-[3px] py-[7px] px-2.5 rounded-[42px] cursor-pointer transition-colors font-sf-display text-[12px] leading-[120%] ${
                active
                    ? "bg-[#4E89FF] text-white"
                    : "border border-[#14161A] text-[#52565F] hover:bg-white/8"
            }`}
        >
            {children}
        </button>
    );
}
