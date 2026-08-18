import { useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import games from '@/routes/games';

export function GameAdminActions({ gameId }: { gameId: number }) {
    const form = useForm({});
    const serverErrors = form.errors as typeof form.errors & { game?: string };

    return (
        <section
            className="border-2 border-destructive p-4"
            aria-labelledby="game-admin-actions-title"
        >
            <h2 id="game-admin-actions-title" className="font-bold">
                Gestione partita
            </h2>
            <p className="mt-1 text-sm text-muted-foreground">
                L'annullamento restituisce tutte le proprietà e rimuove la
                partita.
            </p>
            <Dialog>
                <DialogTrigger asChild>
                    <Button
                        variant="destructive"
                        className="mt-3 min-h-11 w-full"
                        data-testid="cancel-game"
                    >
                        <Trash2 /> Annulla partita
                    </Button>
                </DialogTrigger>
                <DialogContent className="border-2 border-foreground shadow-[4px_4px_0_var(--foreground)]">
                    <DialogHeader>
                        <DialogTitle>Annullare la partita?</DialogTitle>
                        <DialogDescription>
                            Le puntate saranno rimborsate integralmente. Questa
                            azione non può essere annullata.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline" className="min-h-11">
                                Tieni la partita
                            </Button>
                        </DialogClose>
                        <Button
                            type="button"
                            variant="destructive"
                            className="min-h-11"
                            data-testid="confirm-cancel-game"
                            onClick={() =>
                                form.delete(games.destroy({ game: gameId }).url)
                            }
                            disabled={form.processing}
                        >
                            Annulla e rimborsa
                        </Button>
                    </DialogFooter>
                    {serverErrors.game ? (
                        <p className="text-sm text-destructive" role="alert">
                            {serverErrors.game}
                        </p>
                    ) : null}
                </DialogContent>
            </Dialog>
        </section>
    );
}
