import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'FantaDaniele';
const pages = import.meta.glob<{ default: ComponentType }>([
    './pages/auth/**/*.tsx',
    './pages/games/**/*.tsx',
    './pages/leaderboard/**/*.tsx',
    './pages/settings/**/*.tsx',
    './pages/statistics/**/*.tsx',
    './pages/dashboard.tsx',
    './pages/welcome.tsx',
]);

createInertiaApp({
    resolve: async (name) => {
        const page = pages[`./pages/${name}.tsx`];

        if (page === undefined) {
            throw new Error(`Unable to resolve Inertia page [${name}].`);
        }

        return (await page()).default;
    },
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
            case name.startsWith('teams/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
