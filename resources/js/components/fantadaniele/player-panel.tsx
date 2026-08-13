import { WalletCards } from 'lucide-react';
import type { Balance, BalanceHistoryItem } from '@/types';
import { EmptyState } from './empty-state';

type Props = {
    balance: Balance | null | undefined;
    history: BalanceHistoryItem[] | undefined;
};

export function PlayerPanel({ balance, history }: Props) {
    return (
        <section aria-labelledby="player-title">
            <div className="flex items-center gap-2">
                <WalletCards className="size-5" />
                <h2 id="player-title" className="text-lg font-semibold">
                    Il tuo borsellino
                </h2>
            </div>
            {!balance ? (
                <div className="mt-4">
                    <EmptyState>Saldo personale non disponibile.</EmptyState>
                </div>
            ) : (
                <>
                    <dl className="mt-4 grid grid-cols-3 gap-2 border-y py-4 text-sm">
                        <div>
                            <dt className="text-muted-foreground">
                                Disponibili
                            </dt>
                            <dd className="mt-1 text-lg font-semibold">
                                {balance.available}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground">Vinti</dt>
                            <dd className="mt-1 font-medium">
                                {balance.totalWon}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground">Giocati</dt>
                            <dd className="mt-1 font-medium">
                                {balance.totalPlayed}
                            </dd>
                        </div>
                    </dl>
                    <h3 className="mt-5 font-medium">Movimenti recenti</h3>
                    {!history?.length ? (
                        <div className="mt-3">
                            <EmptyState>
                                Nessun movimento registrato.
                            </EmptyState>
                        </div>
                    ) : (
                        <ul className="mt-3 divide-y">
                            {history.map((item) => (
                                <li
                                    key={item.id}
                                    className="flex items-center justify-between gap-4 py-3 text-sm"
                                >
                                    <div>
                                        <p className="font-medium">
                                            {item.description}
                                        </p>
                                        <p className="text-muted-foreground">
                                            {item.label}
                                        </p>
                                    </div>
                                    <strong
                                        className={
                                            item.amount >= 0
                                                ? 'text-emerald-700 dark:text-emerald-400'
                                                : ''
                                        }
                                    >
                                        {item.amount >= 0 ? '+' : ''}
                                        {item.amount}
                                    </strong>
                                </li>
                            ))}
                        </ul>
                    )}
                </>
            )}
        </section>
    );
}
