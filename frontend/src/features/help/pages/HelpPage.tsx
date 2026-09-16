import { ArrowRight, CalendarDays, CircleHelp, HeartHandshake, House, MessageCircleHeart, ShieldCheck, Sparkles, UserRound } from 'lucide-react';
import { Link } from 'react-router-dom';
import { PageHeading } from '../../../shared/ui';

const journeys = [
  { icon: House, title: 'Preparar un encuentro de GP', text: 'Abre la reunión, registra asistencia y deja una nota breve para la siguiente actividad.', link: '/reuniones', action: 'Ir a reuniones' },
  { icon: HeartHandshake, title: 'Encontrar una oportunidad', text: 'Revisa responsabilidades disponibles y conecta talentos con el próximo encuentro.', link: '/oportunidades', action: 'Ver oportunidades' },
  { icon: Sparkles, title: 'Planificar Ministerio Joven', text: 'Publica actividades, organiza responsables y registra el resultado real al finalizar.', link: '/actividades', action: 'Ir a actividades' },
  { icon: MessageCircleHeart, title: 'Acompañar una ausencia', text: 'Consulta alertas del GP y registra solo las acciones de seguimiento necesarias.', link: '/seguimiento', action: 'Ver acompañamiento' },
];

export function HelpPage() {
  return (
    <div className="page-enter help-page">
      <PageHeading eyebrow="CENTRO DE AYUDA" title="La siguiente acción debe sentirse clara" description="Atajos para los flujos principales y principios que protegen el sentido del sistema." />

      <section className="help-hero">
        <span className="help-hero-icon"><CircleHelp size={28} /></span>
        <div><p className="eyebrow">¿POR DÓNDE EMPIEZO?</p><h2>Conecta sigue el ritmo real de la comunidad.</h2><p>Primero nos encontramos. Luego identificamos oportunidades. Confirmamos compromisos, registramos lo que realmente ocurrió y usamos ese resultado para preparar el siguiente encuentro.</p></div>
        <div className="help-flow" aria-label="Flujo principal"><span><House size={16} /> GP</span><i>→</i><span><CalendarDays size={16} /> Oportunidad</span><i>→</i><span><HeartHandshake size={16} /> Compromiso</span><i>→</i><span><Sparkles size={16} /> Participación</span></div>
      </section>

      <div className="help-journey-grid">{journeys.map(({ icon: Icon, title, text, link, action }) => <article className="help-journey-card" key={title}><span><Icon size={20} /></span><h3>{title}</h3><p>{text}</p><Link to={link}>{action} <ArrowRight size={15} /></Link></article>)}</div>

      <div className="help-principles-grid">
        <section className="card help-principle"><span><ShieldCheck size={22} /></span><div><p className="eyebrow">PRIVACIDAD</p><h3>Los permisos dependen de tu rol y alcance.</h3><p>Un líder trabaja con su propio GP. Las peticiones de oración mantienen una restricción especial y no se exponen por tener permisos administrativos generales.</p></div></section>
        <section className="card help-principle"><span><UserRound size={22} /></span><div><p className="eyebrow">PERSONAS PRIMERO</p><h3>Un registro no reemplaza una conversación.</h3><p>Las alertas y recomendaciones ayudan a decidir dónde acompañar; la decisión y el contacto siguen siendo humanos.</p></div></section>
      </div>
    </div>
  );
}
