import { ReactNode } from 'react';
import Header from '@/Components/Layout/Header';
import AppFooter from '@/Components/Layout/AppFooter';
import LiveFeed from '@/Components/Upgrade/LiveFeed';
import LoginModal from '@/Components/UI/LoginModal';

interface AppLayoutProps {
    children: ReactNode;
}

export default function AppLayout({ children }: AppLayoutProps) {
    return (
        <div className="flex items-stretch min-h-screen 1024:flex-row flex-col">
            <LiveFeed />
            <div className="flex flex-col w-full flex-1 min-h-0 overflow-y-auto">
                <Header />
                {children}
                <AppFooter />
            </div>
            <LoginModal />
        </div>
    );
}
