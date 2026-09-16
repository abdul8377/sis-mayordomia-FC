import { useState } from 'react';
import { NavLink, Outlet, Link } from 'react-router-dom';
import { ArrowUpRight, CalendarDays, ChartNoAxesCombined, ChevronDown, CircleHelp, HeartHandshake, House, LayoutGrid, Leaf, LogOut, Menu, MessageCircleHeart, Search, Settings2, Sparkles, Sprout, Users, Vote, X } from 'lucide-react';
import { useAuth } from '../providers/AuthProvider';
import { Avatar } from '../../shared/ui';

const links = [
  { path: '/', label: 'Vista general', icon: LayoutGrid },
  { path: '/grupos', label: 'Grupos pequeños', icon: House, staff: true },
  { path: '/personas', label: 'Personas', icon: Users, staff: true },
  { path: '/reuniones', label: 'Reuniones y asistencia', icon: CalendarDays, gp: true },
  { path: '/actividades', label: 'Ministerio Joven', icon: Sparkles },
  { path: '/oportunidades', label: 'Oportunidades', icon: HeartHandshake },
  { path: '/seguimiento', label: 'Acompañamiento', icon: MessageCircleHeart, gp: true },
  { path: '/encuestas', label: 'Encuestas', icon: Vote },
  { path: '/servicio', label: 'Servicio a la comunidad', icon: Sprout, staff: true },
  { path: '/indicadores', label: 'Indicadores', icon: ChartNoAxesCombined, staff: true },
];

export function AppLayout() {
  const { user, logout } = useAuth(); const [open, setOpen] = useState(false); const [search, setSearch] = useState('');
  if (!user) return null;
  const staff = user.roles.some(role => role !== 'member'); const gp = user.roles.includes('admin') || user.roles.includes('gp_leader');
  const roleName = user.roles.includes('gp_leader') ? 'Líder de Grupo Pequeño' : user.roles.includes('ja_director') ? 'Director de Ministerio Joven' : user.roles.includes('admin') ? 'Administrador' : 'Miembro';
  return <div className="app-shell">
    {open && <button className="sidebar-overlay" aria-label="Cerrar navegación" onClick={() => setOpen(false)} />}
    <aside className={`sidebar ${open ? 'sidebar-open' : ''}`}>
      <Link to="/" className="brand"><span className="brand-symbol"><Leaf size={24} /></span><span>conecta<span className="brand-dot">.</span></span></Link>
      <button className="mobile-close icon-button" aria-label="Cerrar menú" onClick={() => setOpen(false)}><X /></button>
      <div className="workspace-selector"><span className="workspace-icon"><House size={18} /></span><div><strong>Iglesia local</strong><small>Comunidad en acción</small></div><ChevronDown size={14} /></div>
      <p className="nav-label">TU COMUNIDAD</p>
      <nav>{links.filter(link => (!link.staff || staff) && (!link.gp || gp)).map(({ path, label, icon: Icon }) => <NavLink key={path} to={path} end={path === '/'} onClick={() => setOpen(false)} className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}><Icon size={19} strokeWidth={1.7} /><span>{label}</span></NavLink>)}</nav>
      <div className="sidebar-bottom"><div className="sidebar-note"><span className="tiny-star">✳</span><p>Pequeños encuentros.<br /><strong>Grandes conexiones.</strong></p><span className="note-line" /></div>{user.roles.includes('admin') && <NavLink to="/configuracion" className="nav-link"><Settings2 size={18} />Configuración</NavLink>}<Link to="/ayuda" className="nav-link"><CircleHelp size={18} />Centro de ayuda<ArrowUpRight size={14} className="push-right" /></Link></div>
      <Link className="sidebar-profile" to="/perfil"><Avatar name={user.person.full_name} index={1} /><div><strong>{user.person.full_name}</strong><small>{roleName}</small></div><ChevronDown size={14} /></Link>
    </aside>
    <div className="main-shell"><header className="topbar"><div className="topbar-start"><button className="icon-button mobile-menu" aria-label="Abrir menú" onClick={() => setOpen(true)}><Menu size={22} /></button><span className="breadcrumb">Mi comunidad <span>/</span> <strong>Un lugar para cada talento</strong></span></div><div className="topbar-actions">{staff && <form className="global-search" action="/personas"><Search size={16} /><input aria-label="Buscar personas" placeholder="Buscar una persona…" name="q" value={search} onChange={event => setSearch(event.target.value)} /><kbd>↵</kbd></form>}<span className="topbar-divider" /><Link to="/perfil" aria-label="Mi perfil"><Avatar name={user.person.full_name} size="sm" index={1} /></Link><button className="icon-button logout" aria-label="Cerrar sesión" title="Cerrar sesión" onClick={() => { void logout(); }}><LogOut size={17} /></button></div></header><main className="main-content"><Outlet /></main><footer className="app-footer"><span>Conecta · Pertenecer, participar y servir.</span><span>Hecho para crecer juntos <Leaf size={12} /></span></footer></div>
  </div>;
}
