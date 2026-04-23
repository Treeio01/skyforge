export default function AppFooter() {
    return (
        <footer className="bg-[#080B10] border-t border-white/6 px-4 1024:px-8">
            <div className="grid grid-cols-2 1024:grid-cols-4 gap-8 pt-10 pb-8">
                {/* Бренд */}
                <div className="col-span-2 1024:col-span-1 flex flex-col gap-3">
                    <img src="/assets/img/logo.png" alt="SkyForge" className="h-8 w-auto object-contain object-left" />
                    <p className="text-white/30 font-sf-display text-[12px] leading-[180%] max-w-[180px]">
                        Честный апгрейд скинов CS2 с открытым алгоритмом Provably Fair.
                    </p>
                    <div className="flex items-center gap-3 mt-1">
                        <a href="#" className="flex items-center justify-center w-8 h-8 rounded-[8px] bg-white/6 hover:bg-white/12 transition-colors">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" className="text-white/50"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.6.113.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0 1 12 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                        <a href="#" className="flex items-center justify-center w-8 h-8 rounded-[8px] bg-white/6 hover:bg-white/12 transition-colors">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" className="text-white/50"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        </a>
                        <a href="#" className="flex items-center justify-center w-8 h-8 rounded-[8px] bg-white/6 hover:bg-white/12 transition-colors">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" className="text-white/50"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12c0 3.259.014 3.668.072 4.948.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24c3.259 0 3.668-.014 4.948-.072 1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.689.072-4.948 0-3.259-.014-3.667-.072-4.947-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                        </a>
                    </div>
                </div>

                {/* Игры */}
                <div className="flex flex-col gap-3">
                    <span className="text-white/60 font-sf-display text-[11px] uppercase tracking-[0.08em]">Игры</span>
                    <div className="flex flex-col gap-2.5">
                        <a href="/" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">Апгрейд</a>
                        <a href="/market" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">Маркет</a>
                        <a href="/provably-fair" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">Provably Fair</a>
                    </div>
                </div>

                {/* Поддержка */}
                <div className="flex flex-col gap-3">
                    <span className="text-white/60 font-sf-display text-[11px] uppercase tracking-[0.08em]">Поддержка</span>
                    <div className="flex flex-col gap-2.5">
                        <a href="/provably-fair" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">FAQ</a>
                        <a href="#" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">Telegram</a>
                        <a href="#" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">Обратная связь</a>
                    </div>
                </div>

                {/* Правовое */}
                <div className="flex flex-col gap-3">
                    <span className="text-white/60 font-sf-display text-[11px] uppercase tracking-[0.08em]">Правовое</span>
                    <div className="flex flex-col gap-2.5">
                        <a href="#" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">Пользовательское соглашение</a>
                        <a href="#" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">Политика конфиденциальности</a>
                        <a href="#" className="text-white/35 font-sf-display text-[13px] hover:text-white/60 transition-colors">Ответственная игра</a>
                    </div>
                </div>
            </div>

            {/* Нижняя строка */}
            <div className="flex flex-col xs:flex-row items-center justify-between gap-3 py-5 border-t border-white/4">
                <span className="text-white/20 font-sf-display text-[12px]">© 2026 SkyForge. Все права защищены.</span>
                <span className="text-white/15 font-sf-display text-[11px] text-center">
                    Сайт не связан с Valve Corporation. CS2 — товарный знак Valve.
                </span>
            </div>
        </footer>
    );
}
