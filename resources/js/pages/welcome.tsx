import { Head, Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { dashboard, login, register } from '@/routes';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="FantaDaniele" />
            <main className="flex min-h-svh items-center justify-center bg-muted p-6">
                <section className="w-full max-w-md space-y-8 rounded-lg border bg-background p-8">
                    <div className="flex items-center gap-3">
                        <AppLogoIcon className="size-9 fill-current" />
                        <div>
                            <h1 className="text-xl font-semibold">
                                FantaDaniele
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Pronostici, partite e classifica in un unico
                                posto.
                            </p>
                        </div>
                    </div>

                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="inline-flex w-full items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
                        >
                            Vai alla dashboard
                        </Link>
                    ) : (
                        <div className="grid gap-3 sm:grid-cols-2">
                            <Link
                                href={login()}
                                className="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium"
                            >
                                Accedi
                            </Link>
                            <Link
                                href={register()}
                                className="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
                            >
                                Registrati
                            </Link>
                        </div>
                    )}
                </section>
            </main>
        </>
    );
}
