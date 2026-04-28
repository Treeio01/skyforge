import { InputHTMLAttributes, forwardRef } from 'react';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    hint?: string;
    prefix?: string;
    suffix?: string;
}

const Input = forwardRef<HTMLInputElement, InputProps>(({
    label,
    error,
    hint,
    prefix,
    suffix,
    className = '',
    ...props
}, ref) => {
    return (
        <div className="flex flex-col gap-1.5 w-full">
            {label && (
                <label className="text-[11px] uppercase tracking-[0.08em] text-white/40 font-sf-display">{label}</label>
            )}
            <div className="relative flex items-center">
                {prefix && (
                    <span className="absolute left-3 text-white/40 text-[12px] font-sf-display select-none pointer-events-none">{prefix}</span>
                )}
                <input
                    ref={ref}
                    {...props}
                    className={`w-full h-10 bg-[#161B26] hover:bg-[#1B2230] focus:bg-[#1B2230] rounded-[10px] px-3 text-[13px] text-white placeholder:text-white/30 outline-none ring-1 ring-inset ring-transparent focus:ring-white/15 transition-colors duration-200 font-sf-display
                        ${error ? 'ring-red-500/40' : ''}
                        ${prefix ? 'pl-9' : ''}
                        ${suffix ? 'pr-9' : ''}
                        ${className}`}
                />
                {suffix && (
                    <span className="absolute right-3 text-white/40 text-[12px] font-sf-display select-none pointer-events-none">{suffix}</span>
                )}
            </div>
            {error && <p className="text-[11px] text-red-400">{error}</p>}
            {hint && !error && <p className="text-[11px] text-white/35">{hint}</p>}
        </div>
    );
});

Input.displayName = 'Input';

export default Input;
