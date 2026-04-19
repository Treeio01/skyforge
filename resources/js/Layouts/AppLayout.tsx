import { ReactNode } from 'react';
import Header from '@/Components/Layout/Header';
import LiveFeed from '@/Components/Upgrade/LiveFeed';

interface AppLayoutProps {
    children: ReactNode;
}

export default function AppLayout({ children }: AppLayoutProps) {
    return (
        <div className="flex items-stretch min-h-screen 1024:flex-row flex-col">
            <LiveFeed />
            <div className="flex flex-col w-full flex-1 min-h-0">
                <Header />
                {children}
            </div>
        </div>
    );
}
