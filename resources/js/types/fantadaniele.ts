export type GameStatus = 'open' | 'locked' | 'completed';

export type Game = {
    id: number;
    title: string;
    destination: string;
    departureAt: string;
    status: GameStatus;
    houseAmount: number;
};

export type Bet = {
    arrivalTime: string;
    houseAmount: number;
    submittedAt: string;
};

export type TimeSlot = {
    id: number;
    label: string;
    startsAt: string;
    endsAt: string;
    isBlocked: boolean;
};

export type Balance = {
    available: number;
    totalWon: number;
    totalPlayed: number;
};

export type BalanceHistoryItem = {
    id: number;
    label: string;
    amount: number;
    description: string;
};

export type ArrivalProposal = {
    id: number;
    proposedTime: string;
    proposerName: string;
    closesAt: string;
    status: 'open' | 'confirmed' | 'closed';
};

export type ArrivalVote = {
    id: number;
    voterName: string;
    choice: 'yes' | 'no';
};

export type LeaderboardEntry = {
    position: number;
    playerName: string;
    points: number;
    balance: number;
    history: number[];
    isCurrentUser: boolean;
};

export type FantaDashboardProps = {
    game?: Game | null;
    myBet?: Bet | null;
    balance?: Balance | null;
    isAdmin?: boolean;
    arrivalProposal?: ArrivalProposal | null;
    votes?: ArrivalVote[];
    leaderboard?: LeaderboardEntry[];
    slots?: TimeSlot[];
    history?: BalanceHistoryItem[];
};
