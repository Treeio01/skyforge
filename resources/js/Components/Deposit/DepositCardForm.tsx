import React from "react";
import {
    CURRENCIES,
    SBP_SYSTEMS,
    CURRENCY_SYMBOLS,
    MIN_AMOUNTS,
    type Currency,
    type PaySystem,
} from "./depositConstants";
import { ChipButton, AmountBlock } from "./depositShared";
import Button from "@/Components/UI/Button";

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

interface DepositCardFormProps {
    currency: Currency;
    onCurrencyChange: (v: Currency) => void;
    sbpSystem: PaySystem;
    onSbpSystemChange: (v: PaySystem) => void;
    amount: string;
    onAmountChange: (v: string) => void;
    credited: number;
    bonus: { code: string; percent: number } | null;
    processing: boolean;
    onSubmit: () => void;
}

export default function DepositCardForm({
    currency,
    onCurrencyChange,
    sbpSystem,
    onSbpSystemChange,
    amount,
    onAmountChange,
    credited,
    bonus,
    processing,
    onSubmit,
}: DepositCardFormProps) {
    const minAmount = MIN_AMOUNTS[currency];
    const currencySymbol = CURRENCY_SYMBOLS[currency];

    return (
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
                            onClick={() => onCurrencyChange(c.value)}
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
                            active={sbpSystem === ps.value}
                            onClick={() => onSbpSystemChange(ps.value)}
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
                onAmountChange={onAmountChange}
                credited={credited}
                minAmount={minAmount}
                currencySymbol={currencySymbol}
                bonus={bonus}
            />

            {/* Кнопка */}
            <Button loading={processing} onClick={onSubmit} size="lg" className="w-full">
                Пополнить
            </Button>
        </>
    );
}
