import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Chip, ChipLabel } from '../primitives';

export default function ProfileChip() {
    const user = usePage<PageProps>().props.auth.user;

    if (!user) {
        return null;
    }

    return (
        <Link href="/profile" prefetch="hover">
            <Chip interactive className="bg-chip px-2.5">
                <img
                    src={user.avatar_url ?? ''}
                    className="w-[24px] h-[24px] rounded-full overflow-hidden"
                    alt=""
                />
                <ChipLabel className="hidden wide:inline">
                    {user.username}
                </ChipLabel>
            </Chip>
        </Link>
    );
}
