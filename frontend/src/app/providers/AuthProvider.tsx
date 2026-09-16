import { createContext, useContext, type ReactNode } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { api, request } from '../../shared/api/client';
import type { Session } from '../../shared/types/domain';

const AuthContext = createContext<{ user: Session | undefined; loading: boolean; login: (username: string, password: string) => Promise<void>; logout: () => Promise<void> } | null>(null);
export function AuthProvider({ children }: { children: ReactNode }) {
  const client = useQueryClient();
  const session = useQuery({ queryKey: ['session'], queryFn: () => api.get<{ data: Session }>('/me'), retry: false });
  async function login(username: string, password: string) {
    await request('/sanctum/csrf-cookie');
    await request('/login', { method: 'POST', body: JSON.stringify({ username, password }) });
    await client.invalidateQueries({ queryKey: ['session'] });
  }
  async function logout() {
    await request('/logout', { method: 'POST' });
    client.clear();
    window.location.assign('/ingresar');
  }
  return <AuthContext.Provider value={{ user: session.data?.data, loading: session.isPending, login, logout }}>{children}</AuthContext.Provider>;
}
export function useAuth() { const value = useContext(AuthContext); if (!value) throw new Error('AuthProvider missing'); return value; }
