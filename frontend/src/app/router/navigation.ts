import type { LucideIcon } from 'lucide-react';
import { CalendarDays, ChartNoAxesCombined, HeartHandshake, House, LayoutGrid, MessageCircleHeart, Sparkles, Sprout, Users, Vote } from 'lucide-react';
import type { Session } from '../../shared/types/domain';
import { hasAnyRole, GP_MANAGEMENT_ROLES, STAFF_ROLES } from './access';

export type AccessScope = 'all' | 'staff' | 'gp';

export interface NavigationItem {
  path: string;
  label: string;
  shortLabel: string;
  icon: LucideIcon;
  scope: AccessScope;
}

export const NAVIGATION: NavigationItem[] = [
  { path: '/', label: 'Vista general', shortLabel: 'Vista general', icon: LayoutGrid, scope: 'all' },
  { path: '/grupos', label: 'Grupos pequeños', shortLabel: 'Grupos pequeños', icon: House, scope: 'staff' },
  { path: '/personas', label: 'Personas', shortLabel: 'Personas', icon: Users, scope: 'staff' },
  { path: '/reuniones', label: 'Reuniones y asistencia', shortLabel: 'Reuniones', icon: CalendarDays, scope: 'gp' },
  { path: '/actividades', label: 'Ministerio Joven', shortLabel: 'Ministerio Joven', icon: Sparkles, scope: 'all' },
  { path: '/oportunidades', label: 'Oportunidades', shortLabel: 'Oportunidades', icon: HeartHandshake, scope: 'all' },
  { path: '/seguimiento', label: 'Acompañamiento', shortLabel: 'Acompañamiento', icon: MessageCircleHeart, scope: 'gp' },
  { path: '/encuestas', label: 'Encuestas', shortLabel: 'Encuestas', icon: Vote, scope: 'all' },
  { path: '/servicio', label: 'Servicio a la comunidad', shortLabel: 'Servicio', icon: Sprout, scope: 'staff' },
  { path: '/indicadores', label: 'Indicadores', shortLabel: 'Indicadores', icon: ChartNoAxesCombined, scope: 'staff' },
];

export function canViewNavigation(user: Session, item: NavigationItem) {
  if (item.scope === 'all') return true;
  if (item.scope === 'gp') return hasAnyRole(user, GP_MANAGEMENT_ROLES);
  return hasAnyRole(user, STAFF_ROLES);
}

export function currentSection(pathname: string) {
  if (pathname === '/') return NAVIGATION[0];
  return NAVIGATION.find(item => item.path !== '/' && pathname.startsWith(item.path));
}
