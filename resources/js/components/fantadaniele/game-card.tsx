import { Link, useForm } from '@inertiajs/react';
import { ArrowRight, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import games from '@/routes/games';
import { show } from '@/routes/games';
import type { Game } from '@/types';
import { EmptyState } from './empty-state';

type Props = {
    game: Game | null | undefined;
    canStartGame: boolean;
};

function StartGameButton() {
    const form = useForm({});
    const serverErrors = form.errors as typeof form.errors & { game?: string };

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post(games.store().url);
    }

    return (
        <form onSubmit={submit}>
            <Button
                data-testid="start-game"
                type="submit"
                className="min-h-11"
                disabled={form.processing}
            >
                Avvia partita
            </Button>
            {serverErrors.game ? (
                <p className="mt-2 text-sm text-destructive" role="alert">
                    {serverErrors.game}
                </p>
            ) : null}
        </form>
    );
}

export function GameCard({ game, canStartGame }: Props) {
    if (!game) {
        return (
            <section className="border-b pb-6">
                <h1 className="text-xl font-semibold">Partita corrente</h1>
                <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <EmptyState>
                        Nessuna manche aperta. Appena parte la prossima corsa,
                        la trovi qui.
                    </EmptyState>
                    {canStartGame && <StartGameButton />}
                </div>
            </section>
        );
    }

    return (
        <section
            className="border-2 border-foreground bg-card p-4 shadow-[4px_4px_0_var(--foreground)] dark:shadow-[4px_4px_0_var(--background)]"
            aria-labelledby="current-game-title"
            data-testid="active-game-summary"
        >
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-xs font-bold tracking-[0.12em] text-muted-foreground uppercase">
                        Partita attiva
                    </p>
                    <h1
                        id="current-game-title"
                        className="mt-1 text-2xl font-semibold tracking-tight"
                    >
                        {game.title}
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Destinazione: {game.destination} · partenza{' '}
                        {game.departureAt}
                    </p>
                </div>
                <span className="border border-foreground bg-primary px-2 py-1 text-xs font-bold text-primary-foreground">
                    {game.status === 'open'
                        ? 'Puntate aperte'
                        : 'Partita in corso'}
                </span>
            </div>
            <div className="mt-4 grid grid-cols-2 gap-2 border-y border-foreground py-3 text-sm">
                <span className="flex items-center gap-2">
                    <Users className="size-4" /> {game.participantCount ?? 0}{' '}
                    partecipanti
                </span>
                <span className="text-right font-bold tabular-nums">
                    {game.houseAmount} proprietà
                </span>
            </div>
            <div className="mt-4 flex items-center justify-between gap-3">
                <div className="flex min-w-0 items-center gap-2 text-sm">
                    {game.owner ? (
                        <img
                            className="size-8 border border-foreground"
                            src={game.owner.avatarUrl}
                            alt=""
                        />
                    ) : null}
                    <span className="truncate">
                        Gestisce:{' '}
                        <strong>
                            {game.owner?.name ?? 'Account eliminato'}
                        </strong>
                    </span>
                </div>
                <Button
                    asChild
                    className="min-h-11 shrink-0"
                    data-testid="go-to-game"
                >
                    <Link href={show({ game: game.id }).url}>
                        Vai alla partita <ArrowRight />
                    </Link>
                </Button>
            </div>
        </section>
    );
}
