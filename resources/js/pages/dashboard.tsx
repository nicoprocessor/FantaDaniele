import { Head } from '@inertiajs/react';
import { GameCard } from '@/components/fantadaniele/game-card';
import { Leaderboard } from '@/components/fantadaniele/leaderboard';
import { dashboard } from '@/routes';
import type { FantaDashboardProps } from '@/types';

type Props = FantaDashboardProps;

export default function Dashboard({
    game,
    metrics,
    leaderboard,
    canStartGame,
}: Props) {
    return (
        <>
            <Head title="FantaDaniele" />
            <main className="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
                <GameCard game={game} canStartGame={canStartGame ?? false} />
                <div className="grid gap-4 py-6 sm:grid-cols-2 lg:grid-cols-4">
                    {[
                        ['Proprietà disponibili', metrics?.available ?? 0],
                        ['Partite giocate', metrics?.gamesPlayed ?? 0],
                        [
                            'Vinte · pari · perse',
                            `${metrics?.wins ?? 0} · ${metrics?.draws ?? 0} · ${metrics?.losses ?? 0}`,
                        ],
                        ['Percentuale vittorie', `${metrics?.winRate ?? 0}%`],
                    ].map(([label, value]) => (
                        <section className="border p-4" key={label}>
                            <p className="text-sm text-muted-foreground">
                                {label}
                            </p>
                            <p className="mt-1 text-xl font-semibold">
                                {value}
                            </p>
                        </section>
                    ))}
                </div>
                <div className="border-t pt-8">
                    <Leaderboard leaderboard={leaderboard} />
                </div>
            </main>
        </>
    );
}

Dashboard.layout = () => ({
    breadcrumbs: [
        {
            title: 'FantaDaniele',
            href: dashboard(),
        },
    ],
});
