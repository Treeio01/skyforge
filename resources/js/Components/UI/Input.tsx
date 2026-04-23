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
                <label className="text-xs text-text-muted font-medium">{label}</label>
            )}
            <div className="relative flex items-center">
                {prefix && (
                    <span className="absolute left-3 text-text-dim text-sm select-none">{prefix}</span>
                )}
                <input
                    ref={ref}
                    {...props}
                    className={`w-full bg-surface-2 border rounded-[10px] px-3 py-2.5 text-sm text-white placeholder:text-text-dim outline-none transition-colors duration-200
                        focus:border-brand/50
                        ${error ? 'border-danger/60' : 'border-border'}
                        ${prefix ? 'pl-8' : ''}
                        ${suffix ? 'pr-8' : ''}
                        ${className}`}
                />
                {suffix && (
                    <span className="absolute right-3 text-text-dim text-sm select-none">{suffix}</span>
                )}
            </div>
            {error && <p className="text-xs text-danger">{error}</p>}
            {hint && !error && <p className="text-xs text-text-dim">{hint}</p>}
        </div>
    );
});

Input.displayName = 'Input';

export default Input;
