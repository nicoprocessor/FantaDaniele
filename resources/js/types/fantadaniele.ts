export type GameStatus = 'open' | 'locked' | 'completed';

export type Game = {
    id: number;
    title: string;
    destination: string;
    departureAt: string;
    status: GameStatus;
    houseAmount: number;
    actualArrivalTime?: string | null;
    winnerName?: string | null;
    myBet?: Bet | null;
    participantCount?: number;
    owner?: GameOwner | null;
    participants?: GameParticipant[];
};

export type GameOwner = {
    id: number;
    name: string;
    avatarUrl: string;
};

export type GameParticipant = {
    id: number;
    name: string;
    avatarUrl: string;
    arrivalTime: string;
    stake: number;
    betAt: string;
    isCurrentUser: boolean;
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

export type PlayerMetrics = {
    available: number;
    gamesPlayed: number;
    wins: number;
    draws: number;
    losses: number;
    winRate: number;
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
    proposer: GameOwner;
    votes: ArrivalVote[];
    yesVotes: number;
    noVotes: number;
    hasMajority: boolean;
};

export type ArrivalVote = {
    id: number;
    voter: GameOwner;
    choice: 'yes' | 'no';
    isCurrentUser: boolean;
};

export type LeaderboardEntry = {
    position: number;
    id: number;
    playerName: string;
    avatarUrl: string;
    balance: number;
    wins: number;
    gamesPlayed: number;
    winRate: number;
    isCurrentUser: boolean;
};

export type FantaDashboardProps = {
    game?: Game | null;
    metrics?: PlayerMetrics | null;
    canStartGame?: boolean;
    leaderboard?: LeaderboardEntry[];
};

export type GameShowProps = {
    game: Game;
    myBet: Bet | null;
    availableBalance: number;
    canManageGame: boolean;
    proposals: ArrivalProposal[];
    serverNow: string;
    closesAt: string;
};
