import { useEffect, useState } from "react";
import axios from "axios";
import {
    Currency,
    CryptoNetwork,
    PayMethod,
    PaySystem,
    FALLBACK_RATES,
} from "./depositConstants";

export interface DepositState {
    method: PayMethod;
    setMethod: (v: PayMethod) => void;
    currency: Currency;
    setCurrency: (v: Currency) => void;
    cryptoNetwork: CryptoNetwork;
    setCryptoNetwork: (v: CryptoNetwork) => void;
    paySystem: PaySystem;
    setPaySystem: (v: PaySystem) => void;
    amount: string;
    setAmount: (v: string) => void;
    processing: boolean;
    setProcessing: (v: boolean) => void;
    rates: Record<string, number>;
    setRates: (v: Record<string, number>) => void;
    bonus: { code: string; percent: number } | null;
    setBonus: (v: { code: string; percent: number } | null) => void;
    configLoaded: boolean;
    setConfigLoaded: (v: boolean) => void;
    configError: boolean;
}

export function useDeposit(visible: boolean): DepositState {
    const [method, setMethod] = useState<PayMethod>("card");
    const [currency, setCurrency] = useState<Currency>("RUB");
    const [cryptoNetwork, setCryptoNetwork] =
        useState<CryptoNetwork>("USDTTRC");
    const [paySystem, setPaySystem] = useState<PaySystem>("qr_sbp_a");
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
        if (currency === "RUB") {
            if (paySystem === "visa") setPaySystem("qr_sbp_a");
        } else {
            setPaySystem("visa");
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
        paySystem,
        setPaySystem,
        amount,
        setAmount,
        processing,
        setProcessing,
        rates,
        setRates,
        bonus,
        setBonus,
        configLoaded,
        setConfigLoaded,
        configError,
    };
}
