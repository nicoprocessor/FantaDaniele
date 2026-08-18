import { Head, router, useForm } from '@inertiajs/react';
import { Clock3, Landmark } from 'lucide-react';
import { useCallback } from 'react';
import { ArrivalProposalCard } from '@/components/fantadaniele/arrival-proposal';
import { GameAdminActions } from '@/components/fantadaniele/game-admin-actions';
import { GameParticipants } from '@/components/fantadaniele/game-participants';
import { OfficialGameTimer } from '@/components/fantadaniele/official-game-timer';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/games';
import bets from '@/routes/games/bets';
import type { GameShowProps } from '@/types';

function timeToMinutes(value: string): number {
    const [hours, minutes] = value.split(':').map(Number);

    return hours * 60 + minutes;
}

export default function GameShow({
    game,
    myBet,
    availableBalance,
    canManageGame,
    proposals,
    serverNow,
    closesAt,
}: GameShowProps) {
    const form = useForm({ amount: '', arrival_minute: 0 });
    const serverErrors = form.errors as typeof form.errors & { game?: string };
    const navigateToGamesIndex = useCallback((): void => {
        router.visit(index().url, { replace: true });
    }, []);

    function submitBet(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        form.post(bets.store({ game: game.id }).url);
    }

    return (
        <>
            <Head title={game.title} />
            <main className="mx-auto grid w-full max-w-3xl gap-6 px-4 py-5 sm:px-6 sm:py-8">
                <section
                    className="border-2 border-foreground bg-card p-4 shadow-[4px_4px_0_var(--foreground)] dark:shadow-[4px_4px_0_var(--background)]"
                    data-testid="game-detail"
                >
                    <div className="flex items-start justify-between gap-3">
                        <div>
                            <p className="text-xs font-bold tracking-[0.12em] uppercase">
                                Partita attiva
                            </p>
                            <h1 className="mt-1 text-3xl font-black">
                                {game.title}
                            </h1>
                            <p className="mt-1 text-sm">
                                {game.destination} · partenza {game.departureAt}
                            </p>
                        </div>
                        <span className="border border-foreground bg-primary px-2 py-1 text-xs font-bold">
                            {game.status === 'open'
                                ? 'Puntate aperte'
                                : 'Partita in corso'}
                        </span>
                    </div>
                    <div className="mt-4 border-y border-foreground py-3">
                        <p className="text-xs font-bold tracking-[0.1em] uppercase">
                            Chiusura ufficiale
                        </p>
                        <OfficialGameTimer
                            key={`${closesAt}-${serverNow}`}
                            closesAt={closesAt}
                            serverNow={serverNow}
                            onExpire={navigateToGamesIndex}
                        />
                    </div>
                    <div className="mt-4 flex items-center gap-3">
                        {game.owner ? (
                            <img
                                className="size-11 border border-foreground"
                                src={game.owner.avatarUrl}
                                alt=""
                            />
                        ) : null}
                        <div>
                            <p className="text-xs text-muted-foreground">
                                Gestisce la partita
                            </p>
                            <p className="font-bold">
                                {game.owner?.name ?? 'Account eliminato'}
                            </p>
                        </div>
                    </div>
                </section>
                <section
                    className="border-2 border-foreground p-4"
                    aria-labelledby="my-bet-title"
                >
                    <h2 id="my-bet-title" className="text-xl font-bold">
                        La tua puntata
                    </h2>
                    {myBet ? (
                        <div className="mt-3 grid grid-cols-2 gap-2 font-mono text-sm tabular-nums">
                            <p>
                                Arrivo <strong>{myBet.arrivalTime}</strong>
                            </p>
                            <p>
                                Importo <strong>{myBet.houseAmount}</strong>{' '}
                                proprietà
                            </p>
                            <p className="col-span-2 font-sans text-xs text-muted-foreground">
                                Registrata{' '}
                                {new Intl.DateTimeFormat('it-IT', {
                                    dateStyle: 'short',
                                    timeStyle: 'short',
                                }).format(new Date(myBet.submittedAt))}
                                . La puntata è immutabile.
                            </p>
                        </div>
                    ) : (
                        <form className="mt-3 grid gap-3" onSubmit={submitBet}>
                            <p className="text-sm">
                                Disponibili: <strong>{availableBalance}</strong>{' '}
                                proprietà
                            </p>
                            <div className="grid gap-2">
                                <Label htmlFor="bet-arrival-time">
                                    Ora di arrivo esatta
                                </Label>
                                <div className="relative">
                                    <Clock3 className="pointer-events-none absolute top-3 left-3 size-4" />
                                    <Input
                                        id="bet-arrival-time"
                                        data-testid="bet-arrival-time"
                                        type="time"
                                        className="min-h-11 border-foreground pl-9"
                                        onChange={(event) =>
                                            form.setData(
                                                'arrival_minute',
                                                timeToMinutes(
                                                    event.target.value,
                                                ),
                                            )
                                        }
                                        required
                                    />
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="bet-house-amount">
                                    Importo casa
                                </Label>
                                <div className="relative">
                                    <Landmark className="pointer-events-none absolute top-3 left-3 size-4" />
                                    <Input
                                        id="bet-house-amount"
                                        data-testid="bet-house-amount"
                                        type="number"
                                        min="1"
                                        step="1"
                                        className="min-h-11 border-foreground pl-9"
                                        value={form.data.amount}
                                        onChange={(event) =>
                                            form.setData(
                                                'amount',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                </div>
                            </div>
                            <Button
                                data-testid="confirm-bet"
                                type="submit"
                                className="min-h-11"
                                disabled={form.processing}
                            >
                                Conferma puntata
                            </Button>
                            {form.errors.amount ? (
                                <p
                                    className="text-sm text-destructive"
                                    role="alert"
                                >
                                    {form.errors.amount}
                                </p>
                            ) : null}
                            {form.errors.arrival_minute ? (
                                <p
                                    className="text-sm text-destructive"
                                    role="alert"
                                >
                                    {form.errors.arrival_minute}
                                </p>
                            ) : null}
                            {serverErrors.game ? (
                                <p
                                    className="text-sm text-destructive"
                                    role="alert"
                                >
                                    {serverErrors.game}
                                </p>
                            ) : null}
                        </form>
                    )}
                </section>
                <GameParticipants participants={game.participants ?? []} />
                <ArrivalProposalCard
                    game={game}
                    proposals={proposals}
                    canParticipate={myBet !== null}
                    canManageGame={canManageGame}
                />
                {canManageGame ? <GameAdminActions gameId={game.id} /> : null}
            </main>
        </>
    );
}

GameShow.layout = () => ({
    breadcrumbs: [{ title: 'Partite', href: index() }],
});
