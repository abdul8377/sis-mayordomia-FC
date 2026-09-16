import { useMemo, useState } from 'react';
import { Link, NavLink, Outlet, useLocation } from 'react-router-dom';
import { ArrowUpRight, ChevronDown, CircleHelp, House, Leaf, LogOut, Menu, Search, Settings2, X } from 'lucide-react';
import { useAuth } from '../providers/AuthProvider';
import { Avatar } from '../../shared/ui';
import { canViewNavigation, currentSection, NAVIGATION } from '../router/navigation';
import type { Role } from '../../shared/types/domain';

const ROLE_LABELS: Record<Role, string> = {
  admin: 'Administrador',
  gp_leader: 'Líder de Grupo Pequeño',
  ja_director: 'Director de Ministerio Joven',
  member: 'Miembro',
};

const rolePriority: Role[] = ['admin', 'gp_leader', 'ja_director', 'member'];

export function AppLayout() {
  const { user, logout } = useAuth();
  const location = useLocation();
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState('');

  if (!user) return null;

  const visibleNavigation = NAVIGATION.filter(item => canViewNavigation(user, item));
  const section = currentSection(location.pathname);
  const primaryRole = rolePriority.find(role => user.roles.includes(role)) ?? 'member';
  const extraRoles = Math.max(0, user.roles.length - 1);
  const isStaff = user.roles.some(role => role !== 'member');

  const contextLabel = useMemo(() => {
    if (location.pathname.startsWith('/perfil')) return 'Mi perfil';
    if (location.pathname.startsWith('/configuracion')) return 'Configuración';
    if (location.pathname.startsWith('/ayuda')) return 'Centro de ayuda';
    return section?.shortLabel ?? 'Mi comunidad';
  }, [location.pathname, section]);

  return (
    <div className="app-shell">
      {open && <button className="sidebar-overlay" aria-label="Cerrar navegación" onClick={() => setOpen(false)} />}

      <aside className={`sidebar ${open ? 'sidebar-open' : ''}`}>
        <div className="sidebar-brand-row">
          <Link to="/" className="brand" onClick={() => setOpen(false)}>
            <span className="brand-symbol"><Leaf size={24} /></span>
            <span>conecta<span className="brand-dot">.</span></span>
          </Link>
          <button className="mobile-close icon-button" aria-label="Cerrar menú" onClick={() => setOpen(false)}><X /></button>
        </div>

        <div className="workspace-selector">
          <span className="workspace-icon"><House size={18} /></span>
          <div><strong>Iglesia local</strong><small>Comunidad en acción</small></div>
          <ChevronDown size={14} />
        </div>

        <p className="nav-label">TU COMUNIDAD</p>
        <nav className="sidebar-nav" aria-label="Navegación principal">
          {visibleNavigation.map(({ path, label, icon: Icon }) => (
            <NavLink
              key={path}
              to={path}
              end={path === '/'}
              onClick={() => setOpen(false)}
              className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
            >
              <Icon size={19} strokeWidth={1.7} />
              <span>{label}</span>
            </NavLink>
          ))}
        </nav>

        <div className="sidebar-bottom">
          <div className="sidebar-note" aria-hidden="true">
            <span className="tiny-star">✳</span>
            <p>Pequeños encuentros.<br /><strong>Grandes conexiones.</strong></p>
            <span className="note-line" />
          </div>
          {user.roles.includes('admin') && (
            <NavLink to="/configuracion" onClick={() => setOpen(false)} className="nav-link">
              <Settings2 size={18} />Configuración
            </NavLink>
          )}
          <NavLink to="/ayuda" onClick={() => setOpen(false)} className="nav-link">
            <CircleHelp size={18} />Centro de ayuda<ArrowUpRight size={14} className="push-right" />
          </NavLink>
        </div>

        <Link className="sidebar-profile" to="/perfil" onClick={() => setOpen(false)}>
          <Avatar name={user.person.full_name} index={1} />
          <div>
            <strong>{user.person.full_name}</strong>
            <small>{ROLE_LABELS[primaryRole]}{extraRoles > 0 ? ` +${extraRoles}` : ''}</small>
          </div>
          <ChevronDown size={14} />
        </Link>
      </aside>

      <div className="main-shell">
        <header className="topbar">
          <div className="topbar-start">
            <button className="icon-button mobile-menu" aria-label="Abrir menú" onClick={() => setOpen(true)}><Menu size={22} /></button>
            <span className="breadcrumb"><span>Mi comunidad</span><i>/</i><strong>{contextLabel}</strong></span>
          </div>
          <div className="topbar-actions">
            {isStaff && (
              <form className="global-search" action="/personas">
                <Search size={16} />
                <input aria-label="Buscar personas" placeholder="Buscar una persona…" name="q" value={search} onChange={event => setSearch(event.target.value)} />
                <kbd>↵</kbd>
              </form>
            )}
            <span className="topbar-divider" />
            <Link to="/perfil" className="topbar-profile" aria-label="Mi perfil">
              <Avatar name={user.person.full_name} size="sm" index={1} />
              <span><strong>{user.person.full_name.split(' ')[0]}</strong><small>{ROLE_LABELS[primaryRole]}</small></span>
            </Link>
            <button className="icon-button logout" aria-label="Cerrar sesión" title="Cerrar sesión" onClick={() => void logout()}><LogOut size={17} /></button>
          </div>
        </header>

        <main className="main-content"><Outlet /></main>
        <footer className="app-footer"><span>Conecta · Pertenecer, participar y servir.</span><span>Hecho para crecer juntos <Leaf size={12} /></span></footer>
      </div>
    </div>
  );
}
