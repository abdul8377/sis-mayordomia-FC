import { describe, expect, it } from 'vitest';
import { hasAnyRole, homePathFor, isStaff } from '../../src/app/router/access';
import type { Session } from '../../src/shared/types/domain';

function session(overrides: Partial<Session> = {}): Session {
  return {
    id: 1,
    username: 'persona',
    roles: ['member'],
    group_id: null,
    must_change_password: false,
    person: {
      id: 1,
      full_name: 'Persona de prueba',
      phone: null,
      email: null,
      status: 'active',
      talents: [],
      interests: [],
      group: null,
    },
    ...overrides,
  };
}

describe('frontend access helpers', () => {
  it('envía al líder con GP asignado a su flujo de reuniones', () => {
    expect(homePathFor(session({ roles: ['member', 'gp_leader'], group_id: 7 }))).toBe('/reuniones');
  });

  it('prioriza el cambio obligatorio de contraseña', () => {
    expect(homePathFor(session({ roles: ['gp_leader'], group_id: 7, must_change_password: true }))).toBe('/cambiar-contrasena');
  });

  it('acumula roles sin convertir responsabilidades en permisos', () => {
    const user = session({ roles: ['member', 'ja_director'] });
    expect(isStaff(user)).toBe(true);
    expect(hasAnyRole(user, ['admin', 'ja_director'])).toBe(true);
    expect(hasAnyRole(user, ['gp_leader'])).toBe(false);
  });
});
