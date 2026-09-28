import type { Role, Session } from '../../shared/types/domain';

export const STAFF_ROLES: Role[] = ['admin', 'gp_leader', 'ja_director'];
export const GP_MANAGEMENT_ROLES: Role[] = ['admin', 'gp_leader'];

export function hasAnyRole(user: Session, roles: readonly Role[]) {
  return roles.some(role => user.roles.includes(role));
}

export function isStaff(user: Session) {
  return hasAnyRole(user, STAFF_ROLES);
}

export function homePathFor(user: Session) {
  if (user.must_change_password) return '/cambiar-contrasena';
  if (user.group_id && user.roles.includes('gp_leader')) return '/reuniones';
  return '/';
}
