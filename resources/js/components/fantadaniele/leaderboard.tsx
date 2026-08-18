import { Trophy } from 'lucide-react';
import type { LeaderboardEntry } from '@/types';
import { EmptyState } from './empty-state';

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
                                <th className="px-3 py-2 font-medium">Saldo</th>
                                <th className="px-3 py-2 font-medium">Vinte</th>
                                <th className="px-3 py-2 font-medium">
                                    Giocate
                                </th>
                                <th className="px-3 py-2 font-medium">
                                    Vittorie
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {leaderboard.map((entry) => (
                                <tr
                                    key={entry.id}
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
                                        <span className="flex items-center gap-2">
                                            <img
                                                className="size-7 rounded-full"
                                                src={entry.avatarUrl}
                                                alt=""
                                            />
                                            {entry.playerName}
                                            {entry.isCurrentUser && (
                                                <span className="text-xs text-muted-foreground">
                                                    Tu
                                                </span>
                                            )}
                                        </span>
                                    </td>
                                    <td className="px-3 py-3">
                                        {entry.balance}
                                    </td>
                                    <td className="px-3 py-2">{entry.wins}</td>
                                    <td className="px-3 py-2">
                                        {entry.gamesPlayed}
                                    </td>
                                    <td className="px-3 py-2">
                                        {entry.winRate}%
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
