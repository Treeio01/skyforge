import React from "react";
import { CRYPTO_NETWORKS, type CryptoNetwork } from "./depositConstants";
import { ChipButton, AmountBlock } from "./depositShared";

interface DepositCryptoFormProps {
    network: CryptoNetwork;
    onNetworkChange: (v: CryptoNetwork) => void;
    amount: string;
    onAmountChange: (v: string) => void;
    credited: number;
    bonus: { code: string; percent: number } | null;
}

export default function DepositCryptoForm({
    network,
    onNetworkChange,
    amount,
    onAmountChange,
    credited,
    bonus,
}: DepositCryptoFormProps) {
    const activeNetwork =
        CRYPTO_NETWORKS.find((n) => n.value === network) ??
        CRYPTO_NETWORKS[0];

    return (
        <>
            <div className="flex gap-[3px] flex-wrap">
                {CRYPTO_NETWORKS.map((n) => (
                    <ChipButton
                        className={"w-full max-w-[144px] justify-center"}
                        key={n.value}
                        active={network === n.value}
                        onClick={() => onNetworkChange(n.value)}
                    >
                        {n.label}
                    </ChipButton>
                ))}
            </div>

            <AmountBlock
                amount={amount}
                onAmountChange={onAmountChange}
                credited={credited}
                minAmount={activeNetwork.min}
                currencySymbol={activeNetwork.symbol}
                bonus={bonus}
            />
        </>
    );
}
