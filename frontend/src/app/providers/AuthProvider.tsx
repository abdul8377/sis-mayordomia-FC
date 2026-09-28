import { createContext, useContext, type ReactNode } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { api, request } from '../../shared/api/client';
import type { Session } from '../../shared/types/domain';

interface PasswordChangeInput {
  currentPassword: string;
  password: string;
  passwordConfirmation: string;
}

interface AuthContextValue {
  user: Session | undefined;
  loading: boolean;
  login: (username: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  changePassword: (input: PasswordChangeInput) => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);
const SESSION_KEY = ['session'] as const;

export function AuthProvider({ children }: { children: ReactNode }) {
  const client = useQueryClient();
  const session = useQuery({
    queryKey: SESSION_KEY,
    queryFn: () => api.get<{ data: Session }>('/me'),
    retry: false,
  });

  async function login(username: string, password: string) {
    await request('/sanctum/csrf-cookie');
    const authenticated = await request<{ data: Session }>('/login', {
      method: 'POST',
      body: JSON.stringify({ username, password }),
    });
    client.setQueryData(SESSION_KEY, authenticated);
  }

  async function logout() {
    try {
      await request('/logout', { method: 'POST' });
    } finally {
      client.clear();
      window.location.assign('/ingresar');
    }
  }

  async function changePassword(input: PasswordChangeInput) {
    await api.put('/me/password', {
      current_password: input.currentPassword,
      password: input.password,
      password_confirmation: input.passwordConfirmation,
    });
    client.setQueryData<{ data: Session }>(SESSION_KEY, current => current
      ? { data: { ...current.data, must_change_password: false } }
      : current,
    );
    await client.invalidateQueries({ queryKey: SESSION_KEY });
  }

  return (
    <AuthContext.Provider value={{
      user: session.data?.data,
      loading: session.isPending,
      login,
      logout,
      changePassword,
    }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const value = useContext(AuthContext);
  if (!value) throw new Error('AuthProvider missing');
  return value;
}
