import { useForm, usePage } from '@inertiajs/react';
import { Clock3, Landmark, LockKeyhole } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import games from '@/routes/games';
import bets from '@/routes/games/bets';
import type { Bet, Game } from '@/types';
import { EmptyState } from './empty-state';

type Props = {
    game: Game | null | undefined;
    myBet: Bet | null | undefined;
};

function timeToMinutes(value: string): number {
    const [hours, minutes] = value.split(':').map(Number);

    return hours * 60 + minutes;
}

function StartGameButton({ currentTeamSlug }: { currentTeamSlug: string }) {
    const form = useForm({});

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post(games.store({ current_team: currentTeamSlug }).url);
    }

    return (
        <form onSubmit={submit}>
            <Button
                data-testid="start-game"
                type="submit"
                disabled={form.processing}
            >
                Avvia partita
            </Button>
        </form>
    );
}

export function GameCard({ game, myBet }: Props) {
    const currentTeamSlug = usePage().props.currentTeam?.slug;
    const form = useForm({
        amount: myBet?.houseAmount.toString() ?? '',
        arrival_minute: myBet ? timeToMinutes(myBet.arrivalTime) : 0,
    });

    if (!game) {
        return (
            <section className="border-b pb-6">
                <h1 className="text-xl font-semibold">Partita corrente</h1>
                <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <EmptyState>
                        Nessuna manche aperta. Appena parte la prossima corsa,
                        la trovi qui.
                    </EmptyState>
                    {currentTeamSlug && (
                        <StartGameButton currentTeamSlug={currentTeamSlug} />
                    )}
                </div>
            </section>
        );
    }

    const activeGame = game;
    const acceptsBets = activeGame.status !== 'completed' && !myBet;

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!currentTeamSlug) {
            return;
        }

        form.post(
            bets.store({ current_team: currentTeamSlug, game: activeGame.id })
                .url,
        );
    }

    return (
        <section className="border-b pb-6" aria-labelledby="current-game-title">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-sm text-muted-foreground">
                        Partita corrente
                    </p>
                    <h1
                        id="current-game-title"
                        className="mt-1 text-2xl font-semibold tracking-tight"
                    >
                        {activeGame.title}
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Destinazione: {activeGame.destination} · partenza{' '}
                        {activeGame.departureAt}
                    </p>
                </div>
                <span className="rounded-md border px-2 py-1 text-xs font-medium">
                    {activeGame.status === 'open'
                        ? 'Puntate aperte'
                        : activeGame.status === 'locked'
                          ? 'Partita in corso'
                          : 'Risultato registrato'}
                </span>
            </div>

            <form
                className="mt-5 grid gap-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end"
                onSubmit={submit}
            >
                <div className="grid gap-2">
                    <Label htmlFor="arrival-time">Ora di arrivo esatta</Label>
                    <div className="relative">
                        <Clock3 className="pointer-events-none absolute top-2.5 left-3 size-4 text-muted-foreground" />
                        <Input
                            id="arrival-time"
                            data-testid="bet-arrival-time"
                            type="time"
                            defaultValue={myBet?.arrivalTime}
                            onChange={(event) =>
                                form.setData(
                                    'arrival_minute',
                                    timeToMinutes(event.target.value),
                                )
                            }
                            disabled={!acceptsBets}
                            className="pl-9"
                            required
                        />
                    </div>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="house-amount">Importo casa</Label>
                    <div className="relative">
                        <Landmark className="pointer-events-none absolute top-2.5 left-3 size-4 text-muted-foreground" />
                        <Input
                            id="house-amount"
                            data-testid="bet-house-amount"
                            type="number"
                            min="1"
                            step="1"
                            value={form.data.amount}
                            onChange={(event) =>
                                form.setData('amount', event.target.value)
                            }
                            disabled={!acceptsBets}
                            className="pl-9"
                            required
                        />
                    </div>
                </div>
                <Button
                    data-testid="confirm-bet"
                    type="submit"
                    disabled={!acceptsBets || form.processing}
                >
                    {acceptsBets ? (
                        'Conferma puntata'
                    ) : (
                        <>
                            <LockKeyhole /> Bloccata
                        </>
                    )}
                </Button>
            </form>
            {form.errors.amount && (
                <p className="mt-3 text-sm text-destructive">
                    {form.errors.amount}
                </p>
            )}
            {form.errors.arrival_minute && (
                <p className="mt-3 text-sm text-destructive">
                    {form.errors.arrival_minute}
                </p>
            )}
            {myBet && (
                <p className="mt-3 text-sm text-muted-foreground">
                    Puntata registrata: {myBet.arrivalTime} ·{' '}
                    {myBet.houseAmount} crediti.
                </p>
            )}
        </section>
    );
}
