import { Head } from '@inertiajs/react';
import { EmptyState } from '@/components/fantadaniele/empty-state';
import { index } from '@/routes/statistics';

type ArrivalPoint = {
    gameId: number;
    label: string;
    actualMinute: number | null;
    averageBetMinute: number | null;
};
type PropertySeries = {
    id: number;
    name: string;
    avatarUrl: string;
    isCurrentUser: boolean;
    values: number[];
};

function points(values: number[], min: number, max: number): string {
    const range = max - min || 1;

    return values
        .map(
            (value, index) =>
                `${values.length === 1 ? 50 : (index / (values.length - 1)) * 100},${90 - ((value - min) / range) * 80}`,
        )
        .join(' ');
}

export default function StatisticsIndex({
    arrivalTrend,
    propertyLabels,
    propertyTrend,
}: {
    arrivalTrend: ArrivalPoint[];
    propertyLabels: string[];
    propertyTrend: PropertySeries[];
}) {
    const arrivals = arrivalTrend.filter(
        (point) =>
            point.actualMinute !== null && point.averageBetMinute !== null,
    );
    const arrivalValues = arrivals.flatMap((point) => [
        point.actualMinute ?? 0,
        point.averageBetMinute ?? 0,
    ]);
    const propertyValues = propertyTrend.flatMap((series) => series.values);
    const arrivalMin = Math.min(...arrivalValues);
    const arrivalMax = Math.max(...arrivalValues);
    const propertyMin = Math.min(...propertyValues);
    const propertyMax = Math.max(...propertyValues);

    return (
        <>
            <Head title="Statistiche" />
            <main className="mx-auto grid w-full max-w-6xl gap-8 px-4 py-5 sm:px-6 sm:py-8">
                <section>
                    <h1 className="text-xl font-semibold">
                        Arrivi e scostamenti
                    </h1>
                    {arrivals.length === 0 ? (
                        <div className="mt-4">
                            <EmptyState>
                                Servono partite con un arrivo confermato per
                                mostrare l'andamento.
                            </EmptyState>
                        </div>
                    ) : (
                        <div>
                            <svg
                                className="mt-4 h-64 w-full border"
                                role="img"
                                aria-label="Andamento degli arrivi"
                                viewBox="0 0 100 100"
                                preserveAspectRatio="none"
                            >
                                <polyline
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.5"
                                    points={points(
                                        arrivals.map(
                                            (point) => point.actualMinute ?? 0,
                                        ),
                                        arrivalMin,
                                        arrivalMax,
                                    )}
                                />
                                <polyline
                                    fill="none"
                                    stroke="hsl(var(--muted-foreground))"
                                    strokeWidth="1"
                                    strokeDasharray="3 2"
                                    points={points(
                                        arrivals.map(
                                            (point) =>
                                                point.averageBetMinute ?? 0,
                                        ),
                                        arrivalMin,
                                        arrivalMax,
                                    )}
                                />
                            </svg>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Linea piena: arrivo reale. Tratteggiata: media
                                delle puntate.{' '}
                                {arrivals
                                    .map((point) => point.label)
                                    .join(' · ')}
                            </p>
                        </div>
                    )}
                </section>
                <section>
                    <h2 className="text-xl font-semibold">
                        Proprietà nelle partite
                    </h2>
                    {propertyTrend.length === 0 ? (
                        <div className="mt-4">
                            <EmptyState>
                                Servono partite concluse per calcolare il trend
                                delle proprietà puntate.
                            </EmptyState>
                        </div>
                    ) : (
                        <div className="mt-4 border p-3">
                            <svg
                                className="h-64 w-full"
                                role="img"
                                aria-label="Andamento delle proprietà"
                                viewBox="0 0 100 100"
                                preserveAspectRatio="none"
                            >
                                <title>
                                    Andamento delle proprietà nelle partite
                                </title>
                                {propertyTrend.map((series, index) => (
                                    <g key={series.id}>
                                        <polyline
                                            fill="none"
                                            stroke={`hsl(${(index * 67) % 360} 45% 45%)`}
                                            strokeWidth={
                                                series.isCurrentUser ? 3 : 1.5
                                            }
                                            points={points(
                                                series.values,
                                                propertyMin,
                                                propertyMax,
                                            )}
                                        />
                                        <image
                                            href={series.avatarUrl}
                                            x={
                                                series.values.length === 1
                                                    ? 46
                                                    : 92
                                            }
                                            y={Math.max(
                                                0,
                                                86 -
                                                    (((series.values.at(-1) ??
                                                        0) -
                                                        propertyMin) /
                                                        (propertyMax -
                                                            propertyMin || 1)) *
                                                        80,
                                            )}
                                            width="8"
                                            height="8"
                                        />
                                    </g>
                                ))}
                            </svg>
                            <ul className="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm">
                                {propertyTrend.map((series) => (
                                    <li
                                        key={series.id}
                                        className="flex items-center gap-2"
                                    >
                                        <img
                                            src={series.avatarUrl}
                                            alt=""
                                            className="size-5 rounded-full"
                                        />
                                        {series.name}
                                        {series.isCurrentUser ? ' · Tu' : ''}
                                    </li>
                                ))}
                            </ul>
                            <p className="mt-2 text-sm text-muted-foreground">
                                {propertyLabels.join(' · ')}
                            </p>
                        </div>
                    )}
                </section>
            </main>
        </>
    );
}

StatisticsIndex.layout = () => ({
    breadcrumbs: [{ title: 'Statistiche', href: index() }],
});
