import { Ban, CalendarDays } from 'lucide-react';
import type { TimeSlot } from '@/types';
import { EmptyState } from './empty-state';

export function Schedule({ slots }: { slots: TimeSlot[] | undefined }) {
    return (
        <section aria-labelledby="schedule-title">
            <div className="flex items-center gap-2">
                <CalendarDays className="size-5" />
                <h2 id="schedule-title" className="text-lg font-semibold">
                    Fasce disponibili
                </h2>
            </div>
            <p className="mt-1 text-sm text-muted-foreground">
                Le fasce bloccate non accettano nuove puntate.
            </p>
            {!slots?.length ? (
                <div className="mt-4">
                    <EmptyState>Calendario non ancora pubblicato.</EmptyState>
                </div>
            ) : (
                <ol className="mt-4 grid gap-2 sm:grid-cols-2">
                    {slots.map((slot) => (
                        <li
                            key={slot.id}
                            className="flex items-center justify-between gap-3 border p-3"
                        >
                            <div>
                                <p className="font-medium">{slot.label}</p>
                                <p className="text-sm text-muted-foreground">
                                    {slot.startsAt}–{slot.endsAt}
                                </p>
                            </div>
                            {slot.isBlocked ? (
                                <span className="flex items-center gap-1 text-sm text-muted-foreground">
                                    <Ban className="size-4" /> Bloccata
                                </span>
                            ) : (
                                <span className="text-sm font-medium">
                                    Libera
                                </span>
                            )}
                        </li>
                    ))}
                </ol>
            )}
        </section>
    );
}
