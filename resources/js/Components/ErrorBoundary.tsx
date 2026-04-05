import { Component, type ErrorInfo, type ReactNode } from 'react';

interface Props {
    children: ReactNode;
}

interface State {
    hasError: boolean;
}

export default class ErrorBoundary extends Component<Props, State> {
    constructor(props: Props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(): State {
        return { hasError: true };
    }

    componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
        console.error('[ErrorBoundary]', error, errorInfo);
    }

    render(): ReactNode {
        if (this.state.hasError) {
            return (
                <div className="flex min-h-screen flex-col items-center justify-center bg-[#0a0a0a] text-[#f5f5f5] px-4">
                    <h1 className="text-3xl font-bold">Произошла ошибка</h1>
                    <p className="mt-3 text-[#888888]">
                        Что-то пошло не так. Попробуйте перезагрузить страницу.
                    </p>
                    <button
                        onClick={() => window.location.reload()}
                        className="mt-6 inline-flex items-center rounded-lg bg-accent px-6 py-3 text-base font-bold text-[#0a0a0a] hover:bg-accent-hover transition-colors"
                    >
                        Перезагрузить
                    </button>
                </div>
            );
        }

        return this.props.children;
    }
}
