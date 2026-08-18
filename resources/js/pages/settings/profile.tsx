import { Form, Head, usePage } from '@inertiajs/react';
import { Dices, ExternalLink } from 'lucide-react';
import { useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { show as showAvatarPreview } from '@/routes/avatars/previews';
import { edit } from '@/routes/profile';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
    avatarSeeds: string[];
};

const DICEBEAR_OPEN_PEEPS_EDITOR_URL =
    'https://www.dicebear.com/playground?style=open-peeps';

const escapeRegularExpression = (value: string): string =>
    value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

const randomAvatarSeed = (
    avatarSeeds: string[],
    currentSeed: string,
): string => {
    if (avatarSeeds.length === 0) {
        throw new Error('No approved Open Peeps avatar seeds are available.');
    }

    const alternativeSeeds = avatarSeeds.filter(
        (avatarSeed) => avatarSeed !== currentSeed,
    );
    const availableSeeds =
        alternativeSeeds.length === 0 ? avatarSeeds : alternativeSeeds;
    const randomIndex = Math.floor(Math.random() * availableSeeds.length);

    return availableSeeds[randomIndex];
};

export default function Profile() {
    const { auth, avatarSeeds } = usePage<PageProps>().props;
    const [avatarSeed, setAvatarSeed] = useState(auth.user.avatar_seed);
    const avatarSeedPattern = avatarSeeds
        .map(escapeRegularExpression)
        .join('|');
    const maximumAvatarSeedLength = Math.max(
        ...avatarSeeds.map((seed) => seed.length),
    );
    const hasValidAvatarSeed = avatarSeeds.includes(avatarSeed);

    return (
        <>
            <Head title="Profilo" />

            <h1 className="sr-only">Profilo</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Profilo"
                    description="Aggiorna nome, email e avatar"
                />

                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nome</Label>

                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="Nome completo"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>

                                <Input
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="Indirizzo email"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            <fieldset className="grid gap-4 rounded-md border-2 bg-card p-4 shadow-sm">
                                <legend className="px-1 text-sm font-semibold">
                                    Avatar Open Peeps
                                </legend>

                                <div className="flex items-center gap-4">
                                    <div className="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-md border-2 bg-muted">
                                        {hasValidAvatarSeed ? (
                                            <img
                                                className="size-full object-cover"
                                                src={showAvatarPreview.url(
                                                    avatarSeed,
                                                )}
                                                alt={`Anteprima avatar generato dal seed ${avatarSeed}`}
                                                data-test="avatar-preview"
                                            />
                                        ) : (
                                            <span className="px-2 text-center text-xs font-medium text-muted-foreground">
                                                Seed non valido
                                            </span>
                                        )}
                                    </div>

                                    <div className="grid min-w-0 flex-1 gap-2">
                                        <Label htmlFor="avatar_seed">
                                            Seed DiceBear
                                        </Label>
                                        <Input
                                            id="avatar_seed"
                                            name="avatar_seed"
                                            value={avatarSeed}
                                            onChange={(event) =>
                                                setAvatarSeed(
                                                    event.currentTarget.value,
                                                )
                                            }
                                            required
                                            maxLength={maximumAvatarSeedLength}
                                            pattern={avatarSeedPattern}
                                            autoCapitalize="none"
                                            autoCorrect="off"
                                            spellCheck={false}
                                            aria-describedby="avatar-seed-help"
                                            data-test="avatar-seed-input"
                                        />
                                        <p
                                            id="avatar-seed-help"
                                            className="text-xs text-muted-foreground"
                                        >
                                            Incolla soltanto un seed supportato:
                                            link, parametri e stili diversi
                                            vengono rifiutati.
                                        </p>
                                    </div>
                                </div>

                                <div className="grid gap-2 sm:grid-cols-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() =>
                                            setAvatarSeed(
                                                randomAvatarSeed(
                                                    avatarSeeds,
                                                    avatarSeed,
                                                ),
                                            )
                                        }
                                        data-test="randomize-avatar-button"
                                    >
                                        <Dices />
                                        Genera casuale
                                    </Button>

                                    <Button variant="secondary" asChild>
                                        <a
                                            href={
                                                DICEBEAR_OPEN_PEEPS_EDITOR_URL
                                            }
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            data-test="dicebear-editor-link"
                                        >
                                            <ExternalLink />
                                            Apri editor DiceBear
                                        </a>
                                    </Button>
                                </div>

                                <InputError message={errors.avatar_seed} />
                            </fieldset>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Salva
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Profilo',
            href: edit(),
        },
    ],
};
