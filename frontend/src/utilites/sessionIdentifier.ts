/**
 * Gets the session_identifier from the current URL query params.
 * Used to pass session context to the backend on iOS Safari and other
 * browsers that block cross-site cookies.
 */
export const getSessionIdentifierFromUrl = (): string | null => {
    if (typeof window === 'undefined') return null;
    return new URL(window.location.href).searchParams.get('session_identifier');
};

// Alias for backward compatibility
export const getSessionIdentifier = getSessionIdentifierFromUrl;
