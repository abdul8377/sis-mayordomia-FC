import { useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight, CalendarDays, HeartHandshake } from 'lucide-react';
import { useData } from '../../../shared/hooks/useData';
import { EmptyState, ErrorState, Loading, PageHeading } from '../../../shared/ui';
import type { Activity, Opportunity } from '../../../shared/types/domain';
import { shortDate } from '../../../shared/utils/format';
import { OpportunityRow } from '../../youth-activities/pages/ActivitiesPage';
import { CommitmentForm } from '../components/CommitmentForm';
export function OpportunitiesPage() {
  const query = useData<{ data: Activity[] }>('/activities?status=published'); const [selected, setSelected] = useState<{ activity: Activity; opportunity: Opportunity } | null>(null); const activities = query.data?.data.filter(a => new Date(a.ends_at) > new Date()).sort((a, b) => +new Date(a.starts_at) - +new Date(b.starts_at));
  return <div className="page-enter"><PageHeading eyebrow="EL PUENTE GP → JA" title="Tu talento tiene un lugar" description="Pequeñas responsabilidades que hacen posibles grandes encuentros."/><div className="section-intro"><span className="intro-icon"><HeartHandshake size={27}/></span><div><h3>No tienes que hacerlo todo. Solo dar el siguiente paso.</h3><p>Elige dónde aportar o ayuda a alguien de tu grupo a encontrar su oportunidad.</p></div></div>{query.isPending ? <Loading/> : query.error ? <ErrorState error={query.error}/> : activities?.length ? activities.map(a => <section className="card opportunities-section" key={a.id}><div className="section-heading"><div><p className="eyebrow"><CalendarDays size={13}/> {shortDate(a.starts_at)} · {a.group?.name || 'Todos los grupos'}</p><h2>{a.title}</h2></div><Link to={`/actividades/${a.id}`} className="text-link">Ver encuentro <ArrowUpRight size={15}/></Link></div>{a.opportunities.map((o, i) => <OpportunityRow key={o.id} opportunity={o} index={i} available onSelect={() => setSelected({ activity: a, opportunity: o })}/>)}</section>) : <div className="card"><EmptyState title="Pronto habrá nuevas oportunidades" text="Completa tu perfil de talentos mientras preparamos el próximo encuentro." action={<Link to="/perfil" className="button button-primary">Descubrir mi perfil</Link>}/></div>}{selected && <CommitmentForm {...selected} onClose={() => setSelected(null)}/>}</div>;
}
