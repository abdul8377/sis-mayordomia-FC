import { ArrowRight, CalendarClock, Heart, HeartHandshake, Mail, MapPin, Phone, Sparkles, Star, UserRound } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../../app/providers/AuthProvider';
import { Avatar, Badge, PageHeading } from '../../../shared/ui';
import type { CatalogItem, Role } from '../../../shared/types/domain';

const ROLE_LABELS: Record<Role, string> = {
  admin: 'Administrador',
  gp_leader: 'Líder de Grupo Pequeño',
  ja_director: 'Director de Ministerio Joven',
  member: 'Miembro',
};

function ProfileCollection({ title, description, items, icon: Icon, empty }: { title: string; description: string; items?: CatalogItem[]; icon: typeof Sparkles; empty: string }) {
  return (
    <section className="profile-collection">
      <div className="profile-collection-heading"><span><Icon size={18} /></span><div><h3>{title}</h3><p>{description}</p></div></div>
      {items?.length ? <div className="profile-tags">{items.map(item => <span key={item.id}>{item.name}</span>)}</div> : <p className="profile-empty">{empty}</p>}
    </section>
  );
}

export function ProfilePage() {
  const { user } = useAuth();
  if (!user) return null;
  const person = user.person;

  return (
    <div className="page-enter profile-page">
      <PageHeading eyebrow="MI ESPACIO" title="Lo que eres también puede servir" description="Tu perfil ayuda a conectar tus talentos, intereses y disponibilidad con oportunidades reales." />

      <section className="profile-hero">
        <div className="profile-hero-copy">
          <span className="profile-avatar-ring"><Avatar name={person.full_name} size="lg" index={1} /></span>
          <div>
            <p className="eyebrow">TU PERFIL EN CONECTA</p>
            <h2>{person.full_name}</h2>
            <div className="profile-role-list">{user.roles.map(role => <Badge key={role} tone={role === 'admin' ? 'purple' : role === 'gp_leader' ? 'green' : 'muted'}>{ROLE_LABELS[role]}</Badge>)}</div>
          </div>
        </div>
        <div className="profile-hero-art" aria-hidden="true"><span className="profile-art-core"><HeartHandshake size={34} /></span><i /><i /><i /></div>
      </section>

      <div className="profile-grid">
        <section className="card profile-contact-card">
          <div className="section-heading"><div><p className="eyebrow">DATOS PERSONALES</p><h2>Cómo encontrarte</h2></div><UserRound size={20} /></div>
          <div className="contact-list">
            <div><span><Phone size={17} /></span><p><small>Teléfono</small><strong>{person.phone || 'Aún no registrado'}</strong></p></div>
            <div><span><Mail size={17} /></span><p><small>Correo</small><strong>{person.email || 'Aún no registrado'}</strong></p></div>
            <div><span><MapPin size={17} /></span><p><small>Grupo pequeño</small><strong>{person.group?.name || 'Sin grupo asignado'}</strong></p></div>
          </div>
        </section>

        <section className="card profile-purpose-card">
          <div className="section-heading"><div><p className="eyebrow">TU PARTICIPACIÓN</p><h2>Tu mapa de posibilidades</h2></div><Sparkles size={20} /></div>
          <ProfileCollection title="Talentos" description="Lo que ya sabes hacer." items={person.talents} icon={Star} empty="Todavía no has registrado talentos." />
          <ProfileCollection title="Intereses" description="Temas y actividades que te llaman la atención." items={person.interests} icon={Heart} empty="Todavía no has registrado intereses." />
          <ProfileCollection title="Quiero aprender" description="Habilidades que te gustaría desarrollar." items={person.learning} icon={Sparkles} empty="Aún no elegiste algo que quieras aprender." />
          <ProfileCollection title="Disponibilidad" description="Momentos en los que puedes participar." items={person.availability} icon={CalendarClock} empty="Aún no registraste tu disponibilidad." />
        </section>
      </div>

      <section className="profile-next-step">
        <span className="profile-next-icon"><HeartHandshake size={24} /></span>
        <div><p className="eyebrow">EL SIGUIENTE PASO</p><h3>Convierte tu perfil en una oportunidad para participar.</h3><p>Explora actividades publicadas y encuentra responsabilidades que conecten contigo.</p></div>
        <Link to="/oportunidades" className="button button-primary">Ver oportunidades <ArrowRight size={17} /></Link>
      </section>
    </div>
  );
}
