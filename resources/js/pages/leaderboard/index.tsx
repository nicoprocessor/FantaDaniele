import { Head } from '@inertiajs/react';
import { Leaderboard } from '@/components/fantadaniele/leaderboard';
import { index } from '@/routes/leaderboard';
import type { LeaderboardEntry } from '@/types';

export default function LeaderboardIndex({
    leaderboard,
}: {
    leaderboard: LeaderboardEntry[];
}) {
    return (
        <>
            <Head title="Classifica" />
            <main className="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
                <Leaderboard leaderboard={leaderboard} />
            </main>
        </>
    );
}

LeaderboardIndex.layout = () => ({
    breadcrumbs: [{ title: 'Classifica', href: index() }],
});
