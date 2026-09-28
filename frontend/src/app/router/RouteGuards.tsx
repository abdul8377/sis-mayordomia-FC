import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { Leaf } from 'lucide-react';
import { useAuth } from '../providers/AuthProvider';
import type { Role } from '../../shared/types/domain';
import { hasAnyRole, homePathFor } from './access';

function AppBootScreen() {
  return (
    <div className="app-boot" role="status" aria-live="polite">
      <div className="app-boot-mark"><Leaf size={28} /></div>
      <strong>Conecta</strong>
      <span>Preparando tu comunidad…</span>
      <i />
    </div>
  );
}

export function PublicOnly() {
  const { user, loading } = useAuth();
  if (loading) return <AppBootScreen />;
  if (user) return <Navigate to={homePathFor(user)} replace />;
  return <Outlet />;
}

export function RequireSession() {
  const { user, loading } = useAuth();
  const location = useLocation();
  if (loading) return <AppBootScreen />;
  if (!user) return <Navigate to="/ingresar" replace state={{ from: location.pathname }} />;
  return <Outlet />;
}

export function RequirePasswordUpdated() {
  const { user } = useAuth();
  if (user?.must_change_password) return <Navigate to="/cambiar-contrasena" replace />;
  return <Outlet />;
}

export function RequireRoles({ roles }: { roles: readonly Role[] }) {
  const { user } = useAuth();
  if (!user || !hasAnyRole(user, roles)) return <Navigate to="/" replace />;
  return <Outlet />;
}
