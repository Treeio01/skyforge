import { memo } from 'react';
import { motion } from 'framer-motion';
import { LiveFeedEntry } from './LiveFeed';

export type SkinRarity =
    | 'cons'
    | 'indus'
    | 'mils'
    | 'restr'
    | 'class'
    | 'covrt'
    | 'contra'
    | 'extra';

interface LiveFeedItemProps {
    item: LiveFeedEntry;
}

const LiveFeedItem = memo(function LiveFeedItem({ item }: LiveFeedItemProps) {
    return (
        <motion.div
            layout
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.25, ease: 'easeOut' }}
            className={`rarity-${item.rarity} grid`}
        >
            <div className="min-h-0 overflow-hidden">
                <div className="animate-feed-reveal flex w-full items-stretch rounded-r-[6px] overflow-hidden">
                    <div className="flex w-[2px] bg-[var(--rarity-accent)]" />
                    <div className="flex w-full px-2.5 pb-2.5 pt-[62px] relative bg-linear-to-r from-[var(--rarity-from)] to-[var(--rarity-to)]">
                        <img
                            src="/assets/img/live-bar-item-bg.png"
                            className="absolute top-0 left-0 w-full h-full object-cover"
                            alt=""
                        />
                        <img
                            src={item.image}
                            className="absolute left-1/2 top-1/2 -translate-1/2 z-10 max-w-[100px] max-h-[75px] w-full h-auto object-contain"
                            alt=""
                        />
                        <span className="text-white text-[13px] leading-[104%] relative z-10 font-sf-display">
                            {item.weapon} <br /> {item.name}
                        </span>
                    </div>
                </div>
            </div>
        </motion.div>
    );
});

export default LiveFeedItem;
