import type { GameParticipant } from '@/types';

type Props = { participants: GameParticipant[] };

function timestamp(value: string): string {
    return new Intl.DateTimeFormat('it-IT', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
}

export function GameParticipants({ participants }: Props) {
    return (
        <section aria-labelledby="game-participants-title">
            <h2 id="game-participants-title" className="text-xl font-bold">
                Puntate partecipanti
            </h2>
            <div className="mt-3 grid gap-2">
                {participants.map((participant) => (
                    <article
                        key={participant.id}
                        className="grid grid-cols-[auto_1fr_auto] items-center gap-3 border-2 border-foreground bg-card p-3"
                        data-testid={`game-participant-${participant.id}`}
                    >
                        <img
                            className="size-10 border border-foreground"
                            src={participant.avatarUrl}
                            alt=""
                        />
                        <div className="min-w-0">
                            <p className="truncate font-bold">
                                {participant.name}
                                {participant.isCurrentUser ? ' · Tu' : ''}
                            </p>
                            <p className="text-xs text-muted-foreground">
                                Puntata {timestamp(participant.betAt)}
                            </p>
                        </div>
                        <div className="text-right font-mono text-sm tabular-nums">
                            <p className="font-black">
                                {participant.arrivalTime}
                            </p>
                            <p>{participant.stake} proprietà</p>
                        </div>
                    </article>
                ))}
            </div>
        </section>
    );
}
