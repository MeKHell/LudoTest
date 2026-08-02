export class HttpError extends Error {
    status: number;

    constructor(status: number, message?: string) {
        super(message ?? `HTTP ${status}`);
        this.name = 'HttpError';
        this.status = status;
    }
}

/**
 * Fetch JSON and fail closed on non-2xx or invalid JSON.
 * Network/HTTP failures reject so callers can keep the page shell and show empty/error UI.
 */
export async function fetchJson<T>(
    input: RequestInfo | URL,
    init?: RequestInit,
): Promise<T> {
    const response = await fetch(input, {
        ...init,
        headers: {
            Accept: 'application/json',
            ...(init?.headers ?? {}),
        },
    });

    if (!response.ok) {
        throw new HttpError(response.status);
    }

    return (await response.json()) as T;
}
