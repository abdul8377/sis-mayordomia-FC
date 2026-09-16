export class ApiError extends Error {
  constructor(public status: number, message: string, public errors: Record<string, string[]> = {}) { super(message); }
}

function csrfToken() {
  return decodeURIComponent(document.cookie.split('; ').find(cookie => cookie.startsWith('XSRF-TOKEN='))?.split('=').slice(1).join('=') || '');
}

export async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(path, {
    credentials: 'include',
    ...options,
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': csrfToken(), ...options.headers },
  });
  if (response.status === 204) return undefined as T;
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    const messages: Record<number, string> = { 401: 'Tu sesión ha terminado. Vuelve a ingresar.', 403: 'No tienes permiso para realizar esta acción.', 419: 'La sesión ha caducado. Recarga la página.', 429: 'Demasiados intentos. Espera un momento y vuelve a intentarlo.' };
    const detail = Object.values(body.errors || {}).flat().join(' ');
    throw new ApiError(response.status, detail || messages[response.status] || body.message || 'No pudimos completar la solicitud.', body.errors);
  }
  return body as T;
}

export const api = {
  get: <T,>(path: string) => request<T>(`/api/v1${path}`),
  post: <T,>(path: string, data: unknown = {}) => request<T>(`/api/v1${path}`, { method: 'POST', body: JSON.stringify(data) }),
  put: <T,>(path: string, data: unknown) => request<T>(`/api/v1${path}`, { method: 'PUT', body: JSON.stringify(data) }),
  patch: <T,>(path: string, data: unknown) => request<T>(`/api/v1${path}`, { method: 'PATCH', body: JSON.stringify(data) }),
};
