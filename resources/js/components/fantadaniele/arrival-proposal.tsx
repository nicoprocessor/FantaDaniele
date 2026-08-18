import { useForm } from '@inertiajs/react';
import { ThumbsDown, ThumbsUp, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import confirmationRoutes from '@/routes/games/arrival';
import arrivalProposalRoutes from '@/routes/games/arrivals/proposals';
import voteRoutes from '@/routes/games/arrivals/proposals/votes';
import type { ArrivalProposal, Game } from '@/types';

type VoteButtonProps = {
    approved: boolean;
    gameId: number;
    proposal: ArrivalProposal;
};

function VoteButton({ approved, gameId, proposal }: VoteButtonProps) {
    const form = useForm({ approved });
    const serverErrors = form.errors as typeof form.errors & {
        proposal?: string;
    };

    return (
        <div className="flex-1">
            <Button
                data-testid={`${approved ? 'vote-yes' : 'vote-no'}-${proposal.id}`}
                type="button"
                variant="outline"
                className="min-h-11 w-full"
                onClick={() =>
                    form.post(
                        voteRoutes.store({
                            game: gameId,
                            proposal: proposal.id,
                        }).url,
                    )
                }
                disabled={form.processing}
            >
                {approved ? <ThumbsUp /> : <ThumbsDown />}
                {approved
                    ? `Sì ${proposal.yesVotes}`
                    : `No ${proposal.noVotes}`}
            </Button>
            {serverErrors.proposal ? (
                <p className="mt-2 text-sm text-destructive" role="alert">
                    {serverErrors.proposal}
                </p>
            ) : null}
            {form.errors.approved ? (
                <p className="mt-2 text-sm text-destructive" role="alert">
                    {form.errors.approved}
                </p>
            ) : null}
        </div>
    );
}

function ProposalForm({ gameId }: { gameId: number }) {
    const form = useForm({ arrival_minute: 0 });
    const serverErrors = form.errors as typeof form.errors & { game?: string };

    function submit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        form.post(arrivalProposalRoutes.store({ game: gameId }).url);
    }

    return (
        <form
            className="grid gap-3 border-2 border-foreground p-3"
            onSubmit={submit}
        >
            <div className="grid gap-2">
                <Label htmlFor="proposed-arrival-time">
                    Proponi l'orario di arrivo
                </Label>
                <Input
                    id="proposed-arrival-time"
                    data-testid="proposal-arrival-time"
                    type="time"
                    className="min-h-11 border-foreground"
                    onChange={(event) => {
                        const [hours, minutes] = event.target.value
                            .split(':')
                            .map(Number);
                        form.setData('arrival_minute', hours * 60 + minutes);
                    }}
                    required
                />
            </div>
            <Button
                data-testid="submit-arrival-proposal"
                type="submit"
                className="min-h-11"
                disabled={form.processing}
            >
                Proponi arrivo
            </Button>
            {form.errors.arrival_minute ? (
                <p className="text-sm text-destructive" role="alert">
                    {form.errors.arrival_minute}
                </p>
            ) : null}
            {serverErrors.game ? (
                <p className="text-sm text-destructive" role="alert">
                    {serverErrors.game}
                </p>
            ) : null}
        </form>
    );
}

function ConfirmArrivalButton({
    gameId,
    proposalId,
}: {
    gameId: number;
    proposalId: number;
}) {
    const form = useForm({ proposal_id: proposalId });
    const serverErrors = form.errors as typeof form.errors & {
        proposal?: string;
    };

    return (
        <div>
            <Button
                data-testid={`confirm-arrival-${proposalId}`}
                type="button"
                className="min-h-11 w-full"
                onClick={() =>
                    form.post(confirmationRoutes.confirm({ game: gameId }).url)
                }
                disabled={form.processing}
            >
                Conferma arrivo finale
            </Button>
            {serverErrors.proposal ? (
                <p className="mt-2 text-sm text-destructive" role="alert">
                    {serverErrors.proposal}
                </p>
            ) : null}
            {form.errors.proposal_id ? (
                <p className="mt-2 text-sm text-destructive" role="alert">
                    {form.errors.proposal_id}
                </p>
            ) : null}
        </div>
    );
}

type Props = {
    game: Game;
    proposals: ArrivalProposal[];
    canParticipate: boolean;
    canManageGame: boolean;
};

export function ArrivalProposalCard({
    game,
    proposals,
    canParticipate,
    canManageGame,
}: Props) {
    const canInteract = game.status === 'locked' && canParticipate;

    return (
        <section
            className="grid gap-4"
            aria-labelledby="arrival-proposals-title"
        >
            <div className="flex items-center gap-2">
                <Users className="size-5" />
                <div>
                    <h2
                        id="arrival-proposals-title"
                        className="text-xl font-bold"
                    >
                        Proposte di arrivo
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        Ogni proposta ha la sua votazione.
                    </p>
                </div>
            </div>
            {canInteract ? <ProposalForm gameId={game.id} /> : null}
            {proposals.length === 0 ? (
                <p className="border border-dashed border-foreground p-4 text-sm">
                    Nessuna proposta ancora.
                </p>
            ) : null}
            {proposals.map((proposal) => (
                <article
                    key={proposal.id}
                    className="border-2 border-foreground bg-card p-4"
                    data-testid={`arrival-proposal-${proposal.id}`}
                >
                    <div className="flex items-start justify-between gap-3">
                        <div className="flex min-w-0 items-center gap-2">
                            <img
                                className="size-9 border border-foreground"
                                src={proposal.proposer.avatarUrl}
                                alt=""
                            />
                            <div>
                                <p className="font-bold">
                                    {proposal.proposedTime}
                                </p>
                                <p className="truncate text-sm text-muted-foreground">
                                    Proposta di {proposal.proposer.name}
                                </p>
                            </div>
                        </div>
                        <span
                            className={
                                proposal.hasMajority
                                    ? 'border border-foreground bg-primary px-2 py-1 text-xs font-bold'
                                    : 'border border-foreground px-2 py-1 text-xs font-bold'
                            }
                        >
                            {proposal.hasMajority
                                ? 'Maggioranza'
                                : 'In votazione'}
                        </span>
                    </div>
                    <div className="mt-4 flex gap-2">
                        {canInteract ? (
                            <VoteButton
                                approved
                                gameId={game.id}
                                proposal={proposal}
                            />
                        ) : null}
                        {canInteract ? (
                            <VoteButton
                                approved={false}
                                gameId={game.id}
                                proposal={proposal}
                            />
                        ) : null}
                    </div>
                    <ul className="mt-3 grid gap-1 border-t border-foreground pt-3 text-sm">
                        {proposal.votes.map((vote) => (
                            <li
                                key={vote.id}
                                className="flex justify-between gap-3"
                            >
                                <span>
                                    {vote.voter.name}
                                    {vote.isCurrentUser ? ' · Tu' : ''}
                                </span>
                                <strong>
                                    {vote.choice === 'yes' ? 'Sì' : 'No'}
                                </strong>
                            </li>
                        ))}
                    </ul>
                    {canManageGame && proposal.hasMajority ? (
                        <div className="mt-4">
                            <ConfirmArrivalButton
                                gameId={game.id}
                                proposalId={proposal.id}
                            />
                        </div>
                    ) : null}
                </article>
            ))}
        </section>
    );
}
