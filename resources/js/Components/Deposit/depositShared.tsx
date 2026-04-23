import React from "react";

export function ChipButton({
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

export function AmountBlock({
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
