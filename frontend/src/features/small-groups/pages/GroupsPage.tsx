import { useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, CalendarDays, House, MapPin, Plus, Settings2, Users } from 'lucide-react';
import { useAuth } from '../../../app/providers/AuthProvider';
import { useData, useSave } from '../../../shared/hooks/useData';
import {
  Avatar,
  Badge,
  Button,
  EmptyState,
  ErrorState,
  Field,
  Loading,
  Modal,
  Notice,
  PageHeading,
} from '../../../shared/ui';
import type { Group } from '../../../shared/types/domain';

const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

export function GroupsPage() {
  const { user } = useAuth();
  const query = useData<{ data: Group[] }>('/groups');
  const [editing, setEditing] = useState<Group | 'new' | null>(null);
  const admin = user?.roles.includes('admin');

  return (
    <div className="page-enter">
      <PageHeading
        eyebrow="PERTENECER"
        title="Pequeños grupos. Grandes lazos."
        description="Un espacio cercano para compartir la vida y crecer juntos."
        action={admin && (
          <Button onClick={() => setEditing('new')}>
            <Plus size={17} />
            Crear grupo
          </Button>
        )}
      />

      <div className="section-intro">
        <span className="intro-icon"><House size={27} /></span>
        <div>
          <h3>El viernes es solo el comienzo</h3>
          <p>Cada grupo tiene su propia historia y un propósito que nos conecta a todos.</p>
        </div>
        <span className="subtle-chip">{query.data?.data.length || 0} grupos</span>
      </div>

      {query.isPending ? (
        <Loading />
      ) : query.error ? (
        <ErrorState error={query.error} />
      ) : (
        <div className="groups-grid">
          {query.data?.data.map((group, index) => (
            <article className="group-card card" key={group.id}>
              <div className={`group-card-cover ${group.color}`}>
                <House size={38} strokeWidth={1.1} />
                <span className="group-cover-art" />
                <Badge tone={group.status === 'active' ? 'green' : 'muted'}>
                  {group.status === 'active' ? 'Activo' : 'Inactivo'}
                </Badge>
              </div>

              <div className="group-card-body">
                <div className="split-line">
                  <h2>{group.name}</h2>
                  {(admin || user?.group_id === group.id) && (
                    <button
                      className="icon-button"
                      aria-label={`Editar ${group.name}`}
                      onClick={() => setEditing(group)}
                    >
                      <Settings2 size={17} />
                    </button>
                  )}
                </div>

                <p className="group-description">
                  {group.description || 'Un espacio para encontrarnos y compartir.'}
                </p>

                <div className="group-facts">
                  <span>
                    <CalendarDays size={15} />
                    {days[group.usual_weekday]} · {group.usual_time.slice(0, 5)}
                  </span>
                  <span>
                    <MapPin size={15} />
                    {group.meeting_place || 'Lugar por confirmar'}
                  </span>
                </div>

                <div className="group-card-leader">
                  <Avatar name={group.leader?.full_name || group.name} size="sm" index={index} />
                  <span>
                    <small>LÍDER DEL GRUPO</small>
                    <strong>{group.leader?.full_name || 'Por asignar'}</strong>
                  </span>
                  <span className="member-count"><Users size={14} />{group.members_count}</span>
                </div>

                <Link className="button button-secondary full-width" to={`/personas?group=${group.id}`}>
                  Conocer el grupo <ArrowRight size={16} />
                </Link>
              </div>
            </article>
          ))}

          {!query.data?.data.length && (
            <EmptyState
              title="Tu comunidad está por crecer"
              text="Crea un grupo y comienza a conectar personas."
            />
          )}
        </div>
      )}

      {editing && (
        <GroupForm
          group={editing === 'new' ? undefined : editing}
          onClose={() => setEditing(null)}
        />
      )}
    </div>
  );
}

function GroupForm({ group, onClose }: { group?: Group; onClose: () => void }) {
  const { user } = useAuth();
  const admin = user?.roles.includes('admin');
  const accounts = useData<{ data: { id: number; full_name: string; roles: string[] }[] }>(
    admin ? '/accounts' : '/me',
  );
  const save = useSave(group ? `/groups/${group.id}` : '/groups', group ? 'put' : 'post');

  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const values = Object.fromEntries(new FormData(event.currentTarget));
    const data: Record<string, unknown> = {
      ...values,
      usual_weekday: Number(values.usual_weekday),
    };

    if (values.leader_user_id === '') {
      delete data.leader_user_id;
    } else if (values.leader_user_id) {
      data.leader_user_id = Number(values.leader_user_id);
    }

    save.mutate(data, { onSuccess: onClose });
  }

  return (
    <Modal
      title={group ? `Editar ${group.name}` : 'Un nuevo lugar para pertenecer'}
      onClose={onClose}
    >
      <form onSubmit={submit}>
        {save.error && <Notice error>{save.error.message}</Notice>}

        <Field label="Nombre del grupo">
          <input name="name" defaultValue={group?.name} required maxLength={150} />
        </Field>

        <Field label="Descripción">
          <textarea name="description" defaultValue={group?.description || ''} rows={2} />
        </Field>

        <Field label="Lugar de reunión">
          <input name="meeting_place" defaultValue={group?.meeting_place || ''} />
        </Field>

        <div className="form-grid">
          <Field label="Día habitual">
            <select name="usual_weekday" defaultValue={group?.usual_weekday ?? 5}>
              {days.map((day, index) => (
                <option key={day} value={index}>{day}</option>
              ))}
            </select>
          </Field>

          <Field label="Hora">
            <input
              name="usual_time"
              type="time"
              defaultValue={group?.usual_time.slice(0, 5) || '19:00'}
              required
            />
          </Field>
        </div>

        {admin && (
          <Field
            label="Asignar líder"
            hint="Solo se muestran cuentas con rol de líder. Deja esta opción vacía para conservar la asignación actual."
          >
            <select name="leader_user_id">
              <option value="">{group?.leader?.full_name || 'Asignar más adelante'}</option>
              {Array.isArray(accounts.data?.data)
                && accounts.data.data
                  .filter(account => account.roles.includes('gp_leader'))
                  .map(account => (
                    <option value={account.id} key={account.id}>{account.full_name}</option>
                  ))}
            </select>
          </Field>
        )}

        <Field label="Estado">
          <select name="status" defaultValue={group?.status || 'active'}>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
          </select>
        </Field>

        <div className="modal-actions">
          <Button type="button" variant="secondary" onClick={onClose}>Cancelar</Button>
          <Button type="submit" busy={save.isPending}>Guardar grupo</Button>
        </div>
      </form>
    </Modal>
  );
}
