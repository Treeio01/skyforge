import Modal from "@/Components/UI/Modal";
import React, { useMemo } from "react";
import { router } from "@inertiajs/react";
import { useDeposit } from "./useDeposit";
import { CRYPTO_NETWORKS, MIN_AMOUNTS, CURRENCY_SYMBOLS } from "./depositConstants";
import DepositMethodSelector from "./DepositMethodSelector";
import DepositCardForm from "./DepositCardForm";
import DepositCryptoForm from "./DepositCryptoForm";
import DepositSkinsForm from "./DepositSkinsForm";

interface DepositModalProps {
    visible: boolean;
    onClose: () => void;
}

export default function DepositModal({ visible, onClose }: DepositModalProps) {
    const {
        method,
        setMethod,
        currency,
        setCurrency,
        cryptoNetwork,
        setCryptoNetwork,
        sbpSystem,
        setSbpSystem,
        amount,
        setAmount,
        processing,
        setProcessing,
        rates,
        bonus,
    } = useDeposit(visible);

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
                pay_system: method === "card" ? sbpSystem : undefined,
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

            <DepositMethodSelector method={method} onChange={setMethod} />

            {method === "card" && (
                <DepositCardForm
                    currency={currency}
                    onCurrencyChange={setCurrency}
                    sbpSystem={sbpSystem}
                    onSbpSystemChange={setSbpSystem}
                    amount={amount}
                    onAmountChange={setAmount}
                    credited={credited}
                    bonus={bonus}
                />
            )}

            {method === "crypto" && (
                <DepositCryptoForm
                    cryptoNetwork={cryptoNetwork}
                    onNetworkChange={setCryptoNetwork}
                    amount={amount}
                    onAmountChange={setAmount}
                    credited={credited}
                    bonus={bonus}
                />
            )}

            {method === "skins" && <DepositSkinsForm />}

            {/* Кнопка */}
            <button
                onClick={handleDeposit}
                disabled={!canDeposit || processing || method === "skins"}
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
