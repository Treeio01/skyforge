import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { FormEvent, useState } from 'react';

const METHODS = [
    { key: 'sbp', label: 'СБП' },
    { key: 'crypto', label: 'Крипто' },
] as const;

export default function Create() {
    const [selectedMethod, setSelectedMethod] = useState<string>('sbp');

    const form = useForm({
        amount: '',
        method: 'sbp',
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        form.transform((d) => ({
            ...d,
            amount: Math.round(Number(d.amount) * 100),
        }));
        form.post(route('deposit.store'));
    }

    function selectMethod(method: string) {
        setSelectedMethod(method);
        form.setData('method', method);
    }

    return (
        <AppLayout>
            <Head title="Пополнение" />

            <div className="max-w-lg mx-auto px-4 py-10 space-y-8">
                <h1 className="text-2xl font-bold">Пополнение баланса</h1>

                {/* Method Selector */}
                <div className="grid grid-cols-2 gap-4">
                    {METHODS.map((method) => (
                        <button
                            key={method.key}
                            type="button"
                            onClick={() => selectMethod(method.key)}
                            className={`rounded-xl border p-5 text-center font-semibold transition-colors ${
                                selectedMethod === method.key
                                    ? 'border-accent bg-accent-dim text-accent'
                                    : 'border-border bg-card text-[#888888] hover:border-border-hover hover:text-[#f5f5f5]'
                            }`}
                        >
                            {method.label}
                        </button>
                    ))}
                </div>

                {/* Amount Form */}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label htmlFor="amount" className="block text-sm font-medium text-[#888888] mb-2">
                            Сумма (RUB)
                        </label>
                        <input
                            id="amount"
                            type="number"
                            min="100"
                            max="100000"
                            step="1"
                            value={form.data.amount}
                            onChange={(e) => form.setData('amount', e.target.value)}
                            placeholder="500"
                            className="w-full rounded-lg border border-border bg-[#0a0a0a] px-4 py-3 text-lg text-[#f5f5f5] placeholder-[#888888] focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none"
                        />
                        {form.errors.amount && (
                            <p className="mt-2 text-sm text-[#ef4444]">{form.errors.amount}</p>
                        )}
                        <p className="mt-2 text-xs text-[#888888]">Мин. 100 RUB, макс. 100 000 RUB</p>
                    </div>

                    <button
                        type="submit"
                        disabled={form.processing}
                        className="w-full rounded-lg bg-accent px-6 py-3 text-base font-bold text-[#0a0a0a] hover:bg-accent-hover transition-colors disabled:opacity-50"
                    >
                        Пополнить
                    </button>
                </form>
            </div>
        </AppLayout>
    );
}
