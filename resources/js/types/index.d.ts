import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Language {
    code: string;
    name: string;
}

export type Translations = Record<string, string>;

export interface Game {
    id: string;
    name: string;
    thumb_url: string;
    image_url: string;
    pub_year?: number;
    min_age?: number;
    min_players?: number;
    max_players?: number;
    rating?: number;
    box_time?: number;
    min_time?: number;
    max_time?: number;
    languages: Language[];
    descriptions?: Translations;
    artists: string[];
    publishers: string[];
    designers: string[];
    parent?: Game;
}

export interface Comment {
    id: number;
    created_at: string;
    updated_at: string;
    original_lang: Language;
    comment: Translations;
    writer: string;
    editor?: string;
}

export interface Question {
    id: number;
    translations: Translations;
    max_val?: number;
    min_val?: number;
    original_lang: Language;
}
