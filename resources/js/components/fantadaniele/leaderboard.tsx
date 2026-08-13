import { Trophy } from 'lucide-react';
import type { LeaderboardEntry } from '@/types';
import { EmptyState } from './empty-state';

function historyPoints(history: number[]): string {
    if (history.length < 2) {
        return '';
    }

    const min = Math.min(...history);
    const max = Math.max(...history);
    const range = max - min || 1;

    return history
        .map(
            (value, index) =>
                `${(index / (history.length - 1)) * 100},${34 - ((value - min) / range) * 30}`,
        )
        .join(' ');
}

export function Leaderboard({
    leaderboard,
}: {
    leaderboard: LeaderboardEntry[] | undefined;
}) {
    return (
        <section aria-labelledby="leaderboard-title">
            <div className="flex items-center gap-2">
                <Trophy className="size-5" />
                <h2 id="leaderboard-title" className="text-lg font-semibold">
                    Classifica
                </h2>
            </div>
            {!leaderboard?.length ? (
                <div className="mt-4">
                    <EmptyState>
                        Classifica disponibile dopo la prima partita conclusa.
                    </EmptyState>
                </div>
            ) : (
                <div className="mt-4 overflow-x-auto border">
                    <table className="w-full min-w-[520px] text-left text-sm">
                        <thead className="border-b text-muted-foreground">
                            <tr>
                                <th className="px-3 py-2 font-medium">#</th>
                                <th className="px-3 py-2 font-medium">
                                    Giocatore
                                </th>
                                <th className="px-3 py-2 font-medium">Punti</th>
                                <th className="px-3 py-2 font-medium">Saldo</th>
                                <th className="px-3 py-2 font-medium">
                                    Andamento
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {leaderboard.map((entry) => (
                                <tr
                                    key={entry.playerName}
                                    className={
                                        entry.isCurrentUser
                                            ? 'bg-muted/60'
                                            : 'border-t'
                                    }
                                >
                                    <td className="px-3 py-3 font-medium">
                                        {entry.position}
                                    </td>
                                    <td className="px-3 py-3 font-medium">
                                        {entry.playerName}
                                    </td>
                                    <td className="px-3 py-3">
                                        {entry.points}
                                    </td>
                                    <td className="px-3 py-3">
                                        {entry.balance}
                                    </td>
                                    <td className="px-3 py-2">
                                        <svg
                                            aria-label={`Andamento di ${entry.playerName}`}
                                            className="h-9 w-28"
                                            viewBox="0 0 100 36"
                                            role="img"
                                        >
                                            <polyline
                                                fill="none"
                                                points={historyPoints(
                                                    entry.history,
                                                )}
                                                stroke="currentColor"
                                                strokeWidth="2"
                                            />
                                        </svg>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </section>
    );
}
