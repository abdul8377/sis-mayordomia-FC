import { useQuery } from '@tanstack/react-query';
import { ArrowRight, CheckCircle2, HeartHandshake, House, Sparkles, TrendingUp, Users } from 'lucide-react';
import { Link } from 'react-router-dom';
import { getDashboard } from '../../dashboard/api/dashboard';
import { ErrorState, Loading, PageHeading } from '../../../shared/ui';
import { percent } from '../../../shared/utils/format';

export function IndicatorsPage() {
  const query = useQuery({ queryKey: ['/dashboard', 'indicators'], queryFn: getDashboard });
  if (query.isPending) return <Loading />;
  if (query.error || !query.data) return <ErrorState error={query.error} retry={() => void query.refetch()} />;

  const data = query.data;
  const comparison = data.attendance_comparison;
  const maxComparison = Math.max(comparison.gp, comparison.ja, comparison.both, comparison.active, 1);
  const cards = [
    { label: 'Personas en comunidad', value: data.stats.members, icon: Users, note: 'Personas dentro del alcance actual.' },
    { label: 'Asistencia GP', value: data.stats.gp_attendance, icon: House, note: 'Presentes en el último encuentro registrado.' },
    { label: 'Conexión GP → JA', value: percent(data.stats.connection_rate), icon: HeartHandshake, note: 'Presencia conectada entre ambos espacios.' },
    { label: 'Participación activa', value: data.stats.active_roles, icon: Sparkles, note: 'Responsabilidades realmente cumplidas.' },
  ];

  return (
    <div className="page-enter indicators-page">
      <PageHeading eyebrow="INDICADORES" title="Medir conexiones, no solo números" description="Una lectura clara del paso desde el encuentro del GP hasta la presencia y participación en Ministerio Joven." />

      <section className="indicator-hero">
        <div><span className="overline"><TrendingUp size={15} /> LECTURA DE COMUNIDAD</span><h2>{data.scope_name}</h2><p>Los indicadores ayudan a identificar dónde acompañar mejor y qué oportunidades están movilizando a las personas.</p></div>
        <div className="indicator-hero-score"><small>Conexión actual</small><strong>{percent(data.stats.connection_rate)}</strong><span><CheckCircle2 size={15} /> GP → JA</span></div>
      </section>

      <section className="indicator-kpi-grid">{cards.map(({ label, value, icon: Icon, note }) => <article className="indicator-kpi" key={label}><span><Icon size={20} /></span><p>{label}</p><strong>{value}</strong><small>{note}</small></article>)}</section>

      <div className="indicator-detail-grid">
        <section className="card indicator-comparison">
          <div className="section-heading"><div><p className="eyebrow">DEL ENCUENTRO A LA ACCIÓN</p><h2>Comparación de participación</h2></div><HeartHandshake size={20} /></div>
          {[['Presentes en GP', comparison.gp], ['Presentes en JA', comparison.ja], ['Presentes en ambos', comparison.both], ['Con responsabilidad cumplida', comparison.active]].map(([label, value]) => <div className="metric-row" key={String(label)}><div><span>{label}</span><strong>{value}</strong></div><div className="metric-track"><i style={{ width: `${Number(value) / maxComparison * 100}%` }} /></div></div>)}
        </section>

        <section className="card indicator-trend">
          <div className="section-heading"><div><p className="eyebrow">ÚLTIMOS CICLOS</p><h2>Continuidad semanal</h2></div><TrendingUp size={20} /></div>
          <div className="trend-table"><div className="trend-head"><span>Ciclo</span><span>GP</span><span>JA</span><span>Diferencia</span></div>{data.trend.map(row => <div className="trend-row" key={row.label}><strong>{row.label}</strong><span>{row.gp}</span><span>{row.ja}</span><span className={row.ja >= row.gp ? 'positive' : ''}>{row.ja - row.gp > 0 ? '+' : ''}{row.ja - row.gp}</span></div>)}</div>
          {!data.trend.length && <p className="muted">Aún no hay ciclos suficientes para construir una tendencia.</p>}
        </section>
      </div>

      <section className="indicator-footnote"><Sparkles size={20} /><div><strong>El objetivo no es perseguir un porcentaje.</strong><p>Cada dato debe servir para comprender mejor a las personas, acompañarlas y crear oportunidades reales de participación.</p></div><Link to="/oportunidades" className="text-link">Ver oportunidades <ArrowRight size={16} /></Link></section>
    </div>
  );
}
