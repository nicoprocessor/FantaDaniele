import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { EmptyState } from '@/components/fantadaniele/empty-state';
import { GameCard } from '@/components/fantadaniele/game-card';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { index } from '@/routes/games';
import type { Game } from '@/types';

type Props = {
    currentGame: Game | null;
    history: Game[];
    canStartGame: boolean;
};

function timestamp(value: string): string {
    return new Intl.DateTimeFormat('it-IT', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export default function GamesIndex({
    currentGame,
    history,
    canStartGame,
}: Props) {
    const [selectedGame, setSelectedGame] = useState<Game | null>(null);

    return (
        <>
            <Head title="Partite" />
            <main className="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
                <GameCard game={currentGame} canStartGame={canStartGame} />
                <section className="mt-8" aria-labelledby="game-history-title">
                    <h1
                        id="game-history-title"
                        className="text-xl font-semibold"
                    >
                        Partite concluse
                    </h1>
                    {history.length === 0 ? (
                        <div className="mt-4">
                            <EmptyState>
                                La cronologia comparirà qui dopo la prima
                                partita conclusa.
                            </EmptyState>
                        </div>
                    ) : (
                        <div className="mt-4 divide-y border">
                            {history.map((game) => (
                                <button
                                    key={game.id}
                                    type="button"
                                    className="grid w-full gap-2 p-4 text-left hover:bg-muted/50 sm:grid-cols-[1fr_auto_auto_auto] sm:items-center"
                                    onClick={() => setSelectedGame(game)}
                                    data-test={`game-history-${game.id}`}
                                >
                                    <span>
                                        <span className="font-medium">
                                            {game.title}
                                        </span>
                                        <span className="block text-sm text-muted-foreground">
                                            La tua puntata:{' '}
                                            {game.myBet
                                                ? `${game.myBet.arrivalTime} · ${game.myBet.houseAmount} proprietà`
                                                : 'Non hai partecipato'}
                                        </span>
                                    </span>
                                    <span className="text-sm">
                                        Arrivo:{' '}
                                        {game.actualArrivalTime ??
                                            'Non registrato'}
                                    </span>
                                    <span className="text-sm">
                                        Vincitore:{' '}
                                        {game.winnerName ?? 'Lo sport'}
                                    </span>
                                    <span className="text-sm">
                                        {game.houseAmount} proprietà
                                    </span>
                                </button>
                            ))}
                        </div>
                    )}
                </section>
            </main>
            <Sheet
                open={selectedGame !== null}
                onOpenChange={(open) => !open && setSelectedGame(null)}
            >
                <SheetContent side="right" data-test="game-detail-sheet">
                    <SheetHeader>
                        <SheetTitle>{selectedGame?.title}</SheetTitle>
                        <SheetDescription>
                            Partecipanti, orari e proprietà puntate.
                        </SheetDescription>
                    </SheetHeader>
                    <div className="divide-y overflow-y-auto px-4">
                        {selectedGame?.participants?.map((participant) => (
                            <div
                                key={participant.id}
                                className="flex items-center gap-3 py-3"
                            >
                                <img
                                    src={participant.avatarUrl}
                                    alt=""
                                    className="size-9 rounded-full"
                                />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate font-medium">
                                        {participant.name}
                                        {participant.isCurrentUser
                                            ? ' · Tu'
                                            : ''}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        Puntata alle{' '}
                                        {timestamp(participant.betAt)}
                                    </p>
                                </div>
                                <div className="text-right text-sm">
                                    <p>{participant.arrivalTime}</p>
                                    <p className="text-muted-foreground">
                                        {participant.stake} proprietà
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </SheetContent>
            </Sheet>
        </>
    );
}

GamesIndex.layout = () => ({
    breadcrumbs: [{ title: 'Partite', href: index() }],
});
