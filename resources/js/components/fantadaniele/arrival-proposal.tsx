import { useForm, usePage } from '@inertiajs/react';
import { ThumbsDown, ThumbsUp, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import confirmationRoutes from '@/routes/games/arrival';
import arrivalProposalRoutes from '@/routes/games/arrivals/proposals';
import voteRoutes from '@/routes/games/arrivals/proposals/votes';
import type { ArrivalProposal, ArrivalVote, Bet, Game } from '@/types';
import { EmptyState } from './empty-state';

type VoteButtonProps = {
    approved: boolean;
    gameId: number;
    proposalId: number;
    currentTeamSlug: string;
    count: number;
};

function VoteButton({
    approved,
    gameId,
    proposalId,
    currentTeamSlug,
    count,
}: VoteButtonProps) {
    const form = useForm({ approved });

    function submit(): void {
        form.post(
            voteRoutes.store({
                current_team: currentTeamSlug,
                game: gameId,
                proposal: proposalId,
            }).url,
        );
    }

    return (
        <Button
            data-testid={approved ? 'vote-yes' : 'vote-no'}
            type="button"
            variant="outline"
            onClick={submit}
            disabled={form.processing}
        >
            {approved ? <ThumbsUp /> : <ThumbsDown />} {approved ? 'Sì' : 'No'}{' '}
            ({count})
        </Button>
    );
}

type ProposalFormProps = { gameId: number; currentTeamSlug: string };

function ProposalForm({ gameId, currentTeamSlug }: ProposalFormProps) {
    const form = useForm({ arrival_minute: 0 });

    function submit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        form.post(
            arrivalProposalRoutes.store({
                current_team: currentTeamSlug,
                game: gameId,
            }).url,
        );
    }

    return (
        <form className="mt-4 flex flex-wrap items-end gap-3" onSubmit={submit}>
            <div className="grid gap-2">
                <Label htmlFor="proposed-arrival-time">Proponi orario</Label>
                <Input
                    id="proposed-arrival-time"
                    data-testid="proposal-arrival-time"
                    type="time"
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
                disabled={form.processing}
            >
                Proponi arrivo
            </Button>
            {form.errors.arrival_minute && (
                <p className="text-sm text-destructive">
                    {form.errors.arrival_minute}
                </p>
            )}
        </form>
    );
}

type ConfirmationProps = {
    gameId: number;
    proposalId: number;
    currentTeamSlug: string;
};

function ConfirmArrivalButton({
    gameId,
    proposalId,
    currentTeamSlug,
}: ConfirmationProps) {
    const form = useForm({ proposal_id: proposalId });

    function submit(): void {
        form.post(
            confirmationRoutes.confirm({
                current_team: currentTeamSlug,
                game: gameId,
            }).url,
        );
    }

    return (
        <Button
            data-testid="confirm-arrival"
            type="button"
            onClick={submit}
            disabled={form.processing}
        >
            Conferma arrivo
        </Button>
    );
}

type Props = {
    proposal: ArrivalProposal | null | undefined;
    votes: ArrivalVote[] | undefined;
    game: Game | null | undefined;
    myBet: Bet | null | undefined;
    isAdmin: boolean | undefined;
};

export function ArrivalProposalCard({
    proposal,
    votes,
    game,
    myBet,
    isAdmin,
}: Props) {
    const currentTeamSlug = usePage().props.currentTeam?.slug;

    if (!proposal) {
        return (
            <section className="border-t pt-6">
                <h2 className="text-lg font-semibold">Proposta di arrivo</h2>
                <div className="mt-4">
                    <EmptyState>Nessuna proposta da votare.</EmptyState>
                </div>
                {game &&
                    currentTeamSlug &&
                    myBet &&
                    game.status === 'locked' && (
                        <ProposalForm
                            gameId={game.id}
                            currentTeamSlug={currentTeamSlug}
                        />
                    )}
            </section>
        );
    }

    if (!game || !currentTeamSlug) {
        return (
            <section
                className="border-t pt-6"
                aria-labelledby="arrival-proposal-title"
            >
                <h2
                    id="arrival-proposal-title"
                    className="text-lg font-semibold"
                >
                    Proposta di arrivo
                </h2>
                <p className="mt-3 text-sm">
                    {proposal.proposerName} propone{' '}
                    <strong>{proposal.proposedTime}</strong>.
                </p>
            </section>
        );
    }

    const yesVotes = votes?.filter((vote) => vote.choice === 'yes').length ?? 0;
    const noVotes = votes?.filter((vote) => vote.choice === 'no').length ?? 0;
    const canVote = Boolean(myBet && proposal.status === 'open');

    return (
        <section
            className="border-t pt-6"
            aria-labelledby="arrival-proposal-title"
        >
            <div className="flex items-center gap-2">
                <Users className="size-5" />
                <h2
                    id="arrival-proposal-title"
                    className="text-lg font-semibold"
                >
                    Proposta di arrivo
                </h2>
            </div>
            <p className="mt-3 text-sm">
                {proposal.proposerName} propone{' '}
                <strong>{proposal.proposedTime}</strong>. Votazione fino alle{' '}
                {proposal.closesAt}.
            </p>
            {(canVote || isAdmin) && (
                <div className="mt-4 flex flex-wrap items-center gap-2">
                    {canVote && (
                        <>
                            <VoteButton
                                approved
                                gameId={game.id}
                                proposalId={proposal.id}
                                currentTeamSlug={currentTeamSlug}
                                count={yesVotes}
                            />
                            <VoteButton
                                approved={false}
                                gameId={game.id}
                                proposalId={proposal.id}
                                currentTeamSlug={currentTeamSlug}
                                count={noVotes}
                            />
                        </>
                    )}
                    {isAdmin && (
                        <ConfirmArrivalButton
                            gameId={game.id}
                            proposalId={proposal.id}
                            currentTeamSlug={currentTeamSlug}
                        />
                    )}
                </div>
            )}
        </section>
    );
}
