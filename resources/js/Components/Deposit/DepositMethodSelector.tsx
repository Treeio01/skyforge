import React from "react";
import type { DepositMethod } from "./depositConstants";
import { ChipButton } from "./depositShared";

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

const SBPMethodIcon = () => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="10"
        height="13"
        viewBox="0 0 10 13"
        fill="none"
    >
        <g clipPath="url(#clip0_sbp_method)">
            <path d="M0 2.69275L1.49814 5.37058V7.00399L0.00175258 9.67656L0 2.69275Z" fill="#5B57A2" />
            <path d="M5.75195 4.39617L7.15577 3.53576L10.0288 3.53308L5.75195 6.15308V4.39617Z" fill="#D90751" />
            <path d="M5.7442 2.67701L5.75214 6.22237L4.25049 5.29969V0L5.7442 2.67701Z" fill="#FAB718" />
            <path d="M10.0289 3.53309L7.15585 3.53577L5.7442 2.67701L4.25049 0L10.0289 3.53309Z" fill="#ED6F26" />
            <path d="M5.75214 9.69142V7.97132L4.25049 7.06616L4.25131 12.3711L5.75214 9.69142Z" fill="#63B22F" />
            <path d="M7.15258 8.83883L1.49804 5.37058L0 2.69275L10.023 8.83533L7.15258 8.83883Z" fill="#1487C9" />
            <path d="M4.25122 12.3711L5.75184 9.69141L7.15215 8.83883L10.0226 8.83533L4.25122 12.3711Z" fill="#017F36" />
            <path d="M0.00170898 9.6766L4.26284 7.06629L2.83027 6.18732L1.4981 7.00402L0.00170898 9.6766Z" fill="#984995" />
        </g>
        <defs>
            <clipPath id="clip0_sbp_method">
                <rect width="10" height="12.3711" fill="white" />
            </clipPath>
        </defs>
    </svg>
);

const METHODS: { label: string; value: DepositMethod; icon: React.ReactNode }[] = [
    { label: "Карты", value: "card", icon: <CardIcon /> },
    { label: "СБП", value: "sbp", icon: <SBPMethodIcon /> },
    { label: "Crypto", value: "crypto", icon: <CryptoIcon /> },
    { label: "Skins", value: "skins", icon: <SkinsIcon /> },
];

interface DepositMethodSelectorProps {
    method: DepositMethod;
    onChange: (m: DepositMethod) => void;
}

export default function DepositMethodSelector({
    method,
    onChange,
}: DepositMethodSelectorProps) {
    return (
        <div className="flex flex-col gap-1.5">
            <span className="text-[#40404A] font-inter text-[14px] leading-[100%]">
                Выберите метод
            </span>
            <div className="flex gap-1">
                {METHODS.map((m) => (
                    <ChipButton
                        key={m.value}
                        active={method === m.value}
                        onClick={() => onChange(m.value)}
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
    );
}
