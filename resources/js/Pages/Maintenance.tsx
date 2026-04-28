import { Head } from '@inertiajs/react';
import { motion } from 'framer-motion';

interface MaintenanceProps {
    message: string;
}

export default function Maintenance({ message }: MaintenanceProps) {
    return (
        <>
            <Head title="Обслуживание" />
            <div className="min-h-screen flex items-center justify-center bg-[#070A10] px-6 py-12">
                <motion.div
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
                    className="flex flex-col items-center gap-6 max-w-[480px] text-center"
                >
                    <div className="relative">
                        <div
                            className="absolute inset-0 -m-12 rounded-full blur-3xl"
                            style={{
                                background:
                                    'radial-gradient(circle, rgba(78,137,255,0.25) 0%, transparent 70%)',
                            }}
                        />
                        <img
                            src="/assets/img/logo.png"
                            alt="GrowSkins"
                            className="relative h-12 w-auto object-contain"
                        />
                    </div>

                    <div className="flex flex-col gap-3">
                        <h1 className="text-white font-gotham font-medium text-2xl 1024:text-[28px] leading-[110%]">
                            Сайт на обслуживании
                        </h1>
                        <p className="text-white/55 font-sf-display text-[14px] leading-[170%]">
                            {message}
                        </p>
                    </div>

                    <div className="mt-2 h-1 w-32 rounded-full overflow-hidden bg-white/8">
                        <motion.div
                            className="h-full"
                            style={{
                                background:
                                    'linear-gradient(90deg, #4E89FF 0%, #6BA3FF 50%, #4E89FF 100%)',
                            }}
                            initial={{ x: '-100%' }}
                            animate={{ x: '100%' }}
                            transition={{
                                duration: 1.6,
                                ease: 'linear',
                                repeat: Infinity,
                            }}
                        />
                    </div>
                </motion.div>
            </div>
        </>
    );
}
