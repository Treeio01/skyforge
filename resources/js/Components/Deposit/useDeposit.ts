import { useEffect, useState } from "react";
import axios from "axios";
import {
    Currency,
    CryptoNetwork,
    DepositMethod,
    PaySystem,
    FALLBACK_RATES,
} from "./depositConstants";

export interface DepositState {
    method: DepositMethod;
    setMethod: (v: DepositMethod) => void;
    currency: Currency;
    setCurrency: (v: Currency) => void;
    cryptoNetwork: CryptoNetwork;
    setCryptoNetwork: (v: CryptoNetwork) => void;
    sbpSystem: PaySystem;
    setSbpSystem: (v: PaySystem) => void;
    amount: string;
    setAmount: (v: string) => void;
    processing: boolean;
    setProcessing: (v: boolean) => void;
    rates: Record<string, number>;
    bonus: { code: string; percent: number } | null;
    configLoaded: boolean;
    configError: boolean;
}

export function useDeposit(visible: boolean): DepositState {
    const [method, setMethod] = useState<DepositMethod>("card");
    const [currency, setCurrency] = useState<Currency>("RUB");
    const [cryptoNetwork, setCryptoNetwork] =
        useState<CryptoNetwork>("USDTTRC");
    const [sbpSystem, setSbpSystem] = useState<PaySystem>("qr_sbp_a");
    const [amount, setAmount] = useState("1000");
    const [processing, setProcessing] = useState(false);
    const [rates, setRates] = useState<Record<string, number>>(FALLBACK_RATES);
    const [bonus, setBonus] = useState<{
        code: string;
        percent: number;
    } | null>(null);
    const [configLoaded, setConfigLoaded] = useState(false);
    const [configError, setConfigError] = useState(false);

    // При смене валюты — автопереключение платёжной системы
    useEffect(() => {
        if (currency !== "RUB") {
            setSbpSystem("visa");
        } else {
            setSbpSystem((prev) => (prev === "visa" ? "qr_sbp_a" : prev));
        }
    }, [currency]);

    useEffect(() => {
        if (visible && !configLoaded) {
            axios
                .get("/deposit/config")
                .then((res) => {
                    if (res.data.rates) setRates(res.data.rates);
                    if (res.data.bonus) setBonus(res.data.bonus);
                    setConfigLoaded(true);
                })
                .catch((err) => {
                    console.error(
                        "[DepositConfig] error:",
                        err.response?.status,
                        err.response?.data,
                    );
                    setConfigLoaded(true);
                    setConfigError(true);
                });
        }
    }, [visible, configLoaded]);

    return {
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
        configLoaded,
        configError,
    };
}
