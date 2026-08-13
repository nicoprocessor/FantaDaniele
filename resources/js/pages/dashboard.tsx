import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { ArrivalProposalCard } from '@/components/fantadaniele/arrival-proposal';
import { GameCard } from '@/components/fantadaniele/game-card';
import { Leaderboard } from '@/components/fantadaniele/leaderboard';
import { PlayerPanel } from '@/components/fantadaniele/player-panel';
import { Schedule } from '@/components/fantadaniele/schedule';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import { dashboard } from '@/routes';
import type { DashboardInvitation, FantaDashboardProps } from '@/types';

type Props = FantaDashboardProps & {
    pendingInvitations?: DashboardInvitation[];
};

export default function Dashboard({
    pendingInvitations,
    game,
    myBet,
    balance,
    arrivalProposal,
    votes,
    leaderboard,
    slots,
    history,
    isAdmin,
}: Props) {
    const hasPendingInvitations = Boolean(pendingInvitations?.length);
    const [showInvitations, setShowInvitations] = useState(
        hasPendingInvitations,
    );

    return (
        <>
            <Head title="FantaDaniele" />
            <PendingInvitationsModal
                invitations={pendingInvitations ?? []}
                open={hasPendingInvitations && showInvitations}
                onOpenChange={setShowInvitations}
            />
            <main className="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
                <GameCard game={game} myBet={myBet} />
                <div className="grid gap-8 py-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(18rem,0.85fr)]">
                    <div className="grid content-start gap-8">
                        <Schedule slots={slots} />
                        <ArrivalProposalCard
                            proposal={arrivalProposal}
                            votes={votes}
                            game={game}
                            myBet={myBet}
                            isAdmin={isAdmin}
                        />
                    </div>
                    <PlayerPanel balance={balance} history={history} />
                </div>
                <div className="border-t pt-8">
                    <Leaderboard leaderboard={leaderboard} />
                </div>
            </main>
        </>
    );
}

Dashboard.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'FantaDaniele',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
    ],
});
